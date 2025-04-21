<?php

namespace App\Http\Resources;

use App\Services\JourneyLimitations\Facade\JourneyLimitations;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JourneyResource extends JsonResource
{
    public function __construct($resource, protected $includeCartLimitation = false)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'slug' => $this->slug,
            'price' => $this->price,
            'interval' => $this->interval,
            're_buy_interval' => $this->re_buy_interval,
            'is_active' => $this->is_active,
            'dependency_id' => $this->dependency_id,
            'description' => $this->description,
            'summary' => $this->summary,
            'extra' => $this->extra,
            'images' => $this->getMedia('images')->map(fn($image) => [
                'id' => $image->id,
                'url' => $image->getUrl(),
                'name' => $image->name,
                'order' => $image->order_column,
            ]),
            'cart_limitations' => $this->includeCartLimitation && $request->user()
                ? JourneyLimitations::canBeAddedToCart($this->resource, $request->user()->id)
                : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
