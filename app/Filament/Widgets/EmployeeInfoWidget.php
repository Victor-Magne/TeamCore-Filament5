<?php

namespace App\Filament\App\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class EmployeeInfoWidget extends Widget
{
    protected string $view = 'filament.app.widgets.employee-info-widget';

    protected int|string|array $columnSpan = 1;

    public function getEmployee()
    {
        return Auth::user()->employee;
    }
}
