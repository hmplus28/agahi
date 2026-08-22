<?php

declare(strict_types=1);
namespace App\Domains\Billing\Contracts;
use App\Models\Payment;
interface PaymentGateway { /** @return array{authority:string,redirect_url:string} */ public function create(Payment $payment): array; /** @return array{successful:bool,reference_id:?string} */ public function verify(Payment $payment): array; }
