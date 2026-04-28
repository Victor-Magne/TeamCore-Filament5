<?php

/**
 * Ficheiro do Comando Artisan CheckDailyAttendance.
 *
 * Este comando é responsável pela verificação automática de assiduidade.
 * Identifica funcionários que deveriam ter trabalhado numa determinada data mas não
 * registaram qualquer ponto, aplicando automaticamente uma falta injustificada
 * e o respectivo desconto no banco de horas.
 */

namespace App\Console\Commands;

use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Services\Hour\DeductHourBankService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckDailyAttendance extends Command
{
    /**
     * Assinatura do comando na consola.
     * Pode ser executado como: php artisan app:check-daily-attendance {data?}
     *
     * @var string
     */
    protected $signature = 'app:check-daily-attendance {date? : A data para verificar (YYYY-MM-DD), por defeito ontem}';

    /**
     * Descrição do comando exibida na lista do Artisan.
     *
     * @var string
     */
    protected $description = 'Verifica faltas para o dia anterior ou uma data específica';

    /**
     * Executa a lógica do comando.
     *
     * Utiliza o DeductHourBankService para aplicar as regras de negócio de descontos.
     *
     * @param DeductHourBankService $deductService
     */
    public function handle(DeductHourBankService $deductService)
    {
        // Define a data de verificação (ontem se nenhuma for fornecida)
        $dateStr = $this->argument('date') ?: Carbon::yesterday()->toDateString();
        $date = Carbon::parse($dateStr);

        $this->info("A verificar presenças para: {$date->toDateString()}");

        // Obtém apenas funcionários que tinham contrato activo na data em questão
        $employees = Employee::whereHas('contracts', function ($query) use ($date) {
            $query->where('status', 'active')
                ->whereDate('start_date', '<=', $date)
                ->where(function ($q) use ($date) {
                    $q->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $date);
                });
        })->get();

        $count = 0;
        foreach ($employees as $employee) {
            // Verifica se o funcionário registou algum ponto (entrada) no dia
            $hasLog = AttendanceLog::where('employee_id', $employee->id)
                ->whereDate('time_in', $date)
                ->exists();

            if (! $hasLog) {
                // Se não há registo, o serviço verifica se deve aplicar falta.
                // O serviço já ignora automaticamente fins de semana, férias e licenças aprovadas.
                if (! $deductService->shouldSkipDeduction($employee->id, $date)) {
                    $deductService->registerFullAbsence($employee->id, $date, 'Falta automática: Sem registo de ponto');
                    $count++;
                    $this->line("Falta registada para: {$employee->full_name}");
                }
            }
        }

        $this->info("Verificação concluída. {$count} faltas registadas.");

        return self::SUCCESS;
    }
}
