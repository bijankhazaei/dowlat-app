<?php

namespace App\Http\Resources;

use App\Services\JourneyLimitations\Facade\JourneyLimitations;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DefaultOrderJourneyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $res = [
            'id' => $this->id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'slug' => $this->journey->slug,
            'name' => $this->journey->name,
            'journey_id' => $this->journey->id,
            'extra' => $this->extra,
        ];

        return $res;
    }
}
