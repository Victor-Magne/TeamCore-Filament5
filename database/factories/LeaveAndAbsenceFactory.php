<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\LeaveAndAbsence;
use App\Models\User;
use Carbon\Carbon;
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
        $startDate = Carbon::instance($this->faker->dateTimeBetween('-1 month', '+1 month'));
        $endDate = $startDate->copy()->addDays($this->faker->numberBetween(1, 10));

        return [
            'employee_id' => Employee::factory(),
            'type' => $this->faker->randomElement([
                'sick_leave',
                'parental',
                'marriage',
                'bereavement',
                'justified_absence',
            ]),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => $this->faker->sentence(),
            'is_paid' => $this->faker->boolean(70),
            'justification_doc' => null,
            'status' => 'pending',
            'approved_by' => null,
            'rejection_reason' => null,
        ];
    }

    public function sickLeave(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sick_leave',
            'is_paid' => true,
            'reason' => 'Licenca medica',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
        ]);
    }
}
