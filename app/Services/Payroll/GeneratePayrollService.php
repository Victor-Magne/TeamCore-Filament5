<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\HourBank;
use App\Models\Payroll;

class GeneratePayrollService
{
    private const DEFAULT_DAILY_WORK_HOURS = 8;

    private const DEFAULT_WORKING_DAYS_PER_MONTH = 22;

    /**
     * Gera o processamento salarial para um funcionário num determinado mês.
     *
     * Fórmula do Valor da Hora:
     * ValorHora = SalárioBruto / (HorasDiárias × DiasÚteisNoMês)
     *
     * Fórmula das Horas Extras:
     * TotalHorasExtras = (ValorHora × 1.5) × (MinutosNoHourbank / 60)
     */
    public function handle(Employee $employee, string $monthYear): Payroll
    {
        // 1. Obter o contrato ativo
        $contract = $employee->contracts()
            ->where('status', 'active')
            ->first();

        if (! $contract) {
            // Se não houver contrato, usar salário base da designação
            $baseSalary = (float) ($employee->designation?->base_salary ?? 0);
            $dailyWorkHours = self::DEFAULT_DAILY_WORK_HOURS;
        } else {
            $baseSalary = (float) $contract->salary;
            // Converter minutos para horas (contrato pode armazenar em minutos)
            $dailyWorkHours = ($contract->daily_work_minutes ?? (self::DEFAULT_DAILY_WORK_HOURS * 60)) / 60;
        }

        // 2. Calcular o valor da hora normal
        // ValorHora = SalárioBruto / (HorasDiárias × DiasÚteisNoMês)
        $workingDaysPerMonth = self::DEFAULT_WORKING_DAYS_PER_MONTH;
        $hourlyRate = $baseSalary / ($dailyWorkHours * $workingDaysPerMonth);

        // 3. Carregar banco de horas para este período
        $hourBank = HourBank::where('employee_id', $employee->id)
            ->where('month_year', $monthYear)
            ->first();

        $extraHoursMinutes = $hourBank?->extra_hours_added ?? 0;
        $usedHoursMinutes = $hourBank?->extra_hours_used ?? 0;

        // 4. Calcular valor de horas extras com adicional de 1.5
        // TotalHorasExtras = (ValorHora × 1.5) × (MinutosNoHourbank / 60)
        $extraHoursAmount = ($hourlyRate * 1.5) * ($extraHoursMinutes / 60);

        // 5. Calcular deduções por horas utilizadas
        $deductions = $hourlyRate * ($usedHoursMinutes / 60);

        // 6. Calcular salário líquido
        $totalNet = $baseSalary + $extraHoursAmount - $deductions;

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
                'total_net' => max(0, $totalNet), // Não permite salário negativo
                'status' => 'pending',
            ]
        );
    }
}
