<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Health Check Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for health check endpoints and monitoring
    |
    */

    'api_token' => env('HEALTH_CHECK_API_TOKEN', null),

    /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    |
    | Channels to use when sending health notifications
    |
    */

    'notification_channels' => [
        'mail',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Recipients
    |--------------------------------------------------------------------------
    |
    | Email addresses to notify when health checks fail
    |
    */

    'notification_recipients' => [
        env('HEALTH_NOTIFICATION_EMAIL', 'admin@example.com'),
    ],
];
