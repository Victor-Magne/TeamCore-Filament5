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
        $employee = Employee::withoutEvents(fn () => Employee::factory()->create());
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
            'daily_work_minutes' => 480,
        ]);

        $date = Carbon::create(2026, 4, 17);

        AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date->copy()->setHour(9, 0),
            'lunch_break_start' => $date->copy()->setHour(12, 0),
            'lunch_break_end' => $date->copy()->setHour(13, 0),
            'time_out' => $date->copy()->setHour(19, 0),
        ]);

        $hourBank = HourBank::where('employee_id', $employee->id)
            ->where('month_year', '2026-04')
            ->first();

        expect($hourBank)->not->toBeNull();
        expect($hourBank->balance)->toBe(60);
        expect($hourBank->extra_hours_added)->toBe(60);
    });

    it('recalculates hour bank when attendance log is updated', function () {
        $employee = Employee::withoutEvents(fn () => Employee::factory()->create());
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
            'daily_work_minutes' => 480,
        ]);

        $date = Carbon::create(2026, 4, 17);

        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date->copy()->setHour(9, 0),
            'time_out' => $date->copy()->setHour(17, 0),
        ]);

        $hourBank = HourBank::where('employee_id', $employee->id)
            ->where('month_year', '2026-04')
            ->first();

        expect($hourBank->balance)->toBe(0);

        $attendanceLog->update([
            'time_out' => $date->copy()->setHour(19, 0),
        ]);

        $hourBank->refresh();

        expect($hourBank->balance)->toBe(120);
        expect($hourBank->extra_hours_added)->toBe(120);
    });

    it('respects contract daily_work_minutes for calculations', function () {
        $employee = Employee::withoutEvents(fn () => Employee::factory()->create());
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
            'daily_work_minutes' => 240,
        ]);

        $date = Carbon::create(2026, 4, 17);

        AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date->copy()->setHour(9, 0),
            'time_out' => $date->copy()->setHour(14, 0),
        ]);

        $hourBank = HourBank::where('employee_id', $employee->id)
            ->where('month_year', '2026-04')
            ->first();

        expect($hourBank->balance)->toBe(60);
    });

    it('recalculates when attendance log is deleted', function () {
        $employee = Employee::withoutEvents(fn () => Employee::factory()->create());
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
            'daily_work_minutes' => 480,
        ]);

        $date = Carbon::create(2026, 4, 17);

        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date->copy()->setHour(9, 0),
            'time_out' => $date->copy()->setHour(19, 0),
        ]);

        $hourBank = HourBank::where('employee_id', $employee->id)
            ->where('month_year', '2026-04')
            ->first();

        expect($hourBank->balance)->toBe(120);

        $attendanceLog->delete();

        $hourBank->refresh();

        expect($hourBank->balance)->toBe(0);
    });

    it('ignores absence when calculating hour bank for same day', function () {
        $employee = Employee::withoutEvents(fn () => Employee::factory()->create());
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
            'daily_work_minutes' => 480,
        ]);

        $date = Carbon::create(2026, 4, 17);

        Absence::create([
            'employee_id' => $employee->id,
            'absence_date' => $date->toDateString(),
            'hours_deducted' => 480,
            'deduction_type' => 'unjustified_absence',
            'reason' => 'Falta integral',
        ]);

        AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $date->copy()->setHour(9, 0),
            'time_out' => $date->copy()->setHour(12, 0),
        ]);

        $hourBank = HourBank::where('employee_id', $employee->id)
            ->where('month_year', '2026-04')
            ->first();

        expect($hourBank->balance)->toBe(-480);
        expect($hourBank->extra_hours_used)->toBe(480);
    });
});
