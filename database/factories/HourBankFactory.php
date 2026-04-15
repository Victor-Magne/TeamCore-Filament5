<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\HourBank;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HourBank>
 */
class HourBankFactory extends Factory
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
            'month_year' => now()->format('Y-m'),
            'balance' => $this->faker->numberBetween(-100, 100),
            'extra_hours_added' => $this->faker->numberBetween(0, 50),
            'extra_hours_used' => $this->faker->numberBetween(0, 50),
            'previous_balance' => $this->faker->numberBetween(-50, 50),
        ];
    }

    /**
     * Banco de horas com saldo positivo
     */
    public function positive(): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => $this->faker->numberBetween(10, 100),
            'previous_balance' => $this->faker->numberBetween(0, 50),
        ]);
    }

    /**
     * Banco de horas com saldo negativo (débito)
     */
    public function negative(): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => $this->faker->numberBetween(-100, -10),
            'extra_hours_used' => $this->faker->numberBetween(30, 100),
        ]);
    }

    /**
     * Banco de horas para mês específico
     */
    public function forMonth(string $monthYear): static
    {
        return $this->state(fn (array $attributes) => [
            'month_year' => $monthYear,
        ]);
    }
}
