<?php

namespace App\Filament\Widgets;

use App\Models\LeaveAndAbsence;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AbsenceReasonChart extends ChartWidget
{
    protected static ?int $sort = 32;

    protected ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'xl' => 1,
    ];

    public static function canView(): bool
    {
        return Auth::user()?->can('View:AbsenceReasonChart') ?? false;
    }

    public function getHeading(): ?string
    {
        return 'Motivos de Ausência';
    }

    public function getDescription(): ?string
    {
        return 'Últimos 30 dias - Distribuição por motivo';
    }

    protected function getData(): array
    {
        $thirtyDaysAgo = Carbon::today()->subDays(30);

        // Agrupa faltas por tipo otimizado
        $absencesByType = LeaveAndAbsence::where('start_date', '>=', $thirtyDaysAgo)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        if ($absencesByType->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $labels = $absencesByType->keys()->map(function ($type) {
            return match ($type) {
                'sick_leave' => 'Baixa Médica',
                'parental' => 'Licença Parental',
                'marriage' => 'Casamento',
                'bereavement' => 'Falecimento',
                'justified_absence' => 'Falta Justificada',
                'unjustified' => 'Falta Injustificada',
                default => ucfirst(str_replace('_', ' ', $type)),
            };
        })->values();

        $colors = [
            'rgba(239, 68, 68, 0.7)',    // red - sick
            'rgba(245, 158, 11, 0.7)',   // amber - parental
            'rgba(139, 92, 246, 0.7)',   // purple - marriage
            'rgba(99, 102, 241, 0.7)',   // indigo - bereavement
            'rgba(59, 130, 246, 0.7)',   // blue - justified
            'rgba(236, 72, 153, 0.7)',   // pink - unjustified
        ];

        $borderColors = [
            'rgba(220, 38, 38, 1)',
            'rgba(217, 119, 6, 1)',
            'rgba(124, 58, 237, 1)',
            'rgba(79, 70, 229, 1)',
            'rgba(30, 64, 175, 1)',
            'rgba(190, 24, 93, 1)',
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Quantidade de Faltas',
                    'data' => $absencesByType->values()->toArray(),
                    'backgroundColor' => array_slice($colors, 0, count($absencesByType)),
                    'borderColor' => array_slice($borderColors, 0, count($absencesByType)),
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected ?string $maxHeight = '300px';

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
