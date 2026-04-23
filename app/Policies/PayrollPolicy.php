<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payroll;
use App\Traits\HasHierarchicalPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PayrollPolicy
{
    use HandlesAuthorization;
    use HasHierarchicalPolicy;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Payroll');
    }

    public function view(AuthUser $authUser, Payroll $payroll): bool
    {
        return $authUser->can('View:Payroll') && $this->canAccessModel($authUser, $payroll);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Payroll');
    }

    public function update(AuthUser $authUser, Payroll $payroll): bool
    {
        return $authUser->can('Update:Payroll') && $this->canAccessModel($authUser, $payroll);
    }

    public function delete(AuthUser $authUser, Payroll $payroll): bool
    {
        return $authUser->can('Delete:Payroll') && $this->canAccessModel($authUser, $payroll);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Payroll');
    }

    public function restore(AuthUser $authUser, Payroll $payroll): bool
    {
        return $authUser->can('Restore:Payroll') && $this->canAccessModel($authUser, $payroll);
    }

    public function forceDelete(AuthUser $authUser, Payroll $payroll): bool
    {
        return $authUser->can('ForceDelete:Payroll') && $this->canAccessModel($authUser, $payroll);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Payroll');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Payroll');
    }

    public function replicate(AuthUser $authUser, Payroll $payroll): bool
    {
        return $authUser->can('Replicate:Payroll') && $this->canAccessModel($authUser, $payroll);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Payroll');
    }
}
