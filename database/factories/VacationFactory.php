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
        // 1. Geramos a data de início
        $startDate = Carbon::instance($this->faker->dateTimeBetween('-3 months', '+3 months'));

        // 2. Criamos a data de fim (sempre 5 a 20 dias após o início)
        $endDate = $startDate->copy()->addDays($this->faker->numberBetween(5, 20));

        // 3. CORREÇÃO: Forçamos o valor absoluto para evitar números negativos
        // O segundo parâmetro 'true' no diffInDays força o resultado a ser absoluto
        $daysTaken = $endDate->diffInDays($startDate, true);

        return [
            'employee_id' => Employee::factory(),
            'year_reference' => (int) $startDate->format('Y'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_taken' => $daysTaken,
            'status' => $this->faker->randomElement(['approved', 'pending', 'rejected']),
            'approved_by' => User::factory(),
        ];
    }

    /**
     * Férias aprovadas
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Férias aguardando aprovação
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Férias rejeitadas
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }
}
