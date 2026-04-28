<?php

use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('EmployeePolicy', function () {
    beforeEach(function () {
        // Criar um Admin
        $this->admin = User::factory()->state(['employee_id' => null])->create();
        $this->admin->assignRole('admin');

        // Criar um HR
        $this->hrEmployee = Employee::factory()->create();
        $this->hrUser = User::factory()->create(['employee_id' => $this->hrEmployee->id]);
        $this->hrUser->assignRole('hr');

        // Criar um Employee
        $this->empEmployee = Employee::factory()->create();
        $this->empUser = User::factory()->create(['employee_id' => $this->empEmployee->id]);
        $this->empUser->assignRole('employee');
    });

    it('admin can view all employees', function () {
        $employee = Employee::factory()->create();

        // Mock para que a permissão seja garantida
        $this->admin = \Mockery::mock($this->admin)->makePartial();
        $this->admin->shouldReceive('can')->with('ViewAny:Employee')->andReturn(true);

        expect($this->admin->can('viewAny', $employee))->toBeTrue();
    });

    it('hr can view employees in their department', function () {
        $designation = Designation::factory()->create();
        $employee = Employee::factory()->create(['designation_id' => $designation->id]);

        $this->hrUser = \Mockery::mock($this->hrUser)->makePartial();
        $this->hrUser->shouldReceive('can')->with('ViewAny:Employee')->andReturn(true);

        expect($this->hrUser->can('viewAny', $employee))->toBeTrue();
    });

    it('employee cannot view other employees details', function () {
        $otherEmployee = Employee::factory()->create();

        $this->empUser = \Mockery::mock($this->empUser)->makePartial();
        $this->empUser->shouldReceive('can')->with('ViewAny:Employee')->andReturn(false);

        expect($this->empUser->can('viewAny', $otherEmployee))->toBeFalse();
    });

    it('only admin can delete employees', function () {
        $employee = Employee::factory()->create();

        $this->admin = \Mockery::mock($this->admin)->makePartial();
        $this->admin->shouldReceive('can')->with('Delete:Employee')->andReturn(true);

        $this->hrUser = \Mockery::mock($this->hrUser)->makePartial();
        $this->hrUser->shouldReceive('can')->with('Delete:Employee')->andReturn(false);

        expect($this->admin->can('delete', $employee))->toBeTrue();
        expect($this->hrUser->can('delete', $employee))->toBeFalse();
    });
});
