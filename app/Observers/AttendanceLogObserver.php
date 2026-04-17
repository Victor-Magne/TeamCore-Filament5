<?php

namespace App\Observers;

use App\Models\Absence;
use App\Models\AttendanceLog;
use App\Models\LeaveAndAbsence;
use App\Models\Vacation;
use App\Services\Hour\DeductHourBankService;
use Carbon\Carbon;

class AttendanceLogObserver
{
    /**
     * Detecta quando um funcionário falta (AttendanceLog sem entrada/saída)
     * e automaticamente desconta horas do banco de horas
     */
    public function created(AttendanceLog $attendanceLog): void
    {
        $this->processAbsence($attendanceLog);
    }

    /**
     * Também processa se o AttendanceLog é atualizado para marcar uma falta
     */
    public function updated(AttendanceLog $attendanceLog): void
    {
        // Apenas processar se passou de "com horas" para "sem horas" (falta)
        if ($attendanceLog->isDirty(['time_in', 'time_out'])) {
            $this->processAbsence($attendanceLog);
        }
    }

    /**
     * Processa uma ausência detectada num AttendanceLog
     *
     * Uma falta é detectada quando:
     * 1. O time_out é nulo (não terminou o expediente / falta completa)
     * 2. E o total_minutes é nulo ou zero (sem horas registadas)
     *
     * @param  AttendanceLog  $attendanceLog  O registo de presença
     */
    private function processAbsence(AttendanceLog $attendanceLog): void
    {
        // Ignorar se tem horas registadas (não é falta)
        if ($attendanceLog->total_minutes && $attendanceLog->total_minutes > 0) {
            return;
        }

        // Ignorar se tem time_out (fim do expediente registado)
        if ($attendanceLog->time_out) {
            return;
        }

        // Ignorar se já existe um registo de Absence para esta data/funcionário
        $existingAbsence = Absence::where('employee_id', $attendanceLog->employee_id)
            ->where('absence_date', $attendanceLog->time_in->toDateString())
            ->exists();

        if ($existingAbsence) {
            return;
        }

        // Obter a data da ausência
        $absenceDate = $attendanceLog->time_in->toDateString();

        // Verificar se é feriado, fim de semana ou tem licença/férias
        if ($this->shouldSkipDeduction($attendanceLog->employee_id, Carbon::parse($absenceDate))) {
            return;
        }

        // Descontar do banco de horas
        $this->deductHours($attendanceLog, $absenceDate);
    }

    /**
     * Verifica se deve-se pular a deduação de horas
     *
     * Casos:
     * - Fim de semana (sábado/domingo)
     * - Existe LeaveAndAbsence para esta data
     * - Existe Vacation aprovada para esta data
     *
     * @param  int  $employeeId  ID do funcionário
     * @param  Carbon  $date  Data da ausência
     * @return bool True se deve pular a deduação
     */
    private function shouldSkipDeduction(int $employeeId, Carbon $date): bool
    {
        // Ignorar fins de semana
        if ($date->isWeekend()) {
            return true;
        }

        // Verificar se existe licença registada para esta data
        $leave = LeaveAndAbsence::where('employee_id', $employeeId)
            ->where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())
            ->first();

        if ($leave) {
            // Se é uma licença justificada ou não justificada, não descontar
            // (a licença já cobre a ausência)
            return true;
        }

        // Verificar se existe férias aprovadas para esta data
        $vacation = Vacation::where('employee_id', $employeeId)
            ->where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())
            ->where('status', 'approved')
            ->first();

        if ($vacation) {
            return true;
        }

        return false;
    }

    /**
     * Executa a deduação de horas para a ausência detectada
     *
     * @param  AttendanceLog  $attendanceLog  O registo de presença
     * @param  string  $absenceDate  Data da ausência em formato string
     */
    private function deductHours(AttendanceLog $attendanceLog, string $absenceDate): void
    {
        try {
            $service = new DeductHourBankService;

            // Desconta 1 dia completo (8 horas = 480 minutos)
            $absence = $service->handle(
                employeeId: $attendanceLog->employee_id,
                absenceDate: Carbon::parse($absenceDate),
                hoursToDeduct: 480, // 8 horas
                deductionType: 'unjustified_absence',
                reason: sprintf(
                    'Falta automática detectada via ponto (AttendanceLog #%d)',
                    $attendanceLog->id
                )
            );

            // Se foi registada a deduação, guardar a referência
            if ($absence) {
                $attendanceLog->update([
                    'metadata->absence_id' => $absence->id,
                    'metadata->absence_deducted' => true,
                ]);
            }
        } catch (\Exception $e) {
            // Log the error but don't fail the AttendanceLog creation
            \Log::error('Erro ao descontar horas de falta', [
                'employee_id' => $attendanceLog->employee_id,
                'attendance_log_id' => $attendanceLog->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
