<?php

namespace App\Services\Hour;

use App\Models\Absence;
use App\Models\AttendanceLog;
use App\Models\HourBank;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HourBankService
{
    /**
     * Recalcula o banco de horas de um funcionário para um mês específico
     */
    protected array $calculating = [];

    public function recalculate(int $employeeId, string $monthYear): void
    {
        $key = "{$employeeId}-{$monthYear}";
        if (isset($this->calculating[$key])) {
            return;
        }
        $this->calculating[$key] = true;

        try {
            $this->performRecalculate($employeeId, $monthYear);
        } finally {
            unset($this->calculating[$key]);
        }
    }

    protected function performRecalculate(int $employeeId, string $monthYear): void
    {
        DB::transaction(function () use ($employeeId, $monthYear) {
            $month = Carbon::createFromFormat('Y-m', $monthYear);
            $startDate = $month->copy()->startOfMonth();
            $endDate = $month->copy()->endOfMonth();

            $employee = \App\Models\Employee::find($employeeId);
            if (!$employee) return;

            // 1. Calcular Horas Extras Ganhas e Débitos (via AttendanceLog)
            $extraMinutesAdded = 0;
            $extraMinutesUsedFromLogs = 0;

            $logs = AttendanceLog::where('employee_id', $employeeId)
                ->whereBetween('time_in', [$startDate, $endDate])
                ->orderBy('time_in')
                ->get();

            // Carregar todas as absences do mês (excluindo os atrasos automáticos que vamos gerar aqui se necessário)
            $absenceDates = Absence::where('employee_id', $employeeId)
                ->whereBetween('absence_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->where('type', 'falta')
                ->pluck('absence_date')
                ->map(fn ($date) => $date->format('Y-m-d'))
                ->toArray();

            foreach ($logs as $log) {
                $logDate = $log->time_in->format('Y-m-d');
                if (in_array($logDate, $absenceDates)) {
                    continue;
                }

                $contract = $log->employee->contracts()
                    ->where('status', 'active')
                    ->where('start_date', '<=', $log->time_in)
                    ->orderByDesc('start_date')
                    ->first();

                $dailyWorkMinutes = $contract?->daily_work_minutes ?? 480;
                $expectedStartTimeStr = $contract?->expected_start_time ?? '09:00:00';
                $expectedLunchMinutes = $contract?->lunch_duration_minutes ?? 60;

                $expectedStartTime = Carbon::parse($logDate . ' ' . $expectedStartTimeStr);

                $delayPenaltyMinutes = 0;
                $hasSignificantDelay = false;

                // Verificação de atraso na entrada (Tolerância 15min)
                $entryDiff = $expectedStartTime->diffInMinutes($log->time_in, false);
                if ($entryDiff > 15) {
                    // Penalização é o DOBRO. Como o log já vai reportar o atraso simples
                    // (via diferença de total_minutes), adicionamos apenas 1x o atraso
                    // extra para totalizar 2x no banco de horas.
                    $delayPenaltyMinutes += $entryDiff;
                    $hasSignificantDelay = true;
                }

                // Verificação de atraso no almoço (Tolerância 10min)
                if ($log->lunch_break_start && $log->lunch_break_end) {
                    $actualLunchMinutes = $log->lunch_break_start->diffInMinutes($log->lunch_break_end);
                    $lunchDiff = $actualLunchMinutes - $expectedLunchMinutes;
                    if ($lunchDiff > 10) {
                        $delayPenaltyMinutes += $lunchDiff;
                        $hasSignificantDelay = true;
                    }
                }

                // Gestão do contador de atrasos e criação de registos de Absence
                if ($hasSignificantDelay) {
                    $this->handleDelay($employee, $log, $delayPenaltyMinutes);
                }

                if ($log->total_minutes) {
                    $diff = $log->total_minutes - $dailyWorkMinutes;
                    if ($diff > 0) {
                        $extraMinutesAdded += $diff;
                    } else {
                        $extraMinutesUsedFromLogs += abs($diff);
                    }
                }
            }

            // 2. Calcular Horas Perdidas (via Absence)
            // Somamos apenas as absences que estão aprovadas ou pendentes (não rejeitadas)
            $extraMinutesUsed = Absence::where('employee_id', $employeeId)
                ->whereBetween('absence_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->where('status', '!=', 'rejeitado')
                ->sum('hours_deducted');

            $extraMinutesUsed += $extraMinutesUsedFromLogs;

            // 3. Obter o saldo anterior
            $previousBalance = $this->getPreviousBalance($employeeId, $monthYear);

            // 4. Atualizar ou Criar o registo do HourBank
            $hourBank = HourBank::withTrashed()->where('employee_id', $employeeId)
                ->where('month_year', $monthYear)
                ->first();

            $attributes = [
                'previous_balance' => $previousBalance,
                'extra_hours_added' => $extraMinutesAdded,
                'extra_hours_used' => $extraMinutesUsed,
                'balance' => $previousBalance + $extraMinutesAdded - $extraMinutesUsed,
            ];

            if ($hourBank) {
                if ($hourBank->trashed()) {
                    $hourBank->restore();
                }

                $hourBank->fill($attributes)->save();
            } else {
                $hourBank = HourBank::create([
                    'employee_id' => $employeeId,
                    'month_year' => $monthYear,
                    ...$attributes,
                ]);
            }

            // 5. Propagar a alteração para os meses seguintes
            $this->propagate($employeeId, $monthYear);
        });
    }

    /**
     * Propaga o saldo final de um mês para o saldo inicial do mês seguinte
     */
    public function propagate(int $employeeId, string $monthYear): void
    {
        $currentMonth = Carbon::createFromFormat('Y-m', $monthYear);
        $nextMonth = $currentMonth->copy()->addMonth();
        $nextMonthYear = $nextMonth->format('Y-m');

        // Procurar o registo do mês seguinte
        $nextHourBank = HourBank::where('employee_id', $employeeId)
            ->where('month_year', $nextMonthYear)
            ->first();

        // Se não existir, paramos a propagação (ou poderíamos criar se fosse necessário)
        // No entanto, o sistema cria HourBanks conforme necessário via observers
        if (! $nextHourBank) {
            return;
        }

        // Obter o saldo final do mês atual (que será o inicial do próximo)
        $currentBalance = HourBank::where('employee_id', $employeeId)
            ->where('month_year', $monthYear)
            ->value('balance') ?? 0;

        // Atualizar o próximo mês
        $nextHourBank->previous_balance = $currentBalance;
        $nextHourBank->balance = $currentBalance + $nextHourBank->extra_hours_added - $nextHourBank->extra_hours_used;
        $nextHourBank->save();

        // Recursividade para continuar a propagação
        $this->propagate($employeeId, $nextMonthYear);
    }

    /**
     * Gere a lógica de atraso: incrementa contador e cria Absence se necessário.
     * Agora de forma idempotente.
     */
    protected function handleDelay(\App\Models\Employee $employee, AttendanceLog $log, int $penaltyMinutes): void
    {
        $logDate = $log->time_in->toDateString();

        // 1. Garantir o registo do atraso (atraso extra/penalização)
        $delayAbsence = Absence::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'attendance_log_id' => $log->id,
                'type' => 'atraso',
            ],
            [
                'absence_date' => $logDate,
                'hours_deducted' => $penaltyMinutes,
                'deduction_type' => 'partial_absence',
                'status' => 'pendente',
                'reason' => 'Penalização por atraso detectada automaticamente (1x extra para totalizar 2x).',
            ]
        );

        // 2. Calcular atrasos acumulados para ver se gera falta
        // Contamos atrasos que NÃO estão vinculados a uma falta 'parent'
        $delaysCount = Absence::where('employee_id', $employee->id)
            ->where('type', 'atraso')
            ->whereNull('parent_absence_id')
            ->orderBy('absence_date')
            ->count();

        if ($delaysCount >= 3) {
            $contract = $employee->activeContract;
            $fullDayMinutes = $contract?->daily_work_minutes ?? 480;

            // Criar a falta
            $faltaAbsence = Absence::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'absence_date' => $logDate,
                    'type' => 'falta',
                    'reason' => 'Falta automática gerada após 3 atrasos acumulados.',
                ],
                [
                    'hours_deducted' => $fullDayMinutes,
                    'deduction_type' => 'unjustified_absence',
                    'status' => 'pendente',
                ]
            );

            // Vincular os 3 atrasos mais antigos sem parent a esta falta para "consumi-los"
            Absence::where('employee_id', $employee->id)
                ->where('type', 'atraso')
                ->whereNull('parent_absence_id')
                ->orderBy('absence_date')
                ->limit(3)
                ->update(['parent_absence_id' => $faltaAbsence->id]);
        }
    }

    /**
     * Obtém o saldo do mês anterior (procura o registo mais recente antes do mês atual)
     */
    private function getPreviousBalance(int $employeeId, string $currentMonthYear): int
    {
        return HourBank::where('employee_id', $employeeId)
            ->where('month_year', '<', $currentMonthYear)
            ->orderByDesc('month_year')
            ->value('balance') ?? 0;
    }
}
