<?php

namespace App\Filament\Widgets;

use App\Models\Unit;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class EmployeesByUnitChart extends ChartWidget
{
    protected static ?int $sort = 20;

    protected ?string $heading = 'Funcionários por Tipo de Unidade';

    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'xl' => 1,
    ];

    public static function canView(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        // Permitir Super Admin ou permissão específica
        return $user->hasRole('super_admin') || $user->can('view_employees_by_unit_chart');
    }

    protected function getData(): array
    {
        $units = Unit::withCount('employees')
            ->orderBy('type') // Agrupa por tipo para organizar o gráfico
            ->orderByDesc('employees_count')
            ->get();

        $labels = $units->pluck('name')->toArray();

        // Inicializamos os arrays de dados para cada tipo
        $direcoesData = [];
        $departamentosData = [];
        $seccoesData = [];

        foreach ($units as $unit) {
            // Preenchemos o valor no dataset correspondente e 0 nos outros
            // para que as barras fiquem alinhadas corretamente no eixo X
            $direcoesData[] = $unit->type === 'direction' ? $unit->employees_count : 0;
            $departamentosData[] = $unit->type === 'department' ? $unit->employees_count : 0;
            $seccoesData[] = $unit->type === 'section' ? $unit->employees_count : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Direções',
                    'data' => $direcoesData,
                    'backgroundColor' => '#059669', // Emerald 600
                    'borderRadius' => 0,
                ],
                [
                    'label' => 'Departamentos',
                    'data' => $departamentosData,
                    'backgroundColor' => '#3b82f6', // Blue 500
                    'borderRadius' => 0,
                ],
                [
                    'label' => 'Secções',
                    'data' => $seccoesData,
                    'backgroundColor' => '#8b5cf6', // Purple 500
                    'borderRadius' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'stacked' => true, // Empilha as barras para não haver espaços vazios
                ],
                'y' => [
                    'stacked' => true,
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'labels' => [
                        'usePointStyle' => true,
                    ],
                ],
                'tooltip' => [
                    'usePointStyle' => true,
                    'callbacks' => [
                        // Remove os itens com valor 0 do tooltip para ficar limpo
                        'filter' => RawJs::make('
                            function(tooltipItem) {
                                return tooltipItem.raw > 0;
                            }
                        '),
                    ],
                ],
            ],
        ];
    }
}
