<?php

/**
 * Ficheiro do Serviço GeneratePayrollService.
 *
 * Este serviço é responsável pelo motor de cálculo salarial.
 * Transforma os dados contratuais e os saldos do banco de horas num registo
 * de processamento salarial (Payroll), aplicando fórmulas para o valor da hora,
 * suplementos de horas extra e deduções por ausências.
 */

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\HourBank;
use App\Models\Payroll;
use Illuminate\Support\Carbon;

class GeneratePayrollService
{
    /** Horas de trabalho diárias por defeito caso não existam no contrato. */
    private const DEFAULT_DAILY_WORK_HOURS = 8;

    /** Dias úteis médios por mês para cálculo do valor hora (padrão 22 dias). */
    private const DEFAULT_WORKING_DAYS_PER_MONTH = 22;

    /**
     * Gera o processamento salarial para um funcionário num determinado mês.
     *
     * Fórmulas Utilizadas:
     * 1. Valor da Hora: Salário Bruto / (Horas Diárias x 22 dias)
     * 2. Adicional de Horas Extra: (Valor Hora x 1.5) x Horas Extra Acumuladas
     * 3. Deduções: Valor Hora x Horas em Dívida (Faltas/Atrasos)
     *
     * @param Employee $employee O funcionário a processar
     * @param string $monthYear Mês de referência no formato 'Y-m'
     * @return Payroll
     */
    public function handle(Employee $employee, string $monthYear): Payroll
    {
        // 1. Obter o contrato activo para determinar base salarial e carga horária
        $contract = $employee->contracts()
            ->where('status', 'active')
            ->first();

        if (! $contract) {
            // Fallback: Usa dados da designação/cargo se não houver contrato activo
            $baseSalary = (float) ($employee->designation?->base_salary ?? 0);
            $dailyWorkHours = self::DEFAULT_DAILY_WORK_HOURS;
        } else {
            $baseSalary = (float) $contract->salary;
            // Converte minutos de trabalho diários do contrato para horas decimais
            $dailyWorkHours = ($contract->daily_work_minutes ?? (self::DEFAULT_DAILY_WORK_HOURS * 60)) / 60;
        }

        // 2. Calcular o valor da hora normal de trabalho
        $workingDaysPerMonth = self::DEFAULT_WORKING_DAYS_PER_MONTH;
        $hourlyRate = $baseSalary / ($dailyWorkHours * $workingDaysPerMonth);

        // 3. Carregar o banco de horas do período para obter ganhos e perdas de tempo
        $hourBank = HourBank::where('employee_id', $employee->id)
            ->where('month_year', $monthYear)
            ->first();

        $extraHoursMinutes = $hourBank?->extra_hours_added ?? 0;
        $usedHoursMinutes = $hourBank?->extra_hours_used ?? 0;

        // 4. Calcular valor monetário das horas extras (com acréscimo de 50% - factor 1.5)
        $extraHoursAmount = ($hourlyRate * 1.5) * ($extraHoursMinutes / 60);

        // 5. Calcular valor a descontar por ausências (horas não trabalhadas)
        $deductions = $hourlyRate * ($usedHoursMinutes / 60);

        // 6. Consolidar o Salário Líquido (Base + Extras - Deduções)
        $totalNet = $baseSalary + $extraHoursAmount - $deductions;

        // Persiste ou actualiza o registo de Payroll na base de dados
        return Payroll::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'month_year' => $monthYear,
            ],
            [
                'base_salary' => $baseSalary,
                'hourly_rate' => $hourlyRate,
                'extra_hours' => (int) $extraHoursMinutes,
                'extra_hours_amount' => $extraHoursAmount,
                'deductions' => $deductions,
                'total_net' => max(0, $totalNet), // Garante que o salário não é negativo
                'status' => 'pending',
            ]
        );
    }
}
