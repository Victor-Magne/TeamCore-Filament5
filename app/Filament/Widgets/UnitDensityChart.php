<?php

namespace App\Filament\Widgets;

use App\Models\Unit;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class UnitDensityChart extends ChartWidget
{
    protected static ?int $sort = 40;

    protected ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'xl' => 1,
    ];

    public static function canView(): bool
    {
        return Auth::user()?->can('View:UnitDensityChart') ?? false;
    }

    public function getHeading(): ?string
    {
        return 'Densidade Organizacional';
    }

    public function getDescription(): ?string
    {
        return 'Concentração de funcionários (top 10 unidades)';
    }

    protected function getData(): array
    {
        // Busca unidades com contagem de funcionários otimizada
        $units = Unit::withCount('employees')
            ->having('employees_count', '>', 0)
            ->orderByDesc('employees_count')
            ->limit(10)
            ->get(['id', 'name', 'employees_count']);

        if ($units->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $labels = $units->pluck('name')->values();
        $data = $units->pluck('employees_count')->values();

        // Calcula percentual de cada unidade
        $total = $data->sum();
        $percentages = $data->map(fn ($count) => round(($count / $total) * 100, 1))->values();

        return [
            'datasets' => [
                [
                    'label' => 'Distribuição (%)',
                    'data' => $percentages->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.7)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.label + ": " + context.parsed + "%"; }',
                    ],
                ],
            ],
        ];
    }
}
