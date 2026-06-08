<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedDesignations();
        $this->seedOrganizationalUnits();
    }

    private function seedDesignations(): void
    {
        $designations = [
            ['name' => 'Diretor Geral',          'level' => 'lead',     'base_salary' => 5000.00],
            ['name' => 'Diretor',                 'level' => 'lead',     'base_salary' => 3800.00],
            ['name' => 'Chefe de Departamento',   'level' => 'senior',   'base_salary' => 2800.00],
            ['name' => 'Chefe de Secção',         'level' => 'senior',   'base_salary' => 2200.00],
            ['name' => 'Especialista',            'level' => 'specialist', 'base_salary' => 2000.00],
            ['name' => 'Técnico Sênior',          'level' => 'senior',   'base_salary' => 1800.00],
            ['name' => 'Técnico Pleno',           'level' => 'pleno',    'base_salary' => 1400.00],
            ['name' => 'Técnico Junior',          'level' => 'junior',   'base_salary' => 900.00],
            ['name' => 'Administrativo',          'level' => 'junior',   'base_salary' => 850.00],
        ];

        DB::table('designations')->delete();
        DB::table('designations')->insert($designations);
    }

    private function seedOrganizationalUnits(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('organizational_units')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ──────────────────────────────────────────────────────────────────
        // Nível 1 — Raiz da organização
        // ──────────────────────────────────────────────────────────────────
        $direcaoGeral = DB::table('organizational_units')->insertGetId([
            'name' => 'Direção Geral',
            'type' => 'direction',
            'description' => 'Órgão máximo de gestão da organização',
            'parent_id' => null,
            'manager_id' => null,
            'is_main_direction' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ──────────────────────────────────────────────────────────────────
        // Nível 2 — Direções (subdivisões da Direção Geral)
        // ──────────────────────────────────────────────────────────────────
        $direcaoRH = DB::table('organizational_units')->insertGetId([
            'name' => 'Direção de Recursos Humanos',
            'type' => 'direction',
            'description' => 'Gestão de pessoas, recrutamento e desenvolvimento',
            'parent_id' => $direcaoGeral,
            'manager_id' => null,
            'is_main_direction' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $direcaoFinTI = DB::table('organizational_units')->insertGetId([
            'name' => 'Direção Financeira e Tecnológica',
            'type' => 'direction',
            'description' => 'Gestão financeira, contábil e de sistemas de informação',
            'parent_id' => $direcaoGeral,
            'manager_id' => null,
            'is_main_direction' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $direcaoOps = DB::table('organizational_units')->insertGetId([
            'name' => 'Direção de Operações e Qualidade',
            'type' => 'direction',
            'description' => 'Gestão de operações, qualidade e processos organizacionais',
            'parent_id' => $direcaoGeral,
            'manager_id' => null,
            'is_main_direction' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ──────────────────────────────────────────────────────────────────
        // Nível 3 — Departamentos
        // ──────────────────────────────────────────────────────────────────

        // Direção de RH
        $deptRecrutamento = DB::table('organizational_units')->insertGetId([
            'name' => 'Departamento de Recrutamento e Seleção',
            'type' => 'department',
            'description' => 'Atração, seleção e integração de novos colaboradores',
            'parent_id' => $direcaoRH,
            'manager_id' => null,
            'is_main_direction' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $deptDesenvPessoal = DB::table('organizational_units')->insertGetId([
            'name' => 'Departamento de Desenvolvimento de Pessoal',
            'type' => 'department',
            'description' => 'Formação, avaliação de desempenho e planos de carreira',
            'parent_id' => $direcaoRH,
            'manager_id' => null,
            'is_main_direction' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $deptAdminPessoal = DB::table('organizational_units')->insertGetId([
            'name' => 'Departamento de Administração de Pessoal',
            'type' => 'department',
            'description' => 'Processamento salarial, arquivo e gestão administrativa',
            'parent_id' => $direcaoRH,
            'manager_id' => null,
            'is_main_direction' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Direção Financeira e Tecnológica
        $deptContab = DB::table('organizational_units')->insertGetId([
            'name' => 'Departamento de Contabilidade',
            'type' => 'department',
            'description' => 'Registos contábeis, conformidade fiscal e relatórios financeiros',
            'parent_id' => $direcaoFinTI,
            'manager_id' => null,
            'is_main_direction' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $deptTI = DB::table('organizational_units')->insertGetId([
            'name' => 'Departamento de Tecnologia de Informação',
            'type' => 'department',
            'description' => 'Infraestrutura, sistemas e desenvolvimento de software',
            'parent_id' => $direcaoFinTI,
            'manager_id' => null,
            'is_main_direction' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Direção de Operações e Qualidade
        $deptQualidade = DB::table('organizational_units')->insertGetId([
            'name' => 'Departamento de Qualidade e Conformidade',
            'type' => 'department',
            'description' => 'Controlo de qualidade e conformidade normativa',
            'parent_id' => $direcaoOps,
            'manager_id' => null,
            'is_main_direction' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $deptProcessos = DB::table('organizational_units')->insertGetId([
            'name' => 'Departamento de Processos e Métodos',
            'type' => 'department',
            'description' => 'Otimização de processos, metodologias e melhoria contínua',
            'parent_id' => $direcaoOps,
            'manager_id' => null,
            'is_main_direction' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ──────────────────────────────────────────────────────────────────
        // Nível 4 — Secções
        // ──────────────────────────────────────────────────────────────────
        $sections = [
            // Dep. Recrutamento e Seleção
            ['name' => 'Secção de Recrutamento',        'description' => 'Captação e triagem de candidatos',                'parent_id' => $deptRecrutamento],
            ['name' => 'Secção de Seleção e Integração', 'description' => 'Processos de seleção e acolhimento de novos colaboradores', 'parent_id' => $deptRecrutamento],

            // Dep. Desenvolvimento de Pessoal
            ['name' => 'Secção de Formação',            'description' => 'Planos de formação interna e externa',            'parent_id' => $deptDesenvPessoal],
            ['name' => 'Secção de Avaliação de Desempenho', 'description' => 'Ciclos de avaliação e feedback',             'parent_id' => $deptDesenvPessoal],

            // Dep. Administração de Pessoal
            ['name' => 'Secção de Processamento Salarial', 'description' => 'Processamento de vencimentos e benefícios',   'parent_id' => $deptAdminPessoal],
            ['name' => 'Secção de Arquivo e Registo',    'description' => 'Gestão documental e arquivo de pessoal',         'parent_id' => $deptAdminPessoal],

            // Dep. Contabilidade
            ['name' => 'Secção de Contabilidade Geral', 'description' => 'Lançamentos contábeis e balanços',               'parent_id' => $deptContab],
            ['name' => 'Secção Fiscal e Tesouraria',    'description' => 'Obrigações fiscais, caixa e pagamentos',          'parent_id' => $deptContab],

            // Dep. TI
            ['name' => 'Secção de Infraestrutura e Redes', 'description' => 'Servidores, redes e telecomunicações',         'parent_id' => $deptTI],
            ['name' => 'Secção de Desenvolvimento de Software', 'description' => 'Desenvolvimento e manutenção de aplicações', 'parent_id' => $deptTI],
            ['name' => 'Secção de Suporte Técnico',     'description' => 'Helpdesk e suporte ao utilizador final',           'parent_id' => $deptTI],

            // Dep. Qualidade e Conformidade
            ['name' => 'Secção de Controlo de Qualidade', 'description' => 'Auditorias e certificações de qualidade',       'parent_id' => $deptQualidade],
            ['name' => 'Secção de Conformidade Normativa', 'description' => 'Cumprimento regulatório e normativo',          'parent_id' => $deptQualidade],

            // Dep. Processos e Métodos
            ['name' => 'Secção de Processos',           'description' => 'Mapeamento e otimização de processos',            'parent_id' => $deptProcessos],
            ['name' => 'Secção de Melhoria Contínua',   'description' => 'Projetos de melhoria e inovação organizacional',  'parent_id' => $deptProcessos],
        ];

        foreach ($sections as $section) {
            DB::table('organizational_units')->insert([
                'name' => $section['name'],
                'type' => 'section',
                'description' => $section['description'],
                'parent_id' => $section['parent_id'],
                'manager_id' => null,
                'is_main_direction' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Reconstruir _lft/_rgt do nested set com base no parent_id
        Unit::fixTree();
    }
}
