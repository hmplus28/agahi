<?php

declare(strict_types=1);

namespace App\Domains\Notifications\Providers;

use App\Domains\Notifications\Contracts\SmsProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class IpPanelSmsProvider implements SmsProvider
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('sms.ippanel_api_key', '');
        $this->baseUrl = config('sms.ippanel_base_url', 'https://edge.ippanel.com/v1');
    }

    public function send(string $mobile, string $message): array
    {
        $mobile = ltrim($mobile, '0');
        if (strlen($mobile) === 10) {
            $mobile = '98' . $mobile;
        } elseif (strlen($mobile) === 11 && str_starts_with($mobile, '0')) {
            $mobile = '98' . substr($mobile, 1);
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => $this->apiKey,
            ])->timeout(10)->post($this->baseUrl . '/api/smpp/send', [
                'send_number' => config('sms.sender_number', ''),
                'receive_number' => $mobile,
                'message' => $message,
            ]);

            $body = $response->json();

            if ($response->successful() && ($body['meta']['status'] ?? false)) {
                return [
                    'provider_id' => $body['data']['id'] ?? null,
                    'response' => $body,
                ];
            }

            Log::warning('IPPanel SMS failed', [
                'mobile' => $mobile,
                'status' => $response->status(),
                'response' => $body,
            ]);

            return [
                'provider_id' => null,
                'response' => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('IPPanel SMS exception', [
                'mobile' => $mobile,
                'error' => $e->getMessage(),
            ]);

            return [
                'provider_id' => null,
                'response' => ['error' => $e->getMessage()],
            ];
        }
    }
}
