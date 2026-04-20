<?php

use App\Filament\App\Widgets\EmployeeInfoWidget;
use App\Filament\App\Widgets\EmployeeLeaveWidget;
use App\Filament\App\Widgets\EmployeeVacationWidget;
use App\Filament\Widgets\EmployeeActionsWidget;
use App\Models\Employee;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->employee = Employee::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
    ]);

    // EmployeeObserver already creates a user for the employee
    $this->user = User::where('employee_id', $this->employee->id)->first();
});

test('employee info widget displays correct data', function () {
    $this->actingAs($this->user);

    Livewire::test(EmployeeInfoWidget::class)
        ->assertSee('John Doe')
        ->assertSee('john@example.com');
});

test('employee vacation widget can be rendered', function () {
    $this->actingAs($this->user);

    Livewire::test(EmployeeVacationWidget::class)
        ->assertStatus(200);
});

test('employee leave widget can be rendered', function () {
    $this->actingAs($this->user);

    Livewire::test(EmployeeLeaveWidget::class)
        ->assertStatus(200);
});

test('employee actions widget has correct actions', function () {
    $this->actingAs($this->user);

    Livewire::test(EmployeeActionsWidget::class)
        ->assertActionExists('requestVacation')
        ->assertActionExists('requestLeave');
});
