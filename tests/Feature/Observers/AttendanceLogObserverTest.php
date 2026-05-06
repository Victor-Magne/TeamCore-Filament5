<?php

namespace App\Observers;

use App\Models\Absence;
use App\Models\AttendanceLog;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\HourBank;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow(Carbon::create(2026, 4, 17, 12, 0, 0));
});

describe('AttendanceLogObserver - Automatic Absence Detection', function () {
    it('creates absence and deducts hours when attendance log is delayed', function () {
        $employee = Employee::factory()->create();
        $contract = Contract::factory()->create([
            'employee_id' => $employee->id,
            'expected_start_time' => '09:00:00',
            'daily_work_minutes' => 480,
        ]);

        $absenceDate = Carbon::create(2026, 4, 17, 9, 30); // 30m delay

        // Criar AttendanceLog com atraso
        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $absenceDate,
        ]);

        // Verificar que foi criada uma Absence
        $absence = Absence::where('employee_id', $employee->id)
            ->first();

        expect($absence)->not->toBeNull();
        expect($absence->hours_deducted)->toBe(30);
        expect($absence->deduction_type)->toBe('partial_absence');

        // Verificar que o HourBank foi atualizado
        $hourBank = HourBank::where('employee_id', $employee->id)->first();

        expect($hourBank)->not->toBeNull();
        // Nota: o total_minutes é nulo aqui, então calculateDeficit pode atuar no HourBank recalculate
        // Mas como só temos time_in, o total_minutes é null.
        // No meu HourBankService, ele calcula déficit se total_minutes existe.
    });

    it('ignores attendance logs within tolerance', function () {
        $employee = Employee::factory()->create();
        $contract = Contract::factory()->create([
            'employee_id' => $employee->id,
            'expected_start_time' => '09:00:00',
        ]);
        $date = Carbon::create(2026, 4, 17, 9, 10); // 10m delay (tolerance)

        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date,
        ]);

        $absence = Absence::where('employee_id', $employee->id)->first();
        expect($absence)->toBeNull();
    });

    it('recalculates hour bank when attendance log is corrected', function () {
        $employee = Employee::factory()->create();
        $contract = Contract::factory()->create([
            'employee_id' => $employee->id,
            'expected_start_time' => '09:00:00',
            'daily_work_minutes' => 480,
        ]);

        $date = Carbon::create(2026, 4, 17);

        // 1. Registar entrada COM atraso (20 min)
        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date->copy()->setTime(9, 20, 0),
        ]);

        // Verificar que foi criado Absence de 20 min
        $absence = Absence::where('employee_id', $employee->id)->first();
        expect($absence)->not->toBeNull();
        expect($absence->hours_deducted)->toBe(20);

        // 2. CORRIGIR: Colocar entrada no horário certo (9:00)
        $attendanceLog->update([
            'time_in' => $date->copy()->setTime(9, 0, 0),
        ]);

        // Verificar que a Absence foi removida
        $absence = Absence::where('employee_id', $employee->id)->first();
        expect($absence)->toBeNull();
    });
});
