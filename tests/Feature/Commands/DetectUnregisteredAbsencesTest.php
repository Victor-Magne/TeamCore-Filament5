<?php

use App\Console\Commands\DetectUnregisteredAbsences;
use App\Models\Absence;
use App\Models\AttendanceLog;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\LeaveAndAbsence;
use App\Models\Vacation;
use Carbon\Carbon;

beforeEach(function () {
    Carbon::setTestNow(Carbon::create(2026, 4, 17, 12, 0, 0));
});

describe('DetectUnregisteredAbsences Command', function () {
    it('creates absence for employee who did not register any attendance', function () {
        $employee = Employee::factory()->create();
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
            'start_date' => Carbon::create(2026, 1, 1),
            'daily_work_minutes' => 480,
        ]);

        $targetDate = Carbon::create(2026, 4, 16)->toDateString(); // Quinta-feira

        // Nenhum AttendanceLog registado
        expect(AttendanceLog::where('employee_id', $employee->id)
            ->whereDate('time_in', $targetDate)
            ->exists())->toBeFalse();

        // Executar comando
        $this->artisan('absences:detect-unregistered', [
            '--date' => $targetDate,
        ]);

        // Verificar que foi criada uma Absence
        $absence = Absence::where('employee_id', $employee->id)
            ->where('absence_date', $targetDate)
            ->first();

        expect($absence)->not->toBeNull();
        expect($absence->hours_deducted)->toBe(480); // daily_work_minutes do contrato
        expect($absence->deduction_type)->toBe('unjustified_absence');
    });

    it('does not create absence when employee has approved leave', function () {
        $employee = Employee::factory()->create();
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
            'start_date' => Carbon::create(2026, 1, 1),
        ]);

        $targetDate = Carbon::create(2026, 4, 16)->toDateString();

        // Criar licença aprovada
        LeaveAndAbsence::factory()->create([
            'employee_id' => $employee->id,
            'type' => 'sick_leave',
            'start_date' => $targetDate,
            'end_date' => $targetDate,
            'status' => 'approved',
        ]);

        // Executar comando
        $this->artisan('absences:detect-unregistered', [
            '--date' => $targetDate,
        ]);

        // Não deve criar Absence
        $absence = Absence::where('employee_id', $employee->id)
            ->where('absence_date', $targetDate)
            ->first();

        expect($absence)->toBeNull();
    });

    it('does not create absence when employee has approved vacation', function () {
        $employee = Employee::factory()->create();
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
            'start_date' => Carbon::create(2026, 1, 1),
        ]);

        $targetDate = Carbon::create(2026, 4, 16);

        // Criar férias aprovadas
        Vacation::factory()->create([
            'employee_id' => $employee->id,
            'start_date' => $targetDate,
            'end_date' => $targetDate,
            'status' => 'approved',
        ]);

        // Executar comando
        $this->artisan('absences:detect-unregistered', [
            '--date' => $targetDate->toDateString(),
        ]);

        // Não deve criar Absence
        $absence = Absence::where('employee_id', $employee->id)
            ->where('absence_date', $targetDate->toDateString())
            ->first();

        expect($absence)->toBeNull();
    });

    it('does not create absence when employee has attendance log', function () {
        $employee = Employee::factory()->create();
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
            'start_date' => Carbon::create(2026, 1, 1),
        ]);

        $targetDate = Carbon::create(2026, 4, 16);

        // Criar AttendanceLog (mesmo que sem time_out, há registro)
        AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $targetDate->setHour(9, 0),
            'time_out' => null,
        ]);

        // Executar comando
        $this->artisan('absences:detect-unregistered', [
            '--date' => $targetDate->toDateString(),
        ]);

        // Não deve criar Absence (já há AttendanceLog)
        $count = Absence::where('employee_id', $employee->id)
            ->where('absence_date', $targetDate->toDateString())
            ->count();

        expect($count)->toBe(0);
    });

    it('skips weekends', function () {
        $employee = Employee::factory()->create();
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
            'start_date' => Carbon::create(2026, 1, 1),
        ]);

        $saturday = Carbon::create(2026, 4, 18)->toDateString(); // Sábado

        // Executar comando para sábado
        $this->artisan('absences:detect-unregistered', [
            '--date' => $saturday,
        ]);

        // Não deve criar nenhuma Absence
        $count = Absence::where('employee_id', $employee->id)
            ->where('absence_date', $saturday)
            ->count();

        expect($count)->toBe(0);
    });

    it('uses contract daily_work_minutes for absence hours', function () {
        $employee = Employee::factory()->create();
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
            'start_date' => Carbon::create(2026, 1, 1),
            'daily_work_minutes' => 240, // Part-time: 4 horas
        ]);

        $targetDate = Carbon::create(2026, 4, 16)->toDateString();

        // Executar comando
        $this->artisan('absences:detect-unregistered', [
            '--date' => $targetDate,
        ]);

        // Verificar que usou 240 minutos do contrato
        $absence = Absence::where('employee_id', $employee->id)
            ->where('absence_date', $targetDate)
            ->first();

        expect($absence->hours_deducted)->toBe(240);
    });
});
