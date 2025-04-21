<?php


return [

    /*
    |--------------------------------------------------------------------------
    | Default SMS Provider
    |--------------------------------------------------------------------------
    |
    | This field specifies the default SMS provider that will be used for sending messages.
    | The provider must be listed under the "providers" array below.
    |
    */

    'default' => env('SMS_PROVIDER', 'mediana'),

    /*
    |--------------------------------------------------------------------------
    | SMS Providers Configuration
    |--------------------------------------------------------------------------
    |
    | This section defines the available SMS providers and their configurations.
    | Each provider contains details about the client type, API URL, authentication,
    | and the associated class responsible for handling SMS requests.
    |
    */

    'providers' => [

        'mediana' => [
            'base_url' => env('MEDIANA_BASE_URL', 'https://smsapi.medianasms.ir'),
            'default_headers' => [],
            'auth' => [
                'type' => \App\Services\Sms\Contracts\ESmsDriverAuthMethods::ApiKey,
                'header_name' => 'X-API-Key',
                'token' => env('MEDIANA_API_KEY', 'zeeg5dgmxtBRUFk6r6g8Gw6KLfGuF_61_aF0iojK'),
            ],
            'default_source' => '989982001982',
            'driver' => \App\Services\Sms\Providers\MedianaProvider::class,
        ],

    ],
];
