<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Designation;
use App\Models\Employee;
use Carbon\Carbon;
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
        // Usamos Carbon para garantir consistência nas datas
        $startDate = Carbon::instance($this->faker->dateTimeBetween('-5 years', '-6 months'));

        return [
            'employee_id' => Employee::factory(),
            'designation_id' => Designation::factory(),
            // Ajustado para bater exatamente com o ENUM da tua migração
            'type' => $this->faker->randomElement([
                'permanent',
                'fixed_term',
                'unfixed_term',
                'service_provision',
                'internship',
            ]),
            'salary' => $this->faker->numberBetween(100000, 500000) / 100,
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => null,
        ];
    }

    /**
     * Contract encerrado/finalizado
     */
    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'terminated', // Verifique se no seu DB é 'ended' ou 'terminated'
            'end_date' => now()->subMonths($this->faker->numberBetween(1, 24)),
        ]);
    }

    /**
     * Contract a Termo Certo (Substitui o antigo 'temporary')
     */
    public function fixedTerm(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fixed_term',
            'status' => 'active',
            'end_date' => now()->addMonths($this->faker->numberBetween(6, 24)),
        ]);
    }

    /**
     * Contract de Estágio
     */
    public function internship(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'internship',
            'status' => 'active',
            'end_date' => now()->addMonths(9), // Estágios IEFP costumam ser 9 meses
        ]);
    }
}
