<?php

namespace App\Listeners;

use App\Events\LongevityLabTestUploaded;
use App\Jobs\LongevityParsingProducer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StartParsingLongevityLabData
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(LongevityLabTestUploaded $event): void
    {
        $event->model->prepared_at = now();
        $event->model->save();
        
        $jobId = base64_encode(strval($event->model->order_journey_id) . ":" . strval($event->model->id));
        LongevityParsingProducer::dispatch([
            'job_id' => $jobId,
            'caller' => null, // What is this?!
            'request' => $event->model->requirements
        ]);
    }
}
