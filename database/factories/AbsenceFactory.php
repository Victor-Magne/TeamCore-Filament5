<?php

namespace Database\Factories;

use App\Models\Absence;
use App\Models\Employee;
use App\Models\LeaveAndAbsence;
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
            'leave_and_absence_id' => LeaveAndAbsence::factory(),
            'absence_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'hours_deducted' => $this->faker->numberBetween(1, 8),
            'deduction_type' => $this->faker->randomElement(['sick_leave', 'unpaid', 'personal', 'disciplinary']),
            'reason' => $this->faker->sentence(),
        ];
    }

    /**
     * Ausência por doença
     */
    public function sickLeave(): static
    {
        return $this->state(fn (array $attributes) => [
            'deduction_type' => 'sick_leave',
            'reason' => 'Licença médica',
        ]);
    }

    /**
     * Ausência sem remuneração
     */
    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'deduction_type' => 'unpaid',
            'hours_deducted' => 8,
        ]);
    }
}
