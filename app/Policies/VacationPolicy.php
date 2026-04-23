<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Vacation;
use Illuminate\Auth\Access\HandlesAuthorization;

class VacationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Vacation');
    }

    public function view(AuthUser $authUser, Vacation $vacation): bool
    {
        return $authUser->can('View:Vacation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Vacation');
    }

    public function update(AuthUser $authUser, Vacation $vacation): bool
    {
        return $authUser->can('Update:Vacation');
    }

    public function approve(AuthUser $authUser, Vacation $vacation): bool
    {
        if ($vacation->employee_id === $authUser->employee_id) {
            return $authUser->can('Approve:OwnVacation');
        }

        return $authUser->can('Update:Vacation');
    }

    public function delete(AuthUser $authUser, Vacation $vacation): bool
    {
        return $authUser->can('Delete:Vacation');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Vacation');
    }

    public function restore(AuthUser $authUser, Vacation $vacation): bool
    {
        return $authUser->can('Restore:Vacation');
    }

    public function forceDelete(AuthUser $authUser, Vacation $vacation): bool
    {
        return $authUser->can('ForceDelete:Vacation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Vacation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Vacation');
    }

    public function replicate(AuthUser $authUser, Vacation $vacation): bool
    {
        return $authUser->can('Replicate:Vacation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Vacation');
    }

}