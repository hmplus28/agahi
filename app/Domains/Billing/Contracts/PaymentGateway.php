<?php

declare(strict_types=1);

namespace App\Domains\Billing\Contracts;

use App\Models\Payment;

interface PaymentGateway
{


    public function create(Payment $payment): array;



    public function verify(Payment $payment, array $callback = []): array;
}
