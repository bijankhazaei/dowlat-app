<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $this->faker = \Faker\Factory::create('fa_IR');

        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'mobile' => $this->faker->phoneNumber,
            'national_code' => $this->faker->unique()->nationalCode,
            'password' => Hash::make('secret'),
            'is_registered' => $this->faker->boolean,
            'address' => $this->faker->address,
            'postal_code' => $this->faker->postcode,
            'email' => $this->faker->unique()->safeEmail,
            'gender' => $this->faker->randomElement(['male', 'female']),
            'birthdate' => $this->faker->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
        ];
    }
}
