<?php

namespace Database\Factories;

use App\Contracts\Enums\EOrderStates;
use App\Models\MealReservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MealReservationFactory extends Factory
{
    protected $model = MealReservation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => EOrderStates::Pending,
            'total_amount' => $this->faker->numberBetween(50000, 500000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}