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
            'name' => $this->faker->words(2, true),
            // AJUSTE: Removido 'division' e 'team' que estavam a causar o erro
            // Deixe aqui apenas o que definiu no $table->enum(...) da migração
            'type' => $this->faker->randomElement(['department', 'section']),
            'description' => $this->faker->sentence(),
            'parent_id' => null,
            'manager_id' => null,
            'is_main_direction' => false,
        ];
    }

    /**
     * Unidade principal/diretoria
     */
    public function mainDirection(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_main_direction' => true,
            'type' => 'direction', // Garanta que 'direction' está na sua migração
            'parent_id' => null,
        ]);
    }

    /**
     * Departamento
     */
    public function department(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'department',
        ]);
    }

    /**
     * Unidade com hierarquia (depende de parent)
     */
    public function withParent(?Unit $parent = null): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent?->id ?? Unit::factory(),
        ]);
    }
}
