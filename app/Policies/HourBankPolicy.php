<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\HourBank;
use App\Traits\HasHierarchicalPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class HourBankPolicy
{
    use HandlesAuthorization;
    use HasHierarchicalPolicy;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HourBank');
    }

    public function view(AuthUser $authUser, HourBank $hourBank): bool
    {
        return $this->canAccessWithPermission($authUser, 'View:HourBank', $hourBank);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HourBank');
    }

    public function update(AuthUser $authUser, HourBank $hourBank): bool
    {
        return $this->canAccessWithPermission($authUser, 'Update:HourBank', $hourBank);
    }

    public function delete(AuthUser $authUser, HourBank $hourBank): bool
    {
        return $this->canAccessWithPermission($authUser, 'Delete:HourBank', $hourBank);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:HourBank');
    }

    public function restore(AuthUser $authUser, HourBank $hourBank): bool
    {
        return $this->canAccessWithPermission($authUser, 'Restore:HourBank', $hourBank);
    }

    public function forceDelete(AuthUser $authUser, HourBank $hourBank): bool
    {
        return $this->canAccessWithPermission($authUser, 'ForceDelete:HourBank', $hourBank);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:HourBank');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:HourBank');
    }

    public function replicate(AuthUser $authUser, HourBank $hourBank): bool
    {
        return $this->canAccessWithPermission($authUser, 'Replicate:HourBank', $hourBank);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:HourBank');
    }
}
