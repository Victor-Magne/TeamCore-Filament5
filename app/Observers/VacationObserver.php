<?php

namespace App\Observers;

use App\Models\Vacation;
use App\Notifications\RequestStatusNotification;

class VacationObserver
{
    /**
     * Handle the Vacation "updated" event.
     */
    public function updated(Vacation $vacation): void
    {
        if ($vacation->wasChanged('status') && in_array($vacation->status, ['approved', 'rejected'])) {
            $employee = $vacation->employee;
            if ($employee && $employee->user) {
                $employee->user->notify(new RequestStatusNotification(
                    type: 'Férias',
                    status: $vacation->status,
                    reason: $vacation->rejection_reason
                ));
            }
        }
    }
}
