<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payroll>
 */
class PayrollFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $baseSalary = $this->faker->numberBetween(1000, 5000);
        $hourlyRate = $baseSalary / (8 * 22);
        $extraHours = $this->faker->numberBetween(0, 480);
        $extraHoursAmount = ($hourlyRate * 1.5) * ($extraHours / 60);
        
        return [
            'employee_id' => Employee::factory(),
            'month_year' => $this->faker->monthName(true, false).'-'.date('Y'),
            'base_salary' => $baseSalary,
            'hourly_rate' => round($hourlyRate, 2),
            'extra_hours' => $extraHours,
            'extra_hours_amount' => round($extraHoursAmount, 2),
            'deductions' => $this->faker->numberBetween(0, 300),
            'total_net' => round($baseSalary + $extraHoursAmount - $this->faker->numberBetween(0, 300), 2),
            'status' => $this->faker->randomElement(['pending', 'paid', 'cancelled']),
        ];
    }
}
