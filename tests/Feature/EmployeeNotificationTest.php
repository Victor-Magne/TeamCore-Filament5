<?php

use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('sends database notifications to the creator when an employee is created', function () {
    // 1. Setup: Criar um utilizador (Admin/RH) que criará o funcionário
    // Usamos state para evitar que o factory crie um employee automaticamente (causando conflitos de unique)
    $admin = User::factory()->state(['employee_id' => null])->create();
    Auth::login($admin);

    // Precisamos de uma designation para o employee
    $designation = Designation::factory()->create(['base_salary' => 1000]);

    // 2. Action: Criar um Employee (isso dispara o Observer)
    $employee = Employee::factory()->create([
        'designation_id' => $designation->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
    ]);

    // 3. Assert: Verificar se as notificações foram para o banco de dados para o $admin
    // O EmployeeObserver dispara 3 notificações: Utilizador, Contrato, Banco de Horas

    $notifications = DB::table('notifications')->where('notifiable_id', $admin->id)->get();

    expect($notifications)->toHaveCount(3);

    $titles = $notifications->map(fn ($n) => json_decode($n->data)->title)->toArray();

    expect($titles)->toContain('Utilizador criado');
    expect($titles)->toContain('Contrato inicial gerado');
    expect($titles)->toContain('Banco de horas inicializado');
});
