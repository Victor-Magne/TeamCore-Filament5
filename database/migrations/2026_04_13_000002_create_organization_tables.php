<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Unidades (Direções, Departamentos, Secções)
        Schema::create('organizational_units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['direction', 'department', 'section']);
            $table->text('description')->nullable();

            // Hierarquia Recursiva
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('organizational_units')
                ->nullOnDelete();

            // Gestor da Unidade (Foreign Key será validada após a tabela de employees)
            $table->unsignedBigInteger('manager_id')->nullable();

            $table->boolean('is_main_direction')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Garantir apenas UMA Direção Geral ativa como principal
            $table->unique(['is_main_direction'], 'unique_main_direction')
                ->whereRaw('is_main_direction = 1');
        });

        // 2. Designações / Cargos
        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->enum('level', ['junior', 'pleno', 'senior', 'specialist', 'lead']);
            $table->decimal('base_salary', 12, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('designations');
        Schema::dropIfExists('organizational_units');
    }
};
