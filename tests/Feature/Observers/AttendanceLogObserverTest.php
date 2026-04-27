<?php

use App\Models\Absence;
use App\Models\AttendanceLog;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\HourBank;
use Carbon\Carbon;

beforeEach(function () {
    Carbon::setTestNow(Carbon::create(2026, 4, 17, 12, 0, 0));
});

describe('AttendanceLogObserver', function () {
    it('recalculates hour bank when attendance log is created', function () {
        $employee = Employee::factory()->create();
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
            'daily_work_minutes' => 480,
        ]);

        $date = Carbon::create(2026, 4, 17);

        // Criar AttendanceLog com tempo extra
        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date->setHour(9, 0),
            'lunch_break_start' => $date->setHour(12, 0),
            'lunch_break_end' => $date->setHour(13, 0),
            'time_out' => $date->setHour(19, 0), // 10h - 1h almoço = 9h
            'total_minutes' => 540, // 9 horas
        ]);

        // Verificar que HourBank foi criado/atualizado
        $hourBank = HourBank::where('employee_id', $employee->id)
            ->where('month_year', '2026-04')
            ->first();

        expect($hourBank)->not->toBeNull();
        expect($hourBank->balance)->toBe(60); // 540 - 480 = 60 minutos de extra
        expect($hourBank->extra_hours_added)->toBe(60);
    });

    it('recalculates hour bank when attendance log is updated', function () {
        $employee = Employee::factory()->create();
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
            'daily_work_minutes' => 480,
        ]);

        $date = Carbon::create(2026, 4, 17);

        // Criar com 8 horas exatas
        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date->setHour(9, 0),
            'time_out' => $date->setHour(17, 0),
            'total_minutes' => 480,
        ]);

        $hourBank = HourBank::where('employee_id', $employee->id)
            ->where('month_year', '2026-04')
            ->first();

        expect($hourBank->balance)->toBe(0); // Sem extras

        // Atualizar para 10 horas
        $attendanceLog->update([
            'time_out' => $date->setHour(19, 0),
            'total_minutes' => 600,
        ]);

        $hourBank->refresh();

        // Verificar que agora tem 2 horas de extra
        expect($hourBank->balance)->toBe(120);
        expect($hourBank->extra_hours_added)->toBe(120);
    });

    it('respects contract daily_work_minutes for calculations', function () {
        $employee = Employee::factory()->create();
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
            'daily_work_minutes' => 240, // Part-time: 4 horas
        ]);

        $date = Carbon::create(2026, 4, 17);

        // Trabalhar 5 horas em contrato de 4 horas
        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date->setHour(9, 0),
            'time_out' => $date->setHour(14, 0),
            'total_minutes' => 300, // 5 horas
        ]);

        $hourBank = HourBank::where('employee_id', $employee->id)
            ->where('month_year', '2026-04')
            ->first();

        // 300 - 240 = 60 minutos de extra
        expect($hourBank->balance)->toBe(60);
    });

    it('recalculates when attendance log is deleted', function () {
        $employee = Employee::factory()->create();
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
            'daily_work_minutes' => 480,
        ]);

        $date = Carbon::create(2026, 4, 17);

        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date->setHour(9, 0),
            'time_out' => $date->setHour(19, 0),
            'total_minutes' => 600, // 2 horas extra
        ]);

        $hourBank = HourBank::where('employee_id', $employee->id)
            ->where('month_year', '2026-04')
            ->first();

        expect($hourBank->balance)->toBe(120);

        // Deletar AttendanceLog
        $attendanceLog->delete();

        $hourBank->refresh();

        // HourBank deve ser recalculado
        expect($hourBank->balance)->toBe(0);
    });

    it('ignores absence when calculating hour bank for same day', function () {
        $employee = Employee::factory()->create();
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
            'daily_work_minutes' => 480,
        ]);

        $date = Carbon::create(2026, 4, 17);

        // Criar Absence (falta integral)
        Absence::create([
            'employee_id' => $employee->id,
            'absence_date' => $date->toDateString(),
            'hours_deducted' => 480,
            'deduction_type' => 'unjustified_absence',
            'reason' => 'Falta integral',
        ]);

        // Criar AttendanceLog para o MESMO dia (e.g. esqueceu de bater saída)
        // Se houvesse hours, seria penalizado novamente (dupla penalização)
        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date->setHour(9, 0),
            'time_out' => $date->setHour(12, 0),
            'total_minutes' => 180, // 3 horas
        ]);

        $hourBank = HourBank::where('employee_id', $employee->id)
            ->where('month_year', '2026-04')
            ->first();

        // Deve descontar apenas 480 (Absence), NÃO 480 + 300 (dupla penalização)
        // O AttendanceLog é ignorado porque há Absence para o mesmo dia
        expect($hourBank->balance)->toBe(-480);
        expect($hourBank->extra_hours_used)->toBe(480);
    });
});

