<?php

/**
 * Ficheiro do Provedor de Serviços AppServiceProvider.
 *
 * Este é o provedor central da aplicação onde são registados os serviços singleton,
 * configurados os parâmetros globais de ambiente (como codificação UTF-8)
 * e activados os Observers para automatização de eventos nos modelos Eloquent.
 */

namespace App\Providers;

use App\Models\Absence;
use App\Models\AttendanceLog;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\LeaveAndAbsence;
use App\Models\Vacation;
use App\Observers\AbsenceObserver;
use App\Observers\AttendanceLogObserver;
use App\Observers\ContractObserver;
use App\Observers\EmployeeObserver;
use App\Observers\LeaveAndAbsenceObserver;
use App\Observers\VacationObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Regista serviços no contentor de dependências da aplicação.
     */
    public function register(): void
    {
        //
    }

    /**
     * Inicializa os serviços de bootstrap da aplicação.
     */
    public function boot(): void
    {
        // Força a codificação interna para UTF-8 para garantir que a acentuação
        // em Português é tratada correctamente em todas as operações de string.
        if (extension_loaded('mbstring')) {
            mb_internal_encoding('UTF-8');
        }

        // Garante que a serialização JSON e a saída padrão utilizam UTF-8.
        ini_set('default_charset', 'UTF-8');

        // Activação dos Observers para automação de lógica de negócio em eventos da BD
        Contract::observe(ContractObserver::class);
        Employee::observe(EmployeeObserver::class);
        AttendanceLog::observe(AttendanceLogObserver::class);
        Absence::observe(AbsenceObserver::class);
        LeaveAndAbsence::observe(LeaveAndAbsenceObserver::class);
        Vacation::observe(VacationObserver::class);
    }
}
