<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use App\Models\Vacation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vacation>
 */
class VacationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = Carbon::instance($this->faker->dateTimeBetween('-3 months', '+3 months'));
        $endDate = $startDate->copy()->addDays($this->faker->numberBetween(5, 20));

        return [
            'employee_id' => Employee::factory(),
            'year_reference' => (int) $startDate->format('Y'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_taken' => null,
            'status' => 'pending',
            'approved_by' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'approved_by' => null,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'approved_by' => User::factory(),
        ]);
    }
}
