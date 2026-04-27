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
        // 1. Atualizar Employees
        Schema::table('employees', function (Blueprint $table) {
            $table->integer('consecutive_delays')->default(0)->after('vacation_balance');
            // Nota: designer_id não será removido fisicamente ainda para evitar quebra de dados,
            // mas será ignorado pela lógica do model.
        });

        // 2. Atualizar Contracts
        Schema::table('contracts', function (Blueprint $table) {
            $table->time('expected_start_time')->default('09:00:00')->after('salary');
            $table->integer('lunch_duration_minutes')->default(60)->after('expected_start_time');
        });

        // 3. Atualizar Absences
        Schema::table('absences', function (Blueprint $table) {
            // Se já existirem, vamos modificar, senão adicionar
            if (!Schema::hasColumn('absences', 'type')) {
                $table->string('type')->default('atraso')->after('deduction_type');
            }
            if (!Schema::hasColumn('absences', 'status')) {
                $table->enum('status', ['pendente', 'justificado', 'rejeitado'])->default('pendente')->after('type');
            }
            if (!Schema::hasColumn('absences', 'justification')) {
                $table->text('justification')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('consecutive_delays');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['expected_start_time', 'lunch_duration_minutes']);
        });

        Schema::table('absences', function (Blueprint $table) {
            $table->dropColumn(['type', 'status', 'justification']);
        });
    }
};
