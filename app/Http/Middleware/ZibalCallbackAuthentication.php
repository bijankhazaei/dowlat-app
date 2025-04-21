<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use App\Models\Transaction;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ZibalCallbackAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // return $next($request);

        if (!$request->hasValidSignatureWhileIgnoring(['status', 'trackId', 'success', 'orderId'])) {
            return response()->allowNonApiResult()->view('payment_callback', ['data' => ['statusCode' => 404]]);
        }

        $uidParam = $request->query('uid');
        if (!$uidParam) {
            return response()->allowNonApiResult()->view('payment_callback', ['data' => ['statusCode' => 404]]);
        }

        $decodedUid = base64_decode($uidParam, true);
        if ($decodedUid === false) {
            return response()->allowNonApiResult()->view('payment_callback', ['data' => ['statusCode' => 404]]);
        }

        $uidParts = explode(':', $decodedUid);
        $orderIdParam = $request->query('orderId');

        if ($orderIdParam) {
            if (count($uidParts) !== 2 || $uidParts[1] !== $orderIdParam) {
                return response()->allowNonApiResult()->view('payment_callback', ['data' => ['statusCode' => 404]]);
            }
            $customerId = $uidParts[0];
        } else {
            if (count($uidParts) !== 1) {
                return response()->allowNonApiResult()->view('payment_callback', ['data' => ['statusCode' => 404]]);
            }
            $customerId = $uidParts[0];
        }

        $customerId = intval($customerId);

        $success = $request->query('success');
        if (!in_array($success, ['0', '1'], true)) {
            return response()->allowNonApiResult()->view('payment_callback', ['data' => ['statusCode' => 404]]);
        }

        $status = $request->query('status');
        if (!is_numeric($status) || intval($status) < -2 || intval($status) > 18) {
            return response()->allowNonApiResult()->view('payment_callback', ['data' => ['statusCode' => 404]]);
        }

        $trackId = $request->query('trackId');
        $transaction = Transaction::where('authority', $trackId)->first();
        if (!$transaction) {
            return response()->allowNonApiResult()->view('payment_callback', ['data' => ['statusCode' => 404]]);
        }

        $customer = Customer::find($customerId);
        if (!$customer) {
            return response()->allowNonApiResult()->view('payment_callback', ['data' => ['statusCode' => 404]]);
        }

        Auth::setUser($customer);

        return $next($request);
    }
}
