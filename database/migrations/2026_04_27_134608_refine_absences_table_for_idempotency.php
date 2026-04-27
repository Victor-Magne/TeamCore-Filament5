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
        Schema::table('absences', function (Blueprint $table) {
            $table->foreignId('attendance_log_id')->nullable()->after('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_absence_id')->nullable()->after('attendance_log_id')->constrained('absences')->nullOnDelete();
        });

        // Podemos remover o campo consecutive_delays de employees pois vamos calcular via query para ser idempotente
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('consecutive_delays');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            $table->dropForeign(['attendance_log_id']);
            $table->dropColumn('attendance_log_id');
            $table->dropForeign(['parent_absence_id']);
            $table->dropColumn('parent_absence_id');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->integer('consecutive_delays')->default(0)->after('vacation_balance');
        });
    }
};
