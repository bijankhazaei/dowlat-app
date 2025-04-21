<?php

namespace App\Repositories\Payment;

use App\Contracts\Enums\ETransactionStates;
use App\Models\Payment;
use App\Repositories\BaseRepository;

/**
 * @extends parent<Payment>
 */
class PaymentRepository extends BaseRepository
{
    private const MODEL = Payment::class;
    public function __construct()
    {
        parent::__construct(PaymentRepository::MODEL);
    }
    protected static function instantiate(): static
    {
        return new PaymentRepository();
    }

    protected function expireOldTransactions(Payment $payment)
    {
        $payment->transactions()
            ->where('requested_at', '<', now()->subMinutes(5))
            ->whereIn('status', [
                ETransactionStates::Init,
                ETransactionStates::Pending,
            ])
            ->update([
                'status' => ETransactionStates::Expired
            ]);
    }
}
