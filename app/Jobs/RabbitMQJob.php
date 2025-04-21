<?php

namespace App\Jobs;

use App\Contracts\Enums\ERabbitQueues;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob as BaseJob;

class RabbitMQJob extends BaseJob
{
    public function payload()
    {
        try {
            $queue = ERabbitQueues::tryFrom($this->getQueue());
            if (!$queue) {
                throw new \InvalidArgumentException('Provided queue, ' . $this->getQueue() . ' is not implemented');
            }

            $consumerClass = $queue->getConsumerClass();

            return [
                'job' => $consumerClass . '@init',
                'data' => json_decode($this->getRawBody(), true)
            ];
        } catch (\InvalidArgumentException $e) {
            return parent::payload();
        }
    }
}