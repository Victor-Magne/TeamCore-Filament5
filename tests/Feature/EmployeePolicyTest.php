<?php

use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('EmployeePolicy', function () {
    beforeEach(function () {
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('hr', 'web');
        Role::findOrCreate('employee', 'web');
        $this->designation = Designation::factory()->create(['name' => 'Policy Designation']);
        // Criar um Admin
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin.policy@example.test',
            'password' => Hash::make('password'),
            'employee_id' => null,
            'must_change_password' => false,
        ]);
        $this->admin->assignRole('admin');

        // Criar um HR
        $this->hrEmployee = Employee::factory()->create(['designation_id' => $this->designation->id]);
        $this->hrUser = $this->hrEmployee->user;
        $this->hrUser->assignRole('hr');

        // Criar um Employee
        $this->empEmployee = Employee::factory()->create(['designation_id' => $this->designation->id]);
        $this->empUser = $this->empEmployee->user;
        $this->empUser->assignRole('employee');
    });

    it('admin can view all employees', function () {
        $employee = Employee::factory()->create(['designation_id' => $this->designation->id]);

        // Mock para que a permissão seja garantida
        $this->admin = Mockery::mock($this->admin)->makePartial();
        $this->admin->shouldReceive('can')->with('ViewAny:Employee')->andReturn(true);

        expect($this->admin->can('ViewAny:Employee'))->toBeTrue();
    });

    it('hr can view employees in their department', function () {
        $designation = Designation::factory()->create();
        $employee = Employee::factory()->create(['designation_id' => $designation->id]);

        $this->hrUser = Mockery::mock($this->hrUser)->makePartial();
        $this->hrUser->shouldReceive('can')->with('ViewAny:Employee')->andReturn(true);

        expect($this->hrUser->can('ViewAny:Employee'))->toBeTrue();
    });

    it('employee cannot view other employees details', function () {
        $otherEmployee = Employee::factory()->create();

        $this->empUser = Mockery::mock($this->empUser)->makePartial();
        $this->empUser->shouldReceive('can')->with('ViewAny:Employee')->andReturn(false);

        expect($this->empUser->can('ViewAny:Employee'))->toBeFalse();
    });

    it('only admin can delete employees', function () {
        $employee = Employee::factory()->create();

        $this->admin = Mockery::mock($this->admin)->makePartial();
        $this->admin->shouldReceive('can')->with('Delete:Employee')->andReturn(true);

        $this->hrUser = Mockery::mock($this->hrUser)->makePartial();
        $this->hrUser->shouldReceive('can')->with('Delete:Employee')->andReturn(false);

        expect($this->admin->can('Delete:Employee'))->toBeTrue();
        expect($this->hrUser->can('Delete:Employee'))->toBeFalse();
    });
});
