<?php

/**
 * Ficheiro da Policy VacationPolicy.
 *
 * Gere as autorizações de acesso para o modelo Vacation (Férias).
 * Controla quem pode submeter pedidos, visualizar o histórico de férias
 * e aprovar ou rejeitar períodos de descanso, integrando com o Filament Shield.
 */

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Vacation;
use Illuminate\Auth\Access\HandlesAuthorization;

class VacationPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determina se o utilizador pode ver a lista global de férias.
     */
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Vacation');
    }

    /**
     * Determina se o utilizador pode ver os detalhes de um pedido de férias específico.
     */
    public function view(AuthUser $authUser, Vacation $vacation): bool
    {
        return $authUser->can('View:Vacation');
    }

    /**
     * Determina se o utilizador pode submeter novos pedidos de férias.
     */
    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Vacation');
    }

    /**
     * Determina se o utilizador pode alterar um pedido de férias.
     */
    public function update(AuthUser $authUser, Vacation $vacation): bool
    {
        return $authUser->can('Update:Vacation');
    }

    /**
     * Determina se o utilizador pode eliminar (cancelar) um pedido de férias.
     */
    public function delete(AuthUser $authUser, Vacation $vacation): bool
    {
        return $authUser->can('Delete:Vacation');
    }

    /**
     * Determina se o utilizador pode eliminar vários pedidos em massa.
     */
    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Vacation');
    }

    /**
     * Determina se o utilizador pode restaurar um pedido eliminado.
     */
    public function restore(AuthUser $authUser, Vacation $vacation): bool
    {
        return $authUser->can('Restore:Vacation');
    }

    /**
     * Determina se o utilizador pode eliminar permanentemente um registo da base de dados.
     */
    public function forceDelete(AuthUser $authUser, Vacation $vacation): bool
    {
        return $authUser->can('ForceDelete:Vacation');
    }

    /**
     * Determina se o utilizador pode forçar a eliminação em massa.
     */
    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Vacation');
    }

    /**
     * Determina se o utilizador pode restaurar registos em massa.
     */
    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Vacation');
    }

    /**
     * Determina se o utilizador pode duplicar um pedido de férias.
     */
    public function replicate(AuthUser $authUser, Vacation $vacation): bool
    {
        return $authUser->can('Replicate:Vacation');
    }

    /**
     * Determina se o utilizador pode reordenar a listagem.
     */
    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Vacation');
    }

}
