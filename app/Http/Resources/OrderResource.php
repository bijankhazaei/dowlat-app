<?php

namespace App\Http\Resources;

use App\Contracts\Enums\JourneySlug;
use App\Services\JourneyLimitations\Facade\JourneyLimitations;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function __construct($resource, protected $detailed = false)
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
        $res = [
            'id' => $this->id,
            'status' => $this->status,
            'total_price' => $this->total_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        if ($this->detailed) {
            $res['items'] = $this->journeys->map(function ($oj) {
                return match ($oj->journey->slug) {
                    JourneySlug::LIFE_STYLE_SCORE => new OJLifeStyleScoreResource($oj),
                    JourneySlug::LONGEVITY_SCORE => new OJLongevityScoreResource($oj),
                    default => new DefaultOrderJourneyResource($oj)
                };
            });
        } else {
            $res['items'] = $this->journeys->map(fn($oj) => [
                'id' => $oj->journey_id,
                'name' => $oj->journey->name,
            ])->unique('id');
        }

        return $res;
    }
}
