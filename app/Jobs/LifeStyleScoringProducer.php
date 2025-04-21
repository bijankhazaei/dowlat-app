<?php

namespace App\Jobs;

use App\Contracts\Enums\ERabbitQueues;

class LifeStyleScoringProducer extends BaseProducer
{
    protected ERabbitQueues $target_queue = ERabbitQueues::LifeStyleScoringProduction;
}