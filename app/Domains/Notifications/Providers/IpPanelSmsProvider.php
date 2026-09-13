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
        $this->baseUrl = config('sms.ippanel_base_url', 'https://edge.ippanel.com/v1/api');
    }

    public function send(string $mobile, string $message): array
    {
        $mobile = $this->normalizeMobile($mobile);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => $this->apiKey,
            ])->timeout(10)->post($this->baseUrl . '/send', [
                'sending_type' => 'webservice',
                'from_number' => config('sms.sender_number', ''),
                'recipients' => [$mobile],
                'message' => $message,
            ]);

            $body = $response->json();

            if ($response->successful() && ($body['meta']['status'] ?? false)) {
                return [
                    'provider_id' => $body['data']['message_outbox_ids'][0] ?? null,
                    'response' => $body,
                ];
            }

            Log::warning('IPPanel SMS webservice failed', [
                'mobile' => $mobile,
                'status' => $response->status(),
                'response' => $body,
            ]);

            return [
                'provider_id' => null,
                'response' => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('IPPanel SMS webservice exception', [
                'mobile' => $mobile,
                'error' => $e->getMessage(),
            ]);

            return [
                'provider_id' => null,
                'response' => ['error' => $e->getMessage()],
            ];
        }
    }

    public function sendPattern(string $mobile, string $patternCode, array $params): array
    {
        $mobile = $this->normalizeMobile($mobile);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => $this->apiKey,
            ])->timeout(10)->post($this->baseUrl . '/send', [
                'sending_type' => 'pattern',
                'from_number' => config('sms.sender_number', ''),
                'recipients' => [$mobile],
                'code' => $patternCode,
                'params' => $params,
            ]);

            $body = $response->json();

            if ($response->successful() && ($body['meta']['status'] ?? false)) {
                return [
                    'provider_id' => $body['data']['message_outbox_ids'][0] ?? null,
                    'response' => $body,
                ];
            }

            Log::warning('IPPanel SMS pattern failed', [
                'mobile' => $mobile,
                'pattern' => $patternCode,
                'params' => $params,
                'status' => $response->status(),
                'response' => $body,
            ]);

            return [
                'provider_id' => null,
                'response' => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('IPPanel SMS pattern exception', [
                'mobile' => $mobile,
                'pattern' => $patternCode,
                'error' => $e->getMessage(),
            ]);

            return [
                'provider_id' => null,
                'response' => ['error' => $e->getMessage()],
            ];
        }
    }

    private function normalizeMobile(string $mobile): string
    {
        $mobile = ltrim($mobile, '0');

        if (strlen($mobile) === 10) {
            return '98' . $mobile;
        }

        if (strlen($mobile) === 11 && str_starts_with($mobile, '0')) {
            return '98' . substr($mobile, 1);
        }

        return $mobile;
    }
}
