<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\LeaveAndAbsence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveAndAbsence>
 */
class LeaveAndAbsenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
        return [
            'employee_id' => Employee::factory(),
            'type' => $this->faker->randomElement(['sick_leave', 'vacation', 'unpaid', 'other']),
            'start_date' => $startDate,
            'end_date' => $this->faker->dateTimeBetween($startDate, '+10 days'),
            'reason' => $this->faker->sentence(),
            'is_paid' => $this->faker->boolean(),
            'justification_doc' => null,
        ];
    }
}
