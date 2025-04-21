<?php

namespace App\Models;

use App\Contracts\Enums\EGenders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @method static firstOrCreate(array $array, array $array1)
 * @method static create(array $array)
 * @property mixed $first_name
 * @property mixed $last_name
 */
class Customer extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, InteractsWithMedia;

    protected $table = 'customers';

    protected $fillable = [
        'first_name',
        'last_name',
        'mobile',
        'national_code',
        'password',
        'is_registered',
        'address',
        'postal_code',
        'email',
        'gender',
        'birthdate'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_registered' => 'boolean',
        'birthdate' => 'date',
        'gender' => EGenders::class
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->useDisk('minio-public')
            ->singleFile();
    }

    /**
     * @return string
     */
    public function getNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * @return string
     */
    public function getAvatarUrlAttribute(): string
    {
        $media = $this->media()->where('collection_name', 'avatar')->first();

        if ($media) {
            return $media->getUrl();
        }

        return 'http://localhost:8000/images/default-avatar.jpg';
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
}
