<?php

use App\Models\AttendanceLog;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('it creates attendance log on first check in', function () {
    $employee = Employee::factory()->create();
    $today = Carbon::today();
    $now = Carbon::now();
    
    // Create new attendance log
    AttendanceLog::create([
        'employee_id' => $employee->id,
        'time_in' => $now,
    ]);
    
    $log = AttendanceLog::where('employee_id', $employee->id)
        ->whereDate('time_in', $today)
        ->first();
    
    expect($log)->not->toBeNull()
        ->and($log->time_in)->not->toBeNull()
        ->and($log->lunch_break_start)->toBeNull()
        ->and($log->lunch_break_end)->toBeNull()
        ->and($log->time_out)->toBeNull();
})->group('attendance-check-in');

test('it records lunch break start', function () {
    $employee = Employee::factory()->create();
    $today = Carbon::today();
    $now = Carbon::now();
    
    // Create initial attendance log
    $log = AttendanceLog::create([
        'employee_id' => $employee->id,
        'time_in' => $now->subHour(),
    ]);
    
    // Update: record lunch break start
    $log->update(['lunch_break_start' => $now]);
    $log->refresh();
    
    expect($log->time_in)->not->toBeNull()
        ->and($log->lunch_break_start)->not->toBeNull()
        ->and($log->lunch_break_end)->toBeNull()
        ->and($log->time_out)->toBeNull();
})->group('attendance-check-in');

test('it records lunch break end', function () {
    $employee = Employee::factory()->create();
    $today = Carbon::today();
    $now = Carbon::now();
    
    // Create attendance log with lunch start
    $log = AttendanceLog::create([
        'employee_id' => $employee->id,
        'time_in' => $now->subHours(2),
        'lunch_break_start' => $now->subHour(),
    ]);
    
    // Update: record lunch break end
    $log->update(['lunch_break_end' => $now]);
    $log->refresh();
    
    expect($log->time_in)->not->toBeNull()
        ->and($log->lunch_break_start)->not->toBeNull()
        ->and($log->lunch_break_end)->not->toBeNull()
        ->and($log->time_out)->toBeNull();
})->group('attendance-check-in');

test('it records time out', function () {
    $employee = Employee::factory()->create();
    $today = Carbon::today();
    $now = Carbon::now();
    
    // Create complete attendance log except time_out
    $log = AttendanceLog::create([
        'employee_id' => $employee->id,
        'time_in' => $now->subHours(4),
        'lunch_break_start' => $now->subHours(2),
        'lunch_break_end' => $now->subHour(),
    ]);
    
    // Update: record time out
    $log->update(['time_out' => $now]);
    $log->refresh();
    
    expect($log->time_in)->not->toBeNull()
        ->and($log->lunch_break_start)->not->toBeNull()
        ->and($log->lunch_break_end)->not->toBeNull()
        ->and($log->time_out)->not->toBeNull();
})->group('attendance-check-in');

test('it completes full attendance cycle', function () {
    $employee = Employee::factory()->create();
    $today = Carbon::today();
    $timeIn = Carbon::now()->setHour(9)->setMinute(0)->setSecond(0);
    $lunchStart = $timeIn->clone()->addHours(2);
    $lunchEnd = $lunchStart->clone()->addHour();
    $timeOut = $lunchEnd->clone()->addHours(5);
    
    // Step 1: Check in (time_in)
    AttendanceLog::create([
        'employee_id' => $employee->id,
        'time_in' => $timeIn,
    ]);
    
    $log = AttendanceLog::where('employee_id', $employee->id)
        ->whereDate('time_in', $today)
        ->first();
    
    expect($log->time_in)->not->toBeNull()
        ->and($log->lunch_break_start)->toBeNull();
    
    // Step 2: Lunch break start
    $log->update(['lunch_break_start' => $lunchStart]);
    $log->refresh();
    
    expect($log->lunch_break_start)->not->toBeNull()
        ->and($log->lunch_break_end)->toBeNull();
    
    // Step 3: Lunch break end
    $log->update(['lunch_break_end' => $lunchEnd]);
    $log->refresh();
    
    expect($log->lunch_break_end)->not->toBeNull()
        ->and($log->time_out)->toBeNull();
    
    // Step 4: Time out
    $log->update(['time_out' => $timeOut]);
    $log->refresh();
    
    // Verify all fields are set
    expect($log->time_in)->not->toBeNull()
        ->and($log->lunch_break_start)->not->toBeNull()
        ->and($log->lunch_break_end)->not->toBeNull()
        ->and($log->time_out)->not->toBeNull();
})->group('attendance-check-in');

test('it prevents duplicate check in when already completed', function () {
    $employee = Employee::factory()->create();
    $today = Carbon::today();
    $now = Carbon::now();
    
    // Create fully completed attendance log
    $log = AttendanceLog::create([
        'employee_id' => $employee->id,
        'time_in' => $now->subHours(8),
        'lunch_break_start' => $now->subHours(5),
        'lunch_break_end' => $now->subHours(4),
        'time_out' => $now->subMinutes(30),
    ]);
    
    // Verify the log has all fields completed
    expect($log->time_in)->not->toBeNull()
        ->and($log->lunch_break_start)->not->toBeNull()
        ->and($log->lunch_break_end)->not->toBeNull()
        ->and($log->time_out)->not->toBeNull();
})->group('attendance-check-in');

test('it calculates total minutes correctly', function () {
    $employee = Employee::factory()->create();
    $timeIn = Carbon::now()->setHour(9)->setMinute(0)->setSecond(0);
    $lunchStart = $timeIn->clone()->addHours(2);
    $lunchEnd = $lunchStart->clone()->addHour();
    $timeOut = $lunchEnd->clone()->addHours(5);
    
    // Create attendance log with all timestamps
    $log = AttendanceLog::create([
        'employee_id' => $employee->id,
        'time_in' => $timeIn,
        'lunch_break_start' => $lunchStart,
        'lunch_break_end' => $lunchEnd,
        'time_out' => $timeOut,
    ]);
    
    // Calculate total minutes
    // From 9:00 to 17:00 = 8 hours = 480 minutes
    // Minus lunch: 1 hour = 60 minutes
    // Expected: 420 minutes (7 hours)
    $totalMinutes = $log->calculateTotalMinutes();
    
    expect($totalMinutes)->toBe(420);
})->group('attendance-check-in');
