<?php

namespace App\Filament\Widgets;

use App\Models\Unit;
use Filament\Widgets\ChartWidget;

use Illuminate\Support\Facades\Auth;

class UnitDensityChart extends ChartWidget
{
    public static function canView(): bool
    {
        return Auth::user()?->can('View:UnitDensityChart') ?? false;
    }

    protected ?string $heading = 'Densidade por Unidade';

    protected static ?int $sort = 30;

    protected function getData(): array
    {
        $units = Unit::withCount('employees')->get();

        return [
            'datasets' => [
                [
                    'label' => 'Funcionários',
                    'data' => $units->pluck('employees_count')->toArray(),
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
