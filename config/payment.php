<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    | fake | zarinpal | sadad
    */
    'gateway' => env('PAYMENT_GATEWAY', 'fake'),

    'zarinpal' => [
        'merchant_id' => env('ZARINPAL_MERCHANT_ID', ''),
        'sandbox' => (bool) env('ZARINPAL_SANDBOX', true),
        'api_url' => 'https://sandbox.zarinpal.com/pg/rest/WebGate/',
        'start_pay_url' => 'https://sandbox.zarinpal.com/pg/StartPay/',
    ],

    // Sadad PSP — بانک ملی ایران
    'sadad' => [
        'merchant_id' => env('SADAD_MERCHANT_ID', ''),
        'terminal_id' => env('SADAD_TERMINAL_ID', ''),
        'transaction_key' => env('SADAD_TRANSACTION_KEY', ''),
        'request_url' => 'https://sadad.shaparak.ir/vpg/api/v0/Request/PaymentRequest',
        'verify_url' => 'https://sadad.shaparak.ir/vpg/api/v0/Advice/Verify',
        'purchase_url' => 'https://sadad.shaparak.ir/VPG/Purchase',
    ],

    // پرداخت کارت به کارت — اطلاعات شماره کارت واریز
    'card_to_card' => [
        'number' => env('CARD_TO_CARD_NUMBER', ''),
        'cardholder' => env('CARD_TO_CARD_HOLDER', ''),
    ],
];
