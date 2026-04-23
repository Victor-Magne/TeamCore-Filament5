<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Employee;
use App\Traits\HasHierarchicalPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class EmployeePolicy
{
    use HandlesAuthorization;
    use HasHierarchicalPolicy;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Employee');
    }

    public function view(AuthUser $authUser, Employee $employee): bool
    {
        // Regra especial: EmployeePolicy recebe o próprio Employee, não um modelo que tem employee_id
        // Vamos adaptar o canAccessModel ou tratar aqui
        if ($authUser->hasRole('super_admin') || $authUser->can('Scope:View:All')) {
            return true;
        }

        $meuEmployee = $authUser->employee;
        if (! $meuEmployee) return false;

        if ($meuEmployee->id === $employee->id) return true;

        if ($authUser->can('Scope:View:Subordinates')) {
            $minhaUnit = $meuEmployee->unit;
            $unitDoAlvo = $employee->unit;

            if ($minhaUnit && $unitDoAlvo) {
                if ($minhaUnit->id === $unitDoAlvo->id) return true;
                if (in_array($unitDoAlvo->id, $minhaUnit->getAllDescendantIds())) return true;
            }
        }

        return false;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Employee');
    }

    public function update(AuthUser $authUser, Employee $employee): bool
    {
        return $authUser->can('Update:Employee') && $this->view($authUser, $employee);
    }

    public function delete(AuthUser $authUser, Employee $employee): bool
    {
        return $authUser->can('Delete:Employee') && $this->view($authUser, $employee);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Employee');
    }

    public function restore(AuthUser $authUser, Employee $employee): bool
    {
        return $authUser->can('Restore:Employee') && $this->view($authUser, $employee);
    }

    public function forceDelete(AuthUser $authUser, Employee $employee): bool
    {
        return $authUser->can('ForceDelete:Employee') && $this->view($authUser, $employee);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Employee');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Employee');
    }

    public function replicate(AuthUser $authUser, Employee $employee): bool
    {
        return $authUser->can('Replicate:Employee') && $this->view($authUser, $employee);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Employee');
    }

}
