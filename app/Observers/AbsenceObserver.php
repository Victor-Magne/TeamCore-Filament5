<?php

namespace App\Observers;

use App\Models\Absence;
use App\Services\Hour\HourBankService;

class AbsenceObserver
{
    protected HourBankService $hourBankService;

    public function __construct(HourBankService $hourBankService)
    {
        $this->hourBankService = $hourBankService;
    }

    /**
     * Handle the Absence "created" event.
     */
    public function created(Absence $absence): void
    {
        $this->hourBankService->recalculate(
            $absence->employee_id,
            $absence->absence_date->format('Y-m')
        );
    }

    /**
     * Handle the Absence "updated" event.
     */
    public function updated(Absence $absence): void
    {
        // Se a data mudou, recalcular ambos os meses
        if ($absence->isDirty('absence_date')) {
            $originalDate = $absence->getOriginal('absence_date');
            $this->hourBankService->recalculate(
                $absence->employee_id,
                \Carbon\Carbon::parse($originalDate)->format('Y-m')
            );
        }

        $this->hourBankService->recalculate(
            $absence->employee_id,
            $absence->absence_date->format('Y-m')
        );
    }

    /**
     * Handle the Absence "deleted" event.
     */
    public function deleted(Absence $absence): void
    {
        $this->hourBankService->recalculate(
            $absence->employee_id,
            $absence->absence_date->format('Y-m')
        );
    }
}
