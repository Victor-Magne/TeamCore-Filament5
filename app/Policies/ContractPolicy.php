<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Contract;
use App\Traits\HasHierarchicalPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ContractPolicy
{
    use HandlesAuthorization;
    use HasHierarchicalPolicy;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Contract');
    }

    public function view(AuthUser $authUser, Contract $contract): bool
    {
        return $authUser->can('View:Contract') && $this->canAccessModel($authUser, $contract);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Contract');
    }

    public function update(AuthUser $authUser, Contract $contract): bool
    {
        return $authUser->can('Update:Contract') && $this->canAccessModel($authUser, $contract);
    }

    public function delete(AuthUser $authUser, Contract $contract): bool
    {
        return $authUser->can('Delete:Contract') && $this->canAccessModel($authUser, $contract);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Contract');
    }

    public function restore(AuthUser $authUser, Contract $contract): bool
    {
        return $authUser->can('Restore:Contract') && $this->canAccessModel($authUser, $contract);
    }

    public function forceDelete(AuthUser $authUser, Contract $contract): bool
    {
        return $authUser->can('ForceDelete:Contract') && $this->canAccessModel($authUser, $contract);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Contract');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Contract');
    }

    public function replicate(AuthUser $authUser, Contract $contract): bool
    {
        return $authUser->can('Replicate:Contract') && $this->canAccessModel($authUser, $contract);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Contract');
    }
}
