<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'city_id' => City::factory(),
            'unit_id' => Unit::factory(),
            'designation_id' => Designation::factory(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone_number' => $this->faker->phoneNumber(),
            'date_of_birth' => $this->faker->dateTimeBetween('-65 years', '-18 years'),
            'nif' => $this->faker->numerify('###########'),
            'nss' => $this->faker->numerify('###############'),
            'address' => $this->faker->address(),
            'zip_code' => $this->faker->postcode(),
            'date_hired' => $this->faker->dateTimeBetween('-5 years'),
            'date_dismissed' => null,
            'vacation_balance' => 21,
        ];
    }
}
