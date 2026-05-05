<?php

use App\Models\Absence;
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

it('restores hour bank balance when a leave is approved for a date with an existing absence', function () {
    $employee = Employee::factory()->create();
    $contract = Contract::factory()->create([
        'employee_id' => $employee->id,
        'daily_work_minutes' => 480,
        'expected_start_time' => '09:00:00',
    ]);

    $service = new DeductHourBankService;
    $absenceDate = Carbon::create(2026, 4, 20); // A Monday

    // 1. Create a full absence
    $service->registerFullAbsence($employee->id, $absenceDate, 'Falta injustificada');

    // Verify absence exists and balance is negative
    $absence = Absence::where('employee_id', $employee->id)->first();
    expect($absence)->not->toBeNull();
    expect($absence->hours_deducted)->toBe(480);

    $hourBank = HourBank::where('employee_id', $employee->id)->first();
    expect($hourBank->balance)->toBe(-480);

    // 2. Create and approve a paid leave for the same date
    LeaveAndAbsence::create([
        'employee_id' => $employee->id,
        'type' => 'sick_leave',
        'start_date' => $absenceDate,
        'end_date' => $absenceDate,
        'status' => 'approved',
        'is_paid' => true,
    ]);

    // 3. Verify absence is gone
    $absenceAfter = Absence::where('employee_id', $employee->id)->whereDate('absence_date', $absenceDate)->first();
    expect($absenceAfter)->toBeNull();

    // 4. Verify balance is restored to 0
    $hourBank->refresh();
    expect($hourBank->balance)->toBe(0);
    expect($hourBank->extra_hours_used)->toBe(0);
});
