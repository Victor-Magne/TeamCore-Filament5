<?php

/**
 * Ficheiro da Policy EmployeePolicy.
 *
 * Esta classe gere as autorizações de acesso para o modelo Employee.
 * Utiliza o sistema de permissões do Filament Shield para determinar se o utilizador
 * autenticado pode realizar acções de visualização, criação, actualização ou eliminação.
 */

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Employee;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeePolicy
{
    use HandlesAuthorization;
    
    /**
     * Determina se o utilizador pode ver a lista de funcionários.
     */
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Employee');
    }

    /**
     * Determina se o utilizador pode ver os detalhes de um funcionário específico.
     */
    public function view(AuthUser $authUser, Employee $employee): bool
    {
        return $authUser->can('View:Employee');
    }

    /**
     * Determina se o utilizador pode criar novos funcionários.
     */
    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Employee');
    }

    /**
     * Determina se o utilizador pode actualizar os dados de um funcionário.
     */
    public function update(AuthUser $authUser, Employee $employee): bool
    {
        return $authUser->can('Update:Employee');
    }

    /**
     * Determina se o utilizador pode eliminar um funcionário.
     */
    public function delete(AuthUser $authUser, Employee $employee): bool
    {
        return $authUser->can('Delete:Employee');
    }

    /**
     * Determina se o utilizador pode eliminar vários funcionários em massa.
     */
    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Employee');
    }

    /**
     * Determina se o utilizador pode restaurar um funcionário eliminado (Soft Delete).
     */
    public function restore(AuthUser $authUser, Employee $employee): bool
    {
        return $authUser->can('Restore:Employee');
    }

    /**
     * Determina se o utilizador pode eliminar permanentemente um funcionário da BD.
     */
    public function forceDelete(AuthUser $authUser, Employee $employee): bool
    {
        return $authUser->can('ForceDelete:Employee');
    }

    /**
     * Determina se o utilizador pode eliminar permanentemente vários funcionários em massa.
     */
    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Employee');
    }

    /**
     * Determina se o utilizador pode restaurar vários funcionários em massa.
     */
    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Employee');
    }

    /**
     * Determina se o utilizador pode replicar/duplicar um registo de funcionário.
     */
    public function replicate(AuthUser $authUser, Employee $employee): bool
    {
        return $authUser->can('Replicate:Employee');
    }

    /**
     * Determina se o utilizador pode reordenar a lista de funcionários.
     */
    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Employee');
    }

}
