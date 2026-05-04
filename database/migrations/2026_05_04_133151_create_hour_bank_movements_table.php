<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hour_bank_movements', function (Blueprint $table) {
            $table->id();

            // Relacionamento com o funcionário
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            // Valor da movimentação em minutos (positivo para ganho, negativo para dedução)
            $table->integer('amount');

            // Tipo de movimentação (addition ou deduction)
            $table->string('type');

            // Origem da movimentação (AttendanceLog ou Absence)
            $table->nullableMorphs('source');

            // Descrição legível da movimentação
            $table->string('description');

            // Data em que ocorreu o evento
            $table->date('date');

            $table->timestamps();

            // Índices para performance
            $table->index(['employee_id', 'date']);
            // O index para morphs já é criado automaticamente pelo nullableMorphs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hour_bank_movements');
    }
};
