<?php

namespace App\Jobs;

use App\Models\OJLifeStyleScore;
use App\Models\OJLongevityScore;
use Illuminate\Support\Facades\DB;

class LifeStyleParsingConsumer extends BaseConsumer
{
    protected function handle(): void
    {
        \Log::debug(json_encode($this->payload, JSON_PRETTY_PRINT));
        if (
            !empty($this->payload['job_id']) &&
            !empty($this->payload['result']) &&
            !empty($this->payload['result']['data']) &&
            (empty($this->payload['error']) || is_null($this->payload['error']))
        ) {
            DB::beginTransaction();

            $jobId = $this->payload['job_id'];
            $data = $this->payload['result']['data'];

            $decodedJobId = base64_decode($jobId, true);
            if ($decodedJobId === false) {
                \Log::error("[RabbitMQ] [LifeStyleParsingConsumer] Invalid base64 job_id: {$jobId}");
                return;
            }

            $parts = explode(':', $decodedJobId);
            if (count($parts) !== 2 || !is_numeric($parts[0]) || !is_numeric($parts[1])) {
                \Log::error("[RabbitMQ] [LifeStyleParsingConsumer] Invalid job_id format after decoding: {$decodedJobId}");
                return;
            }

            [$orderJourneyId, $scoreId] = $parts;

            $lifeStyleScore = OJLifeStyleScore::where('id', $scoreId)
                ->where('order_journey_id', $orderJourneyId)
                ->first();

            if (!$lifeStyleScore) {
                \Log::error("[RabbitMQ] [LifeStyleParsingConsumer] OJLifeStyleScore not found for job_id: {$decodedJobId}");
                return;
            }
            
            $lifeStyleScore->parsed_data = $data;
            $lifeStyleScore->parsed_at = now();

            LifeStyleScoringProducer::dispatch([
                'job_id' => $jobId,
                'caller' => null, // What is this?!
                'request' => [
                    "data" => [
                        "target" => "lifestyle_score",

                        //https://survey.porsline.ir/n/survey/1368341/build/  ==> bJsq9Qy0
                        //https://survey.porsline.ir/n/survey/1183530/build/  ==> HKVAbbzX
                        "ver" => $lifeStyleScore->requirements['form_id'],
                        "lifestyle_questionnaire" => $data
                    ]
                ]
            ]);

            $lifeStyleScore->enqueued_at = now();
            $lifeStyleScore->save();

            $relatedLongevityJourneys = OJLongevityScore::where('related_life_style_order_journey_id', $lifeStyleScore->order_journey_id)
                ->whereNotNull('parsed_data')
                ->whereNull('enqueued_at')
                ->get();
            if ($relatedLongevityJourneys->count()) {
                foreach ($relatedLongevityJourneys as $longevity_oj) {
                    $longevity_job_id = base64_encode(strval($longevity_oj->order_journey_id) . ":" . strval($longevity_oj->id));

                    LongevityScoringProducer::dispatch([
                        'job_id' => $longevity_job_id,
                        'lab_data' => $longevity_oj->parsed_data,
                        'questionnaire_data' => $data
                    ]);

                    $longevity_oj->enqueued_at = now();
                    $longevity_oj->save();
                }
            }

            \Log::info("[RabbitMQ] [LifeStyleParsingConsumer] Successfully processed job_id: {$jobId}");

            DB::commit();
        } else {
            \Log::warning("[RabbitMQ] [LifeStyleParsingConsumer] Payload result is empty or error is present.");
        }
    }

    protected function onError(\Throwable $th): void
    {
        DB::rollBack();
        \Log::error($th->__toString());
    }
}