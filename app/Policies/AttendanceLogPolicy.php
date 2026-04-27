<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AttendanceLog;
use App\Traits\HasHierarchicalPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AttendanceLogPolicy
{
    use HandlesAuthorization;
    use HasHierarchicalPolicy;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AttendanceLog');
    }

    public function view(AuthUser $authUser, AttendanceLog $attendanceLog): bool
    {
        return $this->canAccessWithPermission($authUser, 'View:AttendanceLog', $attendanceLog);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AttendanceLog');
    }

    public function update(AuthUser $authUser, AttendanceLog $attendanceLog): bool
    {
        return $this->canAccessWithPermission($authUser, 'Update:AttendanceLog', $attendanceLog);
    }

    public function delete(AuthUser $authUser, AttendanceLog $attendanceLog): bool
    {
        return $this->canAccessWithPermission($authUser, 'Delete:AttendanceLog', $attendanceLog);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AttendanceLog');
    }

    public function restore(AuthUser $authUser, AttendanceLog $attendanceLog): bool
    {
        return $this->canAccessWithPermission($authUser, 'Restore:AttendanceLog', $attendanceLog);
    }

    public function forceDelete(AuthUser $authUser, AttendanceLog $attendanceLog): bool
    {
        return $this->canAccessWithPermission($authUser, 'ForceDelete:AttendanceLog', $attendanceLog);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AttendanceLog');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AttendanceLog');
    }

    public function replicate(AuthUser $authUser, AttendanceLog $attendanceLog): bool
    {
        return $this->canAccessWithPermission($authUser, 'Replicate:AttendanceLog', $attendanceLog);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AttendanceLog');
    }
}
