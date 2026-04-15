<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();

            // Relacionamento com o funcionário
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            // Os 4 momentos do dia: entrada, saída para almoço, volta do almoço, fim do expediente
            $table->dateTime('time_in');
            $table->dateTime('lunch_break_start')->nullable();
            $table->dateTime('lunch_break_end')->nullable();
            $table->dateTime('time_out')->nullable();

            // Tempo total em minutos (entrada - fim do expediente, excluindo almoço)
            $table->integer('total_minutes')->nullable();

            // Metadata: Para segurança e auditoria
            $table->json('metadata')->nullable();

            // Notas opcionais do funcionário
            $table->text('notes')->nullable();

            $table->timestamps();

            // Índices para acelerar relatórios
            $table->index(['employee_id', 'time_in']);
            $table->index(['employee_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
