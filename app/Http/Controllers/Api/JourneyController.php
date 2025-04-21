<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Enums\EOrderStates;
use App\Contracts\Enums\JourneySlug;
use App\Http\Controllers\Controller;
use App\Http\Resources\JourneyResource;
use App\Models\Customer;
use App\Models\Journey;
use App\Repositories\Journey\JourneyRepository;
use App\Repositories\Order\OrderRepository;
use App\Services\JourneyLimitations\Facade\JourneyLimitations;
use Illuminate\Http\Request;

class JourneyController extends Controller
{
    /**
     * @return mixed
     *
     * @OA\Get(
     *     path="/api/journeys",
     *     tags={"Journeys"},
     *     summary="Get all journeys",
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="ok", type="boolean", example=true),
     *             @OA\Property(property="data", nullable=true, example=null),
     *            @OA\Property(property="messages", example="[]")
     *        )
     *    ),
     * )
     */
    public function list(): mixed
    {
        $query = JourneyRepository::query();
        $journeys = $query->paginate(request()->integer("count", 10))
            ->through([JourneyResource::class, 'make']);

        return response()->apiResult($journeys);
    }

    /**
     * @param Journey $journey
     * @return mixed
     * @OA\Get(
     *     path="/api/journeys/{journey}",
     *     tags={"Journeys"},
     *     summary="Get a journey",
     *     @OA\Parameter(
     *         name="journey",
     *         in="path",
     *         description="Journey ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="ok", type="boolean", example=true),
     *             @OA\Property(property="data", nullable=true, example=null),
     *             @OA\Property(property="messages", example="[]")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Journey not found"
     *     )
     * )
     */
    public function show(Journey $journey): mixed
    {
        return response()->apiResult(
            new JourneyResource($journey, true)
        );
    }

    /**
     * @param Request $request
     * @return mixed
     *
     * @OA\Get(
     *      path="/api/my-journeys",
     *      summary="Get user's journeys",
     *      tags={"Journeys"},
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="ok", type="boolean", example=true),
     *             @OA\Property(property="data", nullable=true, example=null),
     *             @OA\Property(property="messages", example="[]")
     *         )
     *     ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     *  )
     */
    public function myJourneys(Request $request): mixed
    {
        $customerId = $request->user()->id;

        $aoj = OrderRepository::getActiveOrderJourneys($customerId)
            ->keyBy('journey_id');
        $jrd = JourneyLimitations::getJourneysRemainingDays($customerId);

        $journeys = JourneyRepository::all();

        $result = [];

        foreach ($journeys as $journey) {
            $item = [
                'journey_id' => $journey->id,
                'name' => $journey->name,
                'slug' => $journey->slug,
                'limitations' => JourneyLimitations::canBeAddedToCart(
                    $journey,
                    $customerId
                ),
                'order_id' => null,
                'order_status' => null,
                'start' => null,
                'end' => null,
                'remaining_days' => $jrd[$journey->id]['remainingDays']
            ];

            $oj = isset($aoj[$journey->id]) ? $aoj[$journey->id] : null;
            if ($oj) {
                $item['order_id'] = $oj->order_id;
                $item['order_status'] = $oj->order->status;
                $item['start'] = $oj->start_date;
                $item['end'] = $oj->end_date;
                if ($journey->slug === JourneySlug::LIFE_STYLE_SCORE) {
                    $item['result'] = $oj->meta->result;
                    $item['status'] = !is_null($oj->meta->error)
                        ? 'error'
                        : (!is_null($oj->meta->calculated_at)
                            ? 'calculated'
                            : (!is_null($oj->meta->enqueued_at)
                                ? 'enqueued'
                                : (!is_null($oj->meta->parsed_at)
                                    ? 'parsed'
                                    : (!is_null($oj->meta->prepared_at)
                                        ? 'prepared'
                                        : ($oj->order->status === EOrderStates::Pending
                                            ? 'not-started'
                                            : ($oj->order->status === EOrderStates::Canceled
                                                ? 'cancelled'
                                                : 'pending'))))));
                } elseif ($journey->slug === JourneySlug::LONGEVITY_SCORE) {
                    $sampling = $oj->meta->metadata['sampling'];
                    $item['result'] = $oj->meta->result;
                    $item['sampling'] = $sampling;
                    $item['status'] = !is_null($oj->meta->error)
                        ? 'error'
                        : (!is_null($oj->meta->rejected_at)
                            ? 'rejected'
                            : (!is_null($oj->meta->calculated_at)
                                ? 'calculated'
                                : (!is_null($oj->meta->enqueued_at)
                                    ? 'enqueued'
                                    : (!is_null($oj->meta->parsed_at)
                                        ? 'parsed'
                                        : (!is_null($oj->meta->approved_at)
                                            ? 'approved'
                                            : (!is_null($oj->meta->prepared_at)
                                                ? 'prepared'
                                                : ($sampling && !is_null($oj->meta->sampled_at)
                                                    ? 'pending-upload'
                                                    : ($oj->order->status === EOrderStates::Pending
                                                        ? 'not-started'
                                                        : ($oj->order->status === EOrderStates::Canceled
                                                            ? 'cancelled'
                                                            : ($sampling
                                                                ? 'pending-sample'
                                                                : 'pending-upload'))))))))));
                }
            }

            $result[] = $item;
        }

        return response()->apiResult($result);
    }
}
