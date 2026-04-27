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
        Schema::table('contracts', function (Blueprint $table) {
            $table->time('expected_start_time')->default('09:00:00')->after('daily_work_minutes');
            $table->integer('lunch_duration_minutes')->default(60)->after('expected_start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['expected_start_time', 'lunch_duration_minutes']);
        });
    }
};
