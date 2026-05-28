<?php

namespace App\Services\Hour;

use App\Models\Absence;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\LeaveAndAbsence;
use App\Models\Vacation;
use Carbon\Carbon;

class DeductHourBankService
{
    public function processAttendance(AttendanceLog $attendanceLog): void
    {
        $employeeId = $attendanceLog->employee_id;
        $date = Carbon::parse($attendanceLog->time_in);

        if ($this->shouldSkipDeduction($employeeId, $date)) {
            $this->removeAbsenceForDate($employeeId, $date->toDateString());

            return;
        }

        $contract = $attendanceLog->employee->contracts()
            ->where('status', 'active')
            ->whereNotNull('expected_start_time')
            ->where('start_date', '<=', $date)
            ->orderByDesc('start_date')
            ->first();

        $expectedStartTime = $contract?->expected_start_time ?? '09:00:00';
        $dailyWorkMinutes = (int) ($contract?->daily_work_minutes ?? config('hr.default_daily_work_minutes'));
        $lunchDurationMinutes = (int) ($contract?->lunch_duration_minutes ?? config('hr.default_lunch_minutes'));

        $toleranceMinutes = config('hr.delay_tolerance_minutes');
        $fullAbsenceThreshold = config('hr.full_absence_threshold_minutes');

        $expectedStart = Carbon::parse($date->toDateString().' '.$expectedStartTime);
        $actualStart = Carbon::parse($attendanceLog->time_in);

        $delayMinutes = $expectedStart->diffInMinutes($actualStart, false);

        if ($delayMinutes > $toleranceMinutes) {
            if ($delayMinutes <= $fullAbsenceThreshold) {
                $this->createOrUpdateAbsence(
                    $employeeId,
                    $date,
                    $delayMinutes,
                    'partial_absence',
                    sprintf('Atraso de %d minutos (Entrada: %s, Esperada: %s)', $delayMinutes, $actualStart->format('H:i'), $expectedStart->format('H:i'))
                );

                $this->checkConsecutiveDelays($employeeId);
            } else {
                $this->createOrUpdateAbsence(
                    $employeeId,
                    $date,
                    $dailyWorkMinutes,
                    'unjustified_absence',
                    sprintf('Falta por atraso superior a %dmin (%d min)', $fullAbsenceThreshold, $delayMinutes)
                );

                return;
            }
        } else {
            $this->removeAbsenceForDate($employeeId, $date->toDateString());
        }

        if ($attendanceLog->time_out) {
            $expectedEnd = $expectedStart->copy()->addMinutes($dailyWorkMinutes + $lunchDurationMinutes);
            $actualEnd = Carbon::parse($attendanceLog->time_out);

            $earlyDepartureMinutes = $actualEnd->diffInMinutes($expectedEnd, false);

            if ($earlyDepartureMinutes > $toleranceMinutes) {
                $existingAbsence = Absence::where('employee_id', $employeeId)
                    ->whereDate('absence_date', $date)
                    ->first();

                $totalMinutesToDeduct = max(0, $earlyDepartureMinutes) + ($existingAbsence?->hours_deducted ?? 0);

                if ($totalMinutesToDeduct > $fullAbsenceThreshold) {
                    $this->createOrUpdateAbsence(
                        $employeeId,
                        $date,
                        $dailyWorkMinutes,
                        'unjustified_absence',
                        sprintf('Falta por saída antecipada/atraso acumulado > %dmin (%d min)', $fullAbsenceThreshold, $totalMinutesToDeduct)
                    );
                } else {
                    $this->createOrUpdateAbsence(
                        $employeeId,
                        $date,
                        $totalMinutesToDeduct,
                        'partial_absence',
                        sprintf('Atraso/Saída antecipada acumulada: %d min (Saída: %s, Esperada: %s)', $totalMinutesToDeduct, $actualEnd->format('H:i'), $expectedEnd->format('H:i'))
                    );
                }
            }
        }
    }

    public function registerFullAbsence(int $employeeId, Carbon $date, string $reason = 'Falta injustificada'): void
    {
        if ($this->shouldSkipDeduction($employeeId, $date)) {
            return;
        }

        $contract = Employee::find($employeeId)?->contracts()
            ->where('status', 'active')
            ->where('start_date', '<=', $date)
            ->orderByDesc('start_date')
            ->first();

        $minutes = $contract?->daily_work_minutes ?? config('hr.default_daily_work_minutes');

        $this->createOrUpdateAbsence($employeeId, $date, $minutes, 'unjustified_absence', $reason);
    }

    private function createOrUpdateAbsence(int $employeeId, Carbon $date, int $minutes, string $type, string $reason): void
    {
        $absence = Absence::where('employee_id', $employeeId)
            ->whereDate('absence_date', $date)
            ->first();

        $data = [
            'employee_id' => $employeeId,
            'absence_date' => $date->toDateString(),
            'hours_deducted' => $minutes,
            'deduction_type' => $type,
            'reason' => $reason,
        ];

        if ($absence) {
            $absence->update($data);
        } else {
            Absence::create($data);
        }
    }

    public function removeAbsenceForDate(int $employeeId, string $date): void
    {
        $absences = Absence::where('employee_id', $employeeId)
            ->whereDate('absence_date', $date)
            ->whereNull('leave_and_absence_id')
            ->get();

        foreach ($absences as $absence) {
            $absence->delete();
        }
    }

    public function shouldSkipDeduction(int $employeeId, Carbon $date): bool
    {
        if ($date->isWeekend()) {
            return true;
        }

        $dateStr = $date->toDateString();

        $leaveExists = LeaveAndAbsence::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $dateStr)
            ->whereDate('end_date', '>=', $dateStr)
            ->exists();

        if ($leaveExists) {
            return true;
        }

        return Vacation::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('start_date', '<=', $dateStr)
            ->whereDate('end_date', '>=', $dateStr)
            ->exists();
    }

    private function checkConsecutiveDelays(int $employeeId): void
    {
        $limit = config('hr.consecutive_delays_limit');

        $lastAbsences = Absence::where('employee_id', $employeeId)
            ->where('deduction_type', 'partial_absence')
            ->orderByDesc('absence_date')
            ->take($limit)
            ->get();

        if ($lastAbsences->count() < $limit) {
            return;
        }

        $dates = $lastAbsences->pluck('absence_date')->map(fn ($d) => Carbon::parse($d));

        $isConsecutive = true;
        for ($i = 0; $i < $limit - 1; $i++) {
            $current = $dates[$i];
            $prev = $dates[$i + 1];

            $diff = abs($current->diffInDaysFiltered(fn (Carbon $date) => $date->isWeekday(), $prev));

            if ($diff != 1) {
                $isConsecutive = false;
                break;
            }
        }

        if ($isConsecutive) {
            $contract = Employee::find($employeeId)->contracts()->where('status', 'active')->first();
            $fullDayMinutes = $contract?->daily_work_minutes ?? config('hr.default_daily_work_minutes');

            $latest = $lastAbsences->first();
            $latest->update([
                'hours_deducted' => $fullDayMinutes,
                'deduction_type' => 'unjustified_absence',
                'reason' => $latest->reason.' (Convertido para falta por '.$limit.' atrasos consecutivos)',
            ]);

            foreach ($lastAbsences->slice(1) as $oldAbsence) {
                $oldAbsence->delete();
            }
        }
    }
}
