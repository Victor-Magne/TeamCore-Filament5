<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('designation_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->after('employee_id');

            $table->enum('status', ['active', 'terminated', 'on_hold'])
                ->default('active')
                ->after('end_date');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('contracts_designation_id_foreign');
            $table->dropColumn(['designation_id', 'status']);
        });
    }
};
