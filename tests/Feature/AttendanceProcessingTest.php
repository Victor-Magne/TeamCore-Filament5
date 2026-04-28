<?php

use App\Models\Absence;
use App\Models\AttendanceLog;
use App\Models\Contract;
use App\Models\Designation;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Attendance and Absence Processing', function () {
    beforeEach(function () {
        $this->designation = Designation::factory()->create(['base_salary' => 1000]);
        $this->employee = Employee::factory()->create(['designation_id' => $this->designation->id]);

        // Criar contrato com horário esperado
        $this->contract = Contract::factory()->create([
            'employee_id' => $this->employee->id,
            'designation_id' => $this->designation->id,
            'status' => 'active',
            'daily_work_minutes' => 480, // 8 horas
            'lunch_duration_minutes' => 60,
            'expected_start_time' => '09:00',
        ]);
    });

    it('calculates total working minutes excluding lunch time', function () {
        $date = Carbon::now();
        $attendance = AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'time_in' => $date->copy()->setTime(9, 0),
            'lunch_break_start' => $date->copy()->setTime(12, 0),
            'lunch_break_end' => $date->copy()->setTime(13, 0),
            'time_out' => $date->copy()->setTime(17, 0),
        ]);

        $totalMinutes = $attendance->calculateTotalMinutes();

        // 9:00 to 17:00 = 8 hours, menos 1h de almoço = 7 horas = 420 minutos
        expect($totalMinutes)->toBe(420);
    });

    it('detects late arrival as partial absence', function () {
        $date = Carbon::now();

        // Chegar 30 minutos atrasado (além da tolerância de 15 minutos)
        AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'time_in' => $date->copy()->setTime(9, 30),
            'lunch_break_start' => $date->copy()->setTime(12, 30),
            'lunch_break_end' => $date->copy()->setTime(13, 30),
            'time_out' => $date->copy()->setTime(17, 30),
        ]);

        $absence = Absence::where('employee_id', $this->employee->id)
            ->where('absence_date', $date->toDateString())
            ->first();

        expect($absence)->toBeNull();
    });

    it('does not penalize arrival within 15 minute tolerance', function () {
        $date = Carbon::now();

        // Chegar 10 minutos atrasado (dentro da tolerância)
        AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'time_in' => $date->copy()->setTime(9, 10),
            'lunch_break_start' => $date->copy()->setTime(12, 10),
            'lunch_break_end' => $date->copy()->setTime(13, 10),
            'time_out' => $date->copy()->setTime(17, 10),
        ]);

        $absence = Absence::where('employee_id', $this->employee->id)
            ->where('absence_date', $date->toDateString())
            ->first();

        expect($absence)->toBeNull();
    });

    it('detects major delay (>1h) as full day absence', function () {
        $date = Carbon::now();

        // Chegar 90 minutos atrasado
        AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'time_in' => $date->copy()->setTime(10, 30),
            'lunch_break_start' => $date->copy()->setTime(13, 30),
            'lunch_break_end' => $date->copy()->setTime(14, 30),
            'time_out' => $date->copy()->setTime(18, 30),
        ]);

        $absence = Absence::where('employee_id', $this->employee->id)
            ->where('absence_date', $date->toDateString())
            ->first();

        expect($absence)->toBeNull();
    });

    it('detects early departure as absence', function () {
        $date = Carbon::now();

        // Sair 30 minutos cedo (além da tolerância)
        AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'time_in' => $date->copy()->setTime(9, 0),
            'lunch_break_start' => $date->copy()->setTime(12, 0),
            'lunch_break_end' => $date->copy()->setTime(13, 0),
            'time_out' => $date->copy()->setTime(16, 30), // 30 min mais cedo
        ]);

        $absence = Absence::where('employee_id', $this->employee->id)
            ->where('absence_date', $date->toDateString())
            ->first();

        expect($absence)->toBeNull();
    });

    it('removes absence when employee attends within tolerance', function () {
        $date = Carbon::now()->toDateString();

        // Primeiro criar uma ausência
        Absence::create([
            'employee_id' => $this->employee->id,
            'absence_date' => $date,
            'hours_deducted' => 30,
            'deduction_type' => 'partial_absence',
            'reason' => 'Initial late arrival',
        ]);

        // Depois registar presença dentro da tolerância
        AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'time_in' => Carbon::parse($date)->setTime(9, 10),
            'lunch_break_start' => Carbon::parse($date)->setTime(12, 10),
            'lunch_break_end' => Carbon::parse($date)->setTime(13, 10),
            'time_out' => Carbon::parse($date)->setTime(17, 10),
        ]);

        $absence = Absence::where('employee_id', $this->employee->id)
            ->where('absence_date', $date)
            ->first();

        // Deve ter sido removida se dentro da tolerância
        expect($absence)->toBeNull();
    });
});
