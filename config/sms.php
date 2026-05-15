<?php

return [
    'default' => env('SMS_PROVIDER', 'smsapi'),

    'providers' => [
        'smsapi' => [
            'api_token' => env('SMSAPI_API_TOKEN'),
            'base_url' => env('SMSAPI_BASE_URL', 'https://api.smsapi.pl'),
            'from' => env('SMSAPI_FROM'),
        ],
    ],
];
