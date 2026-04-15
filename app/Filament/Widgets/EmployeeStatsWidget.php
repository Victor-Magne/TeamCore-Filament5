<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 20;

    protected ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 2,
        'xl' => 2,
    ];

    public static function canView(): bool
    {
        return Auth::user()?->can('View:EmployeeStatsWidget') ?? false;
    }

    public function getHeading(): ?string
    {
        return 'Força de Trabalho';
    }

    public function getDescription(): ?string
    {
        return 'Panorama geral de funcionários ativos e histórico';
    }

    protected function getStats(): array
    {
        $today = Carbon::today();

        // Total de funcionários ativos (sem date_dismissed ou dismissidos no futuro)
        $activeEmployees = Employee::where(function ($query) use ($today) {
            $query->whereNull('date_dismissed')
                ->orWhere('date_dismissed', '>', $today);
        })->count();

        // Total de funcionários (incluindo antigos)
        $totalEmployees = Employee::count();

        // Funcionários despedidos
        $dismissedEmployees = $totalEmployees - $activeEmployees;

        // Funcionários contratados este mês
        $hiredThisMonth = Employee::whereMonth('date_hired', $today->month)
            ->whereYear('date_hired', $today->year)
            ->count();

        // Taxa de rotatividade
        $turnoverRate = $totalEmployees > 0 ? round(($dismissedEmployees / $totalEmployees) * 100, 1) : 0;
        $turnoverColor = match (true) {
            $turnoverRate > 10 => 'warning',
            $turnoverRate > 20 => 'danger',
            default => 'success',
        };

        return [
            Stat::make('Funcionários Ativos', $activeEmployees)
                ->description('Força de trabalho operacional')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            Stat::make('Total na Base', $totalEmployees)
                ->description('Histórico completo')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make('Taxa de Rotatividade', $turnoverRate.'%')
                ->description($dismissedEmployees.' desligados')
                ->descriptionIcon('heroicon-m-arrow-right-end-on-rectangle')
                ->color($turnoverColor),
        ];
    }
}
