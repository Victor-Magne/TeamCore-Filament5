<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LeaveAndAbsence;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Traits\HasHierarchicalPolicy;

class LeaveAndAbsencePolicy
{
    use HandlesAuthorization;
    use HasHierarchicalPolicy;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LeaveAndAbsence');
    }

    public function view(AuthUser $authUser, LeaveAndAbsence $leaveAndAbsence): bool
    {
        return $authUser->can('View:LeaveAndAbsence') && $this->canAccessModel($authUser, $leaveAndAbsence);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LeaveAndAbsence');
    }

    public function update(AuthUser $authUser, LeaveAndAbsence $leaveAndAbsence): bool
    {
        return $authUser->can('Update:LeaveAndAbsence') && $this->canAccessModel($authUser, $leaveAndAbsence);
    }

    public function delete(AuthUser $authUser, LeaveAndAbsence $leaveAndAbsence): bool
    {
        return $authUser->can('Delete:LeaveAndAbsence') && $this->canAccessModel($authUser, $leaveAndAbsence);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LeaveAndAbsence');
    }

    public function restore(AuthUser $authUser, LeaveAndAbsence $leaveAndAbsence): bool
    {
        return $authUser->can('Restore:LeaveAndAbsence') && $this->canAccessModel($authUser, $leaveAndAbsence);
    }

    public function forceDelete(AuthUser $authUser, LeaveAndAbsence $leaveAndAbsence): bool
    {
        return $authUser->can('ForceDelete:LeaveAndAbsence') && $this->canAccessModel($authUser, $leaveAndAbsence);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LeaveAndAbsence');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LeaveAndAbsence');
    }

    public function replicate(AuthUser $authUser, LeaveAndAbsence $leaveAndAbsence): bool
    {
        return $authUser->can('Replicate:LeaveAndAbsence') && $this->canAccessModel($authUser, $leaveAndAbsence);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LeaveAndAbsence');
    }

}