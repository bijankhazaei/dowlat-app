<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function verifyPayment(Request $request)
    {
        DB::beginTransaction();

        $transaction = Transaction::with(
            'payment',
            'payment.order',
            'payment.order.customer',
        )
            ->where('authority', $request->query('trackId'))
            ->first();

        PaymentService::validate($transaction);

        $data = [
            "entity" => [
                "payment_id" => $transaction->payment_id,
                "transaction_id" => $transaction->id,
                "order_id" => $transaction->payment->order_id,
                "ref_id" => $transaction->reference,
                "amount" => $transaction->amount,
                "currency" => "IRT",
                "status" => $transaction->status,
                "status_message" => $transaction->status_message,
                "card_pan" => $transaction->metadata && !empty($transaction->metadata['card_pan']) ? $transaction->metadata['card_pan'] : null,
                "summary" => $transaction->payment->summary,
                "return_url" => $transaction->metadata && !empty($transaction->metadata['return_url']) ? $transaction->metadata['return_url'] : null,
                "requested_at" => $transaction->requested_at,
                "validated_at" => $transaction->validated_at,
                "created_at" => $transaction->created_at,
                "user" => [
                    "id" => $transaction->payment->order->customer->id,
                    "mobile" => $transaction->payment->order->customer->mobile,
                    "first_name" => $transaction->payment->order->customer->first_name,
                    "last_name" => $transaction->payment->order->customer->last_name,
                    "gender" => $transaction->payment->order->customer->gender,
                    "email" => $transaction->payment->order->customer->email,
                ],
            ],
            "statusCode" => 200,
        ];

        DB::commit();
        return response()->allowNonApiResult()->view('payment_callback', ['data' => $data]);
    }
}
