<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaves_and_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            $table->enum('type', [
                'sick_leave',       // Baixa Médica (SNS)
                'parental',         // Licença Parental
                'marriage',         // Licença de Casamento (15 dias seguidos)
                'bereavement',      // Falecimento de familiar (Nojo)
                'justified_absence', // Falta Justificada (ex: ida a tribunal)
                'unjustified',      // Falta Injustificada
            ]);

            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable(); // Motivo da ausência
            $table->boolean('is_paid')->default(true); // Se a empresa paga ou se é a Seg. Social
            $table->string('justification_doc')->nullable(); // Caminho para o PDF do atestado

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaves_and_absences');
    }
};
