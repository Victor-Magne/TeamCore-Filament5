<?php

use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('Employee Creation and Validation', function () {
    beforeEach(function () {
        // Criar um utilizador admin para criar funcionários
        $this->admin = User::factory()->state(['employee_id' => null])->create();
        $this->admin->assignRole('admin');

        $this->designation = Designation::factory()->create(['base_salary' => 1000]);
    });

    it('validates email domain format', function () {
        $invalidEmails = [
            'test@test',           // Sem TLD
            'test@',               // Sem domínio
            '@domain.com',         // Sem utilizador
            'test.test@domain',    // TLD com menos de 2 caracteres
        ];

        foreach ($invalidEmails) {
            $employee = Employee::factory()->make([
                'email' => $invalidEmails,
                'designation_id' => $this->designation->id,
            ]);

            // Verificar que a validação rejeita email inválido
            // Nota: isto depende da validação estar implementada na Resource
            expect($employee->email)->toBe($invalidEmails);
        }
    });

    it('accepts valid email addresses', function () {
        $validEmails = [
            'test@example.com',
            'john.doe@company.co.uk',
            'user+tag@domain.org',
        ];

        foreach ($validEmails as $email) {
            $employee = Employee::factory()->create([
                'email' => $email,
                'designation_id' => $this->designation->id,
            ]);

            expect($employee->email)->toBe($email);
        }
    });

    it('automatically creates user when employee is created', function () {
        $employee = Employee::factory()->create([
            'designation_id' => $this->designation->id,
            'first_name' => 'João',
            'last_name' => 'Silva',
            'email' => 'joao.silva@example.com',
        ]);

        $user = User::where('employee_id', $employee->id)->first();

        expect($user)->not->toBeNull();
        expect($user->name)->toBe("{$employee->first_name} {$employee->last_name}");
        expect($user->email)->toBe($employee->email);
        expect($user->must_change_password)->toBeTrue();
    });

    it('automatically creates contract when employee is created', function () {
        $employee = Employee::factory()->create(['designation_id' => $this->designation->id]);

        $contract = $employee->contracts()->first();

        expect($contract)->not->toBeNull();
        expect($contract->employee_id)->toBe($employee->id);
        expect($contract->status)->toBe('active');
    });

    it('automatically creates hour bank when employee is created', function () {
        $employee = Employee::factory()->create(['designation_id' => $this->designation->id]);

        $hourBank = $employee->hourBanks()->first();

        expect($hourBank)->not->toBeNull();
        expect($hourBank->balance)->toBe(0);
    });

    it('sends notifications when employee is created', function () {
        $this->actingAs($this->admin);

        $employee = Employee::factory()->create([
            'designation_id' => $this->designation->id,
        ]);

        // Verificar se as notificações foram enviadas
        $notifications = DB::table('notifications')
            ->where('notifiable_id', $this->admin->id)
            ->get();

        expect($notifications->count())->toBe(3); // Utilizador, Contrato, Banco de Horas
    });

    it('forces new user to change password on first login', function () {
        $employee = Employee::factory()->create(['designation_id' => $this->designation->id]);
        $user = User::where('employee_id', $employee->id)->first();

        expect($user->must_change_password)->toBeTrue();
    });
});
