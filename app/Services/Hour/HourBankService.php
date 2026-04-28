<?php

/**
 * Ficheiro do Serviço HourBankService.
 *
 * Este serviço é o motor central de gestão do Banco de Horas.
 * É responsável por recalcular os saldos mensais, processar ganhos (horas extra)
 * e perdas (ausências), garantindo que as alterações se propagam correctamente
 * para os meses futuros.
 */

namespace App\Services\Hour;

use App\Models\Absence;
use App\Models\AttendanceLog;
use App\Models\HourBank;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HourBankService
{
    /**
     * Serviço auxiliar para cálculo técnico de horas extra.
     */
    protected CalculateExtraHoursService $calculateService;

    /**
     * Cache em memória para evitar recursividade infinita ou cálculos duplicados
     * durante o mesmo ciclo de execução.
     *
     * @var array<string, bool>
     */
    protected array $calculating = [];

    /**
     * Construtor com injecção de dependência.
     */
    public function __construct(CalculateExtraHoursService $calculateService)
    {
        $this->calculateService = $calculateService;
    }

    /**
     * Inicia o recálculo do banco de horas para um funcionário e mês específico.
     * Implementa um mecanismo de protecção contra reentrada.
     *
     * @param int $employeeId ID do funcionário
     * @param string $monthYear Mês e ano no formato 'Y-m'
     */
    public function recalculate(int $employeeId, string $monthYear): void
    {
        $key = "{$employeeId}-{$monthYear}";
        if (isset($this->calculating[$key])) {
            return;
        }
        $this->calculating[$key] = true;

        try {
            $this->performRecalculate($employeeId, $monthYear);
        } finally {
            unset($this->calculating[$key]);
        }
    }

    /**
     * Executa a lógica efectiva de recálculo dentro de uma transacção.
     */
    protected function performRecalculate(int $employeeId, string $monthYear): void
    {
        DB::transaction(function () use ($employeeId, $monthYear) {
            $month = Carbon::createFromFormat('Y-m', $monthYear);
            $startDate = $month->copy()->startOfMonth();
            $endDate = $month->copy()->endOfMonth();

            // 1. Calcular Horas Extras Ganhas e Défices via AttendanceLog
            $extraMinutesAdded = 0;
            $extraMinutesUsedFromLogs = 0;
            $logs = AttendanceLog::where('employee_id', $employeeId)
                ->whereBetween('time_in', [$startDate, $endDate])
                ->get();

            foreach ($logs as $log) {
                // Calcula horas que excedem o horário contratual
                $extraMinutesAdded += $this->calculateService->handle($log);

                // Só contar o défice do log (tempo a menos trabalhado) se não houver
                // um registo formal de Absence (falta/atraso) para este dia.
                // Isto evita que o funcionário seja penalizado duas vezes pelo mesmo tempo.
                $hasAbsence = Absence::where('employee_id', $employeeId)
                    ->where('absence_date', $log->time_in->toDateString())
                    ->exists();

                if (!$hasAbsence) {
                    $extraMinutesUsedFromLogs += $this->calculateService->calculateDeficit($log);
                }
            }

            // 2. Somar Horas Perdidas via registos formais de Absence (faltas injustificadas, etc.)
            $extraMinutesUsed = Absence::where('employee_id', $employeeId)
                ->whereBetween('absence_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->sum('hours_deducted');

            // Combina os descontos de assiduidade com os descontos formais
            $extraMinutesUsed += $extraMinutesUsedFromLogs;

            // 3. Recuperar o saldo final do mês anterior
            $previousBalance = $this->getPreviousBalance($employeeId, $monthYear);

            // 4. Actualizar ou criar o registo de HourBank para este mês
            // O balance é o resultado de: Saldo Anterior + Ganhos - Perdas
            $hourBank = HourBank::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'month_year' => $monthYear,
                ],
                [
                    'previous_balance' => $previousBalance,
                    'extra_hours_added' => $extraMinutesAdded,
                    'extra_hours_used' => $extraMinutesUsed,
                    'balance' => $previousBalance + $extraMinutesAdded - $extraMinutesUsed,
                ]
            );

            // 5. Propagar as alterações em cascata para os meses subsequentes
            // essencial se o recálculo for de um mês passado.
            $this->propagate($employeeId, $monthYear);
        });
    }

    /**
     * Propaga o saldo final de um mês para o saldo inicial (previous_balance) do mês seguinte.
     * Funciona de forma recursiva até não encontrar mais meses registados.
     */
    public function propagate(int $employeeId, string $monthYear): void
    {
        $currentMonth = Carbon::createFromFormat('Y-m', $monthYear);
        $nextMonth = $currentMonth->copy()->addMonth();
        $nextMonthYear = $nextMonth->format('Y-m');

        // Procura se já existe um registo para o mês seguinte
        $nextHourBank = HourBank::where('employee_id', $employeeId)
            ->where('month_year', $nextMonthYear)
            ->first();

        // Se não existir, a cadeia de propagação termina
        if (! $nextHourBank) {
            return;
        }

        // Obtém o saldo final actualizado do mês actual
        $currentBalance = HourBank::where('employee_id', $employeeId)
            ->where('month_year', $monthYear)
            ->value('balance') ?? 0;

        // Actualiza o mês seguinte com o novo saldo transportado
        $nextHourBank->previous_balance = $currentBalance;
        $nextHourBank->balance = $currentBalance + $nextHourBank->extra_hours_added - $nextHourBank->extra_hours_used;
        $nextHourBank->save();

        // Chamada recursiva para continuar a propagação para o mês depois deste
        $this->propagate($employeeId, $nextMonthYear);
    }

    /**
     * Obtém o saldo do último mês registado antes do mês de referência.
     *
     * @return int Saldo em minutos
     */
    private function getPreviousBalance(int $employeeId, string $currentMonthYear): int
    {
        return HourBank::where('employee_id', $employeeId)
            ->where('month_year', '<', $currentMonthYear)
            ->orderByDesc('month_year')
            ->value('balance') ?? 0;
    }
}
