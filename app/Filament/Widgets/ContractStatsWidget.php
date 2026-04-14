<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class ContractStatsWidget extends StatsOverviewWidget
{
    // CORREÇÃO: Verifica permissão do Shield
    public static function canView(): bool
    {
        return Auth::user()?->can('View:ContractStatsWidget') ?? false;
    }

    protected function getStats(): array
    {
        $activeContracts = Contract::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', Carbon::today());
            })
            ->count();

        $expiredContracts = Contract::where('status', 'active')
            ->where('end_date', '<', Carbon::today())
            ->count();

        $totalContracts = Contract::count();

        return [
            Stat::make('Total de Contratos', $totalContracts)
                ->description('Todos os contratos')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
            Stat::make('Contratos Ativos', $activeContracts)
                ->description('Vigentes e válidos')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Contratos Expirados', $expiredContracts)
                ->description('Vencidos')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('warning'),
        ];
    }
}
