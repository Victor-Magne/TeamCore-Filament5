<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasHierarchicalQuery
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        $meuEmployee = $user->employee;

        if (! $meuEmployee) {
            return $query->whereRaw('1 = 0');
        }

        $minhaUnitId = $meuEmployee->unit_id;

        return $query->where(function (Builder $q) use ($user, $meuEmployee, $minhaUnitId) {

            // 1. REGRA UNIVERSAL: Vê os próprios registos
            $q->where('employee_id', $meuEmployee->id);

            // 2. DIRETOR: Vê os Chefes de Departamento (que estão nas unidades "filhas" da sua direção)
            if ($user->can('scope_view_dept_heads')) {
                $q->orWhereHas('employee', function (Builder $empQuery) use ($minhaUnitId) {
                    $empQuery->whereHas('unit', function (Builder $unitQuery) use ($minhaUnitId) {
                        // A unidade do funcionário alvo tem de ter como 'pai' a unidade atual
                        $unitQuery->where('parent_id', $minhaUnitId)
                            ->where('type', 'department');
                    })->whereHas('user.roles', function (Builder $roleQuery) {
                        $roleQuery->where('name', 'chefe_departamento');
                    });
                });
            }

            // 3. CHEFE DE DEPARTAMENTO: Vê os Chefes de Secção (nas unidades "filhas" do departamento)
            if ($user->can('scope_view_section_chiefs')) {
                $q->orWhereHas('employee', function (Builder $empQuery) use ($minhaUnitId) {
                    $empQuery->whereHas('unit', function (Builder $unitQuery) use ($minhaUnitId) {
                        // A secção do funcionário alvo tem de ter como 'pai' o departamento atual
                        $unitQuery->where('parent_id', $minhaUnitId)
                            ->where('type', 'section');
                    })->whereHas('user.roles', function (Builder $roleQuery) {
                        $roleQuery->where('name', 'chefe_seccao');
                    });
                });
            }

            // 4. CHEFE DE SECÇÃO: Vê os Colaboradores da SUA secção (mesma unidade)
            if ($user->can('scope_view_base_employees')) {
                $q->orWhereHas('employee', function (Builder $empQuery) use ($minhaUnitId) {
                    $empQuery->where('unit_id', $minhaUnitId) // Mesmo ID de unidade
                        ->whereDoesntHave('user.roles', function (Builder $roleQuery) {
                            $roleQuery->whereIn('name', ['diretor_geral', 'chefe_departamento', 'chefe_seccao']);
                        });
                });
            }
        });
    }
}
