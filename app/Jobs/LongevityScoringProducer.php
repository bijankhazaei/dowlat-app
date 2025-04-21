<?php

namespace App\Jobs;

use App\Contracts\Enums\ERabbitQueues;

class LongevityScoringProducer extends BaseProducer
{
    protected ERabbitQueues $target_queue = ERabbitQueues::LongevityScoringProduction;
}