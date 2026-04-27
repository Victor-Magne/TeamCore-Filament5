<?php

namespace App\Services\Hour;

use App\Models\AttendanceLog;

class CalculateExtraHoursService
{
    /**
     * Calcula horas extras de um AttendanceLog (apenas cálculo, sem persistência no banco de horas)
     *
     * @param  AttendanceLog  $attendanceLog  O registo de ponto
     * @return int Minutos de horas extras (0 se nenhum)
     */
    public function handle(AttendanceLog $attendanceLog): int
    {
        // Obtém o contrato ativo na data do ponto
        $contract = $attendanceLog->employee->contracts()
            ->where('status', 'active')
            ->where('start_date', '<=', $attendanceLog->time_in)
            ->orderByDesc('start_date')
            ->first();

        $dailyWorkMinutes = $contract?->daily_work_minutes ?? 480;

        // Se trabalhou menos que a jornada, não há horas extras
        if (! $attendanceLog->total_minutes || $attendanceLog->total_minutes <= $dailyWorkMinutes) {
            return 0;
        }

        // Calcular horas extras
        return $attendanceLog->total_minutes - $dailyWorkMinutes;
    }

    /**
     * Calcula o défice de minutos de um AttendanceLog
     *
     * @return int Minutos de défice (positivo) ou 0
     */
    public function calculateDeficit(AttendanceLog $attendanceLog): int
    {
        $contract = $attendanceLog->employee->contracts()
            ->where('status', 'active')
            ->where('start_date', '<=', $attendanceLog->time_in)
            ->orderByDesc('start_date')
            ->first();

        $dailyWorkMinutes = $contract?->daily_work_minutes ?? 480;

        if (! $attendanceLog->total_minutes || $attendanceLog->total_minutes >= $dailyWorkMinutes) {
            return 0;
        }

        return $dailyWorkMinutes - $attendanceLog->total_minutes;
    }
}
