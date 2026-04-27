<?php

namespace App\Traits;

use App\Models\Employee;
use App\Models\User;

trait HasHierarchicalPolicy
{
    protected function canAccessWithPermission(User $user, string $permission, mixed $model): bool
    {
        return $user->can($permission) && $this->canAccessModel($user, $model);
    }

    protected function canAccessModel(User $user, mixed $model): bool
    {
        if ($user->hasRole('super_admin') || $user->can('Scope:View:All')) {
            return true;
        }

        $myEmployee = $user->employee;
        $ownerEmployee = $model instanceof Employee
            ? $model
            : ($model->employee ?? null);

        if (! $myEmployee || ! $ownerEmployee) {
            return false;
        }

        if ($myEmployee->id === $ownerEmployee->id) {
            return true;
        }

        if ($user->can('Scope:View:Subordinates')) {
            $targetUnit = $ownerEmployee->unit;

            if ($targetUnit) {
                // Verifica se o utilizador atual é o manager da unidade do funcionário alvo
                if ($targetUnit->manager_id === $myEmployee->id) {
                    return true;
                }
            }
        }

        return false;
    }
}
