<?php

namespace App\Jobs;

use App\Models\OJLifeStyleScore;
use App\Models\OJLongevityScore;
use Illuminate\Support\Facades\DB;

class LongevityParsingConsumer extends BaseConsumer
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
                \Log::error("[RabbitMQ] [LongevityParsingConsumer] Invalid base64 job_id: {$jobId}");
                return;
            }

            $parts = explode(':', $decodedJobId);
            if (count($parts) !== 2 || !is_numeric($parts[0]) || !is_numeric($parts[1])) {
                \Log::error("[RabbitMQ] [LongevityParsingConsumer] Invalid job_id format after decoding: {$decodedJobId}");
                return;
            }

            [$orderJourneyId, $scoreId] = $parts;

            $longevityScore = OJLongevityScore::where('id', $scoreId)
                ->where('order_journey_id', $orderJourneyId)
                ->first();

            if (!$longevityScore) {
                \Log::error("[RabbitMQ] [LongevityParsingConsumer] OJLongevityScore not found for job_id: {$decodedJobId}");
                return;
            }

            $longevityScore->parsed_data = $data;
            $longevityScore->parsed_at = now();
            $longevityScore->save();

            $lifeStyle_oj = OJLifeStyleScore::where('order_journey_id', $longevityScore->related_life_style_order_journey_id)
                ->whereNotNull('parsed_data')
                ->whereNull('error')
                ->first();
            if ($lifeStyle_oj) {
                LongevityScoringProducer::dispatch([
                    'job_id' => $jobId,
                    'lab_data' => $lifeStyle_oj->parsed_data,
                    'questionnaire_data' => $data
                ]);

                $longevityScore->enqueued_at = now();
                $longevityScore->save();
            }

            \Log::info("[RabbitMQ] [LongevityParsingConsumer] Successfully processed job_id: {$jobId}");

            DB::commit();
        } else {
            \Log::warning("[RabbitMQ] [LongevityParsingConsumer] Payload result is empty or error is present.");
        }
    }
}