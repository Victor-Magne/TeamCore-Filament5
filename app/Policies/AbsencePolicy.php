<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Absence;
use App\Traits\HasHierarchicalPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AbsencePolicy
{
    use HandlesAuthorization;
    use HasHierarchicalPolicy;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Absence');
    }

    public function view(AuthUser $authUser, Absence $absence): bool
    {
        return $authUser->can('View:Absence') && $this->canAccessModel($authUser, $absence);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Absence');
    }

    public function update(AuthUser $authUser, Absence $absence): bool
    {
        return $authUser->can('Update:Absence') && $this->canAccessModel($authUser, $absence);
    }

    public function delete(AuthUser $authUser, Absence $absence): bool
    {
        return $authUser->can('Delete:Absence') && $this->canAccessModel($authUser, $absence);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Absence');
    }

    public function restore(AuthUser $authUser, Absence $absence): bool
    {
        return $authUser->can('Restore:Absence') && $this->canAccessModel($authUser, $absence);
    }

    public function forceDelete(AuthUser $authUser, Absence $absence): bool
    {
        return $authUser->can('ForceDelete:Absence') && $this->canAccessModel($authUser, $absence);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Absence');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Absence');
    }

    public function replicate(AuthUser $authUser, Absence $absence): bool
    {
        return $authUser->can('Replicate:Absence') && $this->canAccessModel($authUser, $absence);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Absence');
    }
}
