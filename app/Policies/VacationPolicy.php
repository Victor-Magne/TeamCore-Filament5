<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Vacation;
use App\Traits\HasHierarchicalPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class VacationPolicy
{
    use HandlesAuthorization;
    use HasHierarchicalPolicy;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Vacation');
    }

    public function view(AuthUser $authUser, Vacation $vacation): bool
    {
        return $this->canAccessWithPermission($authUser, 'View:Vacation', $vacation);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Vacation');
    }

    public function update(AuthUser $authUser, Vacation $vacation): bool
    {
        return $this->canAccessWithPermission($authUser, 'Update:Vacation', $vacation);
    }

    public function delete(AuthUser $authUser, Vacation $vacation): bool
    {
        return $this->canAccessWithPermission($authUser, 'Delete:Vacation', $vacation);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Vacation');
    }

    public function restore(AuthUser $authUser, Vacation $vacation): bool
    {
        return $this->canAccessWithPermission($authUser, 'Restore:Vacation', $vacation);
    }

    public function forceDelete(AuthUser $authUser, Vacation $vacation): bool
    {
        return $this->canAccessWithPermission($authUser, 'ForceDelete:Vacation', $vacation);
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
        return $this->canAccessWithPermission($authUser, 'Replicate:Vacation', $vacation);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Vacation');
    }
}
