<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Order Journey (OJ) Life Style Score
 */
class OJLifeStyleScore extends Model
{
    protected $table = 'order_journeys_life_style_scores';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_journey_id',
        'metadata',
        'requirements',
        'parsed_data',
        'result',
        'error',
        'prepared_at',
        'parsed_at',
        'enqueued_at',
        'calculated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
        'requirements' => 'array',
        'parsed_data' => 'array',
        'result' => 'array',
        'prepared_at' => 'datetime',
        'parsed_at' => 'datetime',
        'enqueued_at' => 'datetime',
        'calculated_at' => 'datetime',
    ];

    /**
     * Get the order journey associated with the lifestyle score.
     */
    public function orderJourney(): BelongsTo
    {
        return $this->belongsTo(OrderJourney::class, 'order_journey_id');
    }
}
