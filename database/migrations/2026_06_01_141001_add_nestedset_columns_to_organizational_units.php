<?php

use App\Models\Unit;
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
        Schema::table('organizational_units', function (Blueprint $table) {
            // _lft and _rgt already exist from a partial run; add missing depth column
            if (! Schema::hasColumn('organizational_units', '_lft')) {
                $table->unsignedInteger('_lft')->default(0)->after('parent_id');
            }
            if (! Schema::hasColumn('organizational_units', '_rgt')) {
                $table->unsignedInteger('_rgt')->default(0)->after('_lft');
            }
            if (! Schema::hasColumn('organizational_units', 'depth')) {
                $table->unsignedInteger('depth')->default(0)->after('_rgt');
            }
        });

        Unit::fixTree();
    }

    public function down(): void
    {
        Schema::table('organizational_units', function (Blueprint $table) {
            $table->dropColumn(
                collect(['_lft', '_rgt', 'depth'])
                    ->filter(fn ($col) => Schema::hasColumn('organizational_units', $col))
                    ->values()
                    ->all()
            );
        });
    }
};
