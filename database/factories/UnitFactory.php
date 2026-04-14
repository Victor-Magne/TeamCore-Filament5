<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'type' => $this->faker->randomElement(['department', 'division', 'team']),
            'description' => $this->faker->sentence(),
            'parent_id' => null,
            'manager_id' => null,
            'is_main_direction' => false,
        ];
    }
}
