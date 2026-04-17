<?php

namespace App\Observers;

use App\Models\Absence;
use App\Models\AttendanceLog;
use App\Models\LeaveAndAbsence;
use App\Models\Vacation;
use App\Services\Hour\DeductHourBankService;
use App\Services\Hour\HourBankService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceLogObserver
{
    protected HourBankService $hourBankService;

    public function __construct(HourBankService $hourBankService)
    {
        $this->hourBankService = $hourBankService;
    }

    /**
     * Detecta quando um funcionário falta (AttendanceLog sem entrada/saída)
     * e automaticamente desconta horas do banco de horas
     */
    public function created(AttendanceLog $attendanceLog): void
    {
        // Primeiro, processa ausências (falta completa)
        $this->processAbsence($attendanceLog);
        
        // Recalcular banco de horas para este mês
        $this->hourBankService->recalculate(
            $attendanceLog->employee_id,
            $attendanceLog->time_in->format('Y-m')
        );
    }

    /**
     * Também processa se o AttendanceLog é atualizado para marcar uma falta
     * OU se foi corrigido (recalcula banco de horas)
     */
    public function updated(AttendanceLog $attendanceLog): void
    {
        // Se os tempos foram alterados, precisa gerir a Absence e recalcular
        if ($attendanceLog->isDirty(['time_in', 'time_out', 'total_minutes'])) {
            
            // Se mudou o mês, recalcular o mês original
            if ($attendanceLog->isDirty('time_in')) {
                $originalTimeIn = $attendanceLog->getOriginal('time_in');
                if ($originalTimeIn) {
                    $this->hourBankService->recalculate(
                        $attendanceLog->employee_id,
                        Carbon::parse($originalTimeIn)->format('Y-m')
                    );
                }
            }

            // Se agora é uma falta, processar
            $this->processAbsence($attendanceLog);

            // Se deixou de ser falta (corrigido), remover a Absence associada
            if ($attendanceLog->total_minutes > 0 || $attendanceLog->time_out) {
                $this->removeAbsenceForDate($attendanceLog->employee_id, $attendanceLog->time_in->toDateString());
            }

            // Recalcular o mês atual
            $this->hourBankService->recalculate(
                $attendanceLog->employee_id,
                $attendanceLog->time_in->format('Y-m')
            );
        }
    }

    /**
     * Se o registo de ponto for apagado, recalcular o banco de horas
     */
    public function deleted(AttendanceLog $attendanceLog): void
    {
        $this->hourBankService->recalculate(
            $attendanceLog->employee_id,
            $attendanceLog->time_in->format('Y-m')
        );
    }

    /**
     * Processa uma ausência detectada num AttendanceLog
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

        // Descontar do banco de horas (via Absence model, que disparará o AbsenceObserver)
        Log::info('Tentando criar falta automática', ['employee_id' => $attendanceLog->employee_id, 'date' => $absenceDate]);
        $this->deductHours($attendanceLog, $absenceDate);
    }

    private function removeAbsenceForDate(int $employeeId, string $date): void
    {
        Absence::where('employee_id', $employeeId)
            ->where('absence_date', $date)
            ->delete();
    }

    /**
     * Verifica se deve-se pular a deduação de horas
     */
    private function shouldSkipDeduction(int $employeeId, Carbon $date): bool
    {
        if ($date->isWeekend()) {
            return true;
        }

        $leave = LeaveAndAbsence::where('employee_id', $employeeId)
            ->where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())
            ->first();

        if ($leave) {
            return true;
        }

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
     */
    private function deductHours(AttendanceLog $attendanceLog, string $absenceDate): void
    {
        try {
            // Criar o registo de falta diretamente. O AbsenceObserver tratará de disparar o recálculo do banco de horas.
            Absence::create([
                'employee_id' => $attendanceLog->employee_id,
                'absence_date' => $absenceDate,
                'hours_deducted' => 480, // 8 horas padrão
                'deduction_type' => 'unjustified_absence',
                'reason' => sprintf(
                    'Falta automática detectada via ponto (AttendanceLog #%d)',
                    $attendanceLog->id
                )
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao registar falta automática', [
                'employee_id' => $attendanceLog->employee_id,
                'attendance_log_id' => $attendanceLog->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
