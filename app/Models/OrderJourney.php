<?php

namespace App\Models;

use App\Contracts\Enums\JourneySlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property mixed $journey
 */
class OrderJourney extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_journeys';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'journey_id',
        'start_date',
        'end_date',
        'extra'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'extra' => 'array'
    ];

    /**
     * Get the order associated with the order journey.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the journey associated with the order journey.
     */
    public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }

    public function getMetaAttribute()
    {
        if ($this->journey->slug === JourneySlug::LIFE_STYLE_SCORE) {
            return $this->hasMany(OJLifeStyleScore::class, 'order_journey_id')
                ->orderByDesc('id')
                ->first();
        } elseif ($this->journey->slug === JourneySlug::LONGEVITY_SCORE) {
            return $this->hasMany(OJLongevityScore::class, 'order_journey_id')
                ->orderByDesc('id')
                ->first();
        }
        return null;
    }

    public function lifeStyleScores(): HasMany
    {
        return $this->hasMany(OJLifeStyleScore::class, 'order_journey_id', 'id');
    }

    public function longevityScores(): HasMany
    {
        return $this->hasMany(OJLongevityScore::class, 'order_journey_id', 'id');
    }
}
