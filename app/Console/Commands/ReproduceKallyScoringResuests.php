<?php

namespace App\Console\Commands;

use App\Jobs\LifeStyleScoringProducer;
use App\Models\OJLifeStyleScore;
use Illuminate\Console\Command;

class ReproduceKallyScoringResuests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reproduce-kally-scoring-resuests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reprocess OJLifeStyleScore records where parsed data are set but result is null';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $records = OJLifeStyleScore::query()
            ->whereNotNull('parsed_data')
            ->whereNull('result')
            ->whereNull('error')
            ->get();

        if ($records->isEmpty()) {
            $this->info('No records found that meet the criteria.');
            return;
        }

        $options = $records->map(function ($record, $index) {
            return "[$index] ID: {$record->id}";
        })->toArray();

        array_unshift($options, '[A] Process All');

        $selection = $this->choice('Select a record to process', $options);

        if ($selection === '[A] Process All') {
            foreach ($records as $record) {
                $this->processRecord($record);
            }
            $this->info("Processed all records.");
        } else {
            $index = array_search($selection, $options);
            if ($index !== false && isset($records[$index - 1])) {
                $this->processRecord($records[$index - 1]);
                $this->info("Processed record ID: " . $records[$index - 1]->id);
            }
        }
    }

    /**
     * Process a single OJLifeStyleScore record.
     */
    protected function processRecord(OJLifeStyleScore $oj)
    {
        $oj->enqueued_at = now();
        $oj->save();

        $jobId = base64_encode(strval($oj->order_journey_id) . ":" . strval($oj->id));

        LifeStyleScoringProducer::dispatch([
            'job_id' => $jobId,
            'caller' => null, 
            'request' => [
                "data" => [
                    "target" => "lifestyle_score",
                    "ver" => $oj->requirements['form_id'],
                    "lifestyle_questionnaire" => $oj->parsed_data
                ]
            ]
        ]);
    }
}
