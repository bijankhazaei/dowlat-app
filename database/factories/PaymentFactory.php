<?php

namespace Database\Factories;

use App\Contracts\Enums\EPaymentStates;
use App\Models\Payment;
use App\Models\MealReservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'meal_reservation_id' => MealReservation::factory(),
            'status' => EPaymentStates::Unpaid,
            'amount' => $this->faker->numberBetween(50000, 500000),
            'summary' => $this->faker->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}