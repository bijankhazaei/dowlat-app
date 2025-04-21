<?php

namespace App\Models;

use App\Contracts\Enums\EOrderStates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'status',
        'total_price',
        'cart_id',
        'address',
        'postal_code'
    ];

    protected $casts = [
        'status' => EOrderStates::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function journeys(): HasMany
    {
        return $this->hasMany(OrderJourney::class);
    }
}
