<?php

namespace Database\Factories;

use App\Models\Designation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $designationName = $this->faker->jobTitle().' '.strtoupper(Str::random(6));

        while (Designation::query()->where('name', $designationName)->exists()) {
            $designationName = $this->faker->jobTitle().' '.strtoupper(Str::random(6));
        }

        return [
            'name' => $designationName,
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
            'level' => $this->faker->randomElement(['specialist', 'lead']),
            'base_salary' => $this->faker->numberBetween(300000, 600000) / 100,
        ]);
    }

    /**
     * Designação de nível operacional
     */
    public function operational(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => $this->faker->randomElement(['junior', 'pleno']),
            'base_salary' => $this->faker->numberBetween(100000, 200000) / 100,
        ]);
    }
}
