<?php

namespace App\Filament\Widgets;

use App\Models\Unit;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class EmployeesByUnitChart extends ChartWidget
{
    protected static ?int $sort = 40;

    protected ?string $heading = 'Distribuição de Funcionários por Unidade';

    protected ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'xl' => 1,
    ];

    public static function canView(): bool
    {
        return Auth::user()?->can('View:EmployeesByUnitChart') ?? false;
    }

    protected function getData(): array
    {
        // Busca unidades com contagem de funcionários, ordenadas por contagem decrescente
        $units = Unit::withCount('employees')
            ->orderByDesc('employees_count')
            ->get(['id', 'name', 'type', 'employees_count']);

        $labels = $units->map(function ($unit) {
            return $unit->name.' ('.match ($unit->type) {
                'direction' => 'Direção',
                'department' => 'Departamento',
                'section' => 'Secção',
                default => $unit->type,
            }.')';
        })->values();

        $data = $units->pluck('employees_count')->values();

        // Define cores baseadas no tipo de unidade
        $colors = $units->map(function ($unit) {
            return match ($unit->type) {
                'direction' => '#059669',    // green-700
                'department' => '#3b82f6',   // blue-500
                'section' => '#8b5cf6',      // purple-500
                default => '#6b7280',        // gray-500
            };
        })->values();

        return [
            'datasets' => [
                [
                    'label' => 'Nº de Funcionários',
                    'data' => $data->toArray(),
                    'backgroundColor' => $colors->toArray(),
                    'borderColor' => $colors->toArray(),
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
