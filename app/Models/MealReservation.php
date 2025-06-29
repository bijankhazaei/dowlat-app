<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealReservation extends Model
{
    protected $table = 'meal_reservations';

    protected $fillable = [
        'user_id',
        'status',
        'price',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(MealReservationItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
