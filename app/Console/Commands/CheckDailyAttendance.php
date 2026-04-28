<?php

namespace App\Console\Commands;

use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Services\Hour\DeductHourBankService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckDailyAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-daily-attendance {date? : The date to check (YYYY-MM-DD), defaults to yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica faltas para o dia anterior ou uma data específica';

    /**
     * Execute the console command.
     */
    public function handle(DeductHourBankService $deductService)
    {
        $dateStr = $this->argument('date') ?: Carbon::yesterday()->toDateString();
        $date = Carbon::parse($dateStr);

        $this->info("Verificando presenças para: {$date->toDateString()}");

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
            // Verificar se já existe ponto para este dia
            $hasLog = AttendanceLog::where('employee_id', $employee->id)
                ->whereDate('time_in', $date)
                ->exists();

            if (! $hasLog) {
                // Tenta registar falta (o serviço já valida férias/licenças/fins de semana)
                if (! $deductService->shouldSkipDeduction($employee->id, $date)) {
                    $deductService->registerFullAbsence($employee->id, $date, 'Falta automática: Sem registo de ponto');
                    $count++;
                    $this->line("Falta registada para: {$employee->full_name}");
                }
            }
        }

        $this->info("Concluído. {$count} faltas registadas.");

        return self::SUCCESS;
    }
}
