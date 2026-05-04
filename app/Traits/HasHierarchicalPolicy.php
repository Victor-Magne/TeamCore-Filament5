<?php

/**
 * Ficheiro do Trait HasHierarchicalPolicy.
 *
 * Este trait centraliza a lógica de autorização baseada na hierarquia organizacional.
 * É utilizado nas Policies (ex: EmployeePolicy, ContractPolicy) para determinar se um
 * utilizador tem permissão para visualizar ou manipular um registo específico,
 * respeitando a estrutura de Unidades Organizacionais (Direcção -> Departamento -> Secção).
 */

namespace App\Traits;

use App\Models\User;

trait HasHierarchicalPolicy
{
    /**
     * Verifica se o utilizador autenticado pode aceder a um modelo específico.
     *
     * Implementa regras de visibilidade em cascata:
     * - Admin vê tudo.
     * - Utilizador vê os seus próprios dados.
     * - Gestores vêem os seus subordinados recursivamente (unidades filhas).
     *
     * @param User $user O utilizador autenticado.
     * @param mixed $model O objecto que se pretende aceder (deve ter relação com Employee).
     * @return bool
     */
    protected function canAccessModel(User $user, $model): bool
    {
        // 0. REGRA SUPER ADMIN: Utilizadores com papel de super_admin têm acesso total sem restrições.
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $meuEmployee = $user->employee;
        $donoDoModel = $model->employee ?? null;

        // Caso especial: se o modelo for o próprio Employee, o dono é o próprio modelo
        if ($model instanceof \App\Models\Employee) {
            $donoDoModel = $model;
        }

        // Se o utilizador não for um funcionário ou o modelo não pertencer a ninguém, o acesso é negado.
        if (! $meuEmployee || ! $donoDoModel) {
            return false;
        }

        // 1. REGRA UNIVERSAL: O utilizador tem sempre acesso ao seu próprio registo.
        if ($meuEmployee->id === $donoDoModel->id) {
            return true;
        }

        // Obtém todas as unidades geridas por este funcionário (diretamente ou via pivot)
        $managedUnits = $meuEmployee->getAllManagedUnits();

        // 2. REGRA DIREÇÃO GERAL: Se gere a direção geral, vê tudo
        $isGeneralManager = $managedUnits->contains(fn ($u) => $u->isGeneralDirection());
        if ($isGeneralManager) {
            return true;
        }

        // 3. REGRA DE GESTÃO HIERÁRQUICA:
        if ($managedUnits->isNotEmpty()) {
            $unitDoAlvoId = $donoDoModel->unit_id;

            if (! $unitDoAlvoId) {
                return false;
            }

            foreach ($managedUnits as $unit) {
                // Se o alvo está na unidade que o utilizador gere
                if ($unitDoAlvoId === $unit->id) {
                    return true;
                }

                // Ou se o alvo está em alguma unidade descendente
                if (in_array($unitDoAlvoId, $unit->getAllDescendantIds())) {
                    return true;
                }
            }
        }

        // Por defeito, se nenhuma regra anterior validou o acesso, este é negado.
        return false;
    }
}
