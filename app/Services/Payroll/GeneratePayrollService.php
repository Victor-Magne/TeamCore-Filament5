<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\HourBank;
use App\Models\Payroll;

class GeneratePayrollService
{
    /**
     * Gera o processamento salarial para um funcionário num determinado mês
     */
    public function handle(Employee $employee, string $monthYear): Payroll
    {
        // 1. Obter o contrato ativo
        $contract = $employee->contracts()->where('status', 'active')->first();
        $baseSalary = $contract?->salary ?? $employee->designation?->base_salary ?? 0;

        // 2. Calcular valor de horas extras (simplificado: valor hora * 1.5)
        $hourBank = HourBank::where('employee_id', $employee->id)
            ->where('month_year', $monthYear)
            ->first();

        $extraHoursMinutes = $hourBank?->extra_hours_added ?? 0;
        $usedHoursMinutes = $hourBank?->extra_hours_used ?? 0;

        $hourlyRate = $baseSalary / 160; // 160h/mês padrão
        $extraHoursAmount = ($extraHoursMinutes / 60) * $hourlyRate * 1.5;
        $deductions = ($usedHoursMinutes / 60) * $hourlyRate;

        $totalNet = $baseSalary + $extraHoursAmount - $deductions;

        return Payroll::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'month_year' => $monthYear,
            ],
            [
                'base_salary' => $baseSalary,
                'extra_hours_amount' => $extraHoursAmount,
                'deductions' => $deductions,
                'total_net' => $totalNet,
                'status' => 'pending',
            ]
        );
    }
}
