<?php

namespace App\Observers;

use App\Models\Absence;
use App\Models\AttendanceLog;
use App\Models\HourBank;
use App\Models\LeaveAndAbsence;
use App\Models\Vacation;
use App\Services\Hour\CalculateExtraHoursService;
use App\Services\Hour\DeductHourBankService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceLogObserver
{
    /**
     * Detecta quando um funcionário falta (AttendanceLog sem entrada/saída)
     * e automaticamente desconta horas do banco de horas
     */
    public function created(AttendanceLog $attendanceLog): void
    {
        // Primeiro, processa ausências (falta completa)
        $this->processAbsence($attendanceLog);
        
        // Depois, calcula e registra horas extras se aplicável
        $this->recalculateExtraHours($attendanceLog);
    }

    /**
     * Também processa se o AttendanceLog é atualizado para marcar uma falta
     * OU se foi corrigido (recalcula horas extras)
     */
    public function updated(AttendanceLog $attendanceLog): void
    {
        // Se os tempos foram alterados, precisa recalcular TUDO
        if ($attendanceLog->isDirty(['time_in', 'time_out'])) {
            // 1. Limpar o saldo anterior deste dia (restaurar)
            $this->cleanupPreviousCalculations($attendanceLog->getOriginal());
            
            // 2. Processar nova situação
            $this->processAbsence($attendanceLog);
            $this->recalculateExtraHours($attendanceLog);
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
            Log::error('Erro ao descontar horas de falta', [
                'employee_id' => $attendanceLog->employee_id,
                'attendance_log_id' => $attendanceLog->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Recalcula horas extras para um AttendanceLog
     * 
     * Quando um ponto é registado com horas acima da jornada diária,
     * adiciona ao banco de horas as horas extras ganhas
     */
    private function recalculateExtraHours(AttendanceLog $attendanceLog): void
    {
        try {
            // Ignorar se não tem horas registadas
            if (! $attendanceLog->total_minutes || $attendanceLog->total_minutes <= 0) {
                return;
            }

            $service = new CalculateExtraHoursService;
            $extraMinutes = $service->handle($attendanceLog);

            if ($extraMinutes > 0) {
                Log::info('Horas extras calculadas', [
                    'employee_id' => $attendanceLog->employee_id,
                    'attendance_log_id' => $attendanceLog->id,
                    'extra_minutes' => $extraMinutes,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao calcular horas extras', [
                'employee_id' => $attendanceLog->employee_id,
                'attendance_log_id' => $attendanceLog->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Limpa/restaura cálculos anteriores quando um AttendanceLog é corrigido
     * 
     * Remove as horas extras que foram adicionadas e as deduções que foram aplicadas
     * para recalcular com os novos valores
     */
    private function cleanupPreviousCalculations(array $originalAttributes): void
    {
        // Se o tempo_in original é nulo, não há cálculos anteriores para limpar
        if (! isset($originalAttributes['time_in']) || ! $originalAttributes['time_in']) {
            return;
        }

        $employeeId = $originalAttributes['employee_id'];
        $monthYear = Carbon::parse($originalAttributes['time_in'])->format('Y-m');

        // Se tinha horas extras registadas, remover
        $originalMinutes = $originalAttributes['total_minutes'] ?? 0;
        if ($originalMinutes > 0) {
            $dailyWorkMinutes = 480; // Padrão 8h
            if ($originalMinutes > $dailyWorkMinutes) {
                $extraMinutes = $originalMinutes - $dailyWorkMinutes;
                
                $hourBank = HourBank::where('employee_id', $employeeId)
                    ->where('month_year', $monthYear)
                    ->first();

                if ($hourBank) {
                    $hourBank->extra_hours_added -= $extraMinutes;
                    $hourBank->balance -= $extraMinutes;
                    $hourBank->save();

                    Log::info('Horas extras removidas (limpeza)', [
                        'employee_id' => $employeeId,
                        'month_year' => $monthYear,
                        'removed_minutes' => $extraMinutes,
                    ]);
                }
            }
        }

        // Se tinha uma deduação registada, remover a Absence
        $absenceDate = Carbon::parse($originalAttributes['time_in'])->toDateString();
        $absence = Absence::where('employee_id', $employeeId)
            ->where('absence_date', $absenceDate)
            ->first();

        if ($absence) {
            // Restaurar horas ao banco (inverter a deduação)
            $hourBank = HourBank::where('employee_id', $employeeId)
                ->where('month_year', $monthYear)
                ->first();

            if ($hourBank) {
                $hourBank->extra_hours_used -= $absence->hours_deducted;
                $hourBank->balance += $absence->hours_deducted;
                $hourBank->save();
            }

            // Deletar o registo de absence
            $absence->delete();

            Log::info('Deduação de falta removida (limpeza)', [
                'employee_id' => $employeeId,
                'absence_date' => $absenceDate,
                'hours_restored' => $absence->hours_deducted,
            ]);
        }
    }
}
