<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ContractStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 38;

    protected ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 2,
        'xl' => 2,
    ];

    public static function canView(): bool
    {
        return Auth::user()?->can('View:ContractStatsWidget') ?? false;
    }

    public function getHeading(): ?string
    {
        return 'Contratos';
    }

    public function getDescription(): ?string
    {
        return 'Análise de status dos contratos por validade';
    }

    protected function getStats(): array
    {
        $today = Carbon::today();

        $activeContracts = Contract::where('status', 'active')
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $today);
            })
            ->count();

        $expiredContracts = Contract::where('status', 'active')
            ->where('end_date', '<', $today)
            ->count();

        $totalContracts = Contract::count();

        $activePercentage = $totalContracts > 0 ? round(($activeContracts / $totalContracts) * 100, 1) : 0;

        return [
            Stat::make('Contratos Ativos', $activeContracts)
                ->description('Vigentes e válidos')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Taxa de Atividade', $activePercentage.'%')
                ->description('Percentagem de ativos')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color('info'),
            Stat::make('Expirados', $expiredContracts)
                ->description('Requerem renovação')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($expiredContracts > 0 ? 'danger' : 'success'),
        ];
    }
}
