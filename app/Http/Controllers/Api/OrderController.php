<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Enums\ECartStates;
use App\Contracts\Enums\EOrderStates;
use App\Events\LongevityLabTestUploaded;
use App\Helpers\CartItemSorter;
use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Http\Resources\OJLongevityScoreResource;
use App\Http\Resources\OrderResource;
use App\Models\OJLongevityScore;
use App\Models\Order;
use App\Repositories\Cart\CartRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Payment\PaymentRepository;
use App\Services\Payment\PaymentService;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use setasign\Fpdi\Fpdi;
use setasign\Fpdf\Fpdf;


class OrderController extends Controller
{
    public function list(Request $request)
    {
        $query = OrderRepository::query();

        $query = $query->where('customer_id', $request->user()->id);

        $journeys = $query->paginate(request()->integer("count", 10))
            ->through(fn($r) => new OrderResource($r));

        return response()->apiResult($journeys);
    }

    public function submitOrder(Request $request)
    {
        try {
            $data = $request->validate([
                'return_url' => 'nullable|string'
            ]);

            $cart = CartRepository::getOpenCart($request->user()->id);
            if (!$cart->items()->exists()) {
                return response()->apiResult(statusCode: 422, messages: ['سبد خرید شما خالی است']);
            }

            DB::beginTransaction();

            $cart->status = ECartStates::Ordered;
            $cart->save();

            $new_cart = CartRepository::getOpenCart($request->user()->id);

            $order = OrderRepository::create([
                'customer_id' => $request->user()->id,
                'status' => $cart->total_price <= 0
                    ? EOrderStates::Processing
                    : EOrderStates::Pending,
                'cart_id' => $cart->id,
                'address' => $request->user()->address,
                'postal_code' => $request->user()->postal_code,
                'total_price' => $cart->total_price
            ]);

            $returnUrl = !empty($data['return_url']) ? str_replace('{ORDER_ID}', $order->id, $data['return_url']) : null;

            $sorter = new CartItemSorter();
            $cart_items = $sorter->sort($cart->items);

            foreach ($cart_items as $item) {
                $extra = collect($item->journey->extra ?? [])->where('type', 'price')->whereIn('uid', $item->extra ?? [])->values();
                OrderRepository::appendJourney($order, $item->journey, $item->quantity, $extra, orderReturnUrl: $returnUrl);
            }

            if ($cart->total_price > 0) {
                $payment = PaymentRepository::create([
                    'order_id' => $order->id,
                    'amount' => $order->total_price,
                    'summary' => "سفارش " . $request->user()->first_name . ' ' . $request->user()->last_name . ' در ' . now()->locale("fa")->format('YYYY/M/dd HH:mm')
                ]);

                $transaction = $payment->activeTransactionOrCreate([
                    'return_url' => $returnUrl
                ]);

                $gateway_url = PaymentService::request($transaction, $request->user()->id);
                DB::commit();
                return response()->apiResult([
                    'redirect_to_pay' => true,
                    'gateway_url' => $gateway_url,
                    'order' => $order,
                    'cart' => new CartResource($new_cart)
                ]);
            }

            DB::commit();
            return response()->apiResult([
                'order' => $order,
                'cart' => new CartResource($new_cart)
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->apiResult(statusCode: 422, messages: $e->errors());
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->apiResult(
                statusCode: 500,
                messages: [
                    'Failed to submit order'
                ],
                metadata: $th->__toString()
            );
        }
    }

    public function show(Request $request, Order $order)
    {
        if ($order->customer_id != $request->user()->id) {
            return response()->apiResult(
                statusCode: 404,
                messages: ["سفارش مورد نظر پیدا نشد"]
            );
        }
        return response()->apiResult(
            new OrderResource($order, true)
        );
    }

    public function retryPayment(Request $request, Order $order)
    {
        try {
            if ($order->customer_id != $request->user()->id) {
                return response()->apiResult(
                    statusCode: 404,
                    messages: ["سفارش مورد نظر پیدا نشد"]
                );
            }

            $data = $request->validate([
                'return_url' => 'nullable|string'
            ]);

            if ($order->total_price <= 0) {
                return response()->apiResult(statusCode: 409, messages: ['سفارش رایگان است']);
            }

            $returnUrl = !empty($data['return_url']) ? str_replace('{ORDER_ID}', $order->id, $data['return_url']) : null;

            DB::beginTransaction();

            $payment = $order->payment;

            PaymentRepository::expireOldTransactions($payment);

            $transaction = $payment->activeTransactionOrCreate([
                'return_url' => $returnUrl
            ]);

            $gateway_url = PaymentService::request($transaction, $request->user()->id);

            DB::commit();
            return response()->apiResult([
                'gateway_url' => $gateway_url,
                'order' => $order,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->apiResult(
                statusCode: 500,
                messages: [
                    'Failed to start order payment'
                ],
                metadata: $th->__toString()
            );
        }
    }

    public function cancel(Request $request, Order $order)
    {
        try {
            if ($order->customer_id != $request->user()->id) {
                return response()->apiResult(
                    statusCode: 404,
                    messages: ["سفارش مورد نظر پیدا نشد"]
                );
            }
            if ($order->status !== EOrderStates::Pending) {
                return response()->apiResult(
                    statusCode: 409,
                    messages: ["سفارش در این مرحله قابل لغو شدن نیست"]
                );
            }

            $order->status = EOrderStates::Canceled;
            $order->save();

            return response()->apiResult(
                new OrderResource($order, true)
            );
        } catch (\Throwable $th) {
            return response()->apiResult(
                statusCode: 500,
                messages: [
                    'Failed to start order payment'
                ],
                metadata: $th->__toString()
            );
        }
    }

    public function submitLabTest(Request $request, Order $order, OJLongevityScore $longevity_score_oj)
    {
        try {
            $data = $request->validate([
                'files' => 'required',
                'files.*' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);

            $files = $data['files'];

            $pdf = new FPDI();
            foreach ($files as $file) {
                $extension = strtolower($file->getClientOriginalExtension());
                $tempPath = tempnam(sys_get_temp_dir(), 'upload_') . '.' . $extension;
                file_put_contents($tempPath, file_get_contents($file->getRealPath()));
                if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                    $pdf->AddPage();
                    $pdf->Image($tempPath, 10, 10, 190);
                } elseif ($extension === 'pdf') {
                    $pageCount = $pdf->setSourceFile($tempPath);

                    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                        $templateId = $pdf->importPage($pageNo);
                        $size = $pdf->getTemplateSize($templateId);
                        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                        $pdf->useTemplate($templateId);
                    }
                }

                unlink($tempPath);
            }
            $outputPath = tempnam(sys_get_temp_dir(), 'merged_') . '.pdf';
            $pdf->Output('F', $outputPath);

            foreach ($files as $file) {
                $longevity_score_oj->addMedia($file)
                    ->toMediaCollection('blood_analysis_lab_results');
            }

            $longevity_score_oj->addMedia($outputPath)
                ->toMediaCollection('blood_analysis_lab_results_merged');

            $media = $longevity_score_oj->getFirstMedia('blood_analysis_lab_results_merged');
            if (!$media) {
                throw new \Exception("Something went wrong during pdf generation");
            }

            $longevity_score_oj->requirements = [
                'file_name' => $media->id . '/' . $media->file_name
            ];
            $longevity_score_oj->save();

            LongevityLabTestUploaded::dispatch($longevity_score_oj);

            return response()->apiResult(
                new OJLongevityScoreResource($longevity_score_oj->orderJourney)
            );
        } catch (ValidationException $e) {
            return response()->apiResult(statusCode: 422, messages: $e->errors());
        } catch (\Throwable $th) {
            return response()->apiResult($th->__toString(), statusCode: 503);
        }
    }
}
