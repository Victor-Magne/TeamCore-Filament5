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

            // 1. Calcular Horas Extras Ganhas (via AttendanceLog)
            // ⚠️ IMPORTANTE: Se houver uma Absence (falta integral) no mesmo dia,
            //    ignorar o AttendanceLog para evitar dupla penalização
            $extraMinutesAdded = 0;
            $extraMinutesUsedFromLogs = 0;
            $logs = AttendanceLog::where('employee_id', $employeeId)
                ->whereBetween('time_in', [$startDate, $endDate])
                ->get();

            // Carregar todas as absences do mês para verificação rápida
            $absenceDates = Absence::where('employee_id', $employeeId)
                ->whereBetween('absence_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->pluck('absence_date')
                ->map(fn ($date) => $date->format('Y-m-d'))
                ->toArray();

            foreach ($logs as $log) {
                // Se há uma Absence para este dia, ignorar o AttendanceLog
                // (a Absence já contabiliza a penalização)
                if (in_array($log->time_in->format('Y-m-d'), $absenceDates)) {
                    continue;
                }

                $dailyWorkMinutes = $log->employee->contracts()
                    ->where('status', 'active')
                    ->where('start_date', '<=', $log->time_in)
                    ->orderByDesc('start_date')
                    ->first()?->daily_work_minutes ?? 480;

                if ($log->total_minutes) {
                    $diff = $log->total_minutes - $dailyWorkMinutes;
                    if ($diff > 0) {
                        $extraMinutesAdded += $diff;
                    } else {
                        // Se trabalhou menos que o esperado, conta como horas usadas (débito)
                        $extraMinutesUsedFromLogs += abs($diff);
                    }
                }
            }

            // 2. Calcular Horas Perdidas (via Absence)
            $extraMinutesUsed = Absence::where('employee_id', $employeeId)
                ->whereBetween('absence_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->sum('hours_deducted');

            $extraMinutesUsed += $extraMinutesUsedFromLogs;

            // 3. Obter o saldo anterior
            $previousBalance = $this->getPreviousBalance($employeeId, $monthYear);

            // 4. Atualizar ou Criar o registo do HourBank
            $hourBank = HourBank::where('employee_id', $employeeId)
                ->where('month_year', $monthYear)
                ->first();

            $hourBank = HourBank::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'month_year' => $monthYear,
                ],
                [
                    'previous_balance' => $previousBalance,
                    'extra_hours_added' => $extraMinutesAdded,
                    'extra_hours_used' => $extraMinutesUsed,
                    'balance' => $previousBalance + $extraMinutesAdded - $extraMinutesUsed,
                ]
            );

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
