<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedDesignations();
        $this->seedOrganizationalUnits();
    }

    private function seedDesignations(): void
    {
        $designations = [
            [
                'name' => 'Diretor',
                'level' => 'lead',
                'base_salary' => 3500.00,
            ],
            [
                'name' => 'Subdiretor',
                'level' => 'senior',
                'base_salary' => 2800.00,
            ],
            [
                'name' => 'Chefe de Departamento',
                'level' => 'senior',
                'base_salary' => 2400.00,
            ],
            [
                'name' => 'Chefe de Secção',
                'level' => 'pleno',
                'base_salary' => 1900.00,
            ],
            [
                'name' => 'Especialista',
                'level' => 'specialist',
                'base_salary' => 2000.00,
            ],
            [
                'name' => 'Técnico Sênior',
                'level' => 'senior',
                'base_salary' => 1800.00,
            ],
            [
                'name' => 'Técnico Pleno',
                'level' => 'pleno',
                'base_salary' => 1400.00,
            ],
            [
                'name' => 'Técnico Junior',
                'level' => 'junior',
                'base_salary' => 900.00,
            ],
            [
                'name' => 'Administrativo',
                'level' => 'junior',
                'base_salary' => 850.00,
            ],
        ];

        DB::table('designations')->delete();
        DB::table('designations')->insert($designations);
    }

    private function seedOrganizationalUnits(): void
    {
        DB::table('organizational_units')->delete();

        // 1. Direção Geral
        $mainDirection = DB::table('organizational_units')->insertGetId([
            'name' => 'Direção Geral',
            'type' => 'direction',
            'description' => 'Órgão máximo de gestão da organização',
            'parent_id' => null,
            'manager_id' => null,
            'is_main_direction' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Departamentos (subordinados à Direção Geral)
        $deptResources = DB::table('organizational_units')->insertGetId([
            'name' => 'Departamento de Recursos Humanos',
            'type' => 'department',
            'description' => 'Gestão de recursos humanos e desenvolvimento pessoal',
            'parent_id' => $mainDirection,
            'manager_id' => null,
            'is_main_direction' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $deptOps = DB::table('organizational_units')->insertGetId([
            'name' => 'Departamento de Operações',
            'type' => 'department',
            'description' => 'Gestão de operações e processos',
            'parent_id' => $mainDirection,
            'manager_id' => null,
            'is_main_direction' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $deptFinance = DB::table('organizational_units')->insertGetId([
            'name' => 'Departamento Financeiro',
            'type' => 'department',
            'description' => 'Gestão financeira e contábil',
            'parent_id' => $mainDirection,
            'manager_id' => null,
            'is_main_direction' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $deptTech = DB::table('organizational_units')->insertGetId([
            'name' => 'Departamento de Tecnologia',
            'type' => 'department',
            'description' => 'Gestão de sistemas e infraestrutura',
            'parent_id' => $mainDirection,
            'manager_id' => null,
            'is_main_direction' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Secções (subordinadas aos departamentos)
        // Secções do Departamento de RH
        DB::table('organizational_units')->insert([
            [
                'name' => 'Secção de Recrutamento e Seleção',
                'type' => 'section',
                'description' => 'Recrutamento, seleção e contratação de pessoal',
                'parent_id' => $deptResources,
                'manager_id' => null,
                'is_main_direction' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Secção de Formação e Desenvolvimento',
                'type' => 'section',
                'description' => 'Programas de formação e desenvolvimento profissional',
                'parent_id' => $deptResources,
                'manager_id' => null,
                'is_main_direction' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Secção de Administração de Pessoal',
                'type' => 'section',
                'description' => 'Gestão administrativa e folha de pagamento',
                'parent_id' => $deptResources,
                'manager_id' => null,
                'is_main_direction' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Secções do Departamento de Operações
        DB::table('organizational_units')->insert([
            [
                'name' => 'Secção de Qualidade e Conformidade',
                'type' => 'section',
                'description' => 'Controle de qualidade e conformidade',
                'parent_id' => $deptOps,
                'manager_id' => null,
                'is_main_direction' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Secção de Processos e Métodos',
                'type' => 'section',
                'description' => 'Otimização de processos e metodologias',
                'parent_id' => $deptOps,
                'manager_id' => null,
                'is_main_direction' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Secções do Departamento Financeiro
        DB::table('organizational_units')->insert([
            [
                'name' => 'Secção de Contabilidade',
                'type' => 'section',
                'description' => 'Registros contábeis e conformidade fiscal',
                'parent_id' => $deptFinance,
                'manager_id' => null,
                'is_main_direction' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Secção de Tesouraria',
                'type' => 'section',
                'description' => 'Gestão de caixa e tesouraria',
                'parent_id' => $deptFinance,
                'manager_id' => null,
                'is_main_direction' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Secções do Departamento de Tecnologia
        DB::table('organizational_units')->insert([
            [
                'name' => 'Secção de Infraestrutura',
                'type' => 'section',
                'description' => 'Gestão de servidores e infraestrutura',
                'parent_id' => $deptTech,
                'manager_id' => null,
                'is_main_direction' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Secção de Desenvolvimento de Software',
                'type' => 'section',
                'description' => 'Desenvolvimento e manutenção de aplicações',
                'parent_id' => $deptTech,
                'manager_id' => null,
                'is_main_direction' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Secção de Suporte Técnico',
                'type' => 'section',
                'description' => 'Suporte ao utilizador final',
                'parent_id' => $deptTech,
                'manager_id' => null,
                'is_main_direction' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
