<?php

namespace Tests\Feature;

use App\Contracts\Enums\EPaymentStates;
use App\Contracts\Enums\ETransactionStates;
use App\Models\Meal;
use App\Models\MealReservation;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MealBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_meal_booking_flow()
    {
        // Create user
        $user = User::factory()->create();
        
        // Create meal
        $meal = Meal::create([
            'name' => 'Test Meal',
            'price' => 50000,
            'day_of_week' => 'monday',
            'week_number' => 1
        ]);

        // Create meal reservation
        $reservation = MealReservation::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'price' => 50000
        ]);

        // Create payment
        $payment = Payment::create([
            'meal_reservation_id' => $reservation->id,
            'amount' => 50000,
            'summary' => 'Test reservation',
            'status' => EPaymentStates::Unpaid
        ]);

        // Create transaction
        $transaction = Transaction::create([
            'payment_id' => $payment->id,
            'status' => ETransactionStates::Pending,
            'amount' => 50000,
            'provider' => 'zibal',
            'authority' => 'test_123'
        ]);

        // Simulate successful payment
        $transaction->update([
            'status' => ETransactionStates::Success,
            'reference' => 'ref_123',
            'validated_at' => now()
        ]);

        $payment->update(['status' => EPaymentStates::Paid]);
        $reservation->update(['status' => 'completed']);

        // Assert final states
        $this->assertEquals('completed', $reservation->fresh()->status);
        $this->assertEquals(EPaymentStates::Paid, $payment->fresh()->status);
        $this->assertEquals(ETransactionStates::Success, $transaction->fresh()->status);
    }

    public function test_failed_meal_booking_flow()
    {
        // Create user
        $user = User::factory()->create();

        // Create meal reservation
        $reservation = MealReservation::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'price' => 50000
        ]);

        // Create payment
        $payment = Payment::create([
            'meal_reservation_id' => $reservation->id,
            'amount' => 50000,
            'summary' => 'Test reservation',
            'status' => EPaymentStates::Unpaid
        ]);

        // Create transaction
        $transaction = Transaction::create([
            'payment_id' => $payment->id,
            'status' => ETransactionStates::Pending,
            'amount' => 50000,
            'provider' => 'zibal',
            'authority' => 'test_456'
        ]);

        // Simulate failed payment
        $transaction->update([
            'status' => ETransactionStates::Error,
            'status_message' => 'Payment failed',
            'validated_at' => now()
        ]);

        $reservation->update(['status' => 'cancelled']);

        // Assert final states
        $this->assertEquals('cancelled', $reservation->fresh()->status);
        $this->assertEquals(EPaymentStates::Unpaid, $payment->fresh()->status);
        $this->assertEquals(ETransactionStates::Error, $transaction->fresh()->status);
    }
}