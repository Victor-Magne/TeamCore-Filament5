<?php

namespace Database\Seeders;

use App\Models\AttendanceLog;
use App\Models\City;
use App\Models\Contract;
use App\Models\Country;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\HourBank;
use App\Models\LeaveAndAbsence;
use App\Models\State;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- 1. GEOGRAFIA ---
        $this->command->info('🌍 Criando dados geográficos...');
        $country = Country::factory()->create(['name' => 'Portugal', 'code' => 'PT', 'phonecode' => 351]);
        $state = State::factory()->create(['name' => 'Lisboa', 'country_id' => $country->id]);
        $city = City::factory()->create(['name' => 'Lisboa', 'state_id' => $state->id]);

        // --- 2. ESTRUTURA ORGANIZACIONAL ---
        $this->command->info('🏢 Criando estrutura organizacional...');
        $mainDirection = Unit::factory()->mainDirection()->create([
            'name' => 'Direção Geral',
            'type' => 'direction',
        ]);

        $hrDepartment = Unit::factory()->department()->withParent($mainDirection)->create(['name' => 'Recursos Humanos']);
        $itDepartment = Unit::factory()->department()->withParent($mainDirection)->create(['name' => 'Tecnologia da Informação']);
        $salesDepartment = Unit::factory()->department()->withParent($mainDirection)->create(['name' => 'Vendas']);

        // --- 3. CARGOS (DESIGNATIONS) ---
        $this->command->info('💼 Criando designações...');
        $directorDesignation = Designation::factory()->manager()->create(['name' => 'Diretor Geral']);
        $managerDesignation = Designation::factory()->manager()->create(['name' => 'Gerente de Departamento']);
        $seniorDesignation = Designation::factory()->create(['name' => 'Senior Specialist', 'level' => 3]);
        $juniorDesignation = Designation::factory()->operational()->create(['name' => 'Junior Developer']);
        $coordinatorDesignation = Designation::factory()->create(['name' => 'Coordenador de RH', 'level' => 3]);

        // --- 4. FUNCIONÁRIOS ---
        $this->command->info('👥 Criando funcionários...');

        // Diretor Geral
        $director = Employee::factory()->manager()->create([
            'first_name' => 'João',
            'last_name' => 'Silva',
            'email' => 'joao.silva@example.com',
            'city_id' => $city->id,
            'unit_id' => $mainDirection->id,
            'designation_id' => $directorDesignation->id,
        ]);
        $mainDirection->update(['manager_id' => $director->id]);

        // Gerentes
        $hrManager = Employee::factory()->manager()->create([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'email' => 'maria.santos@example.com',
            'city_id' => $city->id,
            'unit_id' => $hrDepartment->id,
            'designation_id' => $managerDesignation->id,
        ]);
        $hrDepartment->update(['manager_id' => $hrManager->id]);

        $itManager = Employee::factory()->manager()->create([
            'first_name' => 'Pedro',
            'last_name' => 'Costa',
            'email' => 'pedro.costa@example.com',
            'city_id' => $city->id,
            'unit_id' => $itDepartment->id,
            'designation_id' => $managerDesignation->id,
        ]);
        $itDepartment->update(['manager_id' => $itManager->id]);

        // Equipa Geral
        Employee::factory(5)->create(['city_id' => $city->id, 'unit_id' => $itDepartment->id, 'designation_id' => $juniorDesignation->id]);
        Employee::factory(3)->create(['city_id' => $city->id, 'unit_id' => $salesDepartment->id, 'designation_id' => $seniorDesignation->id]);

        // --- 5. CONTRATOS ---
        $this->command->info('📋 Criando contratos...');
        $employees = Employee::all();
        foreach ($employees as $employee) {
            Contract::factory()->create([
                'employee_id' => $employee->id,
                'designation_id' => $employee->designation_id,
                'status' => 'active',
                'type' => 'permanent', // Valor seguro do ENUM
            ]);
        }

        // --- 6. UTILIZADORES (COM FIX DE DUPLICADOS) ---
        $this->command->info('👤 Criando utilizadores...');

        // Utilizador Admin (João Silva)
        User::factory()->create([
            'name' => 'João Silva (Admin)',
            'email' => 'admin@test.test',
            'password' => Hash::make('password'),
            'employee_id' => $director->id,
        ]);

        // Criar utilizadores para outros 5 funcionários (excluindo o diretor)
        Employee::where('id', '!=', $director->id)
            ->limit(5)
            ->get()
            ->each(function (Employee $employee) {
                User::factory()->create([
                    'name' => $employee->first_name.' '.$employee->last_name,
                    'email' => strtolower($employee->first_name.'.'.$employee->last_name).'@test.test',
                    'employee_id' => $employee->id,
                ]);
            });

        // --- 7. ASSIDUIDADE ---
        $this->command->info('⏰ Criando registos de assiduidade...');
        Employee::query()->limit(10)->get()->each(function (Employee $employee) {
            AttendanceLog::factory(15)->create(['employee_id' => $employee->id]);
        });

        // --- 8. BANCO DE HORAS ---
        $this->command->info('🏦 Criando banco de horas...');
        Employee::query()->limit(5)->get()->each(function (Employee $employee) {
            HourBank::factory()->create([
                'employee_id' => $employee->id,
                'month_year' => now()->format('Y-m'),
            ]);
        });

        // --- 9. FÉRIAS E AUSÊNCIAS ---
        $this->command->info('🎁 Criando férias e ausências...');
        Employee::query()->limit(5)->get()->each(function (Employee $employee) {
            Vacation::factory()->approved()->create([
                'employee_id' => $employee->id,
                'year_reference' => 2026,
            ]);

            LeaveAndAbsence::factory()->sickLeave()->create([
                'employee_id' => $employee->id,
            ]);
        });

        $this->command->info('✅ Dados de teste criados com sucesso!');

        // Tabela de resumo
        $this->command->table(
            ['Entidade', 'Quantidade'],
            [
                ['Unidades', Unit::count()],
                ['Funcionários', Employee::count()],
                ['Utilizadores', User::count()],
                ['Contratos', Contract::count()],
                ['Registos Ponto', AttendanceLog::count()],
            ]
        );
    }
}
