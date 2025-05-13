<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealReservationItem extends Model
{
    protected $table = 'meal_reservation_items';

    protected $fillable = [
        'meal_reservation_id',
        'meal_id',
        'reservation_date',
        'price',
    ];
}
