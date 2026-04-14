<?php

namespace App\Observers;

use App\Models\Contract;

class ContractObserver
{
    public function created(Contract $contract): void
    {
        if ($contract->employee && $contract->designation_id) {
            $contract->employee->update(['designation_id' => $contract->designation_id]);
        }
    }

    public function updated(Contract $contract): void
    {
        if ($contract->employee && $contract->designation_id) {
            $contract->employee->update(['designation_id' => $contract->designation_id]);
        }
    }
}
