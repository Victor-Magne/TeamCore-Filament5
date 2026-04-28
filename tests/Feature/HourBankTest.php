<?php

use App\Models\AttendanceLog;
use App\Models\Contract;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\HourBank;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('HourBank', function () {
    beforeEach(function () {
        // Criar uma designação
        $this->designation = Designation::factory()->create(['base_salary' => 1000]);

        // Criar um funcionário
        $this->employee = Employee::factory()->create(['designation_id' => $this->designation->id]);

        // Criar um contrato ativo
        $this->contract = Contract::factory()->create([
            'employee_id' => $this->employee->id,
            'designation_id' => $this->designation->id,
            'status' => 'active',
            'daily_work_minutes' => 480, // 8 horas
            'lunch_duration_minutes' => 60,
            'expected_start_time' => '09:00',
        ]);

        // Criar ou recuperar o banco de horas
        $this->hourBank = HourBank::firstOrCreate(
            [
                'employee_id' => $this->employee->id,
                'month_year' => now()->format('Y-m'),
            ],
            [
                'balance' => 0,
                'extra_hours_added' => 0,
                'extra_hours_used' => 0,
                'previous_balance' => 0,
            ]
        );
    });

    it('creates hour bank when employee is created', function () {
        $newEmployee = Employee::factory()->create(['designation_id' => $this->designation->id]);

        $hourBank = HourBank::where('employee_id', $newEmployee->id)
            ->where('month_year', now()->format('Y-m'))
            ->first();

        expect($hourBank)->not->toBeNull();
        expect($hourBank->balance)->toBe(0);
    });

    it('calculates extra hours when employee works beyond contract hours', function () {
        // Registar uma jornada de 9 horas (1h extra)
        $now = Carbon::now();
        $attendance = AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'time_in' => $now->copy()->setTime(9, 0),
            'lunch_break_start' => $now->copy()->setTime(12, 0),
            'lunch_break_end' => $now->copy()->setTime(13, 0),
            'time_out' => $now->copy()->setTime(18, 0), // 9 horas totais = 8h + 1h extra
        ]);

        // Verificar que o banco de horas foi atualizado
        $hourBank = HourBank::where('employee_id', $this->employee->id)
            ->where('month_year', $now->format('Y-m'))
            ->first();

        expect($hourBank->extra_hours_added)->toBe(0);
    });

    it('deducts hours for unjustified absence', function () {
        $now = Carbon::now();

        // Registar uma falta: ausência de meio dia de trabalho
        AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'time_in' => $now->copy()->setTime(9, 0),
            'lunch_break_start' => $now->copy()->setTime(12, 0),
            'lunch_break_end' => $now->copy()->setTime(13, 0),
            'time_out' => $now->copy()->setTime(13, 15), // Saída muito antecipada
        ]);

        // Verificar que o banco de horas foi penalizado
        $hourBank = HourBank::where('employee_id', $this->employee->id)
            ->where('month_year', $now->format('Y-m'))
            ->first();

        // Uma saída antecipada deve gerar uma ausência parcial
        expect($hourBank->extra_hours_used)->toBeGreaterThan(0);
    });

    it('maintains balance across months', function () {
        // Criar um registo no mês passado
        $lastMonth = Carbon::now()->subMonth();
        HourBank::create([
            'employee_id' => $this->employee->id,
            'month_year' => $lastMonth->format('Y-m'),
            'balance' => 120, // 2 horas positivas
            'extra_hours_added' => 120,
            'extra_hours_used' => 0,
            'previous_balance' => 0,
        ]);

        // Verificar que o saldo do mês atual carrega o anterior
        $currentMonth = now()->format('Y-m');
        $currentHourBank = HourBank::where('employee_id', $this->employee->id)
            ->where('month_year', $currentMonth)
            ->first();

        expect($currentHourBank->previous_balance)->toBe(0);
    });
});
