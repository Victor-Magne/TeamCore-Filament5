<?php

use App\Models\Contract;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\HourBank;
use App\Models\User;
use App\Services\Employee\EmployeeOnboardingService;
use Illuminate\Database\QueryException;

it('rolls back everything if onboarding fails', function () {
    $designation = Designation::factory()->create(['base_salary' => 1000]);

    // Desactivar o observer para criar o funcionário sem disparar o onboarding real
    Employee::unsetEventDispatcher();

    $employee1 = Employee::factory()->create([
        'email' => 'existing@example.com',
    ]);

    $employee2 = Employee::factory()->create([
        'first_name' => 'Test',
        'last_name' => 'Onboarding',
        'email' => 'fail@example.com',
        'designation_id' => $designation->id,
    ]);

    // Criar um User para o employee1 com o email que o employee2 vai tentar usar
    User::create([
        'employee_id' => $employee1->id,
        'name' => 'Existing',
        'email' => 'fail@example.com', // Mesmo email vai causar erro de UNIQUE
        'password' => 'password',
    ]);

    $service = new EmployeeOnboardingService;

    try {
        $service->handle($employee2);
    } catch (QueryException $e) {
        // Sucesso em apanhar a falha de integridade
    }

    // O User com employee_id do funcionário de teste NÃO deve ter sido criado
    expect(User::where('employee_id', $employee2->id)->exists())->toBeFalse();
    // O contrato também não deve existir (rollback da transação)
    expect(Contract::where('employee_id', $employee2->id)->exists())->toBeFalse();
});

it('completes onboarding successfully within a transaction', function () {
    $designation = Designation::factory()->create(['base_salary' => 1000]);
    $employee = Employee::factory()->make([
        'first_name' => 'Success',
        'last_name' => 'Onboarding',
        'email' => 'success@example.com',
        'designation_id' => $designation->id,
    ]);

    // Desactivar o observer para chamar o serviço manualmente
    Employee::unsetEventDispatcher();
    $employee->save();

    $service = new EmployeeOnboardingService;
    $service->handle($employee);

    expect(User::where('employee_id', $employee->id)->exists())->toBeTrue();
    expect(Contract::where('employee_id', $employee->id)->exists())->toBeTrue();
    expect(HourBank::where('employee_id', $employee->id)->exists())->toBeTrue();
});
