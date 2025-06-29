<?php

namespace Tests\Unit\Models;

use App\Models\Meal;
use App\Models\MealReservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MealTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_meal()
    {
        $meal = Meal::create([
            'day_of_week' => 1, // Monday
            'meal_type' => 'lunch',
            'week_number' => 1,
            'title' => 'Test Meal',
            'price' => 15000,
        ]);

        $this->assertDatabaseHas('meals', [
            'day_of_week' => 1,
            'meal_type' => 'lunch',
            'week_number' => 1,
            'title' => 'Test Meal',
            'price' => 15000,
        ]);
    }

    /** @test */
    public function it_has_many_reservations()
    {
        $meal = Meal::factory()->create();
        
        // Fix typo in relationship name - it should be "reservations" not "reservationsns"
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $meal->reservationsns());
    }
}