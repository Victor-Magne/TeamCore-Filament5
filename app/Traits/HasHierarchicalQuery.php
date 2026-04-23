<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasHierarchicalQuery
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // 0. REGRA SUPER ADMIN / SCOPE ALL: Vê todos os registos
        if ($user->hasRole('super_admin') || $user->can('Scope:View:All')) {
            return $query;
        }

        $meuEmployee = $user->employee;

        if (! $meuEmployee) {
            // Se não for super_admin e não tiver employee associado, não vê nada em recursos hierárquicos
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $q) use ($user, $meuEmployee) {
            $model = $q->getModel();

            // 1. REGRA UNIVERSAL: Vê os próprios registos
            if ($model instanceof \App\Models\Employee) {
                $q->where('id', $meuEmployee->id);
            } else {
                $q->where('employee_id', $meuEmployee->id);
            }

            // 2. REGRA SUBORDINADOS: Vê registos de quem pertence à sua unidade ou unidades descendentes
            if ($user->can('Scope:View:Subordinates')) {
                $minhaUnit = $meuEmployee->unit;

                if ($minhaUnit) {
                    $unitIds = array_merge([$minhaUnit->id], $minhaUnit->getAllDescendantIds());

                    if ($model instanceof \App\Models\Employee) {
                        $q->orWhereIn('unit_id', $unitIds);
                    } else {
                        $q->orWhereHas('employee', function (Builder $empQuery) use ($unitIds) {
                            $empQuery->whereIn('unit_id', $unitIds);
                        });
                    }
                }
            }
        });
    }
}
