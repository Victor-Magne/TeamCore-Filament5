<?php

namespace App\Observers;

use App\Models\LeaveAndAbsence;
use App\Services\Hour\DeductHourBankService;
use App\Services\Hour\HourBankService;
use Carbon\Carbon;

class LeaveAndAbsenceObserver
{
    protected HourBankService $hourBankService;

    protected DeductHourBankService $deductService;

    public function __construct(HourBankService $hourBankService, DeductHourBankService $deductService)
    {
        $this->hourBankService = $hourBankService;
        $this->deductService = $deductService;
    }

    /**
     * Handle the LeaveAndAbsence "saved" event.
     */
    public function saved(LeaveAndAbsence $leaveAndAbsence): void
    {
        if ($leaveAndAbsence->status === 'approved') {
            // Se aprovado, garantir que não há Absences automáticas conflitantes
            // ou se for não paga, criar Absences.

            $startDate = Carbon::parse($leaveAndAbsence->start_date);
            $endDate = Carbon::parse($leaveAndAbsence->end_date);

            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                if ($currentDate->isWeekday()) {
                    // Se for licença não paga, deve descontar
                    if (! $leaveAndAbsence->is_paid) {
                        $this->deductService->registerFullAbsence(
                            $leaveAndAbsence->employee_id,
                            $currentDate->copy(),
                            'Licença aprovada (Não paga): '.($leaveAndAbsence->reason ?? $leaveAndAbsence->type)
                        );
                    } else {
                        // Se for paga, remover qualquer Absence automática (atraso/falta) que tenha sido gerada pelo ponto
                        $this->deductService->removeAbsenceForDate($leaveAndAbsence->employee_id, $currentDate->toDateString());
                    }
                }
                $currentDate->addDay();
            }

            // Recalcular banco de horas para os meses afetados
            $this->hourBankService->recalculate($leaveAndAbsence->employee_id, $startDate->format('Y-m'));
            if ($startDate->format('Y-m') !== $endDate->format('Y-m')) {
                $this->hourBankService->recalculate($leaveAndAbsence->employee_id, $endDate->format('Y-m'));
            }
        }
    }

    /**
     * Handle the LeaveAndAbsence "deleted" event.
     */
    public function deleted(LeaveAndAbsence $leaveAndAbsence): void
    {
        // Se apagado, recalcular
        $startDate = Carbon::parse($leaveAndAbsence->start_date);
        $this->hourBankService->recalculate($leaveAndAbsence->employee_id, $startDate->format('Y-m'));
    }
}
