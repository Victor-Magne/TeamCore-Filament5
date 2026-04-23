<?php

namespace App\Observers;

use App\Models\AttendanceLog;
use App\Services\Hour\HourBankService;
use Carbon\Carbon;

class AttendanceLogObserver
{
    protected HourBankService $hourBankService;

    public function __construct(HourBankService $hourBankService)
    {
        $this->hourBankService = $hourBankService;
    }

    /**
     * Recalcula o banco de horas quando um AttendanceLog é criado
     * 
     * IMPORTANTE: NÃO criamos Absence automaticamente aqui!
     * Faltas completas (dias não trabalhados) são detectadas via Cron Job que roda à noite
     */
    public function created(AttendanceLog $attendanceLog): void
    {
        // Apenas recalcular banco de horas para este mês
        $this->hourBankService->recalculate(
            $attendanceLog->employee_id,
            $attendanceLog->time_in->format('Y-m')
        );
    }

    /**
     * Processa atualizações: se tempo foi alterado, recalcula o mês
     */
    public function updated(AttendanceLog $attendanceLog): void
    {
        // Se os tempos foram alterados, precisa recalcular
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
}

