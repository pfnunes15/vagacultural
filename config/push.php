<?php

declare(strict_types=1);

return [
    // 'log' (default, dev/testing) or 'fcm'
    'driver' => env('PUSH_DRIVER', 'log'),

    'log_channel' => env('PUSH_LOG_CHANNEL', 'stack'),

    'fcm' => [
        'project_id' => env('FCM_PROJECT_ID'),
        // In production, a short-lived OAuth token from the service account.
        'access_token' => env('FCM_ACCESS_TOKEN'),
    ],
];
