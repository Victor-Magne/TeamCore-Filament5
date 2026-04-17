<?php

namespace App\Providers;

use App\Models\AttendanceLog;
use App\Models\Contract;
use App\Models\Employee;
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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Contract::observe(ContractObserver::class);
        Employee::observe(EmployeeObserver::class);
        AttendanceLog::observe(AttendanceLogObserver::class);
    }
}
