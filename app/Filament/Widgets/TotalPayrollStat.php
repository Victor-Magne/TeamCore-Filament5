<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TotalPayrollStat extends StatsOverviewWidget
{
    protected static ?int $sort = 10;

    protected ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 2,
        'xl' => 2,
    ];

    public static function canView(): bool
    {
        return Auth::user()?->can('View:TotalPayrollStat') ?? false;
    }

    public function getHeading(): ?string
    {
        return 'Folha de Pagamento';
    }

    public function getDescription(): ?string
    {
        return 'Visão geral de custos com contratos ativos';
    }

    protected function getStats(): array
    {
        $activeContractsQuery = $this->getActiveContractsQuery();

        $totalPayroll = (clone $activeContractsQuery)->sum('salary');
        $avgPayroll = (clone $activeContractsQuery)->avg('salary') ?? 0;
        $maxSalary = (clone $activeContractsQuery)->max('salary') ?? 0;

        // Calcula crescimento (comparação com mês anterior)
        $lastMonthStart = Carbon::today()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::today()->subMonth()->endOfMonth();

        $lastMonthPayroll = Contract::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', Carbon::today());
            })
            ->sum('salary');

        $payrollTrend = $lastMonthPayroll > 0
            ? ((($totalPayroll - $lastMonthPayroll) / $lastMonthPayroll) * 100)
            : 0;

        $trendIcon = match (true) {
            $payrollTrend > 0 => 'heroicon-m-arrow-trending-up',
            $payrollTrend < 0 => 'heroicon-m-arrow-trending-down',
            default => 'heroicon-m-minus',
        };

        return [
            Stat::make('Folha de Pagamento Total', '€ '.number_format($totalPayroll, 2, ',', '.'))
                ->description(abs(round($payrollTrend, 1)).'% '.($payrollTrend >= 0 ? 'crescimento' : 'redução'))
                ->descriptionIcon($trendIcon)
                ->color('success'),
            Stat::make('Salário Médio', '€ '.number_format($avgPayroll, 2, ',', '.'))
                ->description('Média entre contratos ativos')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
            Stat::make('Salário Máximo', '€ '.number_format($maxSalary, 2, ',', '.'))
                ->description('Maior contrato ativo')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),
        ];
    }

    private function getActiveContractsQuery()
    {
        return Contract::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', Carbon::today());
            });
    }
}
