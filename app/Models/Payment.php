<?php

namespace App\Models;

use App\Contracts\Enums\EPaymentStates;
use App\Contracts\Enums\ETransactionStates;
use App\Services\Payment\PaymentService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property mixed $active_transaction
 * @property mixed $id
 * @property mixed $amount
 */
class Payment extends Model
{
    protected $fillable = [
        'meal_reservation_id',
        'status',
        'amount',
        'summary',
    ];

    protected $casts = [
        'status' => EPaymentStates::class,
        'amount' => 'decimal:2',
    ];

    public function mealReservation(): BelongsTo
    {
        return $this->belongsTo(MealReservation::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function activeTransaction(): HasOne
    {
        return $this->hasOne(Transaction::class)
            ->where('status', ETransactionStates::Success)
            ->orWhere(function ($query) {
                $query->whereIn('status', [ETransactionStates::Pending, ETransactionStates::Init])
                    ->where(function ($query) {
                        $query->whereNull('requested_at')
                            ->orWhere('requested_at', '>=', now()->subMinutes(5));
                    });
            })
            ->orderByRaw("FIELD(status, ?, ?, ?)", [
                ETransactionStates::Success->value,
                ETransactionStates::Pending->value,
                ETransactionStates::Init->value
            ]);
    }

    public function activeTransactionOrCreate(?array $metadata = null, ?string $provider = null)
    {
        $transaction = $this->active_transaction;
        if (!$transaction) {
            $transaction = Transaction::create([
                'payment_id' => $this->id,
                'status' => ETransactionStates::Init,
                'status_message' => 'درخواست پرداخت ایجاد نگردیده است',
                'amount' => $this->amount,
                'provider' => $provider ?? config('payment.default'),
                'metadata' => $metadata
            ]);
        }
        return $transaction;
    }
}
