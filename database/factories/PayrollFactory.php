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
        return [
            'employee_id' => Employee::factory(),
            'month_year' => $this->faker->monthName(true, false).'-'.date('Y'),
            'base_salary' => $this->faker->numberBetween(1000, 5000),
            'extra_hours_amount' => $this->faker->numberBetween(0, 200),
            'deductions' => $this->faker->numberBetween(0, 300),
            'total_net' => $this->faker->numberBetween(1000, 5000),
            'status' => $this->faker->randomElement(['pending', 'paid', 'cancelled']),
            'paid_at' => $this->faker->optional()->dateTime(),
        ];
    }
}
