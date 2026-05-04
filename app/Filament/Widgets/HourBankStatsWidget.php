<?php

/**
 * Ficheiro do Widget HourBankStatsWidget.
 *
 * Este widget apresenta de forma visual e rápida as estatísticas do Banco de Horas
 * do funcionário autenticado. Exibe o saldo acumulado total e o resumo do mês corrente.
 */

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HourBankStatsWidget extends BaseWidget
{
    /**
     * Define a ordem de exibição no dashboard.
     * Valores negativos garantem que aparece no topo.
     */
    protected static ?int $sort = -1;

    /**
     * Determina se o widget deve ser visível para o utilizador actual.
     */
    public static function canView(): bool
    {
        return auth()->user()?->employee !== null;
    }

    /**
     * Prepara os dados estatísticos para exibição no widget.
     *
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $employee = auth()->user()?->employee;

        if (! $employee) {
            return [];
        }

        // Obtém dados do banco de horas acumulado
        $totalBalance = $employee->getTotalHourBankBalance();

        // Obtém estatísticas do mês corrente para detalhe
        $currentMonthStats = $employee->getMonthlyHourBankStats(now()->format('Y-m'));
        $extraHoursAdded = $currentMonthStats['added'];
        $extraHoursUsed = $currentMonthStats['used'];

        /**
         * Função auxiliar para converter minutos em formato legível "Xh Ym".
         */
        $formatTime = function (int $minutes): string {
            $hours = intdiv(abs($minutes), 60);
            $mins = abs($minutes) % 60;
            $sign = $minutes < 0 ? '-' : '';

            return "{$sign}{$hours}h {$mins}m";
        };

        return [
            // Cartão 1: Saldo total acumulado
            Stat::make('Saldo Total Acumulado', $formatTime($totalBalance))
                ->description('Estado actual do banco de horas')
                ->color($totalBalance >= 0 ? 'success' : 'danger')
                ->icon('heroicon-m-chart-bar-square'),

            // Cartão 2: Resumo de ganhos no mês actual
            Stat::make('Ganhos (Este Mês)', $formatTime($extraHoursAdded))
                ->description('Horas extra registadas em ' . now()->format('m/Y'))
                ->color('success')
                ->icon('heroicon-m-plus-circle'),

            // Cartão 3: Resumo de débitos no mês actual
            Stat::make('Descontos (Este Mês)', $formatTime($extraHoursUsed))
                ->description('Faltas/Atrasos em ' . now()->format('m/Y'))
                ->color('danger')
                ->icon('heroicon-m-minus-circle'),
        ];
    }
}
