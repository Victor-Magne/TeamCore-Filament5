<?php

namespace App\Console\Commands;

use App\Models\Absence;
use App\Models\AttendanceLog;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\LeaveAndAbsence;
use App\Models\Vacation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DetectUnregisteredAbsences extends Command
{
    /**
     * O nome e a descrição do comando.
     */
    protected $signature = 'absences:detect-unregistered {--date= : Data específica (YYYY-MM-DD) ou vazio para ontem}';

    protected $description = 'Detecta funcionários que não bateram ponto (faltas não registadas) e cria Absences automaticamente';

    /**
     * Execute o comando.
     */
    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::createFromFormat('Y-m-d', $this->option('date'))
            : Carbon::yesterday();

        // Não processar fins de semana
        if ($date->isWeekend()) {
            $this->info("Data {$date->format('Y-m-d')} é fim de semana. Nenhuma verificação necessária.");
            return self::SUCCESS;
        }

        $this->info("Procurando faltas não registadas para {$date->format('d/m/Y')}...");

        // 1. Obter todos os funcionários com contrato ativo nesta data
        $employees = Employee::whereHas('contracts', function ($q) use ($date) {
            $q->where('status', 'active')
                ->where('start_date', '<=', $date->toDateString())
                ->where(function ($q) use ($date) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $date->toDateString());
                });
        })->get();

        $this->info("Encontrados {$employees->count()} funcionários com contrato ativo.");

        $abencesCreated = 0;
        $monthYear = $date->format('Y-m');

        foreach ($employees as $employee) {
            // 2. Verificar se tem um AttendanceLog nesta data
            $hasAttendanceLog = AttendanceLog::where('employee_id', $employee->id)
                ->whereDate('time_in', $date->toDateString())
                ->exists();

            if ($hasAttendanceLog) {
                // Se tem registro de ponto, não é falta não registada
                continue;
            }

            // 3. Verificar se tem licença ou férias aprovadas
            $hasLeave = LeaveAndAbsence::where('employee_id', $employee->id)
                ->where('start_date', '<=', $date->toDateString())
                ->where('end_date', '>=', $date->toDateString())
                ->where('status', 'approved')
                ->exists();

            if ($hasLeave) {
                // Se tem licença justificada, não é falta
                continue;
            }

            $hasVacation = Vacation::where('employee_id', $employee->id)
                ->where('start_date', '<=', $date->toDateString())
                ->where('end_date', '>=', $date->toDateString())
                ->where('status', 'approved')
                ->exists();

            if ($hasVacation) {
                // Se tem férias aprovadas, não é falta
                continue;
            }

            // 4. Verificar se já existe uma Absence para esta data
            $existingAbsence = Absence::where('employee_id', $employee->id)
                ->where('absence_date', $date->toDateString())
                ->exists();

            if ($existingAbsence) {
                // Já foi registada manualmente
                continue;
            }

            // 5. CRIAR FALTA AUTOMÁTICA
            // Obter o daily_work_minutes do contrato ativo
            $contract = $employee->contracts()
                ->where('status', 'active')
                ->where('start_date', '<=', $date->toDateString())
                ->orderByDesc('start_date')
                ->first();

            $dailyWorkMinutes = $contract?->daily_work_minutes ?? 480; // 8 horas por padrão

            Absence::create([
                'employee_id' => $employee->id,
                'absence_date' => $date->toDateString(),
                'hours_deducted' => $dailyWorkMinutes,
                'deduction_type' => 'unjustified_absence',
                'reason' => sprintf(
                    'Falta automática detectada: funcionário não registou ponto em %s',
                    $date->format('d/m/Y')
                ),
            ]);

            $abencesCreated++;
            $this->line("  ✓ Falta criada para {$employee->first_name} {$employee->last_name}");
        }

        $this->info("✅ Processo concluído! {$abencesCreated} falta(s) criada(s).");

        return self::SUCCESS;
    }
}
