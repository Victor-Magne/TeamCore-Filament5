<?php

namespace App\Traits;

use App\Models\User;

trait HasHierarchicalPolicy
{
    protected function canAccessModel(User $user, $model): bool
    {
        $meuEmployee = $user->employee;
        $donoDoModel = $model->employee ?? null;

        if (! $meuEmployee || ! $donoDoModel) {
            return false;
        }

        // 1. REGRA UNIVERSAL: É o próprio registo
        if ($meuEmployee->id === $donoDoModel->id) {
            return true;
        }

        $userAlvo = $donoDoModel->user;
        $minhaUnit = $meuEmployee->unit; // Modelo OrganizationalUnit do utilizador logado
        $unitDoAlvo = $donoDoModel->unit; // Modelo OrganizationalUnit do dono do registo

        // Segurança caso as unidades não estejam definidas
        if (! $minhaUnit || ! $unitDoAlvo) {
            return false;
        }

        // 2. DIRETOR: O dono do modelo é um Chefe de Departamento e a unidade dele é filha da minha?
        if ($user->can('scope_view_dept_heads')) {
            if (
                $unitDoAlvo->parent_id === $minhaUnit->id &&
                $unitDoAlvo->type === 'department' &&
                $userAlvo && $userAlvo->hasRole('chefe_departamento')
            ) {
                return true;
            }
        }

        // 3. CHEFE DE DEPARTAMENTO: O dono do modelo é um Chefe de Secção e a unidade é filha da minha?
        if ($user->can('scope_view_section_chiefs')) {
            if (
                $unitDoAlvo->parent_id === $minhaUnit->id &&
                $unitDoAlvo->type === 'section' &&
                $userAlvo && $userAlvo->hasRole('chefe_seccao')
            ) {
                return true;
            }
        }

        // 4. CHEFE DE SECÇÃO: O dono é da mesma unidade (secção) e é um Colaborador Base?
        if ($user->can('scope_view_base_employees')) {
            if (
                $unitDoAlvo->id === $minhaUnit->id &&
                $userAlvo && ! $userAlvo->hasAnyRole(['diretor_geral', 'chefe_departamento', 'chefe_seccao'])
            ) {
                return true;
            }
        }

        return false;
    }
}
