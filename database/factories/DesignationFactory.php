<?php

namespace Database\Factories;

use App\Models\Designation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Designation>
 */
class DesignationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->jobTitle(),
            'level' => $this->faker->randomElement(['junior', 'pleno', 'senior', 'specialist', 'lead']),
            'base_salary' => $this->faker->numberBetween(100000, 500000) / 100,
        ];
    }

    /**
     * Designação de nível gerencial
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => $this->faker->numberBetween(4, 5),
            'base_salary' => $this->faker->numberBetween(300000, 600000) / 100,
        ]);
    }

    /**
     * Designação de nível operacional
     */
    public function operational(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => $this->faker->numberBetween(1, 2),
            'base_salary' => $this->faker->numberBetween(100000, 200000) / 100,
        ]);
    }
}
