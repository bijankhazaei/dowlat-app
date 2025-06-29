<?php

namespace App\Services\Payment;

use App\Contracts\Enums\EOrderStates;
use App\Contracts\Enums\EPaymentStates;
use App\Models\Transaction;
use App\Contracts\Enums\ETransactionStates;
use App\Services\Payment\Facade\Shetabit;
use Illuminate\Support\Facades\URL;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Multipay\Exceptions\InvoiceNotFoundException;
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
        if ($transaction->status !== ETransactionStates::Init) {
            throw new \RuntimeException("Transaction is not open to pay.");
        }

        $invoice = (new Invoice())
            ->amount($transaction->amount)
            ->detail('description', $transaction->payment->summary)
            ->detail('mobile', $authCustomerId); // Add customer mobile if available

        $gatewayUrl = Shetabit::via($transaction->provider)
            ->callbackUrl(route('payment.callback', $transaction->id))
            ->purchase($invoice, function ($driver, $transactionId) use ($transaction) {
                $transaction->requested_at = now();
                $transaction->authority = $transactionId;
                $transaction->gateway_url = config('payment.drivers.' . $transaction->provider . '.apiPaymentUrl') . $transactionId;
                $transaction->status = ETransactionStates::Pending;
                $transaction->save();
            })
            ->pay()
            ->getAction(); // Get the gateway URL

        return $gatewayUrl;
    }

    /**
     * Validate a transaction.
     *
     * @param Transaction $transaction
     * @return void
     * @throws InvoiceNotFoundException
     */
    public static function validate(Transaction &$transaction): void
    {
        if ($transaction->status !== ETransactionStates::Pending) {
            return;
        }

        try {
            $receipt = Shetabit::via($transaction->provider)
                ->amount($transaction->amount)
                ->transactionId($transaction->authority)
                ->verify();

            $transaction->status = ETransactionStates::Success;
            $transaction->reference = $receipt->getReferenceId();
            $transaction->status_message = 'پرداخت با موفقیت انجام شد';
            $transaction->provider_status = 100;
            $transaction->validated_at = now();

            // Handle receipt details
            $receiptDetails = $receipt->getDetails();
            if (isset($receiptDetails['cardNumber'])) {
                $m = $transaction->metadata ?? [];
                $m['card_pan'] = $receiptDetails['cardNumber'];
                $transaction->metadata = $m;
            }
            $transaction->save();

            // Update payment and order status
            self::updatePaymentStatus($transaction);

        } catch (PurchaseFailedException|InvalidPaymentException $e) {
            $transaction->status = ETransactionStates::Error;
            $transaction->status_message = $e->getMessage();
            $transaction->provider_status = $e->getCode();
            $transaction->validated_at = now();
            $transaction->save();
        } catch (PreviouslyVerifiedException $e) {
            $transaction->status = ETransactionStates::Success;
            $transaction->status_message = 'پرداخت قبلاً تایید شده است';
            $transaction->validated_at = now();
            $transaction->save();

            // Update payment and order status for previously verified transactions
            self::updatePaymentStatus($transaction);
        }
    }

    /**
     * Update payment and meal reservation status after successful transaction
     */
    private static function updatePaymentStatus(Transaction $transaction): void
    {
        try {
            if ($transaction->payment->status === EPaymentStates::Unpaid) {
                $transaction->payment->status = EPaymentStates::Paid;
                $transaction->payment->save();

                // Update meal reservation status to paid
                if ($transaction->payment->mealReservation->status !== EPaymentStates::Paid) {
                    $transaction->payment->mealReservation->status = EPaymentStates::Paid;
                    $transaction->payment->mealReservation->save();
                }
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

    }
}
