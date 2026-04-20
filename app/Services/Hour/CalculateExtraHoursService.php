<?php

namespace App\Services\Hour;

use App\Models\AttendanceLog;
use Illuminate\Support\Carbon;

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
        // Obtém a jornada diária do contrato ativo do funcionário
        $dailyWorkMinutes = $attendanceLog->employee->contracts()
            ->where('status', 'active')
            ->where('start_date', '<=', $attendanceLog->time_in)
            ->orderByDesc('start_date')
            ->first()?->daily_work_minutes ?? 480;

        // Se ao menos um dos tempos está vazio, não há hora extra para calcular
        if (! $attendanceLog->total_minutes || $attendanceLog->total_minutes <= $dailyWorkMinutes) {
            return 0;
        }

        // Calcular horas extras
        return $attendanceLog->total_minutes - $dailyWorkMinutes;
    }
}
