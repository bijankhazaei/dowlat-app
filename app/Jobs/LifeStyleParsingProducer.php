<?php

namespace App\Jobs;

use App\Contracts\Enums\ERabbitQueues;

class LifeStyleParsingProducer extends BaseProducer
{
    protected ERabbitQueues $target_queue = ERabbitQueues::LifeStyleParsingProduction;
}