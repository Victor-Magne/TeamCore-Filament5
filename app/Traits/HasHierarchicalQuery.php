<?php

namespace App\Traits;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;

trait HasHierarchicalQuery
{
    /**
     * Personaliza a query do Eloquent para aplicar visibilidade hierárquica.
     *
     * Regras:
     * - Super Admin vê tudo.
     * - Manager da Direção Geral vê tudo.
     * - Managers de outras unidades vêem a si mesmos e todos os funcionários das unidades geridas (e sub-unidades).
     * - Colaboradores base vêem apenas a si mesmos.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user) {
            return $query;
        }

        // 0. REGRA SUPER ADMIN: Super admin vê todos os registos
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        $meuEmployee = $user->employee;

        if (! $meuEmployee) {
            return $query->whereRaw('1 = 0');
        }

        // Obtém todas as unidades geridas por este funcionário (diretamente ou via pivot)
        $managedUnits = $meuEmployee->getAllManagedUnits();

        // 1. REGRA DIREÇÃO GERAL: Se gere a direção geral, vê tudo
        $isGeneralManager = $managedUnits->contains(fn ($u) => $u->isGeneralDirection());
        if ($isGeneralManager) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($meuEmployee, $managedUnits) {
            // 2. REGRA UNIVERSAL: Vê os próprios registos
            // No recurso de Employee, a coluna é 'id'. Nos outros, é 'employee_id'.
            if ($q->getModel() instanceof Employee) {
                $q->where('id', $meuEmployee->id);
            } else {
                $q->where('employee_id', $meuEmployee->id);
            }

            // 3. REGRA DE GESTÃO HIERÁRQUICA:
            if ($managedUnits->isNotEmpty()) {
                $allAccessibleUnitIds = [];

                foreach ($managedUnits as $unit) {
                    $allAccessibleUnitIds[] = $unit->id;
                    $allAccessibleUnitIds = array_merge($allAccessibleUnitIds, $unit->getAllDescendantIds());
                }

                $allAccessibleUnitIds = array_unique($allAccessibleUnitIds);

                if ($q->getModel() instanceof Employee) {
                    $q->orWhereIn('unit_id', $allAccessibleUnitIds);
                } else {
                    $q->orWhereHas('employee', function (Builder $empQuery) use ($allAccessibleUnitIds) {
                        $empQuery->whereIn('unit_id', $allAccessibleUnitIds);
                    });
                }
            }
        });
    }
}
