<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite (used in tests) does not support ALTER COLUMN for enums.
        // MySQL supports it. The create_leaves_and_absences migration already
        // defines the full schema for SQLite tests, so we only need MySQL ALTER.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE leaves_and_absences MODIFY COLUMN `type` ENUM('sick_leave','parental','marriage','bereavement','justified_absence','unjustified') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE leaves_and_absences MODIFY COLUMN `type` ENUM('sick_leave','parental','marriage','bereavement','justified_absence') NOT NULL");
        }
    }
};
