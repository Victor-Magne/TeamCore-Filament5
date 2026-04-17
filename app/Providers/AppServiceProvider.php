<?php

namespace App\Providers;

use App\Models\Absence;
use App\Models\AttendanceLog;
use App\Models\Contract;
use App\Models\Employee;
use App\Observers\AbsenceObserver;
use App\Observers\AttendanceLogObserver;
use App\Observers\ContractObserver;
use App\Observers\EmployeeObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\Hour\HourBankService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Contract::observe(ContractObserver::class);
        Employee::observe(EmployeeObserver::class);
        AttendanceLog::observe(AttendanceLogObserver::class);
        Absence::observe(AbsenceObserver::class);
    }
}
