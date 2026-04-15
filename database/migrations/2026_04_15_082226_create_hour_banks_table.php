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
        Schema::create('hour_banks', function (Blueprint $table) {
            $table->id();

            // Relacionamento com o funcionário
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            // Referência de mês/ano (YYYY-MM)
            $table->string('month_year');

            // Saldo total em minutos
            // Positivo = banco tem crédito | Negativo = funcionário deve horas
            $table->integer('balance')->default(0);

            // Horas extras adicionadas neste mês (em minutos)
            $table->integer('extra_hours_added')->default(0);

            // Horas extras usadas/descontadas neste mês (em minutos)
            $table->integer('extra_hours_used')->default(0);

            // Saldo anterior (para auditoria)
            $table->integer('previous_balance')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Índices para queries rápidas
            $table->unique(['employee_id', 'month_year']);
            $table->index(['employee_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hour_banks');
    }
};
