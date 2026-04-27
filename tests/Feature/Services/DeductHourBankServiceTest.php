<?php

use App\Models\Absence;
use App\Models\AttendanceLog;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\HourBank;
use App\Models\LeaveAndAbsence;
use App\Services\Hour\DeductHourBankService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow(Carbon::create(2026, 4, 17, 12, 0, 0));
});

describe('DeductHourBankService', function () {
    it('deducts hours for unjustified full absence', function () {
        $employee = Employee::factory()->create();
        $contract = Contract::factory()->create([
            'employee_id' => $employee->id,
            'daily_work_minutes' => 480,
            'expected_start_time' => '09:00:00',
        ]);

        $service = new DeductHourBankService;
        $absenceDate = Carbon::now();

        $service->registerFullAbsence($employee->id, $absenceDate, 'Falta injustificada');

        $absence = Absence::first();

        expect($absence)->toBeInstanceOf(Absence::class);
        expect($absence->hours_deducted)->toBe(480);
        expect($absence->deduction_type)->toBe('unjustified_absence');

        // Verificar se o banco de horas foi atualizado
        $hourBank = HourBank::where('employee_id', $employee->id)
            ->where('month_year', $absenceDate->format('Y-m'))
            ->first();

        expect($hourBank)->not->toBeNull();
        expect($hourBank->balance)->toBe(-480);
    });

    it('processes delay as partial_absence (15m to 1h)', function () {
        $employee = Employee::factory()->create();
        $contract = Contract::factory()->create([
            'employee_id' => $employee->id,
            'expected_start_time' => '09:00:00',
        ]);

        $date = Carbon::create(2026, 4, 17, 9, 20); // 20 min atraso

        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date,
        ]);

        // O observer já processa via processAttendance
        $absence = Absence::where('employee_id', $employee->id)->first();
        expect($absence)->not->toBeNull();
        expect($absence->hours_deducted)->toBe(20);
        expect($absence->deduction_type)->toBe('partial_absence');
    });

    it('processes delay as full absence if > 1h', function () {
        $employee = Employee::factory()->create();
        $contract = Contract::factory()->create([
            'employee_id' => $employee->id,
            'expected_start_time' => '09:00:00',
            'daily_work_minutes' => 480,
        ]);

        $date = Carbon::create(2026, 4, 17, 10, 10); // 1h10 atraso

        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date,
        ]);

        $absence = Absence::where('employee_id', $employee->id)->first();
        expect($absence)->not->toBeNull();
        expect($absence->hours_deducted)->toBe(480);
        expect($absence->deduction_type)->toBe('unjustified_absence');
    });

    it('applies tolerance for delays <= 15m', function () {
        $employee = Employee::factory()->create();
        $contract = Contract::factory()->create([
            'employee_id' => $employee->id,
            'expected_start_time' => '09:00:00',
        ]);

        $date = Carbon::create(2026, 4, 17, 9, 10); // 10 min atraso

        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date,
        ]);

        $absence = Absence::where('employee_id', $employee->id)->first();
        expect($absence)->toBeNull();
    });

    it('converts 3 consecutive delays into 1 full day absence', function () {
        $employee = Employee::factory()->create();
        $contract = Contract::factory()->create([
            'employee_id' => $employee->id,
            'expected_start_time' => '09:00:00',
            'daily_work_minutes' => 480,
        ]);

        // 3 dias úteis: Segunda (20), Terça (21), Quarta (22)
        $monday = Carbon::create(2026, 4, 20, 9, 20);
        $tuesday = Carbon::create(2026, 4, 21, 9, 20);
        $wednesday = Carbon::create(2026, 4, 22, 9, 20);

        foreach ([$monday, $tuesday, $wednesday] as $date) {
            $log = AttendanceLog::create([
                'employee_id' => $employee->id,
                'time_in' => $date,
            ]);
        }

        // Deve ter apenas 1 Absence ativa (as outras foram removidas ou convertidas)
        // No meu código: a última virou dia inteiro, as outras 2 foram deletadas.
        $absences = Absence::where('employee_id', $employee->id)->get();

        expect($absences->count())->toBe(1);
        expect($absences->first()->hours_deducted)->toBe(480);
        expect($absences->first()->deduction_type)->toBe('unjustified_absence');
        expect($absences->first()->reason)->toContain('Convertido para falta');
    });

    it('does not deduct for justified approved leave', function () {
        $employee = Employee::factory()->create();
        $contract = Contract::factory()->create([
            'employee_id' => $employee->id,
            'expected_start_time' => '09:00:00',
        ]);
        $date = Carbon::create(2026, 4, 17);

        LeaveAndAbsence::create([
            'employee_id' => $employee->id,
            'type' => 'sick_leave',
            'start_date' => $date,
            'end_date' => $date,
            'status' => 'approved',
            'is_paid' => true,
        ]);

        $log = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date->copy()->setHour(10, 0), // Atraso de 1h
        ]);

        $absence = Absence::where('employee_id', $employee->id)->first();
        expect($absence)->toBeNull();
    });
});
