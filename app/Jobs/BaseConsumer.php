<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Queue;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

abstract class BaseConsumer
{
    protected RabbitMQJob $job;
    protected $payload;

    public function init(RabbitMQJob $job, mixed $payload)
    {
        $this->job = $job;
        $this->payload = $payload;
        try {
            if (!$this->validate()) {
                throw new \JsonException("Invalid payload");
            }
            $this->handle();
        } catch (\Throwable $th) {
            $this->onError($th);
        } finally {
            $this->finally();
        }
    }

    /**
     * This method is called right before handle method, and will determine whether handle or onError methods should be called afterwards.
     * @return bool
     */
    protected function validate(): bool
    {
        return true;
    }

    /**
     * This method is called whenever validation fails or an exception is thrown during handling
     * @param \Throwable $th
     * @return void
     */
    protected function onError(\Throwable $th): void
    {
        throw $th;
    }

    /**
     * This method is always called at the end.
     * @return void
     */
    protected function finally(): void
    {
        if (!$this->job->hasFailed() && !$this->job->isDeletedOrReleased()) {
            $this->delete();
        }
    }
    protected abstract function handle(): void;

    protected function rabbit(): RabbitMQQueue
    {
        return Queue::connection('rabbitmq');
    }
    protected function acknowledge()
    {
        $this->rabbit()->ack($this->job);
    }
    protected function reject($requeue = false)
    {
        $this->rabbit()->reject($this->job, $requeue);
    }
    protected function delete()
    {
        $this->job->delete();
    }
    protected function release(int $delay = 0)
    {
        $this->job->release($delay);
    }
}