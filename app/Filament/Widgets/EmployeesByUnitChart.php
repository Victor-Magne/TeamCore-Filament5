<?php

namespace App\Filament\Widgets;

use App\Models\Unit;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class EmployeesByUnitChart extends ChartWidget
{
    protected ?string $heading = 'Funcionários por Unidade';
    // CORREÇÃO: Verifica permissão do Shield
    public static function canView(): bool
    {
        return Auth::user()?->can('View:EmployeesByUnitChart') ?? false;
    }

    protected function getData(): array
    {
        $units = Unit::withCount('employees')
            ->orderByDesc('employees_count')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Quantidade de Funcionários',
                    'data' => $units->pluck('employees_count')->toArray(),
                    'backgroundColor' => [
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)',
                        'rgba(255, 159, 64, 0.5)',
                        'rgba(255, 99, 132, 0.5)',
                    ],
                    'borderColor' => [
                        'rgb(54, 162, 235)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                        'rgb(255, 159, 64)',
                        'rgb(255, 99, 132)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $units->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
