<?php

use App\Models\Contract;
use App\Models\Employee;
use App\Services\Attendance\AttendanceCalculationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('AttendanceCalculationService', function () {
    beforeEach(function () {
        $this->employee = Employee::factory()->create();
        $this->service = app(AttendanceCalculationService::class);
    });

    it('returns null when time_out is missing', function () {
        $log = new \App\Models\AttendanceLog([
            'employee_id' => $this->employee->id,
            'time_in' => Carbon::now()->setTime(9, 0),
        ]);
        $log->employee_id = $this->employee->id;

        expect($this->service->calculateTotalMinutes($log))->toBeNull();
    });

    it('calculates total minutes with registered lunch break', function () {
        Contract::factory()->create([
            'employee_id' => $this->employee->id,
            'lunch_duration_minutes' => 60,
            'daily_work_minutes' => 480,
            'start_date' => now()->subYear(),
        ]);

        $log = new \App\Models\AttendanceLog();
        $log->employee_id = $this->employee->id;
        $log->time_in = Carbon::now()->setTime(9, 0);
        $log->lunch_break_start = Carbon::now()->setTime(13, 0);
        $log->lunch_break_end = Carbon::now()->setTime(14, 0);
        $log->time_out = Carbon::now()->setTime(18, 0);
        $log->setRelation('employee', $this->employee);

        $total = $this->service->calculateTotalMinutes($log);

        expect($total)->toBe(480); // 9h-18h = 9h, menos 1h almoço = 8h = 480min
    });

    it('uses contract lunch duration when actual lunch is shorter', function () {
        Contract::factory()->create([
            'employee_id' => $this->employee->id,
            'lunch_duration_minutes' => 60,
            'daily_work_minutes' => 480,
            'start_date' => now()->subYear(),
        ]);

        $log = new \App\Models\AttendanceLog();
        $log->employee_id = $this->employee->id;
        $log->time_in = Carbon::now()->setTime(9, 0);
        $log->lunch_break_start = Carbon::now()->setTime(13, 0);
        $log->lunch_break_end = Carbon::now()->setTime(13, 30); // apenas 30min de almoço
        $log->time_out = Carbon::now()->setTime(18, 0);
        $log->setRelation('employee', $this->employee);

        $total = $this->service->calculateTotalMinutes($log);

        // Deve usar 60min (contrato) em vez de 30min (real), portanto 9h - 1h = 8h = 480min
        expect($total)->toBe(480);
    });

    it('deducts default lunch when no break registered', function () {
        Contract::factory()->create([
            'employee_id' => $this->employee->id,
            'lunch_duration_minutes' => 60,
            'daily_work_minutes' => 480,
            'start_date' => now()->subYear(),
        ]);

        $log = new \App\Models\AttendanceLog();
        $log->employee_id = $this->employee->id;
        $log->time_in = Carbon::now()->setTime(9, 0);
        $log->time_out = Carbon::now()->setTime(18, 0);
        $log->setRelation('employee', $this->employee);

        $total = $this->service->calculateTotalMinutes($log);

        expect($total)->toBe(480);
    });

    it('never returns negative total minutes', function () {
        $log = new \App\Models\AttendanceLog();
        $log->employee_id = $this->employee->id;
        $log->time_in = Carbon::now()->setTime(9, 0);
        $log->time_out = Carbon::now()->setTime(9, 30); // apenas 30min, menos 60 almoço = negativo
        $log->setRelation('employee', $this->employee);

        $total = $this->service->calculateTotalMinutes($log);

        expect($total)->toBeGreaterThanOrEqual(0);
    });
});
