<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HourBankStatsWidget extends BaseWidget
{
    protected static ?int $sort = -1;

    public static function canView(): bool
    {
        return auth()->user()?->can('View:HourBankStatsWidget') ?? false;
    }

    protected function getStats(): array
    {
        $employee = auth()->user()?->employee;

        if (! $employee) {
            return [];
        }

        $currentMonth = $employee->getCurrentHourBankBalance();
        $totalBalance = $employee->getTotalHourBankBalance();

        $currentBalance = $currentMonth?->balance ?? 0;
        $extraHoursAdded = $currentMonth?->extra_hours_added ?? 0;
        $extraHoursUsed = $currentMonth?->extra_hours_used ?? 0;

        $formatTime = function (int $minutes): string {
            $hours = intdiv(abs($minutes), 60);
            $mins = abs($minutes) % 60;
            $sign = $minutes < 0 ? '-' : '';

            return "{$sign}{$hours}h {$mins}m";
        };

        return [
            Stat::make('Saldo Atual (MÃªs)', $formatTime($currentBalance))
                ->description('Ciclo: ' . ($currentMonth?->month_year ?? now()->format('Y-m')))
                ->color($currentBalance >= 0 ? 'success' : 'danger')
                ->icon($currentBalance >= 0 ? 'heroicon-m-plus-circle' : 'heroicon-m-minus-circle'),
            Stat::make('Extras / Faltas', $formatTime($extraHoursAdded) . ' / ' . $formatTime($extraHoursUsed))
                ->description('Ganhos vs DÃ©bitos (MÃªs)')
                ->color('info')
                ->icon('heroicon-m-arrows-right-left'),
            Stat::make('Saldo Acumulado', $formatTime($totalBalance))
                ->description('Total atÃ© Ã  data')
                ->color($totalBalance >= 0 ? 'success' : 'danger')
                ->icon('heroicon-m-chart-bar-square'),
        ];
    }
}
