<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('month_year'); // YYYY-MM

            // Cálculos de salário
            $table->decimal('base_salary', 12, 2);
            $table->decimal('hourly_rate', 12, 2); // Valor da hora
            $table->integer('extra_hours')->default(0); // Minutos de horas extras (do banco)
            $table->decimal('extra_hours_amount', 12, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('total_net', 12, 2);

            // Status e auditoria
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->unique(['employee_id', 'month_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
