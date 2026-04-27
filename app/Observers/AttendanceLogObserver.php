<?php

namespace App\Observers;

use App\Models\AttendanceLog;
use App\Services\Hour\DeductHourBankService;
use App\Services\Hour\HourBankService;
use Carbon\Carbon;

class AttendanceLogObserver
{
    protected HourBankService $hourBankService;

    protected DeductHourBankService $deductService;

    public function __construct(HourBankService $hourBankService, DeductHourBankService $deductService)
    {
        $this->hourBankService = $hourBankService;
        $this->deductService = $deductService;
    }

    /**
     * Handle the AttendanceLog "created" event.
     */
    public function created(AttendanceLog $attendanceLog): void
    {
        // Processa atrasos e faltas baseados no ponto
        $this->deductService->processAttendance($attendanceLog);

        // Recalcular banco de horas para este mês
        $this->hourBankService->recalculate(
            $attendanceLog->employee_id,
            $attendanceLog->time_in->format('Y-m')
        );
    }

    /**
     * Handle the AttendanceLog "updated" event.
     */
    public function updated(AttendanceLog $attendanceLog): void
    {
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

            // Reprocessar ausências/atrasos
            $this->deductService->processAttendance($attendanceLog);

            // Recalcular o mês atual
            $this->hourBankService->recalculate(
                $attendanceLog->employee_id,
                $attendanceLog->time_in->format('Y-m')
            );
        }
    }

    /**
     * Handle the AttendanceLog "deleted" event.
     */
    public function deleted(AttendanceLog $attendanceLog): void
    {
        // Remover Absence automática se o ponto for apagado
        $this->deductService->removeAbsenceForDate($attendanceLog->employee_id, $attendanceLog->time_in->toDateString());

        $this->hourBankService->recalculate(
            $attendanceLog->employee_id,
            $attendanceLog->time_in->format('Y-m')
        );
    }
}
