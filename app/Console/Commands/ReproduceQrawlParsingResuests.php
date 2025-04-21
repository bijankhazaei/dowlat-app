<?php

namespace App\Console\Commands;

use App\Jobs\LifeStyleParsingProducer;
use App\Models\Customer;
use Illuminate\Console\Command;
use App\Models\OJLifeStyleScore;

class ReproduceQrawlParsingResuests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reproduce-qrawl-parsing-resuests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reprocess OJLifeStyleScore records where requirements are set but parsed_data is null';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $records = OJLifeStyleScore::query()
            ->whereNotNull('requirements')
            ->whereNull('parsed_data')
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
        $oj->prepared_at = now();
        $oj->save();

        $jobId = base64_encode(strval($oj->order_journey_id) . ":" . strval($oj->id));
        LifeStyleParsingProducer::dispatch([
            'job_id' => $jobId,
            'caller' => null, // What is this?!
            'request' => $oj->requirements
        ]);
    }
}