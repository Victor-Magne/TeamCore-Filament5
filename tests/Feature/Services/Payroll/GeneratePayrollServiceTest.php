<?php

use App\Models\Designation;
use App\Models\Employee;
use App\Models\HourBank;
use App\Models\Payroll;
use App\Services\Payroll\GeneratePayrollService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('contract is created and found correctly', function () {
    $designation = Designation::factory()->create();
    $employee = Employee::factory()->create(['designation_id' => $designation->id]);

    // O observer do Employee cria um contrato automaticamente
    // Vamos verificar se conseguimos encontrá-lo
    $foundContract = $employee->contracts()->where('status', 'active')->first();
    expect($foundContract)->not->toBeNull();

    // Agora atualiza o salário do contrato para 2000
    $foundContract->update(['salary' => 2000.00]);

    // Recarrega para obter valor atualizado
    $foundContract->refresh();
    expect((float) $foundContract->salary)->toBe(2000.0);
});

it('generates payroll with base salary from contract', function () {
    $designation = Designation::factory()->create(['base_salary' => 2000.00]);
    $employee = Employee::factory()->create(['designation_id' => $designation->id]);

    // O observer cria um contrato automaticamente com base_salary da designação
    $contract = $employee->contracts()->where('status', 'active')->first();
    expect($contract)->not->toBeNull();
    expect((float) $contract->salary)->toBe(2000.0);

    $service = new GeneratePayrollService;
    $payroll = $service->handle($employee, '2026-04');

    expect($payroll)->toBeInstanceOf(Payroll::class);
    expect($payroll->employee_id)->toBe($employee->id);
    expect($payroll->month_year)->toBe('2026-04');
    expect((float) $payroll->base_salary)->toBe(2000.0);
    expect($payroll->status)->toBe('pending');
});

it('calculates basic salary without extras or deductions', function () {
    $designation = Designation::factory()->create(['base_salary' => 1760.00]);
    $employee = Employee::factory()->create(['designation_id' => $designation->id]);

    $service = new GeneratePayrollService;
    $payroll = $service->handle($employee, '2026-04');

    expect((float) $payroll->base_salary)->toBe(1760.0);
    expect((float) $payroll->extra_hours_amount)->toBe(0.0);
    expect((float) $payroll->deductions)->toBe(0.0);
    expect((float) $payroll->total_net)->toBe(1760.0);
});

it('calculates extra hours with 1.5 multiplier correctly', function () {
    $designation = Designation::factory()->create(['base_salary' => 2200.00]);
    $employee = Employee::factory()->create(['designation_id' => $designation->id]);

    HourBank::factory()->create([
        'employee_id' => $employee->id,
        'month_year' => '2026-05',
        'extra_hours_added' => 240,
        'extra_hours_used' => 0,
        'balance' => 240,
        'previous_balance' => 0,
    ]);

    $service = new GeneratePayrollService;
    $payroll = $service->handle($employee, '2026-05');

    expect((float) $payroll->extra_hours_amount)->toBe(75.0);
    expect((float) $payroll->total_net)->toBe(2275.0);
});

it('applies deductions for used hours', function () {
    $designation = Designation::factory()->create(['base_salary' => 2200.00]);
    $employee = Employee::factory()->create(['designation_id' => $designation->id]);

    HourBank::factory()->create([
        'employee_id' => $employee->id,
        'month_year' => '2026-06',
        'extra_hours_added' => 0,
        'extra_hours_used' => 120,
        'balance' => -120,
        'previous_balance' => 0,
    ]);

    $service = new GeneratePayrollService;
    $payroll = $service->handle($employee, '2026-06');

    expect((float) $payroll->deductions)->toBe(25.0);
    expect((float) $payroll->total_net)->toBe(2175.0);
});

it('combines extras and deductions in payroll', function () {
    $designation = Designation::factory()->create(['base_salary' => 2200.00]);
    $employee = Employee::factory()->create(['designation_id' => $designation->id]);

    HourBank::factory()->create([
        'employee_id' => $employee->id,
        'month_year' => '2026-07',
        'extra_hours_added' => 240,
        'extra_hours_used' => 120,
        'balance' => 120,
        'previous_balance' => 0,
    ]);

    $service = new GeneratePayrollService;
    $payroll = $service->handle($employee, '2026-07');

    expect((float) $payroll->base_salary)->toBe(2200.0);
    expect((float) $payroll->extra_hours_amount)->toBe(75.0);
    expect((float) $payroll->deductions)->toBe(25.0);
    expect((float) $payroll->total_net)->toBe(2250.0);
});

it('updates existing payroll instead of creating duplicate', function () {
    $designation = Designation::factory()->create(['base_salary' => 2000.00]);
    $employee = Employee::factory()->create(['designation_id' => $designation->id]);

    $service = new GeneratePayrollService;
    $payroll1 = $service->handle($employee, '2026-04');
    $payroll2 = $service->handle($employee, '2026-04');

    expect($payroll1->id)->toBe($payroll2->id);
    expect(Payroll::where('employee_id', $employee->id)->where('month_year', '2026-04')->count())->toBe(1);
});
