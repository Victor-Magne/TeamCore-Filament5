<?php

namespace App\Filament\App\Pages;

use App\Filament\Widgets\EmployeeActionsWidget;
use App\Filament\Widgets\EmployeeContractWidget;
use App\Filament\Widgets\EmployeeInfoWidget;
use App\Filament\Widgets\EmployeeLeaveWidget;
use App\Filament\Widgets\EmployeeVacationWidget;
use App\Filament\Widgets\HourBankStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class EmployeeDashboard extends BaseDashboard
{
    public static function canAccess(): bool
    {
        $user = Auth::user();

        return ($user?->can('View:Dashboard') ?? false) && filled($user?->employee_id);
    }

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
