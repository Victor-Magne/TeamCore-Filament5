<?php

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
     * Handle the Employee "created" event.
     */
    public function created(Employee $employee): void
    {
        $creator = Auth::user();

        // 1. Criar Utilizador
        $user = User::create([
            'employee_id' => $employee->id,
            'name' => $employee->full_name,
            'email' => $employee->email,
            'password' => Hash::make(config('app.default_password', 'password123')),
            'must_change_password' => true,
            'email_verified_at' => now(),
        ]);

        $this->syncUserRoles($user, $employee);

        if ($creator) {
            Notification::make()
                ->title('Utilizador criado')
                ->body("O utilizador associado a {$employee->full_name} foi criado com sucesso.")
                ->success()
                ->send()
                ->sendToDatabase($creator);
        }

        // 2. Criar Contrato Inicial
        Contract::create([
            'employee_id' => $employee->id,
            'designation_id' => $employee->designation_id,
            'type' => 'fixed_term', // Default
            'salary' => $employee->designation?->base_salary ?? 0,
            'status' => 'active',
            'start_date' => $employee->date_hired ?? now(),
            'daily_work_minutes' => 480, // Default 8h
        ]);

        if ($creator) {
            Notification::make()
                ->title('Contrato inicial gerado')
                ->body("Um contrato placeholder para {$employee->full_name} foi criado.")
                ->info()
                ->send()
                ->sendToDatabase($creator);
        }

        // 3. Criar Banco de Horas Inicial
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

    /**
     * Handle the Employee "updated" event.
     */
    public function updated(Employee $employee): void
    {
        if ($employee->isDirty('designation_id') || $employee->isDirty('email') || $employee->isDirty('first_name') || $employee->isDirty('last_name')) {
            $user = $employee->user;
            if ($user) {
                $user->update([
                    'name' => $employee->full_name,
                    'email' => $employee->email,
                ]);

                if ($employee->isDirty('designation_id')) {
                    $this->syncUserRoles($user, $employee);
                }
            }
        }
    }

    /**
     * Sincroniza as roles do utilizador baseadas na designação do funcionário.
     */
    protected function syncUserRoles(User $user, Employee $employee): void
    {
        $roles = [];

        // Role base obrigatória
        if (Role::where('name', 'employee')->exists()) {
            $roles[] = 'employee';
        }

        // Role associada ao cargo
        $roleName = $employee->designation?->role_name;
        if ($roleName && Role::where('name', $roleName)->exists()) {
            $roles[] = $roleName;
        }

        if (! empty($roles)) {
            $user->syncRoles($roles);
        }
    }
}
