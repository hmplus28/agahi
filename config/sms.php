<?php

return [
    'provider' => env('SMS_PROVIDER', 'log'),

    'ippanel_api_key' => env('IPPANEL_API_KEY', ''),
    'ippanel_base_url' => env('IPPANEL_BASE_URL', 'https://edge.ippanel.com/v1/api'),
    'sender_number' => env('SMS_SENDER_NUMBER', ''),

    'patterns' => [
        'otp' => env('SMS_PATTERN_OTP', 'gbg6a9k9zw'),
        'expiry_reminder' => env('SMS_PATTERN_EXPIRY', ''),
    ],
];
