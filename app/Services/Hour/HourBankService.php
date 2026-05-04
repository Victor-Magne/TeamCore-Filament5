<?php

/**
 * Ficheiro do Serviço HourBankService.
 *
 * Este serviço é o motor central de gestão do Banco de Horas.
 * Foi actualizado para funcionar de forma incremental, registando movimentos
 * individuais em vez de recalcular saldos mensais do zero.
 */

namespace App\Services\Hour;

use App\Models\Absence;
use App\Models\AttendanceLog;
use App\Models\HourBank;
use App\Models\HourBankMovement;
use Illuminate\Support\Facades\DB;

class HourBankService
{
    /**
     * Serviço auxiliar para cálculo técnico de horas extra.
     */
    protected CalculateExtraHoursService $calculateService;

    /**
     * Construtor com injecção de dependência.
     */
    public function __construct(CalculateExtraHoursService $calculateService)
    {
        $this->calculateService = $calculateService;
    }

    /**
     * Sincroniza um movimento do banco de horas baseado num log de presença.
     * Calcula horas extras ou défices e actualiza o saldo total.
     */
    public function syncLog(AttendanceLog $log): void
    {
        DB::transaction(function () use ($log) {
            $employeeId = $log->employee_id;
            $date = $log->time_in->toDateString();

            // 1. Calcular minutos (positivo extra, negativo défice)
            $extraMinutes = $this->calculateService->handle($log);
            $deficitMinutes = 0;

            // Só contar o défice se não houver Absence para o dia
            $hasAbsence = Absence::where('employee_id', $employeeId)
                ->where('absence_date', $date)
                ->exists();

            if (!$hasAbsence) {
                $deficitMinutes = $this->calculateService->calculateDeficit($log);
            }

            $amount = $extraMinutes - $deficitMinutes;

            // 2. Registar ou actualizar o movimento
            $this->updateMovement(
                $employeeId,
                $log,
                $amount,
                $amount >= 0 ? 'addition' : 'deduction',
                $amount >= 0 ? "Horas extra: {$log->time_in->format('d/m/Y')}" : "Défice de tempo: {$log->time_in->format('d/m/Y')}",
                $date
            );
        });
    }

    /**
     * Sincroniza um movimento do banco de horas baseado num registo de ausência.
     */
    public function syncAbsence(Absence $absence): void
    {
        DB::transaction(function () use ($absence) {
            $amount = -($absence->hours_deducted);

            $this->updateMovement(
                $absence->employee_id,
                $absence,
                $amount,
                'deduction',
                "{$absence->reason} ({$absence->absence_date->format('d/m/Y')})",
                $absence->absence_date->toDateString()
            );

            // Importante: Ao criar/actualizar uma Absence, devemos remover qualquer
            // défice automático do AttendanceLog desse dia para evitar dupla penalização.
            $log = AttendanceLog::where('employee_id', $absence->employee_id)
                ->whereDate('time_in', $absence->absence_date)
                ->first();

            if ($log) {
                $this->syncLog($log);
            }
        });
    }

    /**
     * Remove o movimento associado a uma origem (ex: log ou ausência eliminada).
     */
    public function removeMovement(string $sourceType, int $sourceId): void
    {
        DB::transaction(function () use ($sourceType, $sourceId) {
            $movement = HourBankMovement::where('source_type', $sourceType)
                ->where('source_id', $sourceId)
                ->first();

            if ($movement) {
                $this->adjustHourBank($movement->employee_id, -($movement->amount), $movement->type);
                $movement->delete();
            }
        });
    }

    /**
     * Helper para criar/actualizar movimentos e ajustar o saldo global.
     */
    protected function updateMovement(int $employeeId, $source, int $amount, string $type, string $description, string $date): void
    {
        $movement = HourBankMovement::firstOrNew([
            'source_type' => get_class($source),
            'source_id' => $source->id,
        ]);

        $oldAmount = $movement->exists ? $movement->amount : 0;
        $oldType = $movement->exists ? $movement->type : $type;

        $movement->fill([
            'employee_id' => $employeeId,
            'amount' => $amount,
            'type' => $type,
            'description' => $description,
            'date' => $date,
        ]);

        // Se o movimento é novo ou houve mudança de montante/tipo
        if (! $movement->exists || $movement->isDirty(['amount', 'type'])) {
            // Reverte o impacto do movimento anterior (se existia)
            if ($movement->exists) {
                $this->adjustHourBank($employeeId, -$oldAmount, $oldType);
            }

            // Aplica o novo impacto
            $movement->save();
            $this->adjustHourBank($employeeId, $amount, $type);
        }
    }

    /**
     * Ajusta os totais no registo principal do HourBank.
     */
    protected function adjustHourBank(int $employeeId, int $amount, string $type): void
    {
        $hourBank = HourBank::firstOrCreate(['employee_id' => $employeeId]);

        $hourBank->balance += $amount;

        // Ganhos e perdas acumulados são tratados separadamente para estatísticas
        if ($type === 'addition') {
            $hourBank->extra_hours_added += $amount;
        } elseif ($type === 'deduction') {
            // No caso de dedução, o amount é negativo, então subtraímos para somar ao contador de perdas
            $hourBank->extra_hours_used += abs($amount);
        }

        $hourBank->save();
    }
}
