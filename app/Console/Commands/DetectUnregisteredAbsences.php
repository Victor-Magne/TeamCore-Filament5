<?php

namespace App\Console\Commands;

use App\Models\Absence;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\LeaveAndAbsence;
use App\Models\Vacation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DetectUnregisteredAbsences extends Command
{
    protected $signature = 'absences:detect-unregistered {--date= : Data especifica (YYYY-MM-DD) ou vazio para ontem}';

    protected $description = 'Detecta funcionarios que nao bateram ponto e cria ausencias automaticamente';

    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::createFromFormat('Y-m-d', $this->option('date'))
            : Carbon::yesterday();

        if ($date->isWeekend()) {
            $this->info("Data {$date->format('Y-m-d')} e fim de semana. Nenhuma verificacao necessaria.");

            return self::SUCCESS;
        }

        $this->info("Procurando faltas nao registadas para {$date->format('d/m/Y')}...");

        $employees = Employee::whereHas('contracts', function ($query) use ($date) {
            $query->where('status', 'active')
                ->whereDate('start_date', '<=', $date->toDateString())
                ->where(function ($query) use ($date) {
                    $query->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $date->toDateString());
                });
        })->get();

        $this->info("Encontrados {$employees->count()} funcionarios com contrato ativo.");

        $absencesCreated = 0;

        foreach ($employees as $employee) {
            $hasAttendanceLog = AttendanceLog::where('employee_id', $employee->id)
                ->whereDate('time_in', $date->toDateString())
                ->exists();

            if ($hasAttendanceLog) {
                continue;
            }

            $hasLeave = LeaveAndAbsence::where('employee_id', $employee->id)
                ->whereDate('start_date', '<=', $date->toDateString())
                ->whereDate('end_date', '>=', $date->toDateString())
                ->where('status', 'approved')
                ->exists();

            if ($hasLeave) {
                continue;
            }

            $hasVacation = Vacation::where('employee_id', $employee->id)
                ->whereDate('start_date', '<=', $date->toDateString())
                ->whereDate('end_date', '>=', $date->toDateString())
                ->where('status', 'approved')
                ->exists();

            if ($hasVacation) {
                continue;
            }

            $existingAbsence = Absence::where('employee_id', $employee->id)
                ->whereDate('absence_date', $date->toDateString())
                ->exists();

            if ($existingAbsence) {
                continue;
            }

            $contract = $employee->contracts()
                ->where('status', 'active')
                ->whereDate('start_date', '<=', $date->toDateString())
                ->where(function ($query) use ($date) {
                    $query->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $date->toDateString());
                })
                ->orderByDesc('start_date')
                ->first();

            $dailyWorkMinutes = $contract?->daily_work_minutes ?? 480;

            Absence::create([
                'employee_id' => $employee->id,
                'absence_date' => $date->toDateString(),
                'hours_deducted' => $dailyWorkMinutes,
                'deduction_type' => 'unjustified_absence',
                'reason' => sprintf(
                    'Falta automatica detectada: funcionario nao registou ponto em %s',
                    $date->format('d/m/Y')
                ),
            ]);

            $absencesCreated++;
            $this->line("  * Falta criada para {$employee->first_name} {$employee->last_name}");
        }

        $this->info("Processo concluido. {$absencesCreated} falta(s) criada(s).");

        return self::SUCCESS;
    }
}
