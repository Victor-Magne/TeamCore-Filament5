<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\HourBankMovement;
use App\Models\Payroll;
use Illuminate\Support\Carbon;

class GeneratePayrollService
{
    /**
     * Gera o processamento salarial para um funcionário num determinado mês.
     *
     * Fórmulas:
     * 1. Valor/Hora = Salário / (Horas Diárias × dias_úteis_mês)
     * 2. Adicional Horas Extra = (Valor/Hora × multiplicador) × horas_extra
     * 3. Deduções = Valor/Hora × horas_perdidas
     */
    public function handle(Employee $employee, string $monthYear): Payroll
    {
        $defaultDailyMinutes = config('hr.default_daily_work_minutes');
        $workingDaysPerMonth = config('hr.working_days_per_month');
        $extraHoursMultiplier = config('hr.extra_hours_multiplier');

        $contract = $employee->contracts()
            ->where('status', 'active')
            ->first();

        if (! $contract) {
            $baseSalary = (float) ($employee->designation?->base_salary ?? 0);
            $dailyWorkHours = $defaultDailyMinutes / 60;
        } else {
            $baseSalary = (float) $contract->salary;
            $dailyWorkHours = ($contract->daily_work_minutes ?? $defaultDailyMinutes) / 60;
        }

        $hourlyRate = $baseSalary / ($dailyWorkHours * $workingDaysPerMonth);

        $month = Carbon::createFromFormat('Y-m', $monthYear);
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();

        $movements = HourBankMovement::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw("SUM(CASE WHEN type = 'addition' THEN amount ELSE 0 END) as extra, SUM(CASE WHEN type = 'deduction' THEN amount ELSE 0 END) as deducted")
            ->first();

        $extraHoursMinutes = (int) ($movements->extra ?? 0);
        $usedHoursMinutes = abs((int) ($movements->deducted ?? 0));

        $extraHoursAmount = ($hourlyRate * $extraHoursMultiplier) * ($extraHoursMinutes / 60);
        $deductions = $hourlyRate * ($usedHoursMinutes / 60);
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
                'total_net' => max(0, $totalNet),
                'status' => 'pending',
            ]
        );
    }
}
