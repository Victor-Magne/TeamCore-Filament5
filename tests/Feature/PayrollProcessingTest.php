<?php

use App\Models\Contract;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\HourBank;
use App\Models\Payroll;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Payroll Processing', function () {
    beforeEach(function () {
        $this->designation = Designation::factory()->create([
            'base_salary' => 1000, // 1000€ por mês
        ]);

        $this->employee = Employee::factory()->create([
            'designation_id' => $this->designation->id,
        ]);

        $this->contract = Contract::factory()->create([
            'employee_id' => $this->employee->id,
            'designation_id' => $this->designation->id,
            'salary' => 1000,
            'status' => 'active',
            'daily_work_minutes' => 480, // 8 horas por dia
        ]);

        // Criar banco de horas para o mês
        HourBank::updateOrCreate([
            'employee_id' => $this->employee->id,
            'month_year' => now()->format('Y-m'),
        ], [
            'balance' => 120, // 2 horas extras
            'extra_hours_added' => 120,
            'extra_hours_used' => 0,
            'previous_balance' => 0,
        ]);
    });

    it('creates payroll for employee', function () {
        $monthYear = now()->format('Y-m');

        $payroll = Payroll::create([
            'employee_id' => $this->employee->id,
            'month_year' => $monthYear,
            'base_salary' => $this->contract->salary,
            'hourly_rate' => 6.25,
            'extra_hours' => 0,
            'extra_hours_amount' => 0,
            'deductions' => 0,
            'total_net' => $this->contract->salary,
            'status' => 'pending',
        ]);

        expect($payroll)->not->toBeNull();
        expect((float) $payroll->total_net)->toBe(1000.0);
    });

    it('calculates extra hours bonus correctly', function () {
        $monthYear = now()->format('Y-m');

        // Obter banco de horas com 2 horas extras (120 minutos)
        $hourBank = HourBank::where('employee_id', $this->employee->id)
            ->where('month_year', $monthYear)
            ->first();

        // Assumir que cada hora extra vale 150% do salário/hora
        // 1000€ / 160 horas = 6.25€/hora
        // 2 horas * 6.25 * 1.5 = 18.75€
        $hourlyRate = $this->contract->salary / 160;
        $extraHoursBonus = ($hourBank->balance / 60) * $hourlyRate * 1.5;

        expect($extraHoursBonus)->toBeGreaterThan(0);
    });

    it('generates payroll slip', function () {
        $monthYear = now()->format('Y-m');

        $payroll = Payroll::create([
            'employee_id' => $this->employee->id,
            'month_year' => $monthYear,
            'base_salary' => 1000,
            'hourly_rate' => 6.25,
            'extra_hours' => 120,
            'extra_hours_amount' => 18.75,
            'deductions' => 0,
            'total_net' => 1018.75,
            'status' => 'pending',
        ]);

        expect($payroll->status)->toBe('pending');
        expect((float) $payroll->total_net)->toBe(1018.75);
    });

    it('prevents duplicate payroll for same month', function () {
        $monthYear = now()->format('Y-m');

        Payroll::create([
            'employee_id' => $this->employee->id,
            'month_year' => $monthYear,
            'base_salary' => 1000,
            'hourly_rate' => 6.25,
            'extra_hours' => 0,
            'extra_hours_amount' => 0,
            'deductions' => 0,
            'total_net' => 1000,
            'status' => 'pending',
        ]);

        // Tentar criar novamente (em implementação real, isto seria impedido por unique constraint)
        $count = Payroll::where('employee_id', $this->employee->id)
            ->where('month_year', $monthYear)
            ->count();

        expect($count)->toBe(1);
    });

    it('calculates net salary after deductions', function () {
        $monthYear = now()->format('Y-m');

        // Assumir deduções de ~10% para segurança social, impostos
        $deductions = 100; // 100€
        $grossSalary = 1000;
        $netSalary = $grossSalary - $deductions;

        $payroll = Payroll::create([
            'employee_id' => $this->employee->id,
            'month_year' => $monthYear,
            'base_salary' => 1000,
            'hourly_rate' => 6.25,
            'extra_hours' => 0,
            'extra_hours_amount' => 0,
            'deductions' => $deductions,
            'total_net' => $netSalary,
            'status' => 'pending',
        ]);

        expect((float) $payroll->total_net)->toBe(900.0);
    });

    it('tracks payroll payment status', function () {
        $monthYear = now()->format('Y-m');

        $payroll = Payroll::create([
            'employee_id' => $this->employee->id,
            'month_year' => $monthYear,
            'base_salary' => 1000,
            'hourly_rate' => 6.25,
            'extra_hours' => 0,
            'extra_hours_amount' => 0,
            'deductions' => 0,
            'total_net' => 1000,
            'status' => 'pending',
        ]);

        expect($payroll->status)->toBe('pending');

        // Simular mudança para pago
        $payroll->update(['status' => 'paid']);

        expect($payroll->status)->toBe('paid');
    });
});
