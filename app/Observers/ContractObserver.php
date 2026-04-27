<?php

namespace App\Observers;

use App\Models\Contract;

use App\Models\User;

class ContractObserver
{
    public function created(Contract $contract): void
    {
        if ($contract->status === 'active') {
            $this->syncUserRoles($contract);
        }
    }

    public function updated(Contract $contract): void
    {
        if ($contract->status === 'active') {
            $this->syncUserRoles($contract);
        }
    }

    /**
     * Sincroniza as roles do utilizador baseadas na designação do contrato ativo.
     */
    protected function syncUserRoles(Contract $contract): void
    {
        $employee = $contract->employee;
        if (!$employee) return;

        $user = $employee->user;
        if (!$user) return;

        $roles = [];

        // Role base obrigatória se existir
        if (\Spatie\Permission\Models\Role::where('name', 'employee')->exists()) {
            $roles[] = 'employee';
        }

        // Role associada ao cargo
        $roleName = $contract->designation?->role_name;
        if ($roleName && \Spatie\Permission\Models\Role::where('name', $roleName)->exists()) {
            $roles[] = $roleName;
        }

        if (!empty($roles)) {
            $user->syncRoles($roles);
        }
    }
}
