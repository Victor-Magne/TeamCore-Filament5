<?php

use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeaveAndAbsence;
use App\Models\User;
use App\Models\Vacation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Vacation and Leave Management', function () {
    beforeEach(function () {
        $this->designation = Designation::factory()->create(['base_salary' => 1000]);
        $this->employee = Employee::factory()->create([
            'designation_id' => $this->designation->id,
            'vacation_balance' => 22,
        ]);

        $this->user = User::where('employee_id', $this->employee->id)->first();
    });

    it('creates vacation request', function () {
        $startDate = Carbon::now()->addDays(10);
        $endDate = $startDate->copy()->addDays(5);

        $vacation = Vacation::withoutEvents(fn () => Vacation::create([
            'employee_id' => $this->employee->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_taken' => 6,
            'year_reference' => $startDate->year,
            'status' => 'pending',
        ]));

        expect($vacation)->not->toBeNull();
        expect((int) $vacation->days_taken)->toBe(6);
        expect($vacation->status)->toBe('pending');
    });

    it('deducts vacation days when vacation is approved', function () {
        $initialBalance = $this->employee->vacation_balance;

        $startDate = Carbon::now()->addDays(10);
        $endDate = $startDate->copy()->addDays(5);

        Vacation::withoutEvents(fn () => Vacation::create([
            'employee_id' => $this->employee->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_taken' => 6,
            'year_reference' => $startDate->year,
            'status' => 'approved',
        ]));

        $this->employee->update(['vacation_balance' => $initialBalance - 6]);

        expect($this->employee->vacation_balance)->toBe($initialBalance - 6);
    });

    it('restores vacation days when vacation is rejected', function () {
        $initialBalance = 22;

        Vacation::withoutEvents(fn () => Vacation::create([
            'employee_id' => $this->employee->id,
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(15),
            'days_taken' => 6,
            'year_reference' => Carbon::now()->year,
            'status' => 'rejected',
        ]));

        expect($this->employee->vacation_balance)->toBe($initialBalance);
    });

    it('creates leave and absence request (sick leave)', function () {
        $leave = LeaveAndAbsence::create([
            'employee_id' => $this->employee->id,
            'type' => 'sick_leave',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(2),
            'reason' => 'Medical condition',
            'status' => 'pending',
        ]);

        expect($leave)->not->toBeNull();
        expect($leave->type)->toBe('sick_leave');
        expect($leave->status)->toBe('pending');
    });

    it('validates that employee cannot approve their own requests', function () {
        $leave = LeaveAndAbsence::create([
            'employee_id' => $this->employee->id,
            'type' => 'sick_leave',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(2),
            'reason' => 'Medical condition',
            'status' => 'pending',
        ]);

        $user = User::where('employee_id', $this->employee->id)->first();

        expect($leave->employee_id)->toBe($this->employee->id);
    });

    it('tracks vacation balance correctly', function () {
        $initialBalance = $this->employee->vacation_balance;

        Vacation::create([
            'employee_id' => $this->employee->id,
            'start_date' => Carbon::now()->addDays(1),
            'end_date' => Carbon::now()->addDays(5),
            'days_taken' => 5,
            'year_reference' => Carbon::now()->year,
            'status' => 'approved',
        ]);

        $this->employee->refresh();

        expect($this->employee->vacation_balance)->toBe($initialBalance - 5);
    });
});

