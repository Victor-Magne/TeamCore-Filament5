<?php

/**
 * Migração para criação da tabela 'employees'.
 *
 * Esta migração define a estrutura base da entidade central do sistema: os funcionários.
 * Inclui também a resolução de chaves estrangeiras circulares com as tabelas
 * de unidades organizacionais e utilizadores.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Executa as operações de criação da tabela.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // Relacionamentos Estrangeiros
            $table->foreignId('city_id')->constrained(); // Cidade de residência
            $table->foreignId('unit_id')->constrained('organizational_units'); // Departamento/Unidade
            $table->foreignId('designation_id')->nullable()->constrained()->nullOnDelete(); // Cargo actual

            // Dados Pessoais e Identificação
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email')->unique();
            $table->string('phone_number', 20);
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            // Documentação Fiscal (Portugal)
            $table->string('nif', 20)->unique(); // Número de Identificação Fiscal
            $table->string('nss', 20)->unique(); // Número de Segurança Social

            // Morada e Contactos
            $table->string('address', 500);
            $table->string('zip_code', 20);

            // Datas de Vínculo e Saldo
            $table->date('date_hired'); // Data de entrada na empresa
            $table->dateTime('date_dismissed')->nullable(); // Data de saída/demissão
            $table->unsignedTinyInteger('vacation_balance')->default(22); // Dias de férias disponíveis (mínimo legal PT = 22)

            // Auditoria e Controlo
            $table->timestamps(); // created_at e updated_at
            $table->softDeletes(); // Permite eliminar sem apagar permanentemente (deleted_at)
        });

        /**
         * Resolução de Dependências Circulares.
         *
         * Como 'organizational_units' depende de 'employees' (manager_id)
         * e vice-versa, adicionamos as constraints após a criação da tabela base.
         */

        // 1. Resolvemos o Gestor na tabela de Unidades Organizacionais
        Schema::table('organizational_units', function (Blueprint $table) {
            $table->foreign('manager_id')->references('id')->on('employees')->onDelete('set null');
        });

        // 2. Resolvemos a ligação na tabela de Utilizadores (User <-> Employee)
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverte as operações da migração.
     */
    public function down(): void
    {
        // Remove as chaves estrangeiras antes de apagar a tabela para evitar erros de integridade
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });

        Schema::table('organizational_units', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });

        Schema::dropIfExists('employees');
    }
};
