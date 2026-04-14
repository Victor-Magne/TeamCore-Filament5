<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void
{
// 1. Países
Schema::create('countries', function (Blueprint $table) {
$table->id();
$table->string('name', 100);
$table->char('code', 2)->unique();
$table->unsignedSmallInteger('phonecode');
$table->timestamps();
$table->softDeletes();
});

// 2. Estados/Províncias
Schema::create('states', function (Blueprint $table) {
$table->id();
$table->string('name', 100);
$table->foreignId('country_id')->constrained()->cascadeOnDelete();
$table->timestamps();
$table->softDeletes();
});

// 3. Cidades
Schema::create('cities', function (Blueprint $table) {
$table->id();
$table->string('name', 100);
$table->foreignId('state_id')->constrained()->cascadeOnDelete();
$table->timestamps();
$table->softDeletes();
});
}

public function down(): void
{
Schema::dropIfExists('cities');
Schema::dropIfExists('states');
Schema::dropIfExists('countries');
}
};