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
        return $this->canAccessWithPermission($authUser, 'View:Employee', $employee);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Employee');
    }

    public function update(AuthUser $authUser, Employee $employee): bool
    {
        return $this->canAccessWithPermission($authUser, 'Update:Employee', $employee);
    }

    public function delete(AuthUser $authUser, Employee $employee): bool
    {
        return $this->canAccessWithPermission($authUser, 'Delete:Employee', $employee);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Employee');
    }

    public function restore(AuthUser $authUser, Employee $employee): bool
    {
        return $this->canAccessWithPermission($authUser, 'Restore:Employee', $employee);
    }

    public function forceDelete(AuthUser $authUser, Employee $employee): bool
    {
        return $this->canAccessWithPermission($authUser, 'ForceDelete:Employee', $employee);
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
        return $this->canAccessWithPermission($authUser, 'Replicate:Employee', $employee);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Employee');
    }
}
