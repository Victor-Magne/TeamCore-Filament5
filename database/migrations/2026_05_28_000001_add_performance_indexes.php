<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacations', function (Blueprint $table) {
            $table->index(['employee_id', 'status'], 'vacations_employee_status_idx');
            $table->index(['employee_id', 'start_date', 'end_date'], 'vacations_employee_dates_idx');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->index(['employee_id', 'status', 'start_date'], 'contracts_employee_status_date_idx');
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->index('status', 'payrolls_status_idx');
        });

        Schema::table('leaves_and_absences', function (Blueprint $table) {
            $table->index(['employee_id', 'status', 'start_date', 'end_date'], 'leaves_employee_status_dates_idx');
        });
    }

    public function down(): void
    {
        Schema::table('vacations', function (Blueprint $table) {
            $table->dropIndex('vacations_employee_status_idx');
            $table->dropIndex('vacations_employee_dates_idx');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropIndex('contracts_employee_status_date_idx');
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropIndex('payrolls_status_idx');
        });

        Schema::table('leaves_and_absences', function (Blueprint $table) {
            $table->dropIndex('leaves_employee_status_dates_idx');
        });
    }
};
