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
    /**
     * Processa um AttendanceLog para verificar atrasos ou faltas
     */
    public function processAttendance(AttendanceLog $attendanceLog): void
    {
        $employeeId = $attendanceLog->employee_id;
        $date = Carbon::parse($attendanceLog->time_in);

        // 1. Verificar se deve ignorar (fim de semana, férias, licenças)
        if ($this->shouldSkipDeduction($employeeId, $date)) {
            // Se deve pular, garantir que não há Absence indevida para este dia
            $this->removeAbsenceForDate($employeeId, $date->toDateString());

            return;
        }

        // 2. Obter contrato ativo
        $contract = $attendanceLog->employee->contracts()
            ->where('status', 'active')
            ->where('start_date', '<=', $date)
            ->orderByDesc('start_date')
            ->first();

        if (! $contract || ! $contract->expected_start_time) {
            return;
        }

        $expectedStart = Carbon::parse($date->toDateString().' '.$contract->expected_start_time);
        $actualStart = Carbon::parse($attendanceLog->time_in);

        $delayMinutes = $expectedStart->diffInMinutes($actualStart, false);

        if ($delayMinutes > 15) {
            if ($delayMinutes <= 60) {
                // Atraso: tempo exato
                $this->createOrUpdateAbsence(
                    $employeeId,
                    $date,
                    $delayMinutes,
                    'partial_absence',
                    sprintf('Atraso de %d minutos (Entrada: %s, Esperada: %s)', $delayMinutes, $actualStart->format('H:i'), $expectedStart->format('H:i'))
                );

                // Verificar regra de 3 atrasos consecutivos
                $this->checkConsecutiveDelays($employeeId);
            } else {
                // Falta: dia completo
                $this->createOrUpdateAbsence(
                    $employeeId,
                    $date,
                    $contract->daily_work_minutes,
                    'unjustified_absence',
                    sprintf('Falta por atraso superior a 1h (%d min)', $delayMinutes)
                );

                return; // Se já é falta total por atraso, não precisamos checar saída antecipada
            }
        } else {
            // Tolerância: Remover qualquer Absence de atraso/falta automática para este dia se existir
            $this->removeAbsenceForDate($employeeId, $date->toDateString());
        }

        // 3. Verificar Saída Antecipada
        if ($attendanceLog->time_out) {
            $expectedEnd = $expectedStart->copy()->addMinutes($contract->daily_work_minutes + $contract->lunch_duration_minutes);
            $actualEnd = Carbon::parse($attendanceLog->time_out);

            $earlyDepartureMinutes = $actualEnd->diffInMinutes($expectedEnd, false);

            if ($earlyDepartureMinutes > 15) {
                // Obter minutos já descontados (se houve atraso na entrada)
                $existingAbsence = Absence::where('employee_id', $employeeId)
                    ->whereDate('absence_date', $date)
                    ->first();

                $totalMinutesToDeduct = max(0, $earlyDepartureMinutes) + ($existingAbsence?->hours_deducted ?? 0);

                if ($totalMinutesToDeduct > 60) {
                    $this->createOrUpdateAbsence(
                        $employeeId,
                        $date,
                        $contract->daily_work_minutes,
                        'unjustified_absence',
                        sprintf('Falta por saída antecipada/atraso acumulado > 1h (%d min)', $totalMinutesToDeduct)
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

    /**
     * Regista uma falta total (quando não há AttendanceLog ou é explicitamente marcado)
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

        $this->createOrUpdateAbsence(
            $employeeId,
            $date,
            $minutes,
            'unjustified_absence',
            $reason
        );
    }

    private function createOrUpdateAbsence(int $employeeId, Carbon $date, int $minutes, string $type, string $reason): void
    {
        $absence = Absence::where('employee_id', $employeeId)
            ->whereDate('absence_date', $date)
            ->first();

        if ($absence) {
            $absence->update([
                'hours_deducted' => $minutes,
                'deduction_type' => $type,
                'reason' => $reason,
            ]);
        } else {
            Absence::create([
                'employee_id' => $employeeId,
                'absence_date' => $date->toDateString(),
                'hours_deducted' => $minutes,
                'deduction_type' => $type,
                'reason' => $reason,
            ]);
        }
    }

    public function removeAbsenceForDate(int $employeeId, string $date): void
    {
        Absence::where('employee_id', $employeeId)
            ->whereDate('absence_date', $date)
            ->whereNull('leave_and_absence_id') // Não remover se for uma licença manual
            ->delete();
    }

    public function shouldSkipDeduction(int $employeeId, Carbon $date): bool
    {
        if ($date->isWeekend()) {
            return true;
        }

        $dateStr = $date->toDateString();

        // Verificar licenças aprovadas que cubram esta data
        $leaveExists = LeaveAndAbsence::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $dateStr)
            ->whereDate('end_date', '>=', $dateStr)
            ->exists();

        if ($leaveExists) {
            return true;
        }

        // Verificar férias aprovadas que cubram esta data
        $vacationExists = Vacation::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('start_date', '<=', $dateStr)
            ->where('end_date', '>=', $dateStr)
            ->exists();

        if ($vacationExists) {
            return true;
        }

        return false;
    }

    /**
     * Verifica se o funcionário tem 3 atrasos consecutivos e converte em 1 falta
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

        // Verificar se são dias consecutivos (úteis)
        $dates = $lastAbsences->pluck('absence_date')->map(fn ($d) => Carbon::parse($d));

        $isConsecutive = true;
        for ($i = 0; $i < 2; $i++) {
            $current = $dates[$i];
            $prev = $dates[$i + 1];

            // Diferença deve ser de 1 dia útil
            $diff = abs($current->diffInDaysFiltered(fn (Carbon $date) => $date->isWeekday(), $prev));

            if ($diff != 1) {
                $isConsecutive = false;
                break;
            }
        }

        if ($isConsecutive) {
            $contract = Employee::find($employeeId)->contracts()->where('status', 'active')->first();
            $fullDayMinutes = $contract?->daily_work_minutes ?? 480;

            // Atualizar o mais recente para dia inteiro
            $latest = $lastAbsences->first();
            $latest->update([
                'hours_deducted' => $fullDayMinutes,
                'deduction_type' => 'unjustified_absence',
                'reason' => $latest->reason.' (Convertido para falta por 3 atrasos consecutivos)',
            ]);

            // Remover os outros dois
            Absence::whereIn('id', $lastAbsences->slice(1)->pluck('id'))->delete();
        }
    }
}
