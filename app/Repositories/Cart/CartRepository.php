<?php

namespace App\Repositories\Cart;

use App\Contracts\Enums\ECartStates;
use App\Models\Cart;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

/**
 * @extends parent<Cart>
 */
class CartRepository extends BaseRepository
{
    private const MODEL = Cart::class;
    public function __construct()
    {
        parent::__construct(CartRepository::MODEL);
    }
    protected static function instantiate(): static
    {
        return new CartRepository();
    }

    /**
     * @param $cartId
     * @param $data
     * @return mixed
     */
    protected function addItem(Cart|int $cart, $data): Cart
    {
        if (is_integer($cart)) {
            $cart = $this->findOrFail($cart);
        }
        $ci = $cart->items()->where('journey_id', $data['journey_id'])->first();
        if ($ci) {
            $ci->quantity += $data['quantity'];
            $ci->save();
        } else {
            $cart->items()->create($data);
        }
        $cart->total_price = $cart->items()->sum(DB::raw('quantity * (price + extra_price)'));
        $cart->save();
        return $cart;
    }

    protected function setExtras(Cart|int $cart, $journeyId, $data): Cart
    {
        if (is_integer($cart)) {
            $cart = $this->findOrFail($cart);
        }
        $cart->items()->where(['journey_id' => $journeyId])->update($data);
        $cart->total_price = $cart->items()->sum(DB::raw('quantity * (price + extra_price)'));
        $cart->save();
        return $cart;
    }

    protected function reduceCount(Cart|int $cart, $journeyId, $howMany = 1): Cart
    {
        if (is_integer($cart)) {
            $cart = $this->findOrFail($cart);
        }
        $ci = $cart->items()->where(['journey_id' => $journeyId])->first();
        if ($ci && $ci->quantity > 1) {
            $ci->quantity = $ci->quantity - $howMany;
            $ci->save();
        }
        $cart->total_price = $cart->items()->sum(DB::raw('quantity * (price + extra_price)'));
        $cart->save();
        return $cart;
    }

    /**
     * @param $cartId
     * @param $journeyId
     * @return mixed
     */
    protected function removeItem(Cart|int $cart, $journeyId): Cart
    {
        if (is_integer($cart)) {
            $cart = $this->findOrFail($cart);
        }
        $cart->items()->where(['journey_id' => $journeyId])->delete();
        $cart->total_price = $cart->items()->sum(\Illuminate\Support\Facades\DB::raw('quantity * (price + extra_price)'));
        $cart->save();
        return $cart;
    }

    protected function getOpenCart($customerId): Cart
    {
        return $this->firstOrCreate([
            'customer_id' => $customerId,
            'status' => ECartStates::Open
        ]);
    }
}
