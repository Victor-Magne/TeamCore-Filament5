<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\HourBank;
use Illuminate\Auth\Access\HandlesAuthorization;

class HourBankPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HourBank');
    }

    public function view(AuthUser $authUser, HourBank $hourBank): bool
    {
        return $authUser->can('View:HourBank');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HourBank');
    }

    public function update(AuthUser $authUser, HourBank $hourBank): bool
    {
        return $authUser->can('Update:HourBank');
    }

    public function delete(AuthUser $authUser, HourBank $hourBank): bool
    {
        return $authUser->can('Delete:HourBank');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:HourBank');
    }

    public function restore(AuthUser $authUser, HourBank $hourBank): bool
    {
        return $authUser->can('Restore:HourBank');
    }

    public function forceDelete(AuthUser $authUser, HourBank $hourBank): bool
    {
        return $authUser->can('ForceDelete:HourBank');
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
        return $authUser->can('Replicate:HourBank');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:HourBank');
    }

}