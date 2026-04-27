<?php

namespace Database\Factories;

use App\Models\Absence;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Absence>
 */
class AbsenceFactory extends Factory
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
            'leave_and_absence_id' => null,
            'absence_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'hours_deducted' => $this->faker->numberBetween(60, 480),
            'deduction_type' => $this->faker->randomElement(['unjustified_absence', 'partial_absence', 'other']),
            'reason' => $this->faker->sentence(),
        ];
    }

    public function sickLeave(): static
    {
        return $this->state(fn (array $attributes) => [
            'deduction_type' => 'other',
            'reason' => 'Licenca medica',
        ]);
    }

    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'deduction_type' => 'unjustified_absence',
            'hours_deducted' => 480,
        ]);
    }
}
