<?php

namespace Database\Seeders;

use App\Models\Employee;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkYear2025Seeder extends Seeder
{
    private const YEAR_START = '2025-01-01';

    private const YEAR_END = '2025-12-31';

    /**
     * Feriados nacionais de 2025 que caem em dias úteis.
     * Excluídos: Páscoa (20 Abr = Dom), Implantação da República (5 Out = Dom), Todos os Santos (1 Nov = Sáb).
     */
    private const PT_HOLIDAYS_2025 = [
        '2025-01-01', // Ano Novo (Quarta)
        '2025-04-18', // Sexta-feira Santa
        '2025-04-25', // Dia da Liberdade (Sexta)
        '2025-05-01', // Dia do Trabalhador (Quinta)
        '2025-06-10', // Dia de Portugal (Terça)
        '2025-08-15', // Assunção de Nossa Senhora (Sexta)
        '2025-12-01', // Restauração da Independência (Segunda)
        '2025-12-08', // Imaculada Conceição (Segunda)
        '2025-12-25', // Natal (Quinta)
    ];

    private const SICK_REASONS = [
        'Gripe com febre — atestado do médico de família',
        'Infeção respiratória aguda — baixa médica',
        'Gastroenterite — atestado clínico',
        'Dores lombares agudas — relatório médico',
        'Síndrome viral — baixa por 3 dias úteis',
    ];

    private const JUSTIFIED_REASONS = [
        'Consulta médica especializada (com comprovativo)',
        'Acompanhamento de filho menor em urgência pediátrica',
        'Tratamento dentário urgente',
    ];

    /** @var array<string, true> */
    private array $holidaySet = [];

    /** @var list<string> */
    private array $workingDays2025 = [];

    private string $now;

    public function run(): void
    {
        $this->holidaySet = array_flip(self::PT_HOLIDAYS_2025);
        $this->workingDays2025 = $this->computeWorkingDays(self::YEAR_START, self::YEAR_END);
        $this->now = now()->toDateTimeString();

        $this->clearPreviousData();

        $employees = Employee::with('designation')->get();

        $this->command?->info(sprintf(
            'A gerar dados anuais de 2025: %d colaboradores × %d dias úteis',
            $employees->count(),
            count($this->workingDays2025)
        ));

        $this->createContracts($employees);

        $contractSalaries = DB::table('contracts')
            ->where('status', 'active')
            ->pluck('salary', 'employee_id');

        $allAttendanceLogs = [];
        $allPayrolls = [];
        $allHourBanks = [];

        foreach ($employees as $idx => $employee) {
            $salary = (float) ($contractSalaries[$employee->id] ?? 900.0);
            $hireDate = $employee->date_hired->toDateString();
            $offDays = [];

            // Blocos de férias repartidas por 5 grupos para manter cobertura
            $this->createVacations($employee->id, $idx, $hireDate, $offDays);

            // Licenças médicas e ausências justificadas
            $this->createLeaves($employee->id, $idx, $hireDate, $offDays);

            // Faltas injustificadas e parciais
            $this->createStandaloneAbsences($employee->id, $idx, $hireDate, $offDays);

            // Registos de presença para cada dia útil disponível
            $monthlyExtra = [];
            foreach ($this->workingDays2025 as $day) {
                if ($day < $hireDate || isset($offDays[$day])) {
                    continue;
                }
                $log = $this->buildLog($employee->id, $day);
                $allAttendanceLogs[] = $log;
                $ym = substr($day, 0, 7);
                $extra = max(0, $log['total_minutes'] - 480);
                $monthlyExtra[$ym] = ($monthlyExtra[$ym] ?? 0) + $extra;
            }

            // Movimentos do banco de horas (um resumo por mês)
            $movements = $this->buildHourBankMovements($employee->id, $monthlyExtra);
            if (! empty($movements)) {
                foreach (array_chunk($movements, 200) as $chunk) {
                    DB::table('hour_bank_movements')->insert($chunk);
                }
            }

            $totalAdded = array_sum($monthlyExtra);
            $totalUsed = (int) array_sum(array_column(
                array_filter($movements, fn ($m) => $m['type'] === 'deduction'),
                'amount'
            ));

            // Processamentos salariais mensais
            $hireYm = substr($hireDate, 0, 7);
            foreach ($this->monthList() as $ym => $monthName) {
                if ($ym < $hireYm) {
                    continue;
                }
                $allPayrolls[] = $this->buildPayroll(
                    $employee->id, $salary, $ym, $monthName, $monthlyExtra[$ym] ?? 0
                );
            }

            // Banco de horas acumulado (1 registo por colaborador)
            $allHourBanks[] = [
                'employee_id' => $employee->id,
                'balance' => max(0, $totalAdded - $totalUsed),
                'extra_hours_added' => $totalAdded,
                'extra_hours_used' => $totalUsed,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];
        }

        $this->command?->info('A inserir '.count($allAttendanceLogs).' registos de presença...');
        foreach (array_chunk($allAttendanceLogs, 1000) as $chunk) {
            DB::table('attendance_logs')->insert($chunk);
        }

        $this->command?->info('A inserir '.count($allPayrolls).' processamentos salariais...');
        foreach (array_chunk($allPayrolls, 500) as $chunk) {
            DB::table('payrolls')->insert($chunk);
        }

        $this->command?->info('A inserir '.count($allHourBanks).' bancos de horas...');
        DB::table('hour_banks')->insert($allHourBanks);

        $this->command?->info('Concluído! Dados de 2025 gerados com sucesso.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Limpeza
    // ─────────────────────────────────────────────────────────────────────

    private function clearPreviousData(): void
    {
        DB::table('payrolls')->delete();
        DB::table('hour_bank_movements')->delete();
        DB::table('hour_banks')->delete();
        DB::table('attendance_logs')->delete();
        DB::table('vacations')->delete();
        DB::table('leaves_and_absences')->delete();
        DB::table('absences')->delete();
        DB::table('contracts')->delete();
    }

    // ─────────────────────────────────────────────────────────────────────
    // Contratos
    // ─────────────────────────────────────────────────────────────────────

    private function createContracts(mixed $employees): void
    {
        $rows = [];

        foreach ($employees as $employee) {
            $baseSalary = $employee->designation ? (float) $employee->designation->base_salary : 900.0;
            // Variação individual determinística de ±8%
            $pct = (($employee->id % 17) - 8) / 100;
            $salary = round($baseSalary * (1 + $pct), 2);

            $hireDate = Carbon::parse($employee->date_hired);
            $monthsAtStart = (int) $hireDate->diffInMonths(Carbon::create(2025, 1, 1), false);

            if ($monthsAtStart < 0) {
                $monthsAtStart = 0;
            }

            $type = match (true) {
                $monthsAtStart >= 36 => 'permanent',
                $monthsAtStart >= 24 => 'unfixed_term',
                $monthsAtStart >= 12 => 'fixed_term',
                $monthsAtStart >= 3 => 'fixed_term',
                default => 'internship',
            };

            $endDate = match ($type) {
                'fixed_term' => $hireDate->copy()->addYears(2)->toDateString(),
                'internship' => $hireDate->copy()->addMonths(9)->toDateString(),
                default => null,
            };

            $rows[] = [
                'employee_id' => $employee->id,
                'designation_id' => $employee->designation_id,
                'type' => $type,
                'salary' => $salary,
                'daily_work_minutes' => 480,
                'expected_start_time' => '09:00:00',
                'lunch_duration_minutes' => 60,
                'start_date' => $employee->date_hired->toDateString(),
                'end_date' => $endDate,
                'status' => 'active',
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('contracts')->insert($chunk);
        }

        $this->command?->info(count($rows).' contratos criados.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Férias
    // ─────────────────────────────────────────────────────────────────────

    private function createVacations(int $empId, int $idx, string $hireDate, array &$offDays): void
    {
        $group = $idx % 5;

        // Férias de verão: 15 dias úteis começando em segundas-feiras escalonadas
        $summerStarts = [
            '2025-07-07', // Grupo 0: Jul 7–25
            '2025-07-14', // Grupo 1: Jul 14–Ago 1
            '2025-07-28', // Grupo 2: Jul 28–Ago 18
            '2025-08-04', // Grupo 3: Ago 4–25
            '2025-08-11', // Grupo 4: Ago 11–Set 1
        ];

        $summerStart = $summerStarts[$group];

        if ($hireDate <= $summerStart) {
            $summerEnd = $this->nthWorkingDay($summerStart, 15);
            $this->insertVacationBlock($empId, $summerStart, $summerEnd, $offDays);
        }

        // Natal: grupos 0–2 tiram a semana completa; grupos 3–4 só os últimos 3 dias
        [$xmasStart, $xmasEnd] = $group <= 2
            ? ['2025-12-22', '2025-12-31']
            : ['2025-12-29', '2025-12-31'];

        if ($hireDate <= $xmasStart) {
            $this->insertVacationBlock($empId, $xmasStart, $xmasEnd, $offDays);
        }

        // Ponte da Primavera para grupos 3 & 4 (compensar Natal mais curto)
        if ($group === 3 && $hireDate <= '2025-04-22') {
            $this->insertVacationBlock($empId, '2025-04-22', '2025-04-28', $offDays);
        } elseif ($group === 4 && $hireDate <= '2025-05-26') {
            $this->insertVacationBlock($empId, '2025-05-26', '2025-05-30', $offDays);
        }
    }

    private function insertVacationBlock(int $empId, string $start, string $end, array &$offDays): void
    {
        $days = array_values(array_filter(
            $this->computeWorkingDays($start, $end),
            fn ($d) => ! isset($offDays[$d])
        ));

        if (empty($days)) {
            return;
        }

        DB::table('vacations')->insert([
            'employee_id' => $empId,
            'year_reference' => 2025,
            'start_date' => $days[0],
            'end_date' => end($days),
            'days_taken' => count($days),
            'status' => 'approved',
            'approved_by' => null,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);

        foreach ($days as $d) {
            $offDays[$d] = 'vacation';
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Licenças e ausências justificadas
    // ─────────────────────────────────────────────────────────────────────

    private function createLeaves(int $empId, int $idx, string $hireDate, array &$offDays): void
    {
        // 75% dos colaboradores têm 1 episódio de baixa no ano
        if ($idx % 4 === 3) {
            return;
        }

        // Distribuição sazonal: inverno e outono são as épocas com mais baixas
        if ($idx % 4 === 0) {
            [$periodStart, $periodEnd] = ['2025-02-03', '2025-02-28'];
        } elseif ($idx % 4 === 1) {
            [$periodStart, $periodEnd] = ['2025-11-03', '2025-11-28'];
        } else {
            [$periodStart, $periodEnd] = ['2025-01-13', '2025-01-31'];
        }

        $duration = 3 + ($idx % 3); // 3, 4 ou 5 dias

        $available = array_values(array_filter(
            $this->computeWorkingDays($periodStart, $periodEnd),
            fn ($d) => $d >= $hireDate && ! isset($offDays[$d])
        ));

        if (count($available) < $duration) {
            return;
        }

        $startPos = ($empId * 7) % max(1, count($available) - $duration + 1);
        $leaveDays = array_slice($available, $startPos, $duration);

        if (count($leaveDays) < $duration) {
            return;
        }

        $reason = self::SICK_REASONS[$empId % count(self::SICK_REASONS)];

        $leaveId = DB::table('leaves_and_absences')->insertGetId([
            'employee_id' => $empId,
            'type' => 'sick_leave',
            'start_date' => $leaveDays[0],
            'end_date' => end($leaveDays),
            'reason' => $reason,
            'is_paid' => 1,
            'status' => 'approved',
            'approved_by' => null,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);

        $absenceRows = [];
        foreach ($leaveDays as $day) {
            $offDays[$day] = 'leave';
            $absenceRows[] = [
                'employee_id' => $empId,
                'leave_and_absence_id' => $leaveId,
                'absence_date' => $day,
                'hours_deducted' => 0,
                'deduction_type' => 'other',
                'reason' => $reason,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];
        }

        DB::table('absences')->insert($absenceRows);

        // ~17% dos colaboradores (id par múltiplo de 6) têm uma 2.ª licença (falta justificada)
        if ($empId % 6 === 0 && $hireDate <= '2025-09-01') {
            $this->createJustifiedLeave($empId, $hireDate, $offDays);
        }
    }

    private function createJustifiedLeave(int $empId, string $hireDate, array &$offDays): void
    {
        $available = array_values(array_filter(
            $this->computeWorkingDays('2025-09-08', '2025-09-19'),
            fn ($d) => $d >= $hireDate && ! isset($offDays[$d])
        ));

        if (count($available) < 2) {
            return;
        }

        $leaveDays = array_slice($available, 0, 2);
        $reason = self::JUSTIFIED_REASONS[$empId % count(self::JUSTIFIED_REASONS)];

        $leaveId = DB::table('leaves_and_absences')->insertGetId([
            'employee_id' => $empId,
            'type' => 'justified_absence',
            'start_date' => $leaveDays[0],
            'end_date' => end($leaveDays),
            'reason' => $reason,
            'is_paid' => 1,
            'status' => 'approved',
            'approved_by' => null,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);

        $absenceRows = [];
        foreach ($leaveDays as $day) {
            $offDays[$day] = 'leave';
            $absenceRows[] = [
                'employee_id' => $empId,
                'leave_and_absence_id' => $leaveId,
                'absence_date' => $day,
                'hours_deducted' => 0,
                'deduction_type' => 'other',
                'reason' => $reason,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];
        }

        DB::table('absences')->insert($absenceRows);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Faltas injustificadas e parciais
    // ─────────────────────────────────────────────────────────────────────

    private function createStandaloneAbsences(int $empId, int $idx, string $hireDate, array &$offDays): void
    {
        // 80% dos colaboradores têm 1–3 faltas no ano
        if ($idx % 5 === 0) {
            return;
        }

        $numAbsences = ($idx % 3) + 1; // 1, 2 ou 3

        // Meses candidatos: evitar Jul–Ago (férias de verão) e Dez (Natal)
        $candidateMonths = [
            '2025-01', '2025-02', '2025-03', '2025-04',
            '2025-05', '2025-06', '2025-09', '2025-10', '2025-11',
        ];

        for ($i = 0; $i < $numAbsences; $i++) {
            $ym = $candidateMonths[($empId * ($i + 3)) % count($candidateMonths)];
            [$year, $month] = explode('-', $ym);
            $monthEnd = Carbon::create((int) $year, (int) $month)->endOfMonth()->toDateString();

            $available = array_values(array_filter(
                $this->computeWorkingDays("{$ym}-01", $monthEnd),
                fn ($d) => $d >= $hireDate && ! isset($offDays[$d])
            ));

            if (empty($available)) {
                continue;
            }

            $day = $available[($empId * 11 + $i) % count($available)];
            $isPartial = ($empId + $i) % 4 === 0;

            DB::table('absences')->insert([
                'employee_id' => $empId,
                'leave_and_absence_id' => null,
                'absence_date' => $day,
                'hours_deducted' => $isPartial ? 180 : 480,
                'deduction_type' => $isPartial ? 'partial_absence' : 'unjustified_absence',
                'reason' => $isPartial ? 'Atraso não justificado na entrada' : null,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);

            $offDays[$day] = 'absence';
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Registo de presença
    // ─────────────────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    private function buildLog(int $empId, string $day): array
    {
        // Variação determinística baseada em colaborador + dia (sem aleatoriedade real)
        $seed = abs(crc32("{$empId}_{$day}"));
        $variant = $seed % 100;

        if ($variant < 80) {
            // Dia normal: entrada ~9:00 (±15 min), saída ~18:00 (±15 min)
            $inH = 8;
            $inM = 45 + ($seed % 30);
            $outH = 17;
            $outM = 45 + (($seed >> 4) % 30);
        } elseif ($variant < 92) {
            // Entrada tardia: 9:30–9:59
            $inH = 9;
            $inM = 30 + ($seed % 30);
            $outH = 18;
            $outM = 15 + (($seed >> 4) % 30);
        } else {
            // Horas extra: saída 19:00–19:59
            $inH = 8;
            $inM = 30 + ($seed % 30);
            $outH = 19;
            $outM = ($seed >> 4) % 60;
        }

        if ($inM >= 60) {
            $inH++;
            $inM -= 60;
        }
        if ($outM >= 60) {
            $outH++;
            $outM -= 60;
        }

        $timeIn = Carbon::parse($day)->setTime($inH, $inM, 0);
        $lunchStart = Carbon::parse($day)->setTime(12, 30 + (($seed >> 8) % 30), 0);
        $lunchEnd = $lunchStart->copy()->addMinutes(60);
        $timeOut = Carbon::parse($day)->setTime($outH, $outM, 0);

        return [
            'employee_id' => $empId,
            'time_in' => $timeIn->toDateTimeString(),
            'lunch_break_start' => $lunchStart->toDateTimeString(),
            'lunch_break_end' => $lunchEnd->toDateTimeString(),
            'time_out' => $timeOut->toDateTimeString(),
            'total_minutes' => max(0, (int) $timeIn->diffInMinutes($timeOut) - 60),
            'metadata' => '{"device":"biometric","location":"main_office"}',
            'notes' => null,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // Processamento salarial
    // ─────────────────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    private function buildPayroll(int $empId, float $salary, string $ym, string $monthName, int $extraMins): array
    {
        $hourlyRate = round($salary / (8 * 22), 4);
        $extraAmount = round($hourlyRate * 1.5 * ($extraMins / 60), 2);
        $ssDeduction = round($salary * 0.11, 2);
        $irsDeduction = round($salary * $this->irsRate($salary), 2);
        $deductions = round($ssDeduction + $irsDeduction, 2);

        return [
            'employee_id' => $empId,
            'month_year' => "{$monthName}-2025",
            'base_salary' => $salary,
            'hourly_rate' => $hourlyRate,
            'extra_hours' => $extraMins,
            'extra_hours_amount' => $extraAmount,
            'deductions' => $deductions,
            'total_net' => round($salary + $extraAmount - $deductions, 2),
            'status' => 'paid',
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // Movimentos do banco de horas
    // ─────────────────────────────────────────────────────────────────────

    /** @return list<array<string, mixed>> */
    private function buildHourBankMovements(int $empId, array $monthlyExtra): array
    {
        $lastDayOfMonth = [
            '2025-01' => '2025-01-31', '2025-02' => '2025-02-28', '2025-03' => '2025-03-31',
            '2025-04' => '2025-04-30', '2025-05' => '2025-05-31', '2025-06' => '2025-06-30',
            '2025-07' => '2025-07-31', '2025-08' => '2025-08-31', '2025-09' => '2025-09-30',
            '2025-10' => '2025-10-31', '2025-11' => '2025-11-30', '2025-12' => '2025-12-31',
        ];

        $ptMonthNames = [
            '2025-01' => 'Janeiro',  '2025-02' => 'Fevereiro', '2025-03' => 'Março',
            '2025-04' => 'Abril',    '2025-05' => 'Maio',      '2025-06' => 'Junho',
            '2025-07' => 'Julho',    '2025-08' => 'Agosto',    '2025-09' => 'Setembro',
            '2025-10' => 'Outubro',  '2025-11' => 'Novembro',  '2025-12' => 'Dezembro',
        ];

        $movements = [];
        foreach ($monthlyExtra as $ym => $extraMins) {
            if ($extraMins <= 0 || ! isset($lastDayOfMonth[$ym])) {
                continue;
            }

            $monthName = $ptMonthNames[$ym];
            $date = $lastDayOfMonth[$ym];

            // Adição: horas extra acumuladas no mês
            $movements[] = [
                'employee_id' => $empId,
                'amount' => $extraMins,
                'type' => 'addition',
                'source_type' => null,
                'source_id' => null,
                'description' => "Horas extra — {$monthName} 2025",
                'date' => $date,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];

            // ~30% dos meses têm uma dedução (utilização do banco)
            $monthNum = (int) substr($ym, 5, 2);
            if (($empId + $monthNum) % 3 === 0 && $extraMins > 60) {
                $deductAmount = (int) round($extraMins * 0.4);
                $movements[] = [
                    'employee_id' => $empId,
                    'amount' => $deductAmount,
                    'type' => 'deduction',
                    'source_type' => null,
                    'source_id' => null,
                    'description' => "Utilização do banco — {$monthName} 2025",
                    'date' => $date,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];
            }
        }

        return $movements;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Utilitários
    // ─────────────────────────────────────────────────────────────────────

    /** @return list<string> */
    private function computeWorkingDays(string $start, string $end): array
    {
        $days = [];
        foreach (CarbonPeriod::create($start, $end) as $date) {
            if (! $date->isWeekend() && ! isset($this->holidaySet[$date->toDateString()])) {
                $days[] = $date->toDateString();
            }
        }

        return $days;
    }

    /** Devolve a data do N-ésimo dia útil a partir de $from (inclusive). */
    private function nthWorkingDay(string $from, int $n): string
    {
        $date = Carbon::parse($from);
        $count = 0;
        while ($count < $n) {
            if (! $date->isWeekend() && ! isset($this->holidaySet[$date->toDateString()])) {
                $count++;
            }
            if ($count < $n) {
                $date->addDay();
            }
        }

        return $date->toDateString();
    }

    /** Taxa de IRS mensal simplificada (Portugal 2025). */
    private function irsRate(float $salary): float
    {
        return match (true) {
            $salary <= 900.0 => 0.000,
            $salary <= 1200.0 => 0.055,
            $salary <= 1700.0 => 0.095,
            $salary <= 2500.0 => 0.145,
            $salary <= 3500.0 => 0.200,
            $salary <= 5000.0 => 0.285,
            default => 0.370,
        };
    }

    /** @return array<string, string> */
    private function monthList(): array
    {
        return [
            '2025-01' => 'January',  '2025-02' => 'February', '2025-03' => 'March',
            '2025-04' => 'April',    '2025-05' => 'May',       '2025-06' => 'June',
            '2025-07' => 'July',     '2025-08' => 'August',    '2025-09' => 'September',
            '2025-10' => 'October',  '2025-11' => 'November',  '2025-12' => 'December',
        ];
    }
}
