<?php

namespace App\Jobs;

use App\Models\OJLifeStyleScore;
use Illuminate\Support\Facades\DB;

class LifeStyleScoringConsumer extends BaseConsumer
{
    protected function handle(): void
    {
        \Log::debug(json_encode($this->payload, JSON_PRETTY_PRINT));
        if (
            !empty($this->payload['job_id']) &&
            !empty($this->payload['result']) &&
            !empty($this->payload['result']['data']) &&
            !empty($this->payload['result']['data']['score']) &&
            (empty($this->payload['error']) || is_null($this->payload['error']))
        ) {
            DB::beginTransaction();

            $jobId = $this->payload['job_id'];
            $data = $this->payload['result']['data']['score'];

            $decodedJobId = base64_decode($jobId, true);
            if ($decodedJobId === false) {
                \Log::error("[RabbitMQ] [LifeStyleScoringConsumer] Invalid base64 job_id: {$jobId}");
                return;
            }

            $parts = explode(':', $decodedJobId);
            if (count($parts) !== 2 || !is_numeric($parts[0]) || !is_numeric($parts[1])) {
                \Log::error("[RabbitMQ] [LifeStyleScoringConsumer] Invalid job_id format after decoding: {$decodedJobId}");
                return;
            }

            [$orderJourneyId, $scoreId] = $parts;

            $lifeStyleScore = OJLifeStyleScore::where('id', $scoreId)
                ->where('order_journey_id', $orderJourneyId)
                ->first();

            if (!$lifeStyleScore) {
                \Log::error("[RabbitMQ] [LifeStyleScoringConsumer] OJLifeStyleScore not found for job_id: {$decodedJobId}");
                return;
            }

            $lifeStyleScore->result = $data;
            $lifeStyleScore->calculated_at = now();
            $lifeStyleScore->save();

            \Log::info("[RabbitMQ] [LifeStyleScoringConsumer] Successfully processed job_id: {$jobId}");

            DB::commit();
        } else {
            \Log::warning("[RabbitMQ] [LifeStyleScoringConsumer] Payload result is empty or error is present.");
        }
    }
}