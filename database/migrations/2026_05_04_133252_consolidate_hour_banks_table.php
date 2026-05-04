<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Obter todos os registos actuais de HourBank
        $oldHourBanks = DB::table('hour_banks')->get();

        $consolidatedData = [];
        $movements = [];

        foreach ($oldHourBanks as $hb) {
            // Preparar movimento histórico
            $movements[] = [
                'employee_id' => $hb->employee_id,
                'amount' => $hb->extra_hours_added - $hb->extra_hours_used,
                'type' => ($hb->extra_hours_added >= $hb->extra_hours_used) ? 'addition' : 'deduction',
                'description' => "Saldo consolidado de {$hb->month_year}",
                'date' => $hb->month_year . '-01',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (!isset($consolidatedData[$hb->employee_id])) {
                $consolidatedData[$hb->employee_id] = [
                    'employee_id' => $hb->employee_id,
                    'balance' => 0,
                    'extra_hours_added' => 0,
                    'extra_hours_used' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $consolidatedData[$hb->employee_id]['balance'] += ($hb->extra_hours_added - $hb->extra_hours_used);
            $consolidatedData[$hb->employee_id]['extra_hours_added'] += $hb->extra_hours_added;
            $consolidatedData[$hb->employee_id]['extra_hours_used'] += $hb->extra_hours_used;
        }

        // 2. Limpar a tabela temporariamente para aplicar as alterações estruturais
        DB::table('hour_banks')->truncate();

        // 3. Alterar a estrutura da tabela
        Schema::table('hour_banks', function (Blueprint $table) {
            $table->dropUnique(['employee_id', 'month_year']);
            $table->dropColumn(['month_year', 'previous_balance']);
            $table->unique('employee_id');
        });

        // 4. Reinserir os dados consolidados e os movimentos
        if (!empty($consolidatedData)) {
            DB::table('hour_banks')->insert(array_values($consolidatedData));
        }

        if (!empty($movements)) {
            DB::table('hour_bank_movements')->insert($movements);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hour_banks', function (Blueprint $table) {
            $table->dropUnique(['employee_id']);
            $table->string('month_year')->nullable();
            $table->integer('previous_balance')->default(0);
            $table->unique(['employee_id', 'month_year']);
        });
    }
};
