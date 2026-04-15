<?php

namespace Database\Factories;

use App\Models\AttendanceLog;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceLog>
 */
class AttendanceLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 1. Transformamos o DateTime do Faker em Carbon imediatamente
        $timeIn = Carbon::instance($this->faker->dateTimeBetween('-30 days', 'now'))->setTime(
            $this->faker->numberBetween(7, 9),
            $this->faker->numberBetween(0, 59),
            0
        );

        // 2. Usamos o método ->copy() do Carbon para criar novas instâncias baseadas na entrada
        // Isso evita que o erro "Undefined method" apareça no seu editor
        $lunchStart = $timeIn->copy()->setTime(12, $this->faker->numberBetween(0, 30), 0);
        $lunchEnd = $lunchStart->copy()->addMinutes($this->faker->numberBetween(30, 90));
        $timeOut = $timeIn->copy()->setTime($this->faker->numberBetween(17, 19), $this->faker->numberBetween(0, 59), 0);

        return [
            'employee_id' => Employee::factory(),
            'time_in' => $timeIn,
            'lunch_break_start' => $lunchStart,
            'lunch_break_end' => $lunchEnd,
            'time_out' => $timeOut,
            'total_minutes' => 0, // Pode calcular isto dinamicamente se desejar
            'metadata' => json_encode(['device' => 'biometric', 'location' => 'main_office']),
            'notes' => null,
        ];
    }

    /**
     * Registo sem almoço (trabalhou o dia todo)
     */
    public function withoutLunch(): static
    {
        return $this->state(fn (array $attributes) => [
            'lunch_break_start' => null,
            'lunch_break_end' => null,
        ]);
    }

    /**
     * Registo com horas extras
     */
    public function withExtraHours(): static
    {
        return $this->state(fn (array $attributes) => [
            // Agora que o time_in já é Carbon na definição, o parse é opcional mas seguro
            'time_out' => Carbon::parse($attributes['time_in'])->copy()->setTime(21, $this->faker->numberBetween(0, 59), 0),
        ]);
    }
}
