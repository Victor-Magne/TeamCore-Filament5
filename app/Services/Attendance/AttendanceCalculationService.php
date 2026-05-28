<?php

namespace App\Services\Attendance;

use App\Models\AttendanceLog;

class AttendanceCalculationService
{
    /**
     * Calcula o total de minutos trabalhados deduzindo o almoço.
     * Se o almoço registado for inferior ao contratual, usa o contratual.
     */
    public function calculateTotalMinutes(AttendanceLog $log): ?int
    {
        if (! $log->time_in || ! $log->time_out) {
            return null;
        }

        $totalMinutes = $log->time_in->diffInMinutes($log->time_out);

        $contract = $log->employee->contracts()
            ->where('status', 'active')
            ->where('start_date', '<=', $log->time_in)
            ->orderByDesc('start_date')
            ->first();

        $expectedLunchMinutes = $contract?->lunch_duration_minutes ?? config('hr.default_lunch_minutes', 60);

        if ($log->lunch_break_start && $log->lunch_break_end) {
            $actualLunchMinutes = $log->lunch_break_start->diffInMinutes($log->lunch_break_end);
            $lunchToDeduct = max($actualLunchMinutes, $expectedLunchMinutes);
            $totalMinutes -= $lunchToDeduct;
        } else {
            $totalMinutes -= $expectedLunchMinutes;
        }

        return max(0, $totalMinutes);
    }
}
