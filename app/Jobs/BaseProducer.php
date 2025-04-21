<?php

namespace App\Jobs;

use App\Contracts\Enums\ERabbitQueues;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;
use Illuminate\Support\Facades\Queue;

abstract class BaseProducer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected $payload;
    protected ERabbitQueues $target_queue;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
        $this->onConnection('sync')->afterCommit();
    }

    public function handle()
    {
        $this->rabbit()->pushRaw(json_encode($this->payload), $this->target_queue->value);
    }

    protected function rabbit(): RabbitMQQueue
    {
        return Queue::connection('rabbitmq');
    }
}