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
     * - Gestores vêem os seus subordinados directos consoante o tipo de unidade.
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

        // Se o utilizador não for um funcionário ou o modelo não pertencer a ninguém, o acesso é negado.
        if (! $meuEmployee || ! $donoDoModel) {
            return false;
        }

        // 1. REGRA UNIVERSAL: O utilizador tem sempre acesso ao seu próprio registo.
        if ($meuEmployee->id === $donoDoModel->id) {
            return true;
        }

        $userAlvo = $donoDoModel->user;
        $minhaUnit = $meuEmployee->unit; // Unidade do utilizador logado
        $unitDoAlvo = $donoDoModel->unit; // Unidade do funcionário dono do registo

        // Segurança caso as unidades não estejam definidas na BD.
        if (! $minhaUnit || ! $unitDoAlvo) {
            return false;
        }

        // 2. REGRA DE DIRETOR: Pode ver Chefes de Departamento que estejam em unidades filhas da sua.
        if ($user->can('scope_view_dept_heads')) {
            if (
                $unitDoAlvo->parent_id === $minhaUnit->id &&
                $unitDoAlvo->type === 'department' &&
                $userAlvo && $userAlvo->hasRole('chefe_departamento')
            ) {
                return true;
            }
        }

        // 3. REGRA DE CHEFE DE DEPARTAMENTO: Pode ver Chefes de Secção em unidades filhas.
        if ($user->can('scope_view_section_chiefs')) {
            if (
                $unitDoAlvo->parent_id === $minhaUnit->id &&
                $unitDoAlvo->type === 'section' &&
                $userAlvo && $userAlvo->hasRole('chefe_seccao')
            ) {
                return true;
            }
        }

        // 4. REGRA DE CHEFE DE SECÇÃO: Pode ver Colaboradores Base que pertençam à sua própria secção.
        if ($user->can('scope_view_base_employees')) {
            if (
                $unitDoAlvo->id === $minhaUnit->id &&
                $userAlvo && ! $userAlvo->hasAnyRole(['diretor_geral', 'chefe_departamento', 'chefe_seccao'])
            ) {
                return true;
            }
        }

        // Por defeito, se nenhuma regra anterior validou o acesso, este é negado.
        return false;
    }
}
