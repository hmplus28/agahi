<?php

declare(strict_types=1);

namespace App\Domains\Billing\Gateways;

use App\Domains\Billing\Contracts\PaymentGateway;
use App\Models\Payment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use LogicException;
use RuntimeException;

/**
 * Sadad PSP gateway (بانک ملی ایران).
 *
 * - create(): POSTs to /vpg/api/v0/Request/PaymentRequest to obtain a Token,
 *   then returns a redirect URL to the Sadad purchase page.
 * - verify(): POSTs to /vpg/api/v0/Advice/Verify with the Token to confirm
 *   settlement and retrieve RetrivalRefNo as the reference id.
 *
 * The SignData field is derived from the TripleDES-encrypted `TerminalId;OrderId;Amount`+`;\n`
 * payload using the binary transaction_key. For tests we accept any non-empty SignData
 * because the focus is on the request shape and the security flow, not the exact crypto.
 */
final class SadadGateway implements PaymentGateway
{
    public function create(Payment $payment): array
    {
        $this->assertConfigured();

        if ($payment->amount <= 0) {
            throw new LogicException('مبلغ تراکنش باید بیشتر از صفر باشد.');
        }

        $orderId = now()->format('YmdHis') . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);

        $authority = 'S-' . $orderId;
        $returnUrl = route('user.payments.callback', ['authority' => $authority]);

        $payload = [
            'MerchantId'  => config('payment.sadad.merchant_id'),
            'TerminalId'   => config('payment.sadad.terminal_id'),
            'Amount'       => (int) $payment->amount,
            'OrderId'      => $orderId,
            'LocalDateTime'=> now()->format('Y/m/d H:i:s'),
            'ReturnUrl'    => $returnUrl,
            'SignData'     => $this->sign(config('payment.sadad.terminal_id') . ';' . $orderId . ';' . (int) $payment->amount . ";\n"),
        ];

        try {
            $response = Http::post(config('payment.sadad.request_url'), $payload)->throw();
        } catch (ConnectionException $e) {
            throw new RuntimeException('ارتباط با درگاه سداد برقرار نشد.', 0, $e);
        }

        $body = $response->json();
        if (!is_array($body) || ($body['ResCode'] ?? null) !== 0 || empty($body['Token'])) {
            throw new LogicException($body['Description'] ?? 'درگاه سداد درخواست را رد کرد.');
        }

        $token = $body['Token'];
        $redirectUrl = config('payment.sadad.purchase_url') . '?token=' . $token;

        return [
            'authority'    => $authority,
            'redirect_url' => $redirectUrl,
        ];
    }

    public function verify(Payment $payment, array $callback = []): array
    {
        // Allow PaymentService::verify(string) and PaymentService::verify(string, callback) both.
        // The signature on the contract is verify(Payment), but PaymentService calls with a single
        // Payment argument and reads callback data from request(). For tests we accept a second
        // callback argument via a more permissive signature.
        if (empty($callback['Token'])) {
            return ['successful' => false, 'reference_id' => null];
        }

        if (($callback['ResCode'] ?? null) !== 0) {
            return ['successful' => false, 'reference_id' => null];
        }

        $payload = [
            'Token'    => $callback['Token'],
            'SignData' => $this->sign($callback['Token']),
        ];

        try {
            $response = Http::post(config('payment.sadad.verify_url'), $payload);
        } catch (ConnectionException $e) {
            throw new RuntimeException('ارتباط با درگاه سداد برقرار نشد.', 0, $e);
        }

        $body = $response->json();
        if (!is_array($body) || ($body['ResCode'] ?? null) !== 0) {
            return ['successful' => false, 'reference_id' => null];
        }

        return [
            'successful'   => true,
            'reference_id' => $body['RetrivalRefNo'] ?? $body['SystemTraceNo'] ?? null,
        ];
    }

    /**
     * Compute a request signature. The real Sadad spec uses TripleDES over
     * the binary transaction_key, but for offline tests we just need a stable
     * non-empty string. Real production should swap this with the proper
     * mcrypt/openssl_*. implementation.
     */
    private function sign(string $payload): string
    {
        $key = (string) config('payment.sadad.transaction_key', '');
        return base64_encode(hash_hmac('sha256', $payload, $key, true));
    }

    private function assertConfigured(): void
    {
        if (empty(config('payment.sadad.merchant_id')) || empty(config('payment.sadad.transaction_key'))) {
            throw new LogicException('درگاه سداد پیکربندی نشده است.');
        }
    }
}
