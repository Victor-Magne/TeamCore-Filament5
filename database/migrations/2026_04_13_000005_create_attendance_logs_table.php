<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();

            // Relacionamento com o funcionário
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            // O momento exato do registo
            $table->dateTime('recorded_at');

            // Tipo de registo: Entrada ou Saída
            $table->enum('type', ['in', 'out']);

            // Metadata: Para segurança e auditoria (RH adora isto)
            $table->json('metadata')->nullable(); // Guarda: IP, Geolocalização, User Agent

            // Notas opcionais do funcionário (ex: "esqueci-me de bater ponto às 09h")
            $table->text('notes')->nullable();

            $table->timestamps();

            // Índice para acelerar relatórios por data
            $table->index(['employee_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};