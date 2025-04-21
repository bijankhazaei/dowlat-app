<?php

namespace App\Listeners;

use App\Events\LifeStylePorslineCompleted;
use App\Jobs\LifeStyleParsingProducer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StartParsingLifeStyleData
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
    public function handle(LifeStylePorslineCompleted $event): void
    {
        $event->model->prepared_at = now();
        $event->model->save();

        $jobId = base64_encode(strval($event->model->order_journey_id) . ":" . strval($event->model->id));
        LifeStyleParsingProducer::dispatch([
            'job_id' => $jobId,
            'caller' => null, // What is this?!
            'request' => $event->model->requirements
        ]);
    }
}
