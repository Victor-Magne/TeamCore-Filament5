<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ContractExpirationsStat extends StatsOverviewWidget
{
    protected static ?int $sort = 39;

    protected ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 2,
        'xl' => 1,
    ];

    public static function canView(): bool
    {
        return Auth::user()?->can('View:ContractExpirationsStat') ?? false;
    }

    public function getHeading(): ?string
    {
        return 'Vencimentos de Contratos';
    }

    public function getDescription(): ?string
    {
        return 'Monitoramento de contratos em vencimento próximo';
    }

    protected function getStats(): array
    {
        $today = Carbon::today();
        $sevenDaysFromNow = $today->addDays(7);
        $thirtyDaysFromNow = $today->addDays(30);

        $criticalExpiring = Contract::where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', $sevenDaysFromNow)
            ->where('end_date', '>=', $today)
            ->count();

        $expiringSoon = Contract::where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '>', $sevenDaysFromNow)
            ->where('end_date', '<=', $thirtyDaysFromNow)
            ->count();

        $allExpiringInMonth = Contract::where('status', 'active')
            ->whereNotNull('end_date')
            ->whereMonth('end_date', $today->month)
            ->count();

        return [
            Stat::make('Críticos (< 7 dias)', $criticalExpiring)
                ->description('Ação imediata necessária')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($criticalExpiring > 0 ? 'danger' : 'success'),
            Stat::make('Próximos 30 Dias', $expiringSoon)
                ->description('Contratos a vencer em breve')
                ->descriptionIcon('heroicon-m-calendar')
                ->color($expiringSoon > 0 ? 'warning' : 'success'),
            Stat::make('Vencimentos Este Mês', $allExpiringInMonth)
                ->description('Total de vencimentos mensais')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
        ];
    }
}
