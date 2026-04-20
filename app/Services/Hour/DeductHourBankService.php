<?php

namespace App\Services\Hour;

use App\Models\Absence;
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
     * Desconta horas criando um registo de Absence
     * (O recálculo do HourBank é tratado pelo AbsenceObserver)
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
}
