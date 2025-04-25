<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meal extends Model
{
    protected $table = 'meals';

    protected $fillable = [
        'day_of_week',
        'meal_type',
        'title',
        'price',
    ];

    /**
     * @return HasMany
     */
    public function reservationsns(): HasMany
    {
        return $this->hasMany(MealReservation::class);
    }
}
