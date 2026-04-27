<?php

use App\Models\Employee;
use App\Models\User;
use App\Models\Vacation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('vacation balance is decremented when vacation is approved', function () {
    $employee = Employee::withoutEvents(fn () => Employee::factory()->create(['vacation_balance' => 22]));
    $admin = User::factory()->create(['employee_id' => null]);
    $this->actingAs($admin);

    $vacation = Vacation::factory()->create([
        'employee_id' => $employee->id,
        'start_date' => Carbon::create(2026, 4, 20),
        'end_date' => Carbon::create(2026, 4, 24),
        'status' => 'pending',
    ]);

    $vacation->status = 'approved';
    $vacation->save();

    $employee->refresh();
    expect($employee->vacation_balance)->toBe(17);
});

test('vacation balance is NOT decremented when vacation is rejected', function () {
    $employee = Employee::withoutEvents(fn () => Employee::factory()->create(['vacation_balance' => 22]));
    $admin = User::factory()->create(['employee_id' => null]);
    $this->actingAs($admin);

    $vacation = Vacation::factory()->create([
        'employee_id' => $employee->id,
        'start_date' => Carbon::create(2026, 4, 20),
        'end_date' => Carbon::create(2026, 4, 24),
        'status' => 'pending',
    ]);

    $vacation->update(['status' => 'rejected']);

    $employee->refresh();
    expect($employee->vacation_balance)->toBe(22);
});
