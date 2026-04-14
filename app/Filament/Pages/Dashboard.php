<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\EmployeeStatsWidget;
use App\Filament\Widgets\ContractStatsWidget;
use App\Filament\Widgets\EmployeesByUnitChart;
use App\Filament\Widgets\AttendanceOverviewChart;
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
            EmployeesByUnitChart::class,
            AttendanceOverviewChart::class,
        ];
    }

    public function getColumns(): array|int
    {
        return [
            'lg' => 2,
        ];
    }
}
