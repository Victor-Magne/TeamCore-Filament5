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
        return Auth::user()?->can('View:EmployeesByUnitChart') ?? false;
    }

    protected function getData(): array
    {
        $units = Unit::withCount('employees')
            ->orderBy('type')
            ->orderByDesc('employees_count')
            ->get();

        $labels = $units->pluck('name')->toArray();

        $direcoesData = [];
        $departamentosData = [];
        $seccoesData = [];

        foreach ($units as $unit) {
            $direcoesData[] = $unit->type === 'direction' ? $unit->employees_count : 0;
            $departamentosData[] = $unit->type === 'department' ? $unit->employees_count : 0;
            $seccoesData[] = $unit->type === 'section' ? $unit->employees_count : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Direções',
                    'data' => $direcoesData,
                    'backgroundColor' => '#059669',
                    'borderRadius' => 0,
                ],
                [
                    'label' => 'Departamentos',
                    'data' => $departamentosData,
                    'backgroundColor' => '#3b82f6',
                    'borderRadius' => 0,
                ],
                [
                    'label' => 'Secções',
                    'data' => $seccoesData,
                    'backgroundColor' => '#8b5cf6',
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
                    'stacked' => true,
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
