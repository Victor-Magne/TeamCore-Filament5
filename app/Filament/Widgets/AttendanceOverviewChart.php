<?php

namespace App\Filament\Widgets;

use App\Models\LeaveAndAbsence;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class AttendanceOverviewChart extends ChartWidget
{
    protected ?string $heading = 'Distribuição de Ausências';

    // CORREÇÃO: Verifica permissão do Shield
    public static function canView(): bool
    {
        return Auth::user()?->can('View:AttendanceOverviewChart') ?? false;
    }
    protected function getData(): array
    {
        $absencesByType = LeaveAndAbsence::query()
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        return [
            'datasets' => [
                [
                    'label' => 'Cantidad de Ausências',
                    'data' => $absencesByType->values()->toArray(),
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)',
                    ],
                    'borderColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 206, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $absencesByType->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
