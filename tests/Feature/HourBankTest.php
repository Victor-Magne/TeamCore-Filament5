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

        // Criar um contrato ativo com data de início no passado para garantir que é encontrado
        $this->contract = Contract::factory()->create([
            'employee_id' => $this->employee->id,
            'designation_id' => $this->designation->id,
            'status' => 'active',
            'daily_work_minutes' => 480, // 8 horas
            'lunch_duration_minutes' => 60,
            'expected_start_time' => '09:00',
            'start_date' => now()->subDays(10),
        ]);

        $this->hourBankService = app(\App\Services\Hour\HourBankService::class);
    });

    it('creates hour bank when employee is created', function () {
        $newEmployee = Employee::factory()->create(['designation_id' => $this->designation->id]);

        $hourBank = HourBank::where('employee_id', $newEmployee->id)->first();

        expect($hourBank)->not->toBeNull();
        expect($hourBank->balance)->toBe(0);
    });

    it('calculates extra hours when employee works beyond contract hours', function () {
        // Registar uma jornada de 10 horas (9:00 - 19:00 = 10h. 10h - 1h almoço = 9h efetivas = 1h extra)
        $now = Carbon::now();
        $attendance = AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'time_in' => $now->copy()->setTime(9, 0),
            'lunch_break_start' => $now->copy()->setTime(12, 0),
            'lunch_break_end' => $now->copy()->setTime(13, 0),
            'time_out' => $now->copy()->setTime(19, 0),
        ]);

        $this->hourBankService->syncLog($attendance);

        // Verificar que o banco de horas foi atualizado
        $hourBank = HourBank::where('employee_id', $this->employee->id)->first();

        expect($hourBank->extra_hours_added)->toBe(60);
        expect($hourBank->balance)->toBe(60);
    });

    it('deducts hours for unjustified absence', function () {
        $now = Carbon::now();

        // Registar uma falta: saída antecipada que gera déficit > 1h
        $attendance = AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'time_in' => $now->copy()->setTime(9, 0),
            'lunch_break_start' => $now->copy()->setTime(12, 0),
            'lunch_break_end' => $now->copy()->setTime(13, 0),
            'time_out' => $now->copy()->setTime(13, 15),
        ]);

        $this->hourBankService->syncLog($attendance);

        // Verificar que o banco de horas foi penalizado
        $hourBank = HourBank::where('employee_id', $this->employee->id)->first();

        expect($hourBank->extra_hours_used)->toBeGreaterThan(0);
        expect($hourBank->balance)->toBeLessThan(0);
    });

    it('accumulates balance across multiple logs', function () {
        $date1 = Carbon::now()->subDay();
        $date2 = Carbon::now();

        // Log 1: 1 hora extra (9:00 - 19:00)
        $log1 = AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'time_in' => $date1->copy()->setTime(9, 0),
            'lunch_break_start' => $date1->copy()->setTime(12, 0),
            'lunch_break_end' => $date1->copy()->setTime(13, 0),
            'time_out' => $date1->copy()->setTime(19, 0),
        ]);
        $this->hourBankService->syncLog($log1);

        // Log 2: 30 minutos extra (9:00 - 18:30)
        $log2 = AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'time_in' => $date2->copy()->setTime(9, 0),
            'lunch_break_start' => $date2->copy()->setTime(12, 0),
            'lunch_break_end' => $date2->copy()->setTime(13, 0),
            'time_out' => $date2->copy()->setTime(18, 30),
        ]);
        $this->hourBankService->syncLog($log2);

        $hourBank = HourBank::where('employee_id', $this->employee->id)->first();

        expect($hourBank->extra_hours_added)->toBe(90);
        expect($hourBank->balance)->toBe(90);
    });
});
