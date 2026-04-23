<?php

namespace App\Traits;

use App\Models\User;

trait HasHierarchicalPolicy
{
    protected function canAccessModel(User $user, $model): bool
    {
        // 0. REGRA SUPER ADMIN / SCOPE ALL: Vê tudo
        if ($user->hasRole('super_admin') || $user->can('Scope:View:All')) {
            return true;
        }

        $meuEmployee = $user->employee;
        $donoDoModel = $model->employee ?? null;

        if (! $meuEmployee || ! $donoDoModel) {
            return false;
        }

        // 1. REGRA UNIVERSAL: É o próprio registo
        if ($meuEmployee->id === $donoDoModel->id) {
            return true;
        }

        // 2. REGRA SUBORDINADOS: O dono pertence à minha unidade ou unidades descendentes?
        if ($user->can('Scope:View:Subordinates')) {
            $minhaUnit = $meuEmployee->unit;
            $unitDoAlvo = $donoDoModel->unit;

            if ($minhaUnit && $unitDoAlvo) {
                // Se for a mesma unidade
                if ($minhaUnit->id === $unitDoAlvo->id) {
                    return true;
                }

                // Ou se for uma unidade descendente
                $descendantIds = $minhaUnit->getAllDescendantIds();
                if (in_array($unitDoAlvo->id, $descendantIds)) {
                    return true;
                }
            }
        }

        return false;
    }
}
