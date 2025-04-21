<?php

use Sentry\Event;

return [

    'dsn' => env('SENTRY_LARAVEL_DSN'),

    'release' => env('SENTRY_RELEASE', trim(exec('git rev-parse --short HEAD'))),

    'environment' => env('APP_ENV', 'production'),

    'breadcrumbs' => [
        'logs' => false,           // Disable log breadcrumbs
        'sql_queries' => false,    // Disable SQL breadcrumbs
        'queue_info' => false,     // Disable queue breadcrumbs
    ],

    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.0),

    'send_default_pii' => false,

    'before_send' => function (Event $event) {
        $exceptions = $event->getExceptions();

        if (empty($exceptions)) {
            return null;
        }

        return $event;
    },
];
