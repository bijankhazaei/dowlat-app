<?php

namespace App\Jobs;

use App\Models\OJLongevityScore;
use Illuminate\Support\Facades\DB;

class LongevityScoringConsumer extends BaseConsumer
{
    protected function handle(): void
    {\Log::debug(json_encode($this->payload, JSON_PRETTY_PRINT));
        if (
            !empty($this->payload['result']) &&
            (empty($this->payload['error']) || is_null($this->payload['error']))
        ) {
            DB::beginTransaction();

            $resultData = $this->payload['result']['data'] ?? null;
            if (empty($resultData) || !isset($resultData['job_id'])) {
                \Log::warning("[RabbitMQ] [LongevityScoringConsumer] Missing 'job_id' in result data.");
                return;
            }

            $jobId = $resultData['job_id'];
            $data = $resultData;
            unset($data['job_id']);

            $decodedJobId = base64_decode($jobId, true);
            if ($decodedJobId === false) {
                \Log::error("[RabbitMQ] [LongevityScoringConsumer] Invalid base64 job_id: {$jobId}");
                return;
            }

            $parts = explode(':', $decodedJobId);
            if (count($parts) !== 2 || !is_numeric($parts[0]) || !is_numeric($parts[1])) {
                \Log::error("[RabbitMQ] [LongevityScoringConsumer] Invalid job_id format after decoding: {$decodedJobId}");
                return;
            }

            [$orderJourneyId, $scoreId] = $parts;

            $longevityScore = OJLongevityScore::where('id', $scoreId)
                ->where('order_journey_id', $orderJourneyId)
                ->first();

            if (!$longevityScore) {
                \Log::error("[RabbitMQ] [LongevityScoringConsumer] OJLongevityScore not found for job_id: {$decodedJobId}");
                return;
            }

            $longevityScore->result = $data;
            $longevityScore->calculated_at = now();
            $longevityScore->save();

            \Log::info("[RabbitMQ] [LongevityScoringConsumer] Successfully processed job_id: {$jobId}");

            DB::commit();
        } else {
            \Log::warning("[RabbitMQ] [LongevityScoringConsumer] Payload result is empty or error is present.");
        }
    }
}