<?php

/**
 * Ficheiro do Observer EmployeeObserver.
 *
 * Este observer automatiza o processo de "onboarding" de um novo funcionário.
 * Quando um Employee é criado, o sistema cria automaticamente:
 * 1. Uma conta de utilizador (User) para acesso ao sistema.
 * 2. Um contrato inicial (Contract) com valores por defeito.
 * 3. Um registo de banco de horas (HourBank) para o mês actual.
 */

namespace App\Observers;

use App\Models\Contract;
use App\Models\Employee;
use App\Models\HourBank;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmployeeObserver
{
    /**
     * Manipula o evento "created" do Modelo Employee.
     *
     * Executa a cascata de criação de entidades relacionadas e envia notificações
     * ao utilizador que realizou a operação (normalmente um gestor de RH).
     *
     * @param Employee $employee O funcionário que acabou de ser criado.
     */
    public function created(Employee $employee): void
    {
        // Obtém o utilizador autenticado que está a criar o funcionário
        $creator = Auth::user();

        // 1. Criar a conta de Utilizador (User)
        // Por defeito, a senha é definida no ficheiro .env ou 'password123'
        // Forçamos o utilizador a mudar a senha no primeiro acesso por segurança.
        $user = User::create([
            'employee_id' => $employee->id,
            'name' => $employee->full_name,
            'email' => $employee->email,
            'password' => Hash::make(config('app.default_password', 'password123')),
            'must_change_password' => true,
            'email_verified_at' => now(),
        ]);

        // Atribui o papel (role) de 'employee' se este existir no sistema.
        if (Role::where('name', 'employee')->exists()) {
            $user->assignRole('employee');
        }

        // Notifica o criador sobre o sucesso da criação da conta de utilizador.
        if ($creator) {
            Notification::make()
                ->title('Utilizador criado')
                ->body("O utilizador associado a {$employee->full_name} foi criado com sucesso.")
                ->success()
                ->send()
                ->sendToDatabase($creator);
        }

        // 2. Criar o Contrato Inicial (Placeholder)
        // Isto garante que o funcionário tem sempre um vínculo contratual base
        // que pode ser posteriormente editado.
        Contract::create([
            'employee_id' => $employee->id,
            'designation_id' => $employee->designation_id,
            'type' => 'fixed_term', // Valor por defeito: Termo Certo
            'salary' => $employee->designation?->base_salary ?? 0,
            'status' => 'active',
            'start_date' => $employee->date_hired ?? now(),
            'daily_work_minutes' => 480, // Valor por defeito: 8 horas diárias
        ]);

        if ($creator) {
            Notification::make()
                ->title('Contrato inicial gerado')
                ->body("Um contrato placeholder para {$employee->full_name} foi criado.")
                ->info()
                ->send()
                ->sendToDatabase($creator);
        }

        // 3. Inicializar o Banco de Horas
        // Cria o registo mensal para o mês corrente com saldo a zero.
        HourBank::create([
            'employee_id' => $employee->id,
            'month_year' => now()->format('Y-m'),
            'balance' => 0,
            'extra_hours_added' => 0,
            'extra_hours_used' => 0,
            'previous_balance' => 0,
        ]);

        if ($creator) {
            Notification::make()
                ->title('Banco de horas inicializado')
                ->body("O banco de horas para {$employee->full_name} foi criado com saldo zero.")
                ->warning()
                ->send()
                ->sendToDatabase($creator);
        }
    }
}
