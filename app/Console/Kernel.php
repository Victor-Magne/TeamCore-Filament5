<?php

namespace App\Console;

use App\Console\Commands\DetectUnregisteredAbsences;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define o schedule de comandos que devem ser executados.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Executar diariamente à 00:30 (meia noite e meia)
        // Detecta faltas não registadas do dia anterior
        $schedule->command(DetectUnregisteredAbsences::class)
            ->dailyAt('00:30')
            ->timezone('Europe/Lisbon') // Ajuste para seu fuso horário
            ->name('detect-unregistered-absences')
            ->description('Detecta funcionários que não bateram ponto');
    }

    /**
     * Register os comandos da aplicação.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
