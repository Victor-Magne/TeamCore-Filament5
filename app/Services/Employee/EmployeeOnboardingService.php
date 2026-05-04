<?php

/**
 * Ficheiro do Serviço EmployeeOnboardingService.
 *
 * Este serviço centraliza a lógica de criação de um novo funcionário e das suas
 * entidades relacionadas (Utilizador, Contrato e Banco de Horas).
 * Garante que toda a operação é atómica, utilizando transacções de base de dados,
 * para evitar estados inconsistentes se algum passo falhar.
 */

namespace App\Services\Employee;

use App\Models\Contract;
use App\Models\Employee;
use App\Models\HourBank;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Throwable;

class EmployeeOnboardingService
{
    /**
     * Executa o processo de "onboarding" de um novo funcionário.
     *
     * @param  Employee  $employee  O funcionário que foi criado.
     *
     * @throws Throwable Se alguma parte do processo falhar.
     */
    public function handle(Employee $employee): void
    {
        DB::transaction(function () use ($employee) {
            $creator = Auth::user();

            // 1. Criar a conta de Utilizador (User)
            $user = User::create([
                'employee_id' => $employee->id,
                'name' => $employee->full_name,
                'email' => $employee->email,
                'password' => Hash::make(config('app.default_password', 'password123')),
                'must_change_password' => true,
                'email_verified_at' => now(),
            ]);

            // Atribui o papel (role) de 'employee'
            if (Role::where('name', 'employee')->exists()) {
                $user->assignRole('employee');
            }

            // Notifica o criador sobre o sucesso da criação da conta de utilizador
            if ($creator) {
                Notification::make()
                    ->title('Utilizador criado')
                    ->body("O utilizador associado a {$employee->full_name} foi criado com sucesso.")
                    ->success()
                    ->send()
                    ->sendToDatabase($creator);
            }

            // 2. Criar o Contrato Inicial (Placeholder)
            Contract::create([
                'employee_id' => $employee->id,
                'designation_id' => $employee->designation_id,
                'type' => 'fixed_term',
                'salary' => $employee->designation?->base_salary ?? 0,
                'status' => 'active',
                'start_date' => $employee->date_hired ?? now(),
                'daily_work_minutes' => 480,
            ]);

            if ($creator) {
                Notification::make()
                    ->title('Contrato inicial gerado')
                    ->body("Um contrato placeholder para {$employee->full_name} foi criado.")
                    ->info()
                    ->send()
                    ->sendToDatabase($creator);
            }

            // 3. Inicializar o Banco de Horas (Registo Único Acumulado)
            HourBank::firstOrCreate(
                ['employee_id' => $employee->id],
                [
                    'balance' => 0,
                    'extra_hours_added' => 0,
                    'extra_hours_used' => 0,
                ]
            );

            if ($creator) {
                Notification::make()
                    ->title('Banco de horas inicializado')
                    ->body("O banco de horas para {$employee->full_name} foi criado com saldo zero.")
                    ->warning()
                    ->send()
                    ->sendToDatabase($creator);
            }
        });
    }
}
