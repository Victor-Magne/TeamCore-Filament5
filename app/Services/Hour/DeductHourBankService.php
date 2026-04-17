<?php

namespace App\Services\Hour;

use App\Models\Absence;
use App\Models\HourBank;
use App\Models\LeaveAndAbsence;
use App\Models\Vacation;
use Carbon\Carbon;

class DeductHourBankService
{
    /**
     * Jornada diária padrão em minutos (8 horas)
     */
    private const DAILY_WORK_HOURS = 480; // 8 * 60

    /**
     * Verifica se há uma leave/absence ou férias registadas para uma data
     *
     * @param  int  $employeeId  ID do funcionário
     * @param  Carbon  $date  Data para verificação
     * @return array{has_leave: bool, type: string|null, reason: string|null}
     */
    private function checkForLeaveOrVacation(int $employeeId, Carbon $date): array
    {
        // Buscar leave/absence para esta data
        $leave = LeaveAndAbsence::where('employee_id', $employeeId)
            ->where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())
            ->first();

        if ($leave) {
            return [
                'has_leave' => true,
                'type' => 'leave',
                'leave_type' => $leave->type,
                'is_paid' => $leave->is_paid,
            ];
        }

        // Buscar vacation para esta data
        $vacation = Vacation::where('employee_id', $employeeId)
            ->where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())
            ->where('status', 'approved') // Apenas férias aprovadas
            ->first();

        if ($vacation) {
            return [
                'has_leave' => true,
                'type' => 'vacation',
                'vacation_status' => $vacation->status,
            ];
        }

        return ['has_leave' => false];
    }

    /**
     * Verifica se o tipo de licença justifica a deduação
     *
     * @param  string  $leaveType  Tipo de licença
     * @return bool True se é uma licença justificada
     */
    private function isJustifiedLeave(string $leaveType): bool
    {
        $justifiedTypes = config('hour_bank.justified_leave_types', [
            'sick_leave',
            'parental',
            'marriage',
            'bereavement',
            'justified_absence',
        ]);

        return in_array($leaveType, $justifiedTypes);
    }

    /**
     * Desconta horas do banco quando um funcionário falta
     *
     * @param  int  $employeeId  ID do funcionário
     * @param  Carbon  $absenceDate  Data da ausência
     * @param  int  $hoursToDeduct  Horas a descontar em minutos (padrão = 1 dia completo)
     * @param  string  $deductionType  Tipo de deduação: 'unjustified_absence', 'partial_absence', 'other'
     * @param  string|null  $reason  Motivo da deduação
     * @param  bool  $forceDeduction  Forçar desconto mesmo que haja leave/vacation
     * @return Absence|null O registo de ausência criado ou null se não decontou
     *
     * @throws \Exception Ao tentar descontar dia com leave justificada
     */
    public function handle(
        int $employeeId,
        Carbon $absenceDate,
        int $hoursToDeduct = self::DAILY_WORK_HOURS,
        string $deductionType = 'unjustified_absence',
        ?string $reason = null,
        bool $forceDeduction = false
    ): ?Absence {
        // Verificar se validação de licenças está ativada
        $validateLeaves = config('hour_bank.validate_leaves_before_deduction', true);

        if ($validateLeaves && ! $forceDeduction) {
            $leaveCheck = $this->checkForLeaveOrVacation($employeeId, $absenceDate);

            if ($leaveCheck['has_leave']) {
                // Verificar se é falta injustificada ou licença justificada
                if ($leaveCheck['type'] === 'leave' && $this->isJustifiedLeave($leaveCheck['leave_type'])) {
                    // Não descontar - é uma licença justificada (retorna antes de descontar)
                    return null;
                }

                if ($leaveCheck['type'] === 'vacation') {
                    // Não descontar - é férias aprovadas (retorna antes de descontar)
                    return null;
                }
            }
        }

        // Obter o mês/ano da ausência
        $monthYear = $absenceDate->format('Y-m');

        // Buscar ou criar o registo no banco de horas para este mês
        $hourBank = HourBank::firstOrCreate(
            [
                'employee_id' => $employeeId,
                'month_year' => $monthYear,
            ],
            [
                'balance' => $this->getPreviousBalance($employeeId, $monthYear),
                'extra_hours_added' => 0,
                'extra_hours_used' => 0,
                'previous_balance' => $this->getPreviousBalance($employeeId, $monthYear),
            ]
        );

        // Descontar do banco de horas
        $hourBank->extra_hours_used += $hoursToDeduct;
        $hourBank->balance -= $hoursToDeduct;
        $hourBank->save();

        // Registar a ausência
        $absence = Absence::create([
            'employee_id' => $employeeId,
            'absence_date' => $absenceDate,
            'hours_deducted' => $hoursToDeduct,
            'deduction_type' => $deductionType,
            'reason' => $reason,
        ]);

        return $absence;
    }

    /**
     * Desconta horas de um período inteiro (múltiplos dias)
     *
     * @param  int  $employeeId  ID do funcionário
     * @param  Carbon  $startDate  Data inicial
     * @param  Carbon  $endDate  Data final
     * @param  string  $deductionType  Tipo de deduação
     * @param  string|null  $reason  Motivo da deduação
     * @param  bool  $forceDeduction  Forçar desconto mesmo que haja leaves/vacations
     * @return array Array de registos Absence criados
     */
    public function handlePeriod(
        int $employeeId,
        Carbon $startDate,
        Carbon $endDate,
        string $deductionType = 'unjustified_absence',
        ?string $reason = null,
        bool $forceDeduction = false
    ): array {
        $absences = [];

        // Iterar por cada dia no período
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            // Descontar apenas se for dia útil (segunda a sexta)
            if ($currentDate->isWeekday()) {
                $absence = $this->handle(
                    $employeeId,
                    $currentDate->copy(),
                    self::DAILY_WORK_HOURS,
                    $deductionType,
                    $reason,
                    $forceDeduction
                );

                if ($absence) {
                    $absences[] = $absence;
                }
            }
            $currentDate->addDay();
        }

        return $absences;
    }

    /**
     * Obtém o saldo anterior (do mês anterior)
     *
     * @param  int  $employeeId  ID do funcionário
     * @param  string  $currentMonthYear  Mês/ano atual (YYYY-MM)
     * @return int Saldo em minutos
     */
    private function getPreviousBalance(int $employeeId, string $currentMonthYear): int
    {
        // Calcular o mês anterior
        $currentMonth = Carbon::createFromFormat('Y-m', $currentMonthYear);
        $previousMonthYear = $currentMonth->copy()->subMonth()->format('Y-m');

        // Buscar o saldo do mês anterior
        $previousHourBank = HourBank::where('employee_id', $employeeId)
            ->where('month_year', $previousMonthYear)
            ->first();

        return $previousHourBank?->balance ?? 0;
    }
}
