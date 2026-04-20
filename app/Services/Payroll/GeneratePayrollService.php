<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\HourBank;
use App\Models\Payroll;
use Illuminate\Support\Carbon;

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

        // Calcular dias úteis no mês para um cálculo de valor hora mais preciso
        $date = Carbon::createFromFormat('Y-m', $monthYear);
        $daysInMonth = $date->daysInMonth;
        $weekdays = 0;
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $day = $date->copy()->day($i);
            if ($day->isWeekday()) {
                $weekdays++;
            }
        }

        $dailyWorkMinutes = $contract?->daily_work_minutes ?? 480;
        $monthlyWorkHours = ($weekdays * $dailyWorkMinutes) / 60;

        $hourlyRate = $baseSalary / ($monthlyWorkHours ?: 160);
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
