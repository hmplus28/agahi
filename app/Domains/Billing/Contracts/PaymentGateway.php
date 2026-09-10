<?php

declare(strict_types=1);

namespace App\Domains\Billing\Contracts;

use App\Models\Payment;

interface PaymentGateway
{
    /**
     * Initiate a payment. Returns the gateway authority and the redirect URL
     * the user must be sent to.
     *
     * @return array{authority:string,redirect_url:string}
     */
    public function create(Payment $payment): array;

    /**
     * Verify the payment after the user returns from the gateway.
     *
     * The optional $callback parameter carries the gateway's POST data (e.g.
     * Sadad posts ResCode/Token/OrderId back without CSRF). When it is omitted,
     * the implementation should read from request() to remain compatible with
     * the FakePaymentGateway.
     *
     * @param  array<string,mixed>  $callback
     * @return array{successful:bool,reference_id:?string}
     */
    public function verify(Payment $payment, array $callback = []): array;
}
