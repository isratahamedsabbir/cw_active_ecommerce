<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaConversionApiService
{
    public function isEnabled(): bool
    {
        return get_setting('facebook_conversion_api') == 1
            && !empty(config('services.meta_capi.pixel_id'))
            && !empty(config('services.meta_capi.access_token'));
    }

    public function send(
        string $eventName,
        array $customData,
        array $userData,
        string $eventId,
        string $eventSourceUrl,
        ?string $ip,
        ?string $userAgent
    ): bool {
        $pixelId = config('services.meta_capi.pixel_id');
        $apiVersion = config('services.meta_capi.api_version');
        $testEventCode = config('services.meta_capi.test_event_code');

        $payload = [
            'data' => [
                [
                    'event_name' => $eventName,
                    'event_time' => now()->timestamp,
                    'event_id' => $eventId,
                    'event_source_url' => $eventSourceUrl,
                    'action_source' => 'website',
                    'user_data' => $this->buildUserData($userData, $ip, $userAgent),
                    'custom_data' => $customData,
                ],
            ],
            'access_token' => config('services.meta_capi.access_token'),
        ];

        if (!empty($testEventCode)) {
            $payload['test_event_code'] = $testEventCode;
        }

        try {
            $response = Http::timeout(5)->post(
                "https://graph.facebook.com/{$apiVersion}/{$pixelId}/events",
                $payload
            );

            if (!$response->successful()) {
                Log::warning('Meta CAPI send failed', [
                    'event_name' => $eventName,
                    'event_id' => $eventId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Meta CAPI send exception', [
                'event_name' => $eventName,
                'event_id' => $eventId,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function hashEmail(string $value): string
    {
        return hash('sha256', strtolower(trim($value)));
    }

    public function hashPhone(string $value): string
    {
        return hash('sha256', preg_replace('/\D+/', '', $value));
    }

    protected function buildUserData(array $userData, ?string $ip, ?string $userAgent): array
    {
        $data = [];

        if (!empty($userData['email'])) {
            $data['em'] = [$this->hashEmail($userData['email'])];
        }

        if (!empty($userData['phone'])) {
            $data['ph'] = [$this->hashPhone($userData['phone'])];
        }

        if (!empty($userData['fbp'])) {
            $data['fbp'] = $userData['fbp'];
        }

        if (!empty($userData['fbc'])) {
            $data['fbc'] = $userData['fbc'];
        }

        if (!empty($ip)) {
            $data['client_ip_address'] = $ip;
        }

        if (!empty($userAgent)) {
            $data['client_user_agent'] = $userAgent;
        }

        return $data;
    }
}
