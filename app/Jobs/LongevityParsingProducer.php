<?php

namespace App\Jobs;

use App\Contracts\Enums\ERabbitQueues;

class LongevityParsingProducer extends BaseProducer
{
    protected ERabbitQueues $target_queue = ERabbitQueues::LongevityParsingProduction;
}