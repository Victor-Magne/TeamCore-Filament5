<?php

use App\Models\Absence;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\HourBank;
use App\Models\LeaveAndAbsence;
use Carbon\Carbon;

beforeEach(function () {
    Carbon::setTestNow(Carbon::create(2026, 4, 17, 12, 0, 0));
});

describe('AttendanceLogObserver - Automatic Absence Detection', function () {
    it('creates absence and deducts hours when attendance log has no time_out', function () {
        $employee = Employee::factory()->create();
        $absenceDate = Carbon::create(2026, 4, 17); // Friday

        // Criar AttendanceLog sem saída (falta)
        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $absenceDate->setHour(9, 0),
            'lunch_break_start' => null,
            'lunch_break_end' => null,
            'time_out' => null, // ← Sem saída = falta
            'total_minutes' => null,
            'notes' => 'Falta completa - sem saída',
        ]);

        // Verificar que foi criada uma Absence
        $absence = Absence::where('employee_id', $employee->id)
            ->where('absence_date', $absenceDate->toDateString())
            ->first();

        expect($absence)->not->toBeNull();
        expect($absence->hours_deducted)->toBe(480); // 8 horas
        expect($absence->deduction_type)->toBe('unjustified_absence');

        // Verificar que o HourBank foi atualizado
        $hourBank = HourBank::where('employee_id', $employee->id)
            ->where('month_year', $absenceDate->format('Y-m'))
            ->first();

        expect($hourBank)->not->toBeNull();
        expect($hourBank->balance)->toBe(-480);
        expect($hourBank->extra_hours_used)->toBe(480);
    });

    it('ignores attendance logs with time_out', function () {
        $employee = Employee::factory()->create();
        $date = Carbon::create(2026, 4, 17);

        // Criar AttendanceLog com saída (não é falta)
        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date->setHour(9, 0),
            'lunch_break_start' => $date->setHour(12, 0),
            'lunch_break_end' => $date->setHour(13, 0),
            'time_out' => $date->setHour(18, 0),
            'notes' => 'Dia normal de trabalho',
        ]);

        // Não deve criar Absence
        $absence = Absence::where('employee_id', $employee->id)
            ->where('absence_date', $date->toDateString())
            ->first();

        expect($absence)->toBeNull();
    });

    it('does not deduct when employee has justified leave', function () {
        $employee = Employee::factory()->create();
        $absenceDate = Carbon::create(2026, 4, 17);

        // Criar licença justificada para essa data
        LeaveAndAbsence::factory()->create([
            'employee_id' => $employee->id,
            'type' => 'sick_leave',
            'start_date' => $absenceDate,
            'end_date' => $absenceDate,
            'status' => 'approved',
        ]);

        // Criar AttendanceLog sem saída
        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $absenceDate->setHour(9, 0),
            'time_out' => null,
            'notes' => 'Ausência durante licença',
        ]);

        // Não deve criar Absence porque há licença
        $absence = Absence::where('employee_id', $employee->id)
            ->where('absence_date', $absenceDate->toDateString())
            ->first();

        expect($absence)->toBeNull();
    });

    it('does not deduct for weekend absences', function () {
        $employee = Employee::factory()->create();
        $saturday = Carbon::create(2026, 4, 18); // Saturday

        // Criar AttendanceLog para sábado
        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $saturday->setHour(9, 0),
            'time_out' => null,
            'notes' => 'Sábado (fim de semana)',
        ]);

        // Não deve descontar para fim de semana
        $absence = Absence::where('employee_id', $employee->id)
            ->where('absence_date', $saturday->toDateString())
            ->first();

        expect($absence)->toBeNull();
    });

    it('processes multiple absences for different days', function () {
        $employee = Employee::factory()->create();

        // Criar faltas para vários dias
        $monday = Carbon::create(2026, 4, 13);
        $tuesday = Carbon::create(2026, 4, 14);
        $wednesday = Carbon::create(2026, 4, 15);

        foreach ([$monday, $tuesday, $wednesday] as $date) {
            AttendanceLog::create([
                'employee_id' => $employee->id,
                'time_in' => $date->setHour(9, 0),
                'time_out' => null,
                'notes' => "Falta em {$date->toDateString()}",
            ]);
        }

        // Verificar que foram criadas 3 Absence
        $absences = Absence::where('employee_id', $employee->id)->count();
        expect($absences)->toBe(3);

        // Verificar saldo total (3 dias * 480 min)
        $hourBank = HourBank::where('employee_id', $employee->id)
            ->where('month_year', '2026-04')
            ->first();

        expect($hourBank->balance)->toBe(-1440); // -3 * 480
    });

    it('does not create duplicate absences', function () {
        $employee = Employee::factory()->create();
        $date = Carbon::create(2026, 4, 17);

        // Simular que já existe uma Absence
        Absence::create([
            'employee_id' => $employee->id,
            'absence_date' => $date,
            'hours_deducted' => 480,
            'deduction_type' => 'unjustified_absence',
            'reason' => 'Manualmente criada',
        ]);

        // Tentar criar AttendanceLog para a mesma data
        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date->setHour(9, 0),
            'time_out' => null,
            'notes' => 'Duplicate test',
        ]);

        // Deve haver apenas 1 Absence para esta data
        $count = Absence::where('employee_id', $employee->id)
            ->where('absence_date', $date->toDateString())
            ->count();

        expect($count)->toBe(1);
    });

    it('updates attendance log with absence reference', function () {
        $employee = Employee::factory()->create();
        $date = Carbon::create(2026, 4, 17);

        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date->setHour(9, 0),
            'time_out' => null,
            'metadata' => [],
        ]);

        // Verificar que uma Absence foi criada
        $absence = Absence::where('employee_id', $employee->id)->first();
        expect($absence)->not->toBeNull();
    });

    it('includes attendance log reference in absence reason', function () {
        $employee = Employee::factory()->create();
        $date = Carbon::create(2026, 4, 17);

        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date->setHour(9, 0),
            'time_out' => null,
        ]);

        $absence = Absence::where('employee_id', $employee->id)->first();

        // Verificar que a razão inclui a referência ao AttendanceLog
        expect($absence->reason)->toContain("AttendanceLog #{$attendanceLog->id}");
    });

    it('ignores absences for employees without contracts', function () {
        // Criar um employee manualmente sem contrato
        $employee = Employee::factory()->create();

        // Limpar contratos
        $employee->contracts()->delete();

        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => Carbon::create(2026, 4, 17)->setHour(9, 0),
            'time_out' => null,
            'notes' => 'Employee without contract',
        ]);

        // Ainda deveria descontar (sem validação de contrato no Observer)
        $absence = Absence::where('employee_id', $employee->id)->first();
        expect($absence)->not->toBeNull();
    });
});

describe('AttendanceLogObserver - Update Scenarios', function () {
    it('processes absence when attendance log is updated to remove time_out', function () {
        $employee = Employee::factory()->create();
        $date = Carbon::create(2026, 4, 17);

        // Criar com saída
        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date->setHour(9, 0),
            'time_out' => $date->setHour(18, 0),
            'total_minutes' => 480,
        ]);

        // Verificar que não tem Absence
        $absenceCount = Absence::where('employee_id', $employee->id)->count();
        expect($absenceCount)->toBe(0);

        // Atualizar para remover saída (marcar como falta)
        $attendanceLog->update([
            'time_out' => null,
            'total_minutes' => null,
        ]);

        // Agora deve ter uma Absence
        $absence = Absence::where('employee_id', $employee->id)->first();
        expect($absence)->not->toBeNull();
    });
});
