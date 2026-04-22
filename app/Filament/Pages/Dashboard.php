<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AbsenceReasonChart;
use App\Filament\Widgets\AttendanceOverviewChart;
use App\Filament\Widgets\ContractExpirationsStat;
use App\Filament\Widgets\ContractStatsWidget;
use App\Filament\Widgets\ContractTypeChart;
use App\Filament\Widgets\DailyAbsenceStat;
use App\Filament\Widgets\EmployeesByUnitChart;
use App\Filament\Widgets\EmployeeStatsWidget;
use App\Filament\Widgets\SalaryByLevelStat;
use App\Filament\Widgets\TotalPayrollStat;
use App\Filament\Widgets\UnitDensityChart;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-home';
    }

    public function getWidgets(): array
    {
        return [
            EmployeeStatsWidget::class,
            ContractStatsWidget::class,
            ContractExpirationsStat::class,
            EmployeesByUnitChart::class,
            AttendanceOverviewChart::class,
            TotalPayrollStat::class,
            ContractTypeChart::class,
            UnitDensityChart::class,
            SalaryByLevelStat::class,
            DailyAbsenceStat::class,
            AbsenceReasonChart::class,
        ];
    }

    public function getColumns(): array|int
    {
        return [
            'default' => 'full',
            'md' => 2,
            'xl' => 3,
        ];
    }
}
