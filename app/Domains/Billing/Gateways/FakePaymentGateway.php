<?php

declare(strict_types=1);
namespace App\Domains\Billing\Gateways;
use App\Domains\Billing\Contracts\PaymentGateway;
use App\Models\Payment;
use Illuminate\Support\Str;
final class FakePaymentGateway implements PaymentGateway {
    public function create(Payment $payment): array {
        return ['authority'=>'DEV-'.Str::upper(Str::random(24)),'redirect_url'=>route('user.dashboard')];
    }
    public function verify(Payment $payment, array $callback = []): array {
        return ['successful'=>true,'reference_id'=>'DEV-'.Str::upper(Str::random(12))];
    }
}
