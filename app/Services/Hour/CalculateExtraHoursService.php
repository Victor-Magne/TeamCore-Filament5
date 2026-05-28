<?php

/**
 * Ficheiro do Serviço CalculateExtraHoursService.
 *
 * Este serviço contém a lógica matemática para determinar se um registo
 * de presença (AttendanceLog) resultou em horas extraordinárias ou num défice
 * de tempo, comparando o tempo total trabalhado com a jornada diária contratual.
 */

namespace App\Services\Hour;

use App\Models\AttendanceLog;

class CalculateExtraHoursService
{
    /**
     * Calcula as horas extras de um AttendanceLog.
     *
     * @param  AttendanceLog  $attendanceLog  O registo de ponto.
     * @return int Minutos de horas extras (0 se não existirem).
     */
    public function handle(AttendanceLog $attendanceLog): int
    {
        // Obtém o contrato activo na data do ponto para saber a carga horária esperada
        $contract = $attendanceLog->employee->contracts()
            ->where('status', 'active')
            ->where('start_date', '<=', $attendanceLog->time_in)
            ->orderByDesc('start_date')
            ->first();

        $dailyWorkMinutes = $contract?->daily_work_minutes ?? config('hr.default_daily_work_minutes');

        // Se trabalhou menos ou o mesmo que a jornada, não há horas extras
        if (! $attendanceLog->total_minutes || $attendanceLog->total_minutes <= $dailyWorkMinutes) {
            return 0;
        }

        // Retorna o excesso de minutos trabalhados
        return $attendanceLog->total_minutes - $dailyWorkMinutes;
    }

    /**
     * Calcula o défice de minutos de um AttendanceLog (tempo não trabalhado).
     *
     * @param  AttendanceLog  $attendanceLog  O registo de ponto.
     * @return int Minutos de défice (positivo se existir tempo em falta) ou 0.
     */
    public function calculateDeficit(AttendanceLog $attendanceLog): int
    {
        $contract = $attendanceLog->employee->contracts()
            ->where('status', 'active')
            ->where('start_date', '<=', $attendanceLog->time_in)
            ->orderByDesc('start_date')
            ->first();

        $dailyWorkMinutes = $contract?->daily_work_minutes ?? config('hr.default_daily_work_minutes');

        // Se trabalhou mais ou o mesmo que a jornada, não há défice
        if (! $attendanceLog->total_minutes || $attendanceLog->total_minutes >= $dailyWorkMinutes) {
            return 0;
        }

        // Retorna a diferença em falta
        return $dailyWorkMinutes - $attendanceLog->total_minutes;
    }
}
