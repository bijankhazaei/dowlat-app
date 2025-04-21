<?php

namespace App\Http\Resources;

use App\Services\JourneyLimitations\Facade\JourneyLimitations;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'total_price' => $this->total_price,
            'items' => $this->items->map(function ($item) use ($request) {
                return [
                    'id' => $item->id,
                    'journey_id' => $item->journey_id,
                    'journey' => new JourneyResource($item->journey),
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'extra' => collect($item->journey->extra ?? [])->where('type', 'price')->whereIn('uid', $item->extra ?? [])->values()->toArray(),
                    'extra_price' => $item->extra_price,
                    'limitations' => JourneyLimitations::canBeAddedToCart($item->journey, $request->user()->id)
                ];
            }),
        ];
    }
}
