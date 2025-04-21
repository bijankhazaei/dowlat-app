<?php

namespace App\Repositories\Order;

use App\Contracts\Enums\EOrderStates;
use App\Contracts\Enums\JourneySlug;
use App\Models\Journey;
use App\Models\OJLifeStyleScore;
use App\Models\OJLongevityScore;
use App\Models\Order;
use App\Models\OrderJourney;
use App\Repositories\BaseRepository;
use App\Repositories\Journey\JourneyRepository;
use Illuminate\Support\Facades\URL;

/**
 * @extends parent<Order>
 */
class OrderRepository extends BaseRepository
{
    private const MODEL = Order::class;
    public function __construct()
    {
        parent::__construct(OrderRepository::MODEL);
    }
    protected static function instantiate(): static
    {
        return new OrderRepository();
    }

    protected function getActiveOrderJourneys($customerId, $query = false)
    {
        $q = OrderJourney::with('journey')
            ->whereHas(
                'order',
                function ($subQuery) use ($customerId) {
                    return $subQuery
                        ->where('customer_id', $customerId)
                        ->whereIn('status', [EOrderStates::Pending, EOrderStates::Processing, EOrderStates::Completed]);
                }
            )
            ->where('end_date', '>=', now());
        return $query ? $q : $q->get();
    }

    protected function appendJourney(Order|int $order, Journey|int $journey, $quantity = 1, $extra = [], $authUserId = null, $orderReturnUrl = null)
    {
        if (is_integer($order)) {
            $order = $this->findOrFail($order);
        }
        if (is_integer($journey)) {
            $journey = JourneyRepository::findOrFail($journey);
        }

        switch ($journey->slug) {
            case JourneySlug::LIFE_STYLE_SCORE:
                $this->appendLifeStyleScoreJourney($order, $journey, $extra, $authUserId, $orderReturnUrl);
                break;

            case JourneySlug::LONGEVITY_SCORE:
                $this->appendLongevityScoreJourney($order, $journey, $extra, $authUserId);
                break;

            default: {
                $start = now();
                for ($i = 0; $i < $quantity; $i++) {
                    $order->journeys()->create([
                        'journey_id' => $journey->id,
                        'start_date' => $start,
                        'end_date' => $start->addDays($journey->interval),
                        'extra' => $extra
                    ]);
                    $start = $start->addDays($journey->interval);
                }
            }
        }

        return $order;
    }

    protected function appendLifeStyleScoreJourney(Order $order, Journey $journey, $extra = [], $authUserId = null, $orderReturnUrl = null)
    {
        $free = $order->total_price <= 0 && !Order::where('customer_id', $order->customer_id)->where('total_price', '>', 0)->exists();
        $oj = $order->journeys()->create([
            'journey_id' => $journey->id,
            'start_date' => now(),
            'end_date' => now()->addDays($journey->re_buy_interval),
            'extra' => $extra
        ]);

        OJLifeStyleScore::create([
            'order_journey_id' => $oj->id,
            'metadata' => [
                'free' => $free,
                'return_url' => $orderReturnUrl
            ],
        ]);
    }

    protected function appendLongevityScoreJourney(Order $order, Journey $journey, $extra = [], $authUserId = null)
    {
        $relatedLifeStyleOrderJourney = $this
            ->getActiveOrderJourneys($authUserId ?? $order->customer_id, true)
            ->whereHas(
                'journey',
                function ($subQuery) {
                    return $subQuery->where('slug', JourneySlug::LIFE_STYLE_SCORE);
                }
            )
            ->firstOrFail();

        $oj = $order->journeys()->create([
            'journey_id' => $journey->id,
            'start_date' => now(),
            'end_date' => now()->addDays($journey->re_buy_interval),
            'extra' => $extra
        ]);

        $sampling = collect($extra)->where('type', 'price')->count() > 0;

        OJLongevityScore::create([
            'order_journey_id' => $oj->id,
            'related_life_style_order_journey_id' => $relatedLifeStyleOrderJourney->id,
            'metadata' => [
                'sampling' => $sampling,
                'book_time' => null,
                'book_address' => $order->address,
            ],
        ]);
    }
}
