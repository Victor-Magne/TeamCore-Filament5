<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Contratos
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            // Tipo de contrato
            $table->enum('type', [
                'permanent',              // Sem Termo
                'fixed_term',            // A Termo Certo
                'unfixed_term',          // A Termo Incerto
                'service_provision',     // Prestação de Serviços (Recibos Verdes)
                'internship',            // Estágio Profissional (IEFP)
            ]);

            // Dados do contrato
            $table->decimal('salary', 12, 2);
            $table->date('start_date');
            $table->date('end_date')->nullable(); // Obrigatório se for "fixed_term"
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
