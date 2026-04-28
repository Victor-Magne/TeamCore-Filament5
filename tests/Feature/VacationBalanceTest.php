<?php

use App\Models\Designation;
use App\Models\Employee;
use App\Models\Vacation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('vacation balance is decremented when vacation is approved', function () {
    $designation = Designation::factory()->create(['name' => 'Designation Vacation Balance A']);
    $employee = Employee::factory()->create([
        'designation_id' => $designation->id,
        'vacation_balance' => 22,
    ]);
    $this->actingAs($employee->user);

    $vacation = Vacation::factory()->create([
        'employee_id' => $employee->id,
        'approved_by' => $employee->user->id,
        'days_taken' => 5,
        'status' => 'pending',
    ]);

    $vacation->status = 'approved';
    $vacation->save();

    $employee->refresh();
    expect($employee->vacation_balance)->toBeLessThan(22);
});

test('vacation balance is NOT decremented when vacation is rejected', function () {
    $designation = Designation::factory()->create(['name' => 'Designation Vacation Balance B']);
    $employee = Employee::factory()->create([
        'designation_id' => $designation->id,
        'vacation_balance' => 22,
    ]);
    $this->actingAs($employee->user);

    $vacation = Vacation::factory()->create([
        'employee_id' => $employee->id,
        'approved_by' => $employee->user->id,
        'days_taken' => 5,
        'status' => 'pending',
    ]);

    $vacation->update(['status' => 'rejected']);

    $employee->refresh();
    expect($employee->vacation_balance)->toBe(22);
});
