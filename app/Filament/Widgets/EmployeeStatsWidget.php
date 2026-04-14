<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class EmployeeStatsWidget extends StatsOverviewWidget
{
    // CORREÇÃO: Verifica permissão do Shield
    public static function canView(): bool
    {
        return Auth::user()?->can('View:EmployeesByUnitChart') ?? false;
    }

    protected function getStats(): array
    {
        $activeEmployees = Employee::whereNull('date_dismissed')->count();
        $inactiveEmployees = Employee::whereNotNull('date_dismissed')->count();
        $totalEmployees = Employee::count();

        return [
            Stat::make('Total de Funcionários', $totalEmployees)
                ->description('Todos os funcionários')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make('Funcionários Ativos', $activeEmployees)
                ->description('Sem data de desligamento')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Funcionários Inativos', $inactiveEmployees)
                ->description('Com data de desligamento')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
