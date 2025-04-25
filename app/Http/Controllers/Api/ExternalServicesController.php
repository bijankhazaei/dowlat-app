<?php

namespace App\Http\Controllers\Api;

use App\Events\LifeStylePorslineCompleted;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExternalServicesController extends Controller
{
    public function porsline(Request $request)
    {
        if (!$request->hasValidSignatureWhileIgnoring(['quest'])) {
            return response()->allowNonApiResult()->view('redirection', ['data' => [
                'ok' => false,
                'text' => 'Error Code 1'
            ]]);
        }

        $uidParam = $request->query('uid');
        if (!$uidParam) {
            return response()->allowNonApiResult()->view('redirection', ['data' => [
                'ok' => false,
                'text' => 'Error Code 2'
            ]]);
        }

        $decodedUid = hex2bin($uidParam);
        if ($decodedUid === false) {
            return response()->allowNonApiResult()->view('redirection', ['data' => [
                'ok' => false,
                'text' => 'Error Code 3'
            ]]);
        }

        $uidParts = explode(':', $decodedUid); // CustomerId:OJLifeStyleScoreId:FormId

        if (count($uidParts) !== 3) {
            return response()->allowNonApiResult()->view('redirection', ['data' => [
                'ok' => false,
                'text' => 'Error Code 4'
            ]]);
        }

        $customer_id = intval($uidParts[0]);
        $lifestyle_oj_id = intval($uidParts[1]);
        $form_id = $uidParts[2];
        $response_id = $request->query('quest');
        if (empty($response_id)) {
            return response()->allowNonApiResult()->view('redirection', ['data' => [
                'ok' => false,
                'text' => 'Error Code 5'
            ]]);
        }

        $customer = Customer::find($customer_id);
        if (!$customer) {
            return response()->allowNonApiResult()->view('redirection', ['data' => ['statusCode' => 404]]);
        }

        Auth::setUser($customer);

        $lifestyle_oj = OJLifeStyleScore::where('id', $lifestyle_oj_id)->first();
        if (!$lifestyle_oj) {
            return response()->allowNonApiResult()->view('redirection', ['data' => [
                'ok' => false,
                'text' => 'Error Code 6'
            ]]);
        }

        if (empty($lifestyle_oj->requirements)) {

            $lifestyle_oj->requirements = [
                'form_id' => $form_id,
                'response_id' => $response_id
            ];
            $lifestyle_oj->prepared_at = now();
            $lifestyle_oj->save();

            LifeStylePorslineCompleted::dispatch($lifestyle_oj);
        }

        $metadata = $lifestyle_oj->metadata ?? [];
        $free = isset($metadata['free']) && $metadata['free'];
        $returnUrl = $free
            ? ('https://t.me/ZeeO_Longevity_bot?start=quest=' . $response_id)
            : str_replace("{ORDER_ID}", $lifestyle_oj->orderJourney->order_id, $metadata['return_url'] ?? "");
        $text = $free
            ? 'در حال انتقال به ربات تلگرامی'
            : 'در حال انتقال به برنامه';
        $logo = $free
            ? 'https://img.icons8.com/?size=100&id=lUktdBVdL4Kb&format=png&color=FFFFFF'
            : '';

        return response()->allowNonApiResult()->view('redirection', [
            'data' => [
                'ok' => true,
                'toward' => $returnUrl,
                'text' => $text,
                'logo' => $logo,
            ]
        ]);
    }
}
