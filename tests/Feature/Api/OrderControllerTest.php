<?php

namespace Tests\Feature\Api;

use App\Contracts\Enums\ECartStates;
use App\Contracts\Enums\EOrderStates;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test listing user orders.
     *
     * @return void
     */
    public function test_list_orders()
    {
        $user = User::factory()->create();
        
        // Create some orders for the user
        Order::factory()->count(3)->create([
            'customer_id' => $user->id,
            'status' => EOrderStates::Processing,
        ]);
        
        // Create orders for another user to ensure they don't appear in results
        Order::factory()->count(2)->create();
        
        $response = $this->actingAs($user)
                         ->getJson('/api/orders');
        
        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    /**
     * Test showing a specific order.
     *
     * @return void
     */
    public function test_show_order()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $user->id,
            'status' => EOrderStates::Processing,
        ]);
        
        $response = $this->actingAs($user)
                         ->getJson("/api/orders/{$order->id}");
        
        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $order->id);
    }

    /**
     * Test order cannot be viewed by another user.
     *
     * @return void
     */
    public function test_cannot_view_other_users_order()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $user1->id,
        ]);
        
        $response = $this->actingAs($user2)
                         ->getJson("/api/orders/{$order->id}");
        
        $response->assertStatus(404);
    }

    /**
     * Test submitting a new order.
     *
     * @return void
     */
    public function test_submit_order_with_items()
    {
        $this->markTestIncomplete(
            'This test needs to be implemented with proper cart and journey setup.'
        );
        
        // Test setup would need to:
        // 1. Create a user
        // 2. Create cart with items
        // 3. Call the submitOrder endpoint
        // 4. Assert a new order is created
    }

    /**
     * Test cannot submit order with empty cart.
     *
     * @return void
     */
    public function test_cannot_submit_empty_order()
    {
        $user = User::factory()->create();
        
        // Ensure user has an empty cart
        $cart = Cart::factory()->create([
            'customer_id' => $user->id,
            'status' => ECartStates::Open,
        ]);
        
        $response = $this->actingAs($user)
                         ->postJson('/api/orders', [
                             'return_url' => 'https://example.com/order/{ORDER_ID}'
                         ]);
        
        $response->assertStatus(422)
                 ->assertJsonFragment(['سبد خرید شما خالی است']);
    }

    /**
     * Test canceling an order.
     *
     * @return void
     */
    public function test_cancel_pending_order()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $user->id,
            'status' => EOrderStates::Pending,
        ]);
        
        $response = $this->actingAs($user)
                         ->postJson("/api/orders/{$order->id}/cancel");
        
        $response->assertStatus(200);
        $this->assertEquals(EOrderStates::Canceled, $order->fresh()->status);
    }

    /**
     * Test cannot cancel non-pending order.
     *
     * @return void
     */
    public function test_cannot_cancel_non_pending_order()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $user->id,
            'status' => EOrderStates::Processing,
        ]);
        
        $response = $this->actingAs($user)
                         ->postJson("/api/orders/{$order->id}/cancel");
        
        $response->assertStatus(409);
        $this->assertEquals(EOrderStates::Processing, $order->fresh()->status);
    }

    /**
     * Test retry payment on an order.
     *
     * @return void
     */
    public function test_retry_payment()
    {
        $this->markTestIncomplete(
            'This test needs to be implemented with proper payment setup.'
        );
        
        // Test setup would need to:
        // 1. Create a user
        // 2. Create an order with payment
        // 3. Call the retryPayment endpoint
        // 4. Assert a new transaction is created
    }
}