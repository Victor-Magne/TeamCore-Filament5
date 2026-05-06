<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use App\Models\Vacation;
use App\Models\LeaveAndAbsence;

class TeamStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $meuEmployee = $user?->employee;

        if (!$meuEmployee) {
            return [];
        }

        $employeeIds = $meuEmployee->getAllSubordinateEmployeeIds();

        // Team Size
        $teamSize = count($employeeIds);

        // Pending Requests
        $pendingVacations = Vacation::whereIn('employee_id', $employeeIds)->where('status', 'pending')->count();
        $pendingLeaves = LeaveAndAbsence::whereIn('employee_id', $employeeIds)->where('status', 'pending')->count();

        // Team Hour Bank Balance (Total)
        $teamBalance = Employee::whereIn('id', $employeeIds)
            ->with('hourBank')
            ->get()
            ->sum(fn($emp) => $emp->hourBank?->balance ?? 0);

        $hours = floor(abs($teamBalance) / 60);
        $minutes = abs($teamBalance) % 60;
        $balanceFormatted = ($teamBalance < 0 ? '-' : '') . "{$hours}h {$minutes}m";

        return [
            Stat::make('Tamanho da Equipa', $teamSize)
                ->description('Total de colaboradores sob gestão')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
            Stat::make('Pedidos Pendentes', $pendingVacations + $pendingLeaves)
                ->description("{$pendingVacations} Férias / {$pendingLeaves} Licenças")
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Saldo Global da Equipa', $balanceFormatted)
                ->description('Horas acumuladas no banco de horas')
                ->descriptionIcon('heroicon-m-scale')
                ->color($teamBalance >= 0 ? 'success' : 'danger'),
        ];
    }
}
