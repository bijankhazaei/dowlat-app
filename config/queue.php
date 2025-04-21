<?php

use App\Contracts\Enums\ERabbitQueues;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue supports a variety of backends via a single, unified
    | API, giving you convenient access to each backend using identical
    | syntax for each. The default queue connection is defined below.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection options for every queue backend
    | used by your application. An example configuration is provided for
    | each backend supported by Laravel. You're also free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_QUEUE_CONNECTION'),
            'table' => env('DB_QUEUE_TABLE', 'jobs'),
            'queue' => env('DB_QUEUE', 'default'),
            'retry_after' => (int) env('DB_QUEUE_RETRY_AFTER', 90),
            'after_commit' => false,
        ],

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => env('BEANSTALKD_QUEUE_HOST', 'localhost'),
            'queue' => env('BEANSTALKD_QUEUE', 'default'),
            'retry_after' => (int) env('BEANSTALKD_QUEUE_RETRY_AFTER', 90),
            'block_for' => 0,
            'after_commit' => false,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_QUEUE', 'default'),
            'suffix' => env('SQS_SUFFIX'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 90),
            'block_for' => null,
            'after_commit' => false,
        ],
        'rabbitmq' => [
            'driver' => 'rabbitmq',
            'host' => env('RABBITMQ_HOST', 'rabbitmq'),
            'port' => env('RABBITMQ_PORT', 5672),
            'vhost' => env('RABBITMQ_VHOST', '/'),
            'user' => env('RABBITMQ_USER', 'admin'),
            'password' => env('RABBITMQ_PASSWORD', 'admin'),
            'queue' => env('RABBITMQ_QUEUE', 'default'),
            'options' => [
                'queue' => [
                    'job' => \App\Jobs\RabbitMQJob::class,
                ],
                'exchange' => [
                    'name' => 'tasks_exchange',
                    'type' => 'direct',
                    'declare' => false,
                    'durable' => true,
                    'bind' => [],
                ],
                'queues' => [
                    env('RABBITMQ_QUEUE', 'default') => [
                        'declare' => true,
                        'bind' => true,
                    ],
                    ERabbitQueues::LifeStyleParsingProduction->value => [
                        'declare' => true,
                        'bind' => true,
                        'durable' => true,
                    ],
                    ERabbitQueues::LifeStyleParsingConsumption->value => [
                        'declare' => true,
                        'bind' => true,
                        'durable' => true,
                    ],
                    ERabbitQueues::LifeStyleScoringProduction->value => [
                        'declare' => true,
                        'bind' => true,
                        'durable' => true,
                    ],
                    ERabbitQueues::LifeStyleScoringConsumption->value => [
                        'declare' => true,
                        'bind' => true,
                        'durable' => true,
                    ],
                    ERabbitQueues::LongevityParsingProduction->value => [
                        'declare' => true,
                        'bind' => true,
                        'durable' => true,
                    ],
                    ERabbitQueues::LongevityParsingConsumption->value => [
                        'declare' => true,
                        'bind' => true,
                        'durable' => true,
                    ],
                    ERabbitQueues::LongevityScoringProduction->value => [
                        'declare' => true,
                        'bind' => true,
                        'durable' => true,
                    ],
                    ERabbitQueues::LongevityScoringConsumption->value => [
                        'declare' => true,
                        'bind' => true,
                        'durable' => true,
                    ],
                ],
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Job Batching
    |--------------------------------------------------------------------------
    |
    | The following options configure the database and table that store job
    | batching information. These options can be updated to any database
    | connection and table which has been defined by your application.
    |
    */

    'batching' => [
        'database' => env('DB_CONNECTION', 'sqlite'),
        'table' => 'job_batches',
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control how and where failed jobs are stored. Laravel ships with
    | support for storing failed jobs in a simple file or in a database.
    |
    | Supported drivers: "database-uuids", "dynamodb", "file", "null"
    |
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'sqlite'),
        'table' => 'failed_jobs',
    ],

];
