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
        Schema::create('absences', function (Blueprint $table) {
            $table->id();

            // Relacionamento com o funcionário
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            // Referência opcional para a leave/absence existente
            $table->foreignId('leave_and_absence_id')->nullable()->constrained('leaves_and_absences')->cascadeOnDelete();

            // Data da ausência
            $table->date('absence_date');

            // Horas descontadas do banco (em minutos)
            // Uma falta de 1 dia completo = 8 horas (480 minutos)
            $table->integer('hours_deducted');

            // Tipo de deduções
            $table->enum('deduction_type', [
                'unjustified_absence',        // Falta injustificada
                'partial_absence',             // Meia falta ou atraso
                'other',                       // Outro motivo
            ]);

            // Motivo da dedução
            $table->text('reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices para queries rápidas
            $table->index(['employee_id', 'absence_date']);
            $table->index(['employee_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
