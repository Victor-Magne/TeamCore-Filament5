<?php

/**
 * Ficheiro do Widget HourBankStatsWidget.
 *
 * Este widget apresenta de forma visual e rápida as estatísticas do Banco de Horas
 * do funcionário autenticado. Exibe o saldo do mês corrente, a relação entre
 * horas extra ganhas e ausências, e o saldo total acumulado.
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
        // Visível para todos os utilizadores autenticados que tenham perfil de funcionário
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

        // Obtém dados do banco de horas
        $currentMonth = $employee->getCurrentHourBankBalance();
        $totalBalance = $employee->getTotalHourBankBalance();

        $currentBalance = $currentMonth?->balance ?? 0;
        $extraHoursAdded = $currentMonth?->extra_hours_added ?? 0;
        $extraHoursUsed = $currentMonth?->extra_hours_used ?? 0;

        /**
         * Função auxiliar para converter minutos em formato legível "Xh Ym".
         * Mantém o sinal negativo se o valor for inferior a zero.
         */
        $formatTime = function (int $minutes): string {
            $hours = intdiv(abs($minutes), 60);
            $mins = abs($minutes) % 60;
            $sign = $minutes < 0 ? '-' : '';

            return "{$sign}{$hours}h {$mins}m";
        };

        return [
            // Cartão 1: Saldo específico do mês actual
            Stat::make('Saldo Actual (Mês)', $formatTime($currentBalance))
                ->description('Ciclo: ' . ($currentMonth?->month_year ?? now()->format('Y-m')))
                ->color($currentBalance >= 0 ? 'success' : 'danger')
                ->icon($currentBalance >= 0 ? 'heroicon-m-plus-circle' : 'heroicon-m-minus-circle'),

            // Cartão 2: Resumo de ganhos vs perdas no mês
            Stat::make('Extras / Faltas', $formatTime($extraHoursAdded) . ' / ' . $formatTime($extraHoursUsed))
                ->description('Ganhos vs Débitos (Mês)')
                ->color('info')
                ->icon('heroicon-m-arrows-right-left'),

            // Cartão 3: Saldo total acumulado transportado de meses anteriores
            Stat::make('Saldo Acumulado', $formatTime($totalBalance))
                ->description('Total até à data')
                ->color($totalBalance >= 0 ? 'success' : 'danger')
                ->icon('heroicon-m-chart-bar-square'),
        ];
    }
}
