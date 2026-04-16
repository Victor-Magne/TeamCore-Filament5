<?php

use App\Models\Employee;
use App\Models\User;
use App\Models\Vacation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('vacation balance is decremented when vacation is approved', function () {
    $employee = Employee::factory()->create(['vacation_balance' => 22]);
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $vacation = Vacation::factory()->create([
        'employee_id' => $employee->id,
        'days_taken' => 5,
        'status' => 'pending',
    ]);

    $vacation->update(['status' => 'approved']);

    $employee->refresh();
    expect($employee->vacation_balance)->toBe(17);
});

test('vacation balance is NOT decremented when vacation is rejected', function () {
    $employee = Employee::factory()->create(['vacation_balance' => 22]);
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $vacation = Vacation::factory()->create([
        'employee_id' => $employee->id,
        'days_taken' => 5,
        'status' => 'pending',
    ]);

    $vacation->update(['status' => 'rejected']);

    $employee->refresh();
    expect($employee->vacation_balance)->toBe(22);
});
