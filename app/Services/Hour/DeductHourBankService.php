<?php

/**
 * Ficheiro do Serviço DeductHourBankService.
 *
 * Este serviço centraliza a lógica de punição por assiduidade.
 * Gere a identificação de atrasos na entrada, saídas antecipadas e faltas injustificadas.
 * Implementa também regras de tolerância (15 min) e a regra de conversão
 * de 3 atrasos consecutivos numa falta total de um dia.
 */

namespace App\Services\Hour;

use App\Models\Absence;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\LeaveAndAbsence;
use App\Models\Vacation;
use Carbon\Carbon;

class DeductHourBankService
{
    /**
     * Processa um registo de presença para verificar se houve incumprimento de horário.
     */
    public function processAttendance(AttendanceLog $attendanceLog): void
    {
        $employeeId = $attendanceLog->employee_id;
        $date = Carbon::parse($attendanceLog->time_in);

        // 1. Verifica se deve ignorar a verificação (ex: Férias ou Fim de Semana)
        if ($this->shouldSkipDeduction($employeeId, $date)) {
            // Se o funcionário trabalhou num dia que deveria estar de folga,
            // removemos qualquer falta automática que tenha sido gerada por erro.
            $this->removeAbsenceForDate($employeeId, $date->toDateString());

            return;
        }

        // 2. Obter o contrato para saber o horário esperado
        $contract = $attendanceLog->employee->contracts()
            ->where('status', 'active')
            ->whereNotNull('expected_start_time')
            ->where('start_date', '<=', $date)
            ->orderByDesc('start_date')
            ->first();

        $expectedStartTime = $contract?->expected_start_time ?? '09:00:00';
        $dailyWorkMinutes = (int) ($contract?->daily_work_minutes ?? 480);
        $lunchDurationMinutes = (int) ($contract?->lunch_duration_minutes ?? 60);

        $expectedStart = Carbon::parse($date->toDateString().' '.$expectedStartTime);
        $actualStart = Carbon::parse($attendanceLog->time_in);

        // Calcula a diferença em minutos (positivo se chegou atrasado)
        $delayMinutes = $expectedStart->diffInMinutes($actualStart, false);

        if ($delayMinutes > 15) { // Tolerância de 15 minutos
            if ($delayMinutes <= 60) {
                // Atraso Parcial (até 1 hora): desconta o tempo exacto de atraso
                $this->createOrUpdateAbsence(
                    $employeeId,
                    $date,
                    $delayMinutes,
                    'partial_absence',
                    sprintf('Atraso de %d minutos (Entrada: %s, Esperada: %s)', $delayMinutes, $actualStart->format('H:i'), $expectedStart->format('H:i'))
                );

                // Verifica se este é o 3º atraso consecutivo para converter em falta
                $this->checkConsecutiveDelays($employeeId);
            } else {
                // Atraso Superior a 1 hora: Considerado falta injustificada de um dia inteiro
                $this->createOrUpdateAbsence(
                    $employeeId,
                    $date,
                    $dailyWorkMinutes,
                    'unjustified_absence',
                    sprintf('Falta por atraso superior a 1h (%d min)', $delayMinutes)
                );

                return;
            }
        } else {
            // Se chegou dentro da tolerância, removemos registos de atraso prévios para este dia
            $this->removeAbsenceForDate($employeeId, $date->toDateString());
        }

        // 3. Verificar Saída Antecipada (se a saída já tiver sido registada)
        if ($attendanceLog->time_out) {
            // Hora esperada de saída = Início esperado + Horas Trabalho + Almoço
            $expectedEnd = $expectedStart->copy()->addMinutes($dailyWorkMinutes + $lunchDurationMinutes);
            $actualEnd = Carbon::parse($attendanceLog->time_out);

            $earlyDepartureMinutes = $actualEnd->diffInMinutes($expectedEnd, false);

            if ($earlyDepartureMinutes > 15) {
                // Combina o atraso da manhã com a saída antecipada da tarde
                $existingAbsence = Absence::where('employee_id', $employeeId)
                    ->whereDate('absence_date', $date)
                    ->first();

                $totalMinutesToDeduct = max(0, $earlyDepartureMinutes) + ($existingAbsence?->hours_deducted ?? 0);

                if ($totalMinutesToDeduct > 60) {
                    // Acumulado de atrasos > 1h = Falta total
                    $this->createOrUpdateAbsence(
                        $employeeId,
                        $date,
                        $dailyWorkMinutes,
                        'unjustified_absence',
                        sprintf('Falta por saída antecipada/atraso acumulado > 1h (%d min)', $totalMinutesToDeduct)
                    );
                } else {
                    // Acumulado parcial
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

    /**
     * Regista uma falta total quando não há qualquer registo de ponto.
     */
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

        $minutes = $contract?->daily_work_minutes ?? 480;

        $this->createOrUpdateAbsence($employeeId, $date, $minutes, 'unjustified_absence', $reason);
    }

    /**
     * Helper para persistir o registo de ausência na base de dados.
     */
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

    /**
     * Remove registos de ausência indevidos para uma data específica.
     * Utiliza o método delete() na instância para garantir o disparo de observers.
     */
    public function removeAbsenceForDate(int $employeeId, string $date): void
    {
        $absences = Absence::where('employee_id', $employeeId)
            ->whereDate('absence_date', $date)
            ->whereNull('leave_and_absence_id') // Não remove se for uma licença oficial inserida manualmente
            ->get();

        foreach ($absences as $absence) {
            $absence->delete();
        }
    }

    /**
     * Determina se uma data deve ser ignorada para efeitos de descontos.
     * Ignora fins de semana, férias aprovadas e licenças médicas/outras.
     */
    public function shouldSkipDeduction(int $employeeId, Carbon $date): bool
    {
        if ($date->isWeekend()) {
            return true;
        }

        $dateStr = $date->toDateString();

        // 1. Verificar Licenças/Baixas Médicas
        $leaveExists = LeaveAndAbsence::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $dateStr)
            ->whereDate('end_date', '>=', $dateStr)
            ->exists();

        if ($leaveExists) {
            return true;
        }

        // 2. Verificar Férias
        $vacationExists = Vacation::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('start_date', '<=', $dateStr)
            ->whereDate('end_date', '>=', $dateStr)
            ->exists();

        return $vacationExists;
    }

    /**
     * Regra de Negócio: 3 Atrasos Consecutivos = 1 Falta.
     * Se o funcionário tiver 3 atrasos parciais em dias úteis seguidos,
     * o sistema converte o último atraso numa falta de dia inteiro e remove os outros dois.
     */
    private function checkConsecutiveDelays(int $employeeId): void
    {
        $lastAbsences = Absence::where('employee_id', $employeeId)
            ->where('deduction_type', 'partial_absence')
            ->orderByDesc('absence_date')
            ->take(3)
            ->get();

        if ($lastAbsences->count() < 3) {
            return;
        }

        $dates = $lastAbsences->pluck('absence_date')->map(fn ($d) => Carbon::parse($d));

        $isConsecutive = true;
        for ($i = 0; $i < 2; $i++) {
            $current = $dates[$i];
            $prev = $dates[$i + 1];

            // A diferença entre as datas deve ser de apenas 1 dia útil
            $diff = abs($current->diffInDaysFiltered(fn (Carbon $date) => $date->isWeekday(), $prev));

            if ($diff != 1) {
                $isConsecutive = false;
                break;
            }
        }

        if ($isConsecutive) {
            $contract = Employee::find($employeeId)->contracts()->where('status', 'active')->first();
            $fullDayMinutes = $contract?->daily_work_minutes ?? 480;

            $latest = $lastAbsences->first();
            $latest->update([
                'hours_deducted' => $fullDayMinutes,
                'deduction_type' => 'unjustified_absence',
                'reason' => $latest->reason.' (Convertido para falta por 3 atrasos consecutivos)',
            ]);

            // Elimina os dois atrasos anteriores que deram origem à falta.
            // Percorre a colecção para disparar o AbsenceObserver para cada um.
            foreach ($lastAbsences->slice(1) as $oldAbsence) {
                $oldAbsence->delete();
            }
        }
    }
}
