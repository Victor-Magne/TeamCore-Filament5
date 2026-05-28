<?php

use App\Models\Designation;
use App\Models\Employee;
use App\Models\Vacation;
use App\Services\Vacation\VacationBalanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('VacationBalanceService', function () {
    beforeEach(function () {
        $designation = Designation::factory()->create();
        $this->employee = Employee::factory()->create([
            'designation_id' => $designation->id,
            'vacation_balance' => 22,
        ]);
        $this->service = app(VacationBalanceService::class);
    });

    it('deducts balance on approval', function () {
        $vacation = Vacation::factory()->create([
            'employee_id' => $this->employee->id,
            'days_taken' => 5,
            'status' => 'pending',
        ]);

        $this->service->deductOnApproval($vacation);

        expect($this->employee->fresh()->vacation_balance)->toBe(17);
    });

    it('restores balance on revocation', function () {
        $vacation = Vacation::factory()->create([
            'employee_id' => $this->employee->id,
            'days_taken' => 5,
            'status' => 'approved',
        ]);

        $this->employee->update(['vacation_balance' => 17]);

        $this->service->restoreOnRevocation($vacation);

        expect($this->employee->fresh()->vacation_balance)->toBe(22);
    });

    it('adjusts balance when approved vacation duration increases', function () {
        $vacation = Vacation::factory()->create([
            'employee_id' => $this->employee->id,
            'days_taken' => 5,
            'status' => 'approved',
        ]);

        $this->employee->update(['vacation_balance' => 17]);

        // Simula mudança de days_taken de 5 para 8
        $vacation->forceFill(['days_taken' => 8])->save(['timestamps' => false]);

        $this->service->adjustOnDaysChange($vacation);

        expect($this->employee->fresh()->vacation_balance)->toBe(14);
    });

    it('restores balance when approved vacation is deleted', function () {
        $vacation = Vacation::factory()->create([
            'employee_id' => $this->employee->id,
            'days_taken' => 5,
            'status' => 'approved',
        ]);

        $this->employee->update(['vacation_balance' => 17]);

        $this->service->restoreOnDelete($vacation);

        expect($this->employee->fresh()->vacation_balance)->toBe(22);
    });

    it('does not restore balance when pending vacation is deleted', function () {
        $vacation = Vacation::factory()->create([
            'employee_id' => $this->employee->id,
            'days_taken' => 5,
            'status' => 'pending',
        ]);

        $this->service->restoreOnDelete($vacation);

        expect($this->employee->fresh()->vacation_balance)->toBe(22);
    });

    it('does not deduct zero days', function () {
        $vacation = Vacation::factory()->create([
            'employee_id' => $this->employee->id,
            'days_taken' => 0,
            'status' => 'pending',
        ]);

        $this->service->deductOnApproval($vacation);

        expect($this->employee->fresh()->vacation_balance)->toBe(22);
    });
});

describe('Vacation model balance integration', function () {
    beforeEach(function () {
        $designation = Designation::factory()->create();
        $this->employee = Employee::factory()->create([
            'designation_id' => $designation->id,
            'vacation_balance' => 22,
        ]);
    });

    it('deducts balance automatically when vacation is created as approved', function () {
        Vacation::create([
            'employee_id' => $this->employee->id,
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(14),
            'days_taken' => 5,
            'status' => 'approved',
        ]);

        expect($this->employee->fresh()->vacation_balance)->toBe(17);
    });

    it('deducts balance when status changes from pending to approved', function () {
        $vacation = Vacation::create([
            'employee_id' => $this->employee->id,
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(14),
            'days_taken' => 5,
            'status' => 'pending',
        ]);

        expect($this->employee->fresh()->vacation_balance)->toBe(22);

        $vacation->update(['status' => 'approved']);

        expect($this->employee->fresh()->vacation_balance)->toBe(17);
    });

    it('restores balance when approved vacation is revoked to rejected', function () {
        $vacation = Vacation::create([
            'employee_id' => $this->employee->id,
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(14),
            'days_taken' => 5,
            'status' => 'approved',
        ]);

        expect($this->employee->fresh()->vacation_balance)->toBe(17);

        $vacation->update(['status' => 'rejected']);

        expect($this->employee->fresh()->vacation_balance)->toBe(22);
    });

    it('restores balance when approved vacation is soft deleted', function () {
        $vacation = Vacation::create([
            'employee_id' => $this->employee->id,
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(14),
            'days_taken' => 5,
            'status' => 'approved',
        ]);

        expect($this->employee->fresh()->vacation_balance)->toBe(17);

        $vacation->delete();

        expect($this->employee->fresh()->vacation_balance)->toBe(22);
    });

    it('does not deduct balance when vacation is rejected', function () {
        Vacation::create([
            'employee_id' => $this->employee->id,
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(14),
            'days_taken' => 5,
            'status' => 'rejected',
        ]);

        expect($this->employee->fresh()->vacation_balance)->toBe(22);
    });
});
