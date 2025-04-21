<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Enums\ECartStates;
use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Repositories\Cart\CartRepository;
use App\Repositories\Journey\JourneyRepository;
use App\Services\JourneyLimitations\Facade\JourneyLimitations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Cart",
 *     description="Cart management endpoints"
 * )
 */
class CartController extends Controller
{
    /**
     * Get cart details.
     *
     * @OA\Get(
     *     path="/api/cart",
     *     summary="Get current open cart details",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cart details",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cart not found"
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function details(Request $request): JsonResponse
    {
        $cart = CartRepository::getOpenCart($request->user()->id);
        return response()->apiResult(new CartResource($cart));
    }

    /**
     * Add an item to the cart.
     *
     * @OA\Post(
     *     path="/api/cart/add-item",
     *     summary="Add item to cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"journey_id", "interval"},
     *             @OA\Property(property="journey_id", type="integer", example=1, description="Journey ID"),
     *             @OA\Property(property="interval", type="string", example="weekly", description="Interval for journey"),
     *             @OA\Property(property="cart_id", type="integer", example=1, description="Cart ID (optional if cart is created automatically)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item added to cart successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addItem(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'journey_id' => 'required|integer|exists:journeys,id',
                'quantity' => 'nullable|integer|min:1',
                'extra' => 'nullable|array',
                'extra.*' => 'required|string'
            ]);

            $journey = JourneyRepository::find($data['journey_id']);
            $data['price'] = $journey->price;
            if (empty($data['quantity'])) {
                $data['quantity'] = 1;
            }

            $extras = collect($journey->extra ?? [])->where('type', 'price')->keyBy('uid');
            $extraPrice = 0;
            if (!empty($data['extra'])) {
                foreach ($data['extra'] as $extraUid) {
                    if (!$extras->has($extraUid)) {
                        return response()->apiResult(statusCode: 422, messages: [
                            'افزودنی مد نظر مربوط به سرویس انتخابی نیست'
                        ]);
                    }
                    $extraPrice += $extras->get($extraUid)['value'];
                }
            }
            $data['extra_price'] = $extraPrice;

            $limitations = JourneyLimitations::canBeAddedToCart($journey, $request->user()->id);
            if ($limitations->maxCount < $data['quantity']) {
                return response()->apiResult(statusCode: 422, messages: [
                    $limitations->description ?? "شما می‌توانید حداکثر {$limitations->maxCount} عدد به سبد خرید خود اضافه کنید"
                ]);
            }

            $cart = CartRepository::getOpenCart($request->user()->id);

            $journeysRemaining = JourneyLimitations::getJourneysRemainingDays($request->user()->id);
            $messages = [];
            $dep = $journey->dependency;

            while ($dep) {
                if ($dep->price <= 0) {
                    if ($journeysRemaining[$dep->id]['remainingDays'] <= 0) {
                        $depLimitations = JourneyLimitations::canBeAddedToCart($dep, $request->user()->id);
                        if ($depLimitations->maxCount > 0) {
                            CartRepository::addItem($cart, [
                                'journey_id' => $dep->id,
                                'quantity' => 1,
                                'price' => $dep->price
                            ]);
                            $messages[] = 'سرویس ' . $dep->name . ' نیز به صورت خودکار به سبد خرید شما افزوده شد';
                        }
                    }

                    $dep = $dep->dependency;
                } else {
                    break;
                }
            }

            CartRepository::addItem($cart, $data);

            return response()->apiResult(new CartResource($cart), messages: $messages);
        } catch (ValidationException $e) {
            return response()->apiResult(statusCode: 422, messages: $e->errors());
        } catch (\Exception $e) {
            return response()->apiResult(
                statusCode: 500,
                messages: [
                    'Failed to add item to cart'
                ],
                metadata: $e->getMessage()
            );
        }
    }

    public function setExtras(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'journey_id' => 'required|integer|exists:journeys,id',
                'extra' => 'nullable|array',
                'extra.*' => 'required|string'
            ]);

            $journey = JourneyRepository::find($data['journey_id']);
            $extras = collect($journey->extra ?? [])->where('type', 'price')->keyBy('uid');

            if (empty($data['extra'])) {
                $data['extra'] = [];
            }

            $extraPrice = 0;
            if (!empty($data['extra'])) {
                foreach ($data['extra'] as $extraUid) {
                    if (!$extras->has($extraUid)) {
                        return response()->apiResult(statusCode: 422, messages: [
                            'افزودنی مد نظر مربوط به سرویس انتخابی نیست'
                        ]);
                    }
                    $extraPrice += $extras->get($extraUid)['value'];
                }
            }

            $data['extra_price'] = $extraPrice;

            $cart = CartRepository::getOpenCart($request->user()->id);
            if (!$cart->items()->where('journey_id', $journey->id)->exists()) {
                return response()->apiResult(statusCode: 422, messages: [
                    'سرویس مد نظر در سبد خرید شما وجود ندارد'
                ]);
            }

            CartRepository::setExtras($cart, $journey->id, $data);

            return response()->apiResult(new CartResource($cart));
        } catch (ValidationException $e) {
            return response()->apiResult(statusCode: 422, messages: $e->errors());
        } catch (\Exception $e) {
            return response()->apiResult(
                statusCode: 500,
                messages: [
                    'Failed to add item to cart'
                ],
                metadata: $e->getMessage()
            );
        }
    }

    /**
     * Remove an item from the cart.
     *
     * @OA\Delete(
     *     path="/api/cart/reduce",
     *     summary="Remove item from cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cart_id", "item_id"},
     *             @OA\Property(property="cart_id", type="integer", example=1, description="Cart ID"),
     *             @OA\Property(property="item_id", type="integer", example=10, description="Item ID in the cart")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item removed from cart",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to remove item"
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reduceCount(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'journey_id' => 'required|integer|exists:journeys,id',
            ]);

            $cart = CartRepository::getOpenCart($request->user()->id);
            CartRepository::reduceCount($cart, $data['journey_id']);

            // TODO: handle scenarios where a reduces quantity of a journey in the cart invalidates some of existing journeys in the cart due to dependency limitations
            // $invalidDependants = $this->findInvalidDependants($request, $cart, $data['journey_id'], []);
            // $messages = [];
            // if (count($invalidDependants) > 0) {
            //     $cart->unsetRelation('items');
            // }
            // foreach ($invalidDependants as $item) {
            //     if ($item->remove) {
            //         CartRepository::removeItem($cart, $item->dependant->id);
            //         $messages[] = 'سرویس ' . $item->dependant->name . ' نیز به علت وابستگی به این سرویس، از سبد خرید شما حذف شد';
            //     } else {
            //         CartRepository::reduceCount($cart, $item->dependant->id, $item->reduce);
            //         $messages[] = 'تعداد سرویس ' . $item->dependant->name . ' نیز به علت وابستگی به این سرویس، به ' . $item->limitation->maxCount . ' کاهش یافت';
            //     }
            // }

            return response()->apiResult(new CartResource($cart));
        } catch (ValidationException $e) {
            return response()->apiResult(statusCode: 422, messages: $e->errors());
        } catch (\Exception $e) {
            return response()->apiResult(
                statusCode: 500,
                messages: [
                    'Failed to remove item from cart'
                ],
                metadata: $e->getMessage()
            );
        }
    }

    /**
     * Remove an item from the cart.
     *
     * @OA\Delete(
     *     path="/api/cart/remove-item",
     *     summary="Remove item from cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cart_id", "item_id"},
     *             @OA\Property(property="cart_id", type="integer", example=1, description="Cart ID"),
     *             @OA\Property(property="item_id", type="integer", example=10, description="Item ID in the cart")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item removed from cart",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to remove item"
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function removeItem(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'journey_id' => 'required|integer|exists:journeys,id',
            ]);

            $cart = CartRepository::getOpenCart($request->user()->id);
            CartRepository::removeItem($cart, $data['journey_id']);

            $invalidDependants = $this->findInvalidDependants($request, $cart, $data['journey_id'], [$data['journey_id']]);
            $messages = [];
            if (count($invalidDependants) > 0) {
                $cart->unsetRelation('items');
            }
            foreach ($invalidDependants as $item) {
                if ($item->remove) {
                    CartRepository::removeItem($cart, $item->dependant->id);
                    $messages[] = 'سرویس ' . $item->dependant->name . ' نیز به علت وابستگی به این سرویس، از سبد خرید شما حذف شد';
                } else {
                    CartRepository::reduceCount($cart, $item->dependant->id, $item->reduce);
                    $messages[] = 'تعداد سرویس ' . $item->dependant->name . ' نیز به علت وابستگی به این سرویس، به ' . $item->limitation->maxCount . ' کاهش یافت';
                }
            }

            return response()->apiResult(new CartResource($cart), messages: $messages);
        } catch (ValidationException $e) {
            return response()->apiResult(statusCode: 422, messages: $e->errors());
        } catch (\Exception $e) {
            return response()->apiResult(
                statusCode: 500,
                messages: [
                    'Failed to remove item from cart'
                ],
                metadata: $e->getMessage()
            );
        }
    }

    private function findInvalidDependants(Request $request, Cart $cart, $journey_id, $ignored = [])
    {
        $cartItems = $cart->items;
        $dependants = JourneyRepository::query()
            ->where('dependency_id', $journey_id)
            ->whereIn('id', $cartItems->pluck('journey_id')->values()->toArray())
            ->get();
        $result = [];
        foreach ($dependants as $dependant) {
            $limitations = JourneyLimitations::canBeAddedToCart($dependant, $request->user()->id, $ignored);
            $ci = $cartItems->where('journey_id', $dependant->id)->first();
            if ($ci->quantity > $limitations->maxCount) {
                $result[] = (object) [
                    'reduce' => $ci->quantity - $limitations->maxCount,
                    'remove' => $limitations->maxCount <= 0,
                    'limitation' => $limitations,
                    'dependant' => $dependant
                ];
            }
            array_push($result, ...$this->findInvalidDependants($request, $cart, $dependant->id, [...$ignored, $dependant->id]));
        }
        return $result;
    }

    public function clear(Request $request): JsonResponse
    {
        try {
            $cart = CartRepository::getOpenCart($request->user()->id);
            if ($cart->items()->exists()) {
                $cart->status = ECartStates::Cancelled;
                $cart->save();
            }
            $cart = CartRepository::getOpenCart($request->user()->id);
            return response()->apiResult(new CartResource($cart));
        } catch (\Exception $e) {
            return response()->apiResult(
                statusCode: 500,
                messages: [
                    'Failed to remove item from cart'
                ],
                metadata: $e->getMessage()
            );
        }
    }
}
