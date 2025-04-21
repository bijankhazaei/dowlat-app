<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Order Journey (OJ) Longevity Score
 */
class OJLongevityScore extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'order_journeys_longevity_scores';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_journey_id',
        'related_life_style_order_journey_id',
        'metadata',
        'requirements',
        'parsed_data',
        'result',
        'error',
        'sampled_at',
        'prepared_at',
        'approved_at',
        'rejected_at',
        'rejected_due',
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
        'sampled_at' => 'datetime',
        'prepared_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'parsed_at' => 'datetime',
        'enqueued_at' => 'datetime',
        'calculated_at' => 'datetime',
    ];

    /**
     * Get the order journey associated with the longevity score.
     */
    public function orderJourney()
    {
        return $this->belongsTo(OrderJourney::class, 'order_journey_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('blood_analysis_lab_results')
            ->useDisk('minio-private');

        $this->addMediaCollection('blood_analysis_lab_results_merged')
            ->singleFile()
            ->useDisk('minio-private');
    }

    /**
     * Get the related lifestyle order journey associated with this longevity score.
     */
    public function relatedLifeStyleOrderJourney()
    {
        return $this->belongsTo(OrderJourney::class, 'related_life_style_order_journey_id');
    }
}
