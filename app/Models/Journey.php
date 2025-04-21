<?php

namespace App\Models;

use App\Contracts\Enums\EJourneyTypes;
use App\Contracts\Enums\JourneySlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @method static find($id)
 */
class Journey extends Model implements HasMedia
{
    use InteractsWithMedia;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'type',
        'price',
        'interval',
        're_buy_interval',
        'is_active',
        'dependency_id',
        'summary',
        'description',
        'extra',
    ];

    protected $casts = [
        'type' => EJourneyTypes::class,
        'slug' => JourneySlug::class,
        'extra' => 'array'
    ];


    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('minio-public');
    }

    /**
     * @return BelongsTo
     */
    public function dependency(): BelongsTo
    {
        return $this->belongsTo(Journey::class, 'dependency_id', 'id');
    }
}
