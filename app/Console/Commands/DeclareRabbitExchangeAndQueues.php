<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

class DeclareRabbitExchangeAndQueues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:declare';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This ';

    protected ?RabbitMQQueue $queue = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Connecting with parameters:');
        $this->info('Host: ' . env('RABBITMQ_HOST'));
        $this->info('Port: ' . env('RABBITMQ_PORT'));
        $this->info('User: ' . env('RABBITMQ_USER'));
        $this->info('VHost: ' . env('RABBITMQ_VHOST'));



        try {
            $this->info("Starting declaration");
            $this->queue ??= Queue::connection('rabbitmq');
            $this->info("Found queue");
            $this->info("Queue is" . $this->queue->getConnection()->isConnected() ? "connected" : "not connected");
            $c = config('queue.connections.rabbitmq');
            $exchange = $c['options']['exchange'];
            if ($exchange['declare']) {
                $this->info("Declaring exchange; name: " . $exchange['name']);
                $this->queue->declareExchange($exchange['name'], $exchange['type'], $exchange['durable'] ?? true, $exchange['autoDelete'] ?? false);
            }
            $qs = $c['options']['queues'];
            $bs = collect($exchange['bind'] ?? []);
            foreach ($qs as $qn => $q) {
                if ($q['declare']) {
                    $this->info("Declaring queue; name: " . $qn);
                    $this->queue->declareQueue($qn, $q['durable'] ?? true, $q['autoDelete'] ?? false);
                }
                $qb = $bs->firstWhere('queue', $qn);
                if ($q['bind'] && $qb) {
                    $this->info("Binding queue; queue_name: " . $qn . " exchange_name: " . $exchange['name']);
                    $this->queue->bindQueue($qn, $exchange['name'], $qb['routing_key']);
                }
            }
            $this->info("Completed Successfully");
        } catch (\Throwable $th) {
            $this->error($th->__toString());
        }
    }
}
