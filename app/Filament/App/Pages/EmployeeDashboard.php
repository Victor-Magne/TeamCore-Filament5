<?php

namespace App\Filament\App\Pages;

use App\Filament\App\Widgets\EmployeeActionsWidget;
use App\Filament\App\Widgets\EmployeeContractWidget;
use App\Filament\App\Widgets\EmployeeInfoWidget;
use App\Filament\App\Widgets\EmployeeLeaveWidget;
use App\Filament\App\Widgets\EmployeeVacationWidget;
use App\Filament\Widgets\HourBankStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class EmployeeDashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            EmployeeInfoWidget::class,
            EmployeeContractWidget::class,
            EmployeeActionsWidget::class,
            HourBankStatsWidget::class,
            EmployeeVacationWidget::class,
            EmployeeLeaveWidget::class,
        ];
    }

    public function getColumns(): array|int
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 3,
        ];
    }
}
