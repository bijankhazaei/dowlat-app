<?php
namespace App\Services\JourneyLimitations;

use App\Contracts\Enums\EJourneyTypes;
use App\Models\Journey;
use App\Repositories\Cart\CartRepository;
use App\Repositories\Journey\JourneyRepository;
use App\Repositories\Order\OrderRepository;
use App\Services\JourneyLimitations\Contracts\JourneyLimitation;
use Carbon\Carbon;

class JourneyLimitationsService
{
    /**
     * Maximum number of iterations for dependency chain resolution.
     *
     * @var int
     */
    protected int $maxDependencyIterations = 10;

    /**
     * Compute the remaining active days and any additional days (from cart items)
     * for each journey for the given customer.
     *
     * @param int $customerId
     * @return array Associative array keyed by journey ID with keys:
     *               - remainingDays: int
     *               - remainingInCart: int
     */
    public function getJourneysRemainingDays(int $customerId, $ignoredCartJourneys = []): array
    {
        // TODO: Should memoize the result
        // $cacheKey = "customer-{$customerId}-journeys-remaining-days";
        // $result = $this->cache->get($cacheKey);
        // if ($result !== null) {
        //     return $result;
        // }

        $cart = CartRepository::getOpenCart($customerId);
        $activeOrderJourneys = OrderRepository::getActiveOrderJourneys($customerId);

        // Group active order journeys by journey_id and obtain the maximum end_date for each.
        $activeJourneys = collect($activeOrderJourneys)
            ->groupBy('journey_id')
            ->map(function ($orders) {
                return collect($orders)->max('end_date');
            });

        // Index cart items by journey_id for quick lookup.
        $cartItems = collect($cart->items)->whereNotIn('journey_id', $ignoredCartJourneys)->keyBy('journey_id');

        $now = Carbon::now();
        $result = [];

        $journeys = JourneyRepository::all();
        foreach ($journeys as $journey) {
            $result[$journey->id] = [
                'remainingDays' => 0,
                'remainingInCart' => 0,
            ];

            $maxActiveDate = isset($activeJourneys[$journey->id])
                ? Carbon::parse($activeJourneys[$journey->id])
                : Carbon::now();

            // If there is a matching cart item, extend the active date.
            if ($cartItems->has($journey->id)) {
                $cartItem = $cartItems->get($journey->id);
                $additionalDays = $journey->re_buy_interval * $cartItem->quantity;
                $maxActiveDate->addDays($additionalDays);
                $result[$journey->id]['remainingInCart'] = $additionalDays;
            }

            $result[$journey->id]['remainingDays'] = (int) $now->diffInDays($maxActiveDate);
        }

        return $result;
    }

    /**
     * Check whether the given journey can be added to the cart for the customer.
     * For journeys of type "Once", it returns an error if already active.
     * For journeys with dependencies, it traverses the dependency chain to determine
     * the maximum number of times the journey can be added.
     *
     * @param Journey $journey
     * @param int     $customerId
     * @return JourneyLimitation
     */
    public function canBeAddedToCart(Journey $journey, int $customerId, $ignoredCartJourneys = []): JourneyLimitation
    {
        $journeysRemaining = $this->getJourneysRemainingDays($customerId, $ignoredCartJourneys);

        // "Once" journeys can not be bought twice during active period.
        if ($journey->type === EJourneyTypes::Once) {
            $journeyRemaining = $journeysRemaining[$journey->id] ?? ['remainingDays' => 0, 'remainingInCart' => 0];

            if ($journeyRemaining['remainingDays'] > 0) {
                $message = $journeyRemaining['remainingInCart']
                    ? 'شما تنها مجازید در یک بازه زمانی یک بار این سرویس را دریافت نمائید.'
                    : 'این سرویس در حال حاظر برای شما فعال است.';
                return new JourneyLimitation(0, $message);
            }
        }

        $maxDays = null;
        $dependency = $journey->dependency;
        $iterations = 0;
        while ($dependency && $iterations++ < $this->maxDependencyIterations) {
            $dependencyRemaining = $journeysRemaining[$dependency->id] ?? ['remainingDays' => 0, 'remainingInCart' => 0];

            if ($dependencyRemaining['remainingDays'] > 0) {
                $maxDays = is_null($maxDays) ? $dependencyRemaining['remainingDays'] : min($maxDays, $dependencyRemaining['remainingDays']);
            } else {
                if ($dependency->price > 0) {
                    return new JourneyLimitation(
                        0,
                        'دریافت این سرویس وابسته به سرویس ' . $dependency->name . ' است. لطفا ابتدا آن را به سبد خرید خود بیافزایید.',
                        $dependency
                    );
                } else {
                    $maxDays = is_null($maxDays) ? $dependency->re_buy_interval : min($maxDays, $dependency->re_buy_interval);
                }
            }

            $dependency = $dependency->dependency;
        }

        // Reduce the days occupied by currently active items of the same journey in orders and cart
        $maxCount = null;
        if (!is_null($maxDays)) {
            $journeyRemaining = $journeysRemaining[$journey->id] ?? ['remainingDays' => 0, 'remainingInCart' => 0];
            $maxDays = $maxDays - $journeyRemaining['remainingDays'];
            if ($maxDays < 0) {
                return new JourneyLimitation(
                    0,
                    'با توجه به وابستگی‌های این سرویس، در حال حاظر شما مجاز به خریداری این سرویس نیستید. لطفا پس از پایان دوره مجددا اقدام فرمایید..',
                );
            }
            $maxCount = (int) ceil($maxDays / $journey->interval);
        }

        if ($journey->type === EJourneyTypes::Once && $maxCount !== 0) {
            $maxCount = 1;
        }

        return new JourneyLimitation($maxCount, null);
    }
}
