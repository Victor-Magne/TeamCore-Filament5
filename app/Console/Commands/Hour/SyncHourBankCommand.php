<?php

/**
 * Ficheiro do Comando SyncHourBankCommand.
 *
 * Este comando de manutenção serve para identificar e corrigir inconsistências
 * no Banco de Horas, especificamente movimentos "órfãos" cujas origens (Faltas/Logs)
 * foram eliminadas sem que o saldo fosse actualizado correctamente.
 */

namespace App\Console\Commands\Hour;

use App\Models\HourBankMovement;
use App\Services\Hour\HourBankService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncHourBankCommand extends Command
{
    /**
     * O nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'app:sync-hour-bank {--dry-run : Apenas simula as alterações sem as gravar}';

    /**
     * A descrição do comando.
     *
     * @var string
     */
    protected $description = 'Sincroniza o Banco de Horas e remove movimentos órfãos de faltas eliminadas';

    /**
     * Serviço de gestão do banco de horas.
     */
    protected HourBankService $hourBankService;

    /**
     * Construtor com injecção de dependência.
     */
    public function __construct(HourBankService $hourBankService)
    {
        parent::__construct();
        $this->hourBankService = $hourBankService;
    }

    /**
     * Executa o comando.
     */
    public function handle(): int
    {
        $this->info('A iniciar a verificação de integridade do Banco de Horas...');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('*** MODO DE SIMULAÇÃO ACTIVADO - Nenhuma alteração será gravada ***');
        }

        // 1. Identificar movimentos cujas fontes já não existem
        // Focamos especificamente em Absence e AttendanceLog
        $movements = HourBankMovement::all();
        $orphanedCount = 0;
        $restoredMinutes = 0;

        foreach ($movements as $movement) {
            // Verificar se a fonte existe.
            // Nota: Se usar SoftDeletes, o movement->source será null a menos que use withTrashed().
            // No nosso caso, se a fonte foi apagada (ou está no lixo e não devia contar), queremos corrigir.
            if ($movement->source === null) {
                $orphanedCount++;
                $restoredMinutes += abs($movement->amount);

                $this->line(sprintf(
                    'Encontrado movimento órfão ID %d: %s (%d min) - Funcionário: %d',
                    $movement->id,
                    $movement->description,
                    $movement->amount,
                    $movement->employee_id
                ));

                if (! $dryRun) {
                    DB::transaction(function () use ($movement) {
                        $this->hourBankService->removeMovement($movement->source_type, $movement->source_id);
                    });
                }
            }
        }

        if ($orphanedCount === 0) {
            $this->info('Nenhuma inconsistência encontrada. O sistema está íntegro.');
        } else {
            $this->info(sprintf(
                '%d movimentos %s corrigidos. Total de %d minutos repostos nos saldos.',
                $orphanedCount,
                $dryRun ? 'seriam' : 'foram',
                $restoredMinutes
            ));
        }

        return Command::SUCCESS;
    }
}
