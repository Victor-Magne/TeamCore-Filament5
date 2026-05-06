<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $countryCode = strtoupper(Str::random(2));

        while (Country::query()->where('code', $countryCode)->exists()) {
            $countryCode = strtoupper(Str::random(2));
        }

        return [
            'name' => $this->faker->country(),
            'code' => $countryCode,
            'phonecode' => $this->faker->numberBetween(1, 999),
        ];
    }
}
