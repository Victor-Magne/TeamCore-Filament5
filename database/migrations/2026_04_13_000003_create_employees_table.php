<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained();
            $table->foreignId('unit_id')->constrained('organizational_units');
            $table->foreignId('designation_id')->nullable()->constrained()->nullOnDelete();

            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email')->unique();
            $table->string('phone_number', 20);
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('nif', 20)->unique();
            $table->string('nss', 20)->unique();
            $table->string('address', 500);
            $table->string('zip_code', 20);
            $table->date('date_hired');
            $table->dateTime('date_dismissed')->nullable();
            $table->unsignedTinyInteger('vacation_balance')->default(22);

            $table->timestamps();
            $table->softDeletes();
        });

        // 1. Resolvemos o Manager na Organizational Units
        Schema::table('organizational_units', function (Blueprint $table) {
            $table->foreign('manager_id')->references('id')->on('employees')->onDelete('set null');
        });

        // 2. Resolvemos a ligação na tabela Users (O que faltava!)
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });

        Schema::table('organizational_units', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });

        Schema::dropIfExists('employees');
    }
};
