<?php

namespace App\Models;

use App\Contracts\Enums\ETransactionFeeTypes;
use App\Contracts\Enums\ETransactionStates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Shetabit\Multipay\Invoice;

class Transaction extends Model
{
    protected $fillable = [
        'payment_id',
        'status',
        'status_message',
        'amount',
        'provider',
        'authority',
        'gateway_url',
        'reference',
        'provider_status',
        'requested_at',
        'validated_at',
        'fee_type',
        'metadata'
    ];

    protected $casts = [
        'status' => ETransactionStates::class,
        'requested_at' => 'datetime',
        'validated_at' => 'datetime',
        'fee_type' => ETransactionFeeTypes::class,
        'metadata' => 'array'
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
