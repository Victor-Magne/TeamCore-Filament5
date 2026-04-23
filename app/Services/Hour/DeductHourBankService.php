<?php

namespace App\Services\Hour;

use App\Models\Absence;
use App\Models\Employee;
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
     * Obtém as horas diárias de trabalho do funcionário (do contrato ativo)
     * Se nenhum contrato ativo existir, retorna o padrão de 480 minutos (8h)
     */
    private function getDailyWorkMinutes(int $employeeId, ?Carbon $date = null): int
    {
        $employee = Employee::findOrFail($employeeId);

        $query = $employee->contracts()
            ->where('status', 'active');

        if ($date) {
            $query = $query->where('start_date', '<=', $date->toDateString());
        }

        $contract = $query->orderByDesc('start_date')->first();

        return $contract?->daily_work_minutes ?? self::DAILY_WORK_HOURS;
    }

    /**
     * Desconta horas criando um registo de Absence
     * (O recálculo do HourBank é tratado pelo AbsenceObserver)
     * 
     * Se hoursToDeduct não for especificado, usa o daily_work_minutes do contrato ativo
     */
    public function handle(
        int $employeeId,
        Carbon $absenceDate,
        ?int $hoursToDeduct = null,
        string $deductionType = 'unjustified_absence',
        ?string $reason = null,
        bool $forceDeduction = false
    ): ?Absence {
        // Se não foi especificado quantas horas descontar, usar o contrato ativo
        if ($hoursToDeduct === null) {
            $hoursToDeduct = $this->getDailyWorkMinutes($employeeId, $absenceDate);
        }

        // Verificar se validação de licenças está ativada
        $validateLeaves = config('hour_bank.validate_leaves_before_deduction', true);

        if ($validateLeaves && ! $forceDeduction) {
            $leaveCheck = $this->checkForLeaveOrVacation($employeeId, $absenceDate);

            if ($leaveCheck['has_leave']) {
                if ($leaveCheck['type'] === 'leave' && $this->isJustifiedLeave($leaveCheck['leave_type'])) {
                    return null;
                }

                if ($leaveCheck['type'] === 'vacation') {
                    return null;
                }
            }
        }

        // Registar a ausência. O AbsenceObserver tratará de atualizar o HourBank.
        return Absence::create([
            'employee_id' => $employeeId,
            'absence_date' => $absenceDate,
            'hours_deducted' => $hoursToDeduct,
            'deduction_type' => $deductionType,
            'reason' => $reason,
        ]);
    }
    /**
     * Desconta horas de um período inteiro (múltiplos dias)
     * Usa daily_work_minutes do contrato para cada dia
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
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            if ($currentDate->isWeekday()) {
                // Usar o daily_work_minutes do contrato para esta data
                $dailyMinutes = $this->getDailyWorkMinutes($employeeId, $currentDate->copy());

                $absence = $this->handle(
                    $employeeId,
                    $currentDate->copy(),
                    $dailyMinutes,
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
}
