<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contract>
 */
class ContractFactory extends Factory
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
            'designation_id' => Designation::factory(),
            'type' => $this->faker->randomElement(['permanent', 'temporary', 'part_time']),
            'salary' => $this->faker->numberBetween(1000, 10000),
            'status' => 'active',
            'start_date' => $this->faker->dateTimeBetween('-2 years'),
            'end_date' => null,
        ];
    }
}
