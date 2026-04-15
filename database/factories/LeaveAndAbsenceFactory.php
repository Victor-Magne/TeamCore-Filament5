<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\LeaveAndAbsence;
use Carbon\Carbon; // Importante: Importar o Carbon
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
        // 1. Geramos a data de início (convertendo logo para Carbon)
        $startDate = Carbon::instance($this->faker->dateTimeBetween('-1 month', '+1 month'));

        // 2. A data de fim será SEMPRE entre 1 e 10 dias APÓS a data de início
        // Usamos o copy() para não alterar o $startDate original
        $endDate = $startDate->copy()->addDays($this->faker->numberBetween(1, 10));

        return [
            'employee_id' => Employee::factory(),
            'type' => $this->faker->randomElement(['sick_leave', 'vacation', 'unpaid', 'other', 'personal']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => $this->faker->sentence(),
            'is_paid' => $this->faker->boolean(70),
            'justification_doc' => null,
        ];
    }

    /**
     * Licença médica
     */
    public function sickLeave(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sick_leave',
            'is_paid' => true,
            'reason' => 'Licença médica',
        ]);
    }

    /**
     * Férias
     */
    public function vacation(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'vacation',
            'is_paid' => true,
        ]);
    }

    /**
     * Falta sem remuneração
     */
    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'unpaid',
            'is_paid' => false,
            'reason' => 'Falta sem remuneração',
        ]);
    }

    /**
     * Licença pessoal
     */
    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'personal',
            'is_paid' => true,
            'reason' => 'Assunto pessoal',
        ]);
    }
}
