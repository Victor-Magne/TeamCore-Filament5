<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HourBankStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    /**
     * Mostrar este widget apenas na página do Employee
     */
    public static function canView(): bool
    {
        // Mostrar apenas em contextos específicos se necessário
        return true;
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

        // Converter minutos para horas com símbolos
        $formatTime = function (int $minutes): string {
            $hours = intdiv(abs($minutes), 60);
            $mins = abs($minutes) % 60;
            $sign = $minutes < 0 ? '-' : '';

            return "{$sign}{$hours}h {$mins}m";
        };

        return [
            Stat::make('Saldo Atual (Mês)', $formatTime($currentBalance))
                ->description('Mês: '.($currentMonth?->month_year ?? 'N/A'))
                ->color($currentBalance >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-clock'),

            Stat::make('Horas Extras Adicionadas', $formatTime($extraHoursAdded))
                ->description('Este mês')
                ->color('info')
                ->icon('heroicon-o-arrow-up'),

            Stat::make('Horas Descontadas', $formatTime($extraHoursUsed))
                ->description('Este mês')
                ->color('warning')
                ->icon('heroicon-o-arrow-down'),

            Stat::make('Saldo Acumulado', $formatTime($totalBalance))
                ->description('Todos os meses')
                ->color($totalBalance >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-chart-bar'),
        ];
    }
}
