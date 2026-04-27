<?php

namespace App\Services\Hour;

use App\Models\Absence;
use App\Models\Employee;
use App\Models\LeaveAndAbsence;
use App\Models\Vacation;
use Carbon\Carbon;

class DeductHourBankService
{
    private const DAILY_WORK_HOURS = 480;

    private function checkForLeaveOrVacation(int $employeeId, Carbon $date): array
    {
        $leave = LeaveAndAbsence::where('employee_id', $employeeId)
            ->whereDate('start_date', '<=', $date->toDateString())
            ->whereDate('end_date', '>=', $date->toDateString())
            ->where('status', 'approved')
            ->first();

        if ($leave) {
            return [
                'has_leave' => true,
                'type' => 'leave',
                'leave_type' => $leave->type,
                'is_paid' => $leave->is_paid,
            ];
        }

        $vacation = Vacation::where('employee_id', $employeeId)
            ->whereDate('start_date', '<=', $date->toDateString())
            ->whereDate('end_date', '>=', $date->toDateString())
            ->where('status', 'approved')
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

    private function getDailyWorkMinutes(int $employeeId, ?Carbon $date = null): int
    {
        $employee = Employee::findOrFail($employeeId);

        $query = $employee->contracts()
            ->where('status', 'active');

        if ($date) {
            $query = $query->whereDate('start_date', '<=', $date->toDateString())
                ->where(function ($query) use ($date) {
                    $query->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $date->toDateString());
                });
        }

        $contract = $query->orderByDesc('start_date')->first();

        return $contract?->daily_work_minutes ?? self::DAILY_WORK_HOURS;
    }

    public function handle(
        int $employeeId,
        Carbon $absenceDate,
        ?int $hoursToDeduct = null,
        string $deductionType = 'unjustified_absence',
        ?string $reason = null,
        bool $forceDeduction = false
    ): ?Absence {
        if ($hoursToDeduct === null) {
            $hoursToDeduct = $this->getDailyWorkMinutes($employeeId, $absenceDate);
        }

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

        return Absence::create([
            'employee_id' => $employeeId,
            'absence_date' => $absenceDate,
            'hours_deducted' => $hoursToDeduct,
            'deduction_type' => $deductionType,
            'reason' => $reason,
        ]);
    }

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
