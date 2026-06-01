<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Employee;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncEmployeesToUsers')
                ->label('Employees → Users')
                ->icon(Heroicon::UserPlus)
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Sincronizar Employees → Users')
                ->modalDescription(fn () => $this->pendingEmployeesWithoutUser().' employee(s) sem conta de utilizador serão criados.')
                ->modalSubmitActionLabel('Criar contas')
                ->action(function (): void {
                    $employees = Employee::doesntHave('user')->get();

                    foreach ($employees as $employee) {
                        User::create([
                            'employee_id' => $employee->id,
                            'name' => $employee->full_name,
                            'email' => $employee->email,
                            'password' => Hash::make(Str::random(16)),
                            'must_change_password' => true,
                            'is_active' => true,
                        ]);
                    }

                    Notification::make()
                        ->success()
                        ->title("{$employees->count()} conta(s) criada(s) com sucesso.")
                        ->send();
                }),

            Action::make('syncUsersToEmployees')
                ->label('Users → Employees')
                ->icon(Heroicon::ArrowPath)
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Sincronizar Users → Employees')
                ->modalDescription(fn () => $this->pendingUsersWithoutEmployee().' user(s) sem employee serão ligados por email.')
                ->modalSubmitActionLabel('Ligar contas')
                ->action(function (): void {
                    $users = User::whereNull('employee_id')->get();
                    $linked = 0;

                    foreach ($users as $user) {
                        $employee = Employee::where('email', $user->email)->first();

                        if ($employee && ! $employee->user()->exists()) {
                            $user->update(['employee_id' => $employee->id]);
                            $linked++;
                        }
                    }

                    Notification::make()
                        ->success()
                        ->title("{$linked} user(s) ligado(s) a um employee com sucesso.")
                        ->send();
                }),

            CreateAction::make(),
        ];
    }

    private function pendingEmployeesWithoutUser(): int
    {
        return Employee::doesntHave('user')->count();
    }

    private function pendingUsersWithoutEmployee(): int
    {
        return User::whereNull('employee_id')->count();
    }
}
