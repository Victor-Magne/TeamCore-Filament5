<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class ContractTypeChart extends ChartWidget
{
    protected static ?int $sort = 39;

    protected ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'xl' => 1,
    ];

    public static function canView(): bool
    {
        return Auth::user()?->can('View:ContractTypeChart') ?? false;
    }

    public function getHeading(): ?string
    {
        return 'Portfolio de Contratos';
    }

    public function getDescription(): ?string
    {
        return 'Percentagem de tipos de contrato ativos';
    }

    protected function getData(): array
    {
        $contractTypes = Contract::where('status', 'active')
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        if ($contractTypes->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $totalContracts = $contractTypes->sum();

        $labels = $contractTypes->keys()->map(function ($type) {
            return match ($type) {
                'permanent' => 'Sem Termo',
                'fixed_term' => 'A Termo Certo',
                'unfixed_term' => 'A Termo Incerto',
                'service_provision' => 'Prestação Serviços',
                'internship' => 'Estágio',
                default => ucfirst(str_replace('_', ' ', $type)),
            };
        })->values();

        $percentages = $contractTypes->map(function ($count) use ($totalContracts) {
            return $totalContracts > 0 ? round(($count / $totalContracts) * 100, 1) : 0;
        })->values();

        $colors = [
            'rgba(16, 185, 129, 0.7)',   // emerald
            'rgba(59, 130, 246, 0.7)',   // blue
            'rgba(245, 158, 11, 0.7)',   // amber
            'rgba(239, 68, 68, 0.7)',    // red
            'rgba(139, 92, 246, 0.7)',   // violet
        ];

        $borderColors = [
            'rgba(5, 150, 105, 1)',
            'rgba(30, 64, 175, 1)',
            'rgba(217, 119, 6, 1)',
            'rgba(220, 38, 38, 1)',
            'rgba(124, 58, 237, 1)',
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Percentagem (%)',
                    'data' => $percentages->toArray(),
                    'backgroundColor' => array_slice($colors, 0, count($contractTypes)),
                    'borderColor' => array_slice($borderColors, 0, count($contractTypes)),
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
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
