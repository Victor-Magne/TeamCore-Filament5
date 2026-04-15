<?php

namespace App\Services\Hour;

use App\Models\AttendanceLog;
use App\Models\HourBank;
use Illuminate\Support\Carbon;

class CalculateExtraHoursService
{
    /**
     * Jornada diária padrão em minutos (8 horas)
     */
    private const DAILY_WORK_HOURS = 480; // 8 * 60

    /**
     * Calcula e registra horas extras de um AttendanceLog
     *
     * @param  AttendanceLog  $attendanceLog  O registo de ponto
     * @return int Minutos de horas extras adicionados (0 se nenhum)
     */
    public function handle(AttendanceLog $attendanceLog): int
    {
        // Se ao menos um dos tempos está vazio, não há hora extra para calcular
        if (! $attendanceLog->total_minutes || $attendanceLog->total_minutes <= self::DAILY_WORK_HOURS) {
            return 0;
        }

        // Calcular horas extras (tudo acima de 8 horas)
        $extraMinutes = $attendanceLog->total_minutes - self::DAILY_WORK_HOURS;

        // Obter o mês/ano do dia trabalhado
        $monthYear = $attendanceLog->time_in->format('Y-m');

        // Buscar ou criar o registo no banco de horas para este mês
        $hourBank = HourBank::firstOrCreate(
            [
                'employee_id' => $attendanceLog->employee_id,
                'month_year' => $monthYear,
            ],
            [
                'balance' => 0,
                'extra_hours_added' => 0,
                'extra_hours_used' => 0,
                'previous_balance' => $this->getPreviousBalance($attendanceLog->employee_id, $monthYear),
            ]
        );

        // Atualizar o banco de horas
        $hourBank->extra_hours_added += $extraMinutes;
        $hourBank->balance += $extraMinutes;
        $hourBank->save();

        return $extraMinutes;
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
        $previousMonthYear = $currentMonth->subMonth()->format('Y-m');

        // Buscar o saldo do mês anterior
        $previousHourBank = HourBank::where('employee_id', $employeeId)
            ->where('month_year', $previousMonthYear)
            ->first();

        return $previousHourBank?->balance ?? 0;
    }
}
