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



        $signature = hash_hmac('sha256', $authority, (string) config('app.key'));
        $returnUrl = route('user.payments.callback', [
            'authority' => $authority,
            'signature' => $signature,
        ]);

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



    private function sign(string $payload): string
    {
        $keyB64 = (string) config('payment.sadad.transaction_key', '');
        if ($keyB64 === '') {
            throw new RuntimeException('کلید تراکنش درگاه سداد تنظیم نشده است.');
        }



        $key = base64_decode($keyB64, false);
        if ($key === false || strlen($key) !== 24) {
            throw new RuntimeException('کلید تراکنش درگاه سداد معتبر نیست؛ باید base64 معادل ۲۴ بایت باشد.');
        }

        $iv = str_repeat("\x00", 8);
        $encrypted = openssl_encrypt($payload, 'DES-EDE3-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($encrypted === false) {
            throw new RuntimeException('امضای درگاه سداد تولید نشد؛ کلید تراکنش را بررسی کنید.');
        }

        return base64_encode($encrypted);
    }

    private function assertConfigured(): void
    {
        if (empty(config('payment.sadad.merchant_id')) || empty(config('payment.sadad.transaction_key'))) {
            throw new LogicException('درگاه سداد پیکربندی نشده است.');
        }
    }
}
