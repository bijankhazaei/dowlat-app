<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'journey_id',
        'quantity',
        'price',
        'extra',
        'extra_price',
    ];
    protected $casts = [
        'extra' => 'array'
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }
}
