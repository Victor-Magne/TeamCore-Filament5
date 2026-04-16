<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\HourBank;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Traits\HasHierarchicalPolicy;

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
        return $authUser->can('View:HourBank') && $this->canAccessModel($authUser, $hourBank);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HourBank');
    }

    public function update(AuthUser $authUser, HourBank $hourBank): bool
    {
        return $authUser->can('Update:HourBank') && $this->canAccessModel($authUser, $hourBank);
    }

    public function delete(AuthUser $authUser, HourBank $hourBank): bool
    {
        return $authUser->can('Delete:HourBank') && $this->canAccessModel($authUser, $hourBank);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:HourBank');
    }

    public function restore(AuthUser $authUser, HourBank $hourBank): bool
    {
        return $authUser->can('Restore:HourBank') && $this->canAccessModel($authUser, $hourBank);
    }

    public function forceDelete(AuthUser $authUser, HourBank $hourBank): bool
    {
        return $authUser->can('ForceDelete:HourBank') && $this->canAccessModel($authUser, $hourBank);
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
        return $authUser->can('Replicate:HourBank') && $this->canAccessModel($authUser, $hourBank);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:HourBank');
    }

}