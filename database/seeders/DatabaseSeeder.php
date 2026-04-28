<?php

/**
 * Ficheiro do Seeder Principal da Base de Dados.
 *
 * Esta classe orquestra o preenchimento inicial da base de dados,
 * executando os seeders específicos por ordem de dependência (ex: Países -> Estados -> Cidades).
 */

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * O trait WithoutModelEvents é utilizado para evitar que os Observers
     * sejam disparados durante o seeding inicial, o que poderia causar
     * comportamentos inesperados ou lentidão ao inserir dados em massa.
     */
    use WithoutModelEvents;

    /**
     * Executa os seeders da aplicação.
     */
    public function run(): void
    {
        $this->call([
            // 1. Dados Geográficos (necessários para Moradas)
            Countries::class,
            States::class,
            Cities::class,

            // 2. Estrutura da Empresa (Unidades e Cargos)
            OrganizationSeeder::class,

            // 3. Dados de Demonstração (Funcionários, Contratos, etc.)
            EmployeeSeeder::class,
        ]);
    }
}
