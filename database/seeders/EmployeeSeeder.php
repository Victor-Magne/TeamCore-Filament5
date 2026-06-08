<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    use WithoutModelEvents;

    private Collection $units;

    private Collection $designations;

    private int $cityId;

    public function run(): void
    {
        // Limpar dados dependentes antes de apagar funcionários
        DB::table('unit_manager')->delete();
        DB::table('hour_bank_movements')->delete();
        DB::table('hour_banks')->delete();
        DB::table('attendance_logs')->delete();
        DB::table('vacations')->delete();
        DB::table('leaves_and_absences')->delete();
        DB::table('absences')->delete();
        DB::table('contracts')->delete();
        DB::table('notifications')->delete();

        // Apagar TODOS os role assignments de users com employee (incluindo entradas órfãs
        // de runs anteriores cujos users já foram apagados)
        // Apagar todos os role assignments de utilizadores (todos são empregados nesta app)
        DB::table('model_has_roles')
            ->where('model_type', 'App\\Models\\User')
            ->delete();

        // Apagar todos os utilizadores — o ShieldSeeder pode ter criado utilizadores
        // sem employee_id (snapshot de execuções anteriores com emails aleatórios de factory).
        // Todos os utilizadores nesta app correspondem a empregados, pelo que é seguro.
        DB::table('users')->delete();

        Employee::withTrashed()->forceDelete();

        $this->units = Unit::pluck('id', 'name');
        $this->designations = DB::table('designations')->pluck('id', 'name');
        $this->cityId = DB::table('cities')->value('id');

        // IDs das designações de nível colaborador para os funcionários criados por factory
        $colaboradorDesigs = DB::table('designations')
            ->whereIn('name', ['Especialista', 'Técnico Sênior', 'Técnico Pleno', 'Técnico Junior', 'Administrativo'])
            ->pluck('id')
            ->toArray();

        // ──────────────────────────────────────────────────────────────────
        // Nível 1 — Direção Geral
        // ──────────────────────────────────────────────────────────────────
        $dirGeralEmp = $this->createManager(
            firstName: 'João', lastName: 'Silva',
            email: 'joao.silva@organization.pt',
            dob: '1965-05-15', hired: '2010-01-15',
            nif: '123456789', nss: '10000000000',
            designation: 'Diretor Geral',
            role: 'diretor_geral',
            unit: 'Direção Geral',
        );

        // ──────────────────────────────────────────────────────────────────
        // Nível 2 — Diretores de Direção
        // ──────────────────────────────────────────────────────────────────
        $this->createManager(
            firstName: 'Maria', lastName: 'Santos',
            email: 'maria.santos@organization.pt',
            dob: '1972-08-22', hired: '2015-03-01',
            nif: '223456789', nss: '10000000001',
            designation: 'Diretor',
            role: 'diretor',
            unit: 'Direção de Recursos Humanos',
        );

        $this->createManager(
            firstName: 'Ricardo', lastName: 'Alves',
            email: 'ricardo.alves@organization.pt',
            dob: '1970-09-18', hired: '2011-02-01',
            nif: '323456789', nss: '10000000002',
            designation: 'Diretor',
            role: 'diretor',
            unit: 'Direção Financeira e Tecnológica',
        );

        $this->createManager(
            firstName: 'Carlos', lastName: 'Ferreira',
            email: 'carlos.ferreira@organization.pt',
            dob: '1971-07-05', hired: '2012-04-20',
            nif: '423456789', nss: '10000000003',
            designation: 'Diretor',
            role: 'diretor',
            unit: 'Direção de Operações e Qualidade',
        );

        // ──────────────────────────────────────────────────────────────────
        // Nível 3 — Chefes de Departamento
        // ──────────────────────────────────────────────────────────────────
        $this->createManager(
            firstName: 'Ana', lastName: 'Costa',
            email: 'ana.costa@organization.pt',
            dob: '1978-11-28', hired: '2016-06-15',
            nif: '523456789', nss: '10000000004',
            designation: 'Chefe de Departamento',
            role: 'chefe_de_departamento',
            unit: 'Departamento de Recrutamento e Seleção',
        );

        $this->createManager(
            firstName: 'Paulo', lastName: 'Rocha',
            email: 'paulo.rocha@organization.pt',
            dob: '1980-04-09', hired: '2017-09-01',
            nif: '623456789', nss: '10000000005',
            designation: 'Chefe de Departamento',
            role: 'chefe_de_departamento',
            unit: 'Departamento de Desenvolvimento de Pessoal',
        );

        $this->createManager(
            firstName: 'Isabel', lastName: 'Martins',
            email: 'isabel.martins@organization.pt',
            dob: '1976-02-14', hired: '2014-01-10',
            nif: '723456789', nss: '10000000006',
            designation: 'Chefe de Departamento',
            role: 'chefe_de_departamento',
            unit: 'Departamento de Administração de Pessoal',
        );

        $this->createManager(
            firstName: 'Fernanda', lastName: 'Gomes',
            email: 'fernanda.gomes@organization.pt',
            dob: '1975-12-03', hired: '2013-05-15',
            nif: '823456789', nss: '10000000007',
            designation: 'Chefe de Departamento',
            role: 'chefe_de_departamento',
            unit: 'Departamento de Contabilidade',
        );

        $this->createManager(
            firstName: 'Tiago', lastName: 'Neves',
            email: 'tiago.neves@organization.pt',
            dob: '1978-06-21', hired: '2013-08-01',
            nif: '923456789', nss: '10000000008',
            designation: 'Chefe de Departamento',
            role: 'chefe_de_departamento',
            unit: 'Departamento de Tecnologia de Informação',
        );

        $this->createManager(
            firstName: 'Bruno', lastName: 'Mendes',
            email: 'bruno.mendes@organization.pt',
            dob: '1980-01-30', hired: '2015-02-15',
            nif: '1023456789', nss: '10000000009',
            designation: 'Chefe de Departamento',
            role: 'chefe_de_departamento',
            unit: 'Departamento de Qualidade e Conformidade',
        );

        $this->createManager(
            firstName: 'Luísa', lastName: 'Faria',
            email: 'luisa.faria@organization.pt',
            dob: '1982-03-17', hired: '2016-10-01',
            nif: '1123456789', nss: '10000000010',
            designation: 'Chefe de Departamento',
            role: 'chefe_de_departamento',
            unit: 'Departamento de Processos e Métodos',
        );

        // ──────────────────────────────────────────────────────────────────
        // Nível 4 — Chefes de Secção
        // ──────────────────────────────────────────────────────────────────
        $sections = [
            ['firstName' => 'Pedro',    'lastName' => 'Oliveira',  'email' => 'pedro.oliveira@organization.pt',    'dob' => '1985-03-10', 'hired' => '2018-06-15', 'nif' => '2023456789',  'nss' => '10000000011', 'unit' => 'Secção de Recrutamento'],
            ['firstName' => 'Sofia',    'lastName' => 'Pinto',     'email' => 'sofia.pinto@organization.pt',      'dob' => '1988-10-12', 'hired' => '2019-07-01', 'nif' => '2123456789',  'nss' => '10000000012', 'unit' => 'Secção de Seleção e Integração'],
            ['firstName' => 'André',    'lastName' => 'Lopes',     'email' => 'andre.lopes@organization.pt',      'dob' => '1984-05-20', 'hired' => '2018-01-10', 'nif' => '2223456789',  'nss' => '10000000013', 'unit' => 'Secção de Formação'],
            ['firstName' => 'Mariana',  'lastName' => 'Teixeira',  'email' => 'mariana.teixeira@organization.pt', 'dob' => '1987-08-07', 'hired' => '2019-02-01', 'nif' => '2323456789',  'nss' => '10000000014', 'unit' => 'Secção de Avaliação de Desempenho'],
            ['firstName' => 'Rui',      'lastName' => 'Carvalho',  'email' => 'rui.carvalho@organization.pt',     'dob' => '1983-11-15', 'hired' => '2017-05-20', 'nif' => '2423456789',  'nss' => '10000000015', 'unit' => 'Secção de Processamento Salarial'],
            ['firstName' => 'Catarina', 'lastName' => 'Sousa',     'email' => 'catarina.sousa@organization.pt',   'dob' => '1986-07-30', 'hired' => '2018-09-15', 'nif' => '2523456789',  'nss' => '10000000016', 'unit' => 'Secção de Arquivo e Registo'],
            ['firstName' => 'Gonçalo', 'lastName' => 'Matos',     'email' => 'goncalo.matos@organization.pt',    'dob' => '1982-04-25', 'hired' => '2016-03-01', 'nif' => '2623456789',  'nss' => '10000000017', 'unit' => 'Secção de Contabilidade Geral'],
            ['firstName' => 'Helena',   'lastName' => 'Correia',   'email' => 'helena.correia@organization.pt',   'dob' => '1985-09-08', 'hired' => '2017-11-01', 'nif' => '2723456789',  'nss' => '10000000018', 'unit' => 'Secção Fiscal e Tesouraria'],
            ['firstName' => 'Filipe',   'lastName' => 'Monteiro',  'email' => 'filipe.monteiro@organization.pt',  'dob' => '1987-01-19', 'hired' => '2018-04-15', 'nif' => '2823456789',  'nss' => '10000000019', 'unit' => 'Secção de Infraestrutura e Redes'],
            ['firstName' => 'Patrícia', 'lastName' => 'Vieira',    'email' => 'patricia.vieira@organization.pt',  'dob' => '1990-06-02', 'hired' => '2020-01-05', 'nif' => '2923456789',  'nss' => '10000000020', 'unit' => 'Secção de Desenvolvimento de Software'],
            ['firstName' => 'Miguel',   'lastName' => 'Barbosa',   'email' => 'miguel.barbosa@organization.pt',   'dob' => '1992-12-14', 'hired' => '2021-03-10', 'nif' => '3023456789',  'nss' => '10000000021', 'unit' => 'Secção de Suporte Técnico'],
            ['firstName' => 'Teresa',   'lastName' => 'Cunha',     'email' => 'teresa.cunha@organization.pt',     'dob' => '1983-08-28', 'hired' => '2016-07-01', 'nif' => '3123456789',  'nss' => '10000000022', 'unit' => 'Secção de Controlo de Qualidade'],
            ['firstName' => 'Nuno',     'lastName' => 'Ribeiro',   'email' => 'nuno.ribeiro@organization.pt',     'dob' => '1981-03-11', 'hired' => '2015-10-15', 'nif' => '3223456789',  'nss' => '10000000023', 'unit' => 'Secção de Conformidade Normativa'],
            ['firstName' => 'Diana',    'lastName' => 'Pereira',   'email' => 'diana.pereira@organization.pt',    'dob' => '1988-05-07', 'hired' => '2018-02-20', 'nif' => '3323456789',  'nss' => '10000000024', 'unit' => 'Secção de Processos'],
            ['firstName' => 'Rodrigo',  'lastName' => 'Azevedo',   'email' => 'rodrigo.azevedo@organization.pt',  'dob' => '1986-11-23', 'hired' => '2017-06-10', 'nif' => '3423456789',  'nss' => '10000000025', 'unit' => 'Secção de Melhoria Contínua'],
        ];

        foreach ($sections as $data) {
            $this->createManager(
                firstName: $data['firstName'],
                lastName: $data['lastName'],
                email: $data['email'],
                dob: $data['dob'],
                hired: $data['hired'],
                nif: $data['nif'],
                nss: $data['nss'],
                designation: 'Chefe de Secção',
                role: 'chefe_de_seccao',
                unit: $data['unit'],
            );
        }

        // ──────────────────────────────────────────────────────────────────
        // Nível 5 — Colaboradores (3 por secção via factory)
        // ──────────────────────────────────────────────────────────────────
        $sectionNames = [
            'Secção de Recrutamento',
            'Secção de Seleção e Integração',
            'Secção de Formação',
            'Secção de Avaliação de Desempenho',
            'Secção de Processamento Salarial',
            'Secção de Arquivo e Registo',
            'Secção de Contabilidade Geral',
            'Secção Fiscal e Tesouraria',
            'Secção de Infraestrutura e Redes',
            'Secção de Desenvolvimento de Software',
            'Secção de Suporte Técnico',
            'Secção de Controlo de Qualidade',
            'Secção de Conformidade Normativa',
            'Secção de Processos',
            'Secção de Melhoria Contínua',
        ];

        foreach ($sectionNames as $sectionIndex => $sectionName) {
            $employees = Employee::factory()->count(3)->create([
                'unit_id' => $this->units[$sectionName],
                'designation_id' => $colaboradorDesigs[$sectionIndex % count($colaboradorDesigs)],
                'date_hired' => fake()->dateTimeBetween('2018-01-01', '2024-06-30'),
            ]);

            foreach ($employees as $employee) {
                $user = $this->ensureUser($employee);
                $user->syncRoles(['colaborador']);
            }
        }

        // Atualizar a árvore nested set após atribuição dos gestores
        Unit::fixTree();
    }

    private function createManager(
        string $firstName,
        string $lastName,
        string $email,
        string $dob,
        string $hired,
        string $nif,
        string $nss,
        string $designation,
        string $role,
        string $unit,
    ): Employee {
        $employee = Employee::create([
            'city_id' => $this->cityId,
            'unit_id' => $this->units[$unit],
            'designation_id' => $this->designations[$designation],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone_number' => '+351'.fake()->numerify('9########'),
            'date_of_birth' => $dob,
            'nif' => $nif,
            'nss' => $nss,
            'address' => fake()->streetAddress().', Lisboa',
            'zip_code' => fake()->numerify('####-###'),
            'date_hired' => $hired,
            'date_dismissed' => null,
            'vacation_balance' => 22,
        ]);

        $unitModel = Unit::where('name', $unit)->first();
        $unitModel->update(['manager_id' => $employee->id]);
        $unitModel->managers()->syncWithoutDetaching([$employee->id]);

        $user = $this->ensureUser($employee);
        $user->syncRoles([$role]);

        return $employee;
    }

    private function ensureUser(Employee $employee): User
    {
        // Usa updateOrCreate para ligar o employee_id a utilizadores já criados pelo ShieldSeeder
        // (que cria utilizadores sem employee_id por não ter acesso aos IDs nessa fase).
        return User::updateOrCreate(
            ['email' => $employee->email],
            [
                'employee_id' => $employee->id,
                'name' => "{$employee->first_name} {$employee->last_name}",
                'password' => Hash::make('password'),
                'must_change_password' => false,
                'email_verified_at' => now(),
            ]
        );
    }
}
