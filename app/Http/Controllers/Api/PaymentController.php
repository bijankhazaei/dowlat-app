<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Enums\ETransactionStates;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Shetabit\Multipay\Invoice;
use App\Services\Payment\Facade\Shetabit;


class PaymentController extends Controller
{

    public function redirectToGateway(Transaction $transaction, Request $request)
    {
        if ($transaction->status !== ETransactionStates::Init) {
            abort(403, 'Invalid transaction status.');
        }

        $invoice = (new Invoice())
            ->amount($transaction->amount)
            ->detail('description', $transaction->payment->summary);

        return Shetabit::via($transaction->provider)
            ->callbackUrl(
                route('payment.callback')
            )
            ->purchase($invoice, function($driver, $transactionId) use ($transaction) {
                $transaction->update([
                    'requested_at' => now(),
                    'authority'    => $transactionId,
                    'gateway_url'  => config("payment.drivers.{$transaction->provider}.apiPaymentUrl").$transactionId,
                    'status'       => ETransactionStates::Pending,
                ]);
            })
            ->pay()      // generates the auto-submit form
            ->render();
    }

    public function callback(Request $request)
    {
        try {
            DB::beginTransaction();

            // Get trackId from Zibal callback
            $trackId = $request->query('trackId');

            if (!$trackId) {
                return view('payment_callback', ['data' => ['statusCode' => 400, 'message' => 'trackId missing']]);
            }

            $transaction = Transaction::with('payment.mealReservation.user')
                ->where('authority', $trackId)
                ->first();

            if (!$transaction) {
                return view('payment_callback', ['data' => ['statusCode' => 404, 'message' => 'Transaction not found']]);
            }
            // Validate the transaction
            PaymentService::validate($transaction);

            $data = [
                "entity" => [
                    "payment_id" => $transaction->payment_id,
                    "transaction_id" => $transaction->id,
                    "reservation_id" => $transaction->payment->meal_reservation_id,
                    "ref_id" => $transaction->reference,
                    "amount" => $transaction->amount,
                    "currency" => "IRT",
                    "status" => $transaction->status->value,
                    "status_message" => $transaction->status_message,
                    "summary" => $transaction->payment->summary,
                    "requested_at" => $transaction->requested_at,
                    "validated_at" => $transaction->validated_at,
                    "created_at" => $transaction->created_at,
                    "user" => [
                        "id" => $transaction->payment->mealReservation->user->id,
                        "mobile" => $transaction->payment->mealReservation->user->mobile,
                        "first_name" => $transaction->payment->mealReservation->user->first_name,
                        "last_name" => $transaction->payment->mealReservation->user->last_name,
                    ],
                ],
                "statusCode" => 200,
            ];

            DB::commit();
            return view('payment_callback', ['data' => $data]);

        } catch (\Exception $e) {
            DB::rollBack();

            $data = [
                "entity" => null,
                "statusCode" => 500,
                "message" => $e->getMessage()
            ];

            return view('payment_callback', ['data' => $data]);
        }
    }

    public function verifyPayment(Request $request)
    {
        try {
            DB::beginTransaction();

            // Get trackId from Zibal callback
            $trackId = $request->query('trackId');
            if (!$trackId) {
                return response()->json([
                    'success' => false,
                    'message' => 'trackId parameter is missing'
                ], 400);
            }

            $transaction = Transaction::with('payment.mealReservation.user')
                ->where('authority', $trackId)
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            // Validate the transaction
            PaymentService::validate($transaction);

            $data = [
                "entity" => [
                    "payment_id" => $transaction->payment_id,
                    "transaction_id" => $transaction->id,
                    "reservation_id" => $transaction->payment->meal_reservation_id,
                    "ref_id" => $transaction->reference,
                    "amount" => $transaction->amount,
                    "currency" => "IRT",
                    "status" => $transaction->status->value,
                    "status_message" => $transaction->status_message,
                    "card_pan" => $transaction->metadata && !empty($transaction->metadata['card_pan']) ? $transaction->metadata['card_pan'] : null,
                    "summary" => $transaction->payment->summary,
                    "return_url" => $transaction->metadata && !empty($transaction->metadata['return_url']) ? $transaction->metadata['return_url'] : null,
                    "requested_at" => $transaction->requested_at,
                    "validated_at" => $transaction->validated_at,
                    "created_at" => $transaction->created_at,
                    "user" => [
                        "id" => $transaction->payment->mealReservation->user->id,
                        "mobile" => $transaction->payment->mealReservation->user->mobile,
                        "first_name" => $transaction->payment->mealReservation->user->first_name,
                        "last_name" => $transaction->payment->mealReservation->user->last_name,
                        "email" => $transaction->payment->mealReservation->user->email ?? null,
                    ],
                ],
                "success" => true,
                "statusCode" => 200,
            ];

            DB::commit();
            return response()->json($data);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'statusCode' => 500
            ], 500);
        }
    }
}
