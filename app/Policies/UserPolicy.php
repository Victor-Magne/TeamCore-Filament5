<?php

namespace App\Policies;

use App\Models\User;
use App\Traits\HasHierarchicalPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class UserPolicy
{
    use HandlesAuthorization;
    use HasHierarchicalPolicy;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:User');
    }

    public function view(AuthUser $authUser, User $user): bool
    {
        return $this->canAccessWithPermission($authUser, 'View:User', $user);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:User');
    }

    public function update(AuthUser $authUser, User $user): bool
    {
        return $this->canAccessWithPermission($authUser, 'Update:User', $user);
    }

    public function delete(AuthUser $authUser, User $user): bool
    {
        return $this->canAccessWithPermission($authUser, 'Delete:User', $user);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:User');
    }

    public function restore(AuthUser $authUser, User $user): bool
    {
        return $this->canAccessWithPermission($authUser, 'Restore:User', $user);
    }

    public function forceDelete(AuthUser $authUser, User $user): bool
    {
        return $this->canAccessWithPermission($authUser, 'ForceDelete:User', $user);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:User');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:User');
    }

    public function replicate(AuthUser $authUser, User $user): bool
    {
        return $this->canAccessWithPermission($authUser, 'Replicate:User', $user);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:User');
    }
}