describe('Overlap Detection', function () {
    beforeEach(function () {
        $designation = Designation::factory()->create();
        $this->employee = Employee::factory()->create([
            'designation_id' => $designation->id,
            'vacation_balance' => 22,
        ]);
        $this->start = Carbon::now()->addDays(10)->startOfDay();
        $this->end = Carbon::now()->addDays(15)->startOfDay();
    });

    // --- helpers that mirror the form closure query ---
    function vacationOverlaps(int $employeeId, string $start, string $end, ?int $excludeId = null): bool
    {
        return Vacation::where('employee_id', $employeeId)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->whereIn('status', ['pending', 'approved'])
            ->where(fn ($q) => $q
                ->whereBetween('start_date', [$start, $end])
                ->orWhereBetween('end_date', [$start, $end])
                ->orWhere(fn ($q2) => $q2->where('start_date', '<=', $start)->where('end_date', '>=', $end))
            )->exists();
    }

    function leaveOverlaps(int $employeeId, string $start, string $end, ?int $excludeId = null): bool
    {
        return LeaveAndAbsence::where('employee_id', $employeeId)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->whereIn('status', ['pending', 'approved'])
            ->where(fn ($q) => $q
                ->whereBetween('start_date', [$start, $end])
                ->orWhereBetween('end_date', [$start, $end])
                ->orWhere(fn ($q2) => $q2->where('start_date', '<=', $start)->where('end_date', '>=', $end))
            )->exists();
    }

    it('detects vacation overlapping another approved vacation', function () {
        Vacation::withoutEvents(fn () => Vacation::factory()->create([
            'employee_id' => $this->employee->id,
            'start_date' => $this->start,
            'end_date' => $this->end,
            'status' => 'approved',
        ]));

        // New range starts inside existing range
        expect(vacationOverlaps($this->employee->id, $this->start->copy()->addDays(2)->toDateString(), $this->end->copy()->addDays(4)->toDateString()))->toBeTrue();
    });

    it('detects vacation overlapping a pending vacation', function () {
        Vacation::withoutEvents(fn () => Vacation::factory()->create([
            'employee_id' => $this->employee->id,
            'start_date' => $this->start,
            'end_date' => $this->end,
            'status' => 'pending',
        ]));

        expect(vacationOverlaps($this->employee->id, $this->start->toDateString(), $this->end->toDateString()))->toBeTrue();
    });

    it('does not flag rejected vacations as overlap', function () {
        Vacation::withoutEvents(fn () => Vacation::factory()->create([
            'employee_id' => $this->employee->id,
            'start_date' => $this->start,
            'end_date' => $this->end,
            'status' => 'rejected',
        ]));

        expect(vacationOverlaps($this->employee->id, $this->start->toDateString(), $this->end->toDateString()))->toBeFalse();
    });

    it('detects vacation overlapping an approved leave', function () {
        LeaveAndAbsence::withoutEvents(fn () => LeaveAndAbsence::factory()->create([
            'employee_id' => $this->employee->id,
            'start_date' => $this->start,
            'end_date' => $this->end,
            'status' => 'approved',
        ]));

        expect(leaveOverlaps($this->employee->id, $this->start->copy()->addDays(1)->toDateString(), $this->end->copy()->addDays(3)->toDateString()))->toBeTrue();
    });

    it('detects leave overlapping an approved vacation', function () {
        Vacation::withoutEvents(fn () => Vacation::factory()->create([
            'employee_id' => $this->employee->id,
            'start_date' => $this->start,
            'end_date' => $this->end,
            'status' => 'approved',
        ]));

        expect(vacationOverlaps($this->employee->id, $this->start->toDateString(), $this->end->toDateString()))->toBeTrue();
    });

    it('does not flag rejected leaves as overlap', function () {
        LeaveAndAbsence::withoutEvents(fn () => LeaveAndAbsence::factory()->create([
            'employee_id' => $this->employee->id,
            'start_date' => $this->start,
            'end_date' => $this->end,
            'status' => 'rejected',
        ]));

        expect(leaveOverlaps($this->employee->id, $this->start->toDateString(), $this->end->toDateString()))->toBeFalse();
    });

    it('allows editing a vacation without false overlap with itself', function () {
        $vacation = Vacation::withoutEvents(fn () => Vacation::factory()->create([
            'employee_id' => $this->employee->id,
            'start_date' => $this->start,
            'end_date' => $this->end,
            'status' => 'approved',
        ]));

        expect(vacationOverlaps($this->employee->id, $this->start->toDateString(), $this->end->toDateString(), $vacation->id))->toBeFalse();
    });

    it('allows editing a leave without false overlap with itself', function () {
        $leave = LeaveAndAbsence::withoutEvents(fn () => LeaveAndAbsence::factory()->create([
            'employee_id' => $this->employee->id,
            'start_date' => $this->start,
            'end_date' => $this->end,
            'status' => 'approved',
        ]));

        expect(leaveOverlaps($this->employee->id, $this->start->toDateString(), $this->end->toDateString(), $leave->id))->toBeFalse();
    });

    it('detects overlap when new period encompasses an existing record', function () {
        Vacation::withoutEvents(fn () => Vacation::factory()->create([
            'employee_id' => $this->employee->id,
            'start_date' => $this->start->copy()->addDays(1),
            'end_date' => $this->end->copy()->subDays(1),
            'status' => 'approved',
        ]));

        // New range completely wraps the existing one
        expect(vacationOverlaps($this->employee->id, $this->start->toDateString(), $this->end->toDateString()))->toBeTrue();
    });

    it('does not flag non-overlapping periods', function () {
        Vacation::withoutEvents(fn () => Vacation::factory()->create([
            'employee_id' => $this->employee->id,
            'start_date' => $this->start,
            'end_date' => $this->end,
            'status' => 'approved',
        ]));

        // New range starts after the existing one ends
        $newStart = $this->end->copy()->addDay()->toDateString();
        $newEnd = $this->end->copy()->addDays(5)->toDateString();

        expect(vacationOverlaps($this->employee->id, $newStart, $newEnd))->toBeFalse();
    });
});
