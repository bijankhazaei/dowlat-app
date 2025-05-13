<?php

namespace App\Services\Payment;

use App\Contracts\Enums\EOrderStates;
use App\Contracts\Enums\EPaymentStates;
use App\Models\Transaction;
use App\Contracts\Enums\ETransactionStates;
use App\Services\Payment\Facade\Shetabit;
use Illuminate\Support\Facades\URL;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Multipay\Exceptions\PreviouslyVerifiedException;
use Shetabit\Multipay\Exceptions\PurchaseFailedException;
use Shetabit\Multipay\Invoice;
use Exception;
class PaymentService
{
    /**
     * Request a payment and return the gateway URL.
     *
     * @param Transaction $transaction
     * @param $authCustomerId
     * @return string
     * @throws Exception
     */
    public static function request(Transaction &$transaction, $authCustomerId): string
    {
        if ($transaction->status === ETransactionStates::Init) {
            $invoice = (new Invoice())
                ->amount($transaction->amount)
                ->detail('description', $transaction->payment->summary);

             Shetabit::via($transaction->provider)
                ->callbackUrl(route('payment.callback', $transaction->id))
                ->purchase($invoice, function ($driver, $transactionId) use ($transaction) {
                    $transaction->requested_at = now();
                    $transaction->authority = $transactionId;
                    $transaction->gateway_url = config('payment.drivers.' . $transaction->provider . '.apiPaymentUrl') . $transactionId;
                    $transaction->status = ETransactionStates::Pending;
                    $transaction->save();
                });
        }


        throw new \RuntimeException("Transaction is not open to pay.");
    }

    /**
     * Validate a transaction.
     *
     * @param Transaction $transaction
     * @param int $userId
     * @return void
     */
    public static function validate(Transaction &$transaction): void
    {
        if ($transaction->status !== ETransactionStates::Pending) {
            return;
        }

        try {
            $receipt = Shetabit::amount($transaction->amount)
                ->transactionId($transaction->authority)
                ->verify();

            $transaction->status = ETransactionStates::Success;
            $transaction->reference = $receipt->refNumber;
            $transaction->status_message = 'پرداخت با موفقیت انجام شد';
            $transaction->provider_status = 100;
            $transaction->validated_at = now();
            if ($receipt->cardNumber) {
                $m = $transaction->metadata ?? [];
                $m['card_pan'] = $receipt->cardNumber;
                $transaction->metadata = $m;
            }
            $transaction->save();

            if ($transaction->payment->status === EPaymentStates::Unpaid) {
                $transaction->payment->status = EPaymentStates::Paid;
                $transaction->payment->save();

                if ($transaction->payment->mealReservation->status === EOrderStates::Pending) {
                    $transaction->payment->mealReservation->status = EOrderStates::Processing;
                    $transaction->payment->mealReservation->save();
                }
            }
        } catch (PurchaseFailedException|InvalidPaymentException $e) {
            $transaction->status = ETransactionStates::Error;
            $transaction->status_message = $e->getMessage();
            $transaction->provider_status = $e->getCode();
            $transaction->save();
        } catch (PreviouslyVerifiedException $e) {
            $transaction->status = ETransactionStates::Success;
            $transaction->status_message = 'پرداخت با موفقیت انجام شد';
            $transaction->validated_at = now();
            $transaction->save();

            if ($transaction->payment->status === EPaymentStates::Unpaid) {
                $transaction->payment->status = EPaymentStates::Paid;
                $transaction->payment->save();

                if ($transaction->payment->mealReservation->status === EOrderStates::Pending) {
                    $transaction->payment->mealReservation->status = EOrderStates::Processing;
                    $transaction->payment->mealReservation->save();
                }
            }
        }
    }
}
