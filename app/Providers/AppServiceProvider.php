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
use App\Services\Hour\HourBankService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(HourBankService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurar o charset UTF-8 para todo o PHP
        if (extension_loaded('mbstring')) {
            mb_internal_encoding('UTF-8');
        }

        // Garantir que json_encode funciona com UTF-8
        ini_set('default_charset', 'UTF-8');

        Contract::observe(ContractObserver::class);
        Employee::observe(EmployeeObserver::class);
        AttendanceLog::observe(AttendanceLogObserver::class);
        Absence::observe(AbsenceObserver::class);
    }
}
