<?php

namespace App\Services\Webhook;

use App\Models\Webhook\WebhookDelivery;
use App\Models\Webhook\WebhookDeliveryLog;
use Illuminate\Support\Facades\Http;

class WebhookDeliveryService
{
    public function deliver(WebhookDelivery $delivery, array $payload): array
    {
        $subscription = $delivery->subscription;
        $startTime = microtime(true);

        $normalizedPayload = $this->buildNormalizedPayload($delivery, $payload);

        $timestamp = time();
        $signature = $this->generateSignature($delivery, $normalizedPayload, $timestamp);

        $headers = $this->buildHeaders($subscription, $delivery, $signature, $timestamp);

        try {
            $response = Http::withHeaders($headers)
                ->timeout($subscription->timeout_ms / 1000)
                ->post($subscription->url, $normalizedPayload);

            $latencyMs = (int) ((microtime(true) - $startTime) * 1000);

            $this->logDelivery($delivery, $headers, $normalizedPayload, $response, $latencyMs);

            $httpStatus = $response->status();
            $isSuccess = $httpStatus >= 200 && $httpStatus < 300;

            return [
                'success' => $isSuccess,
                'http_status' => $httpStatus,
                'latency_ms' => $latencyMs,
                'error' => $isSuccess ? null : "HTTP {$httpStatus}: ".$response->body(),
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $latencyMs = (int) ((microtime(true) - $startTime) * 1000);

            $this->logDelivery($delivery, $headers, $normalizedPayload, null, $latencyMs, $e->getMessage());

            return [
                'success' => false,
                'http_status' => null,
                'latency_ms' => $latencyMs,
                'error' => 'Connection error: '.$e->getMessage(),
            ];
        }
    }

    protected function buildNormalizedPayload(WebhookDelivery $delivery, array $transformedData): array
    {
        $event = $delivery->event;

        return [
            'meta' => [
                'event_key' => $event->event_key,
                'event_version' => $event->event_version,
                'event_id' => $event->uid,
                'delivery_id' => $delivery->uid,
                'integration_id' => $delivery->integration_id,
                'occurred_at' => $event->received_at->toIso8601String(),
                'sent_at' => now()->toIso8601String(),
            ],
            'data' => $transformedData,
        ];
    }

    protected function generateSignature(WebhookDelivery $delivery, array $payload, int $timestamp): string
    {
        $bodyHash = hash('sha256', json_encode($payload));
        $canonical = "{$timestamp}\n{$delivery->uid}\n{$bodyHash}";

        return 'sha256='.hash_hmac('sha256', $canonical, $delivery->subscription->signing_secret);
    }

    protected function buildHeaders($subscription, $delivery, string $signature, int $timestamp): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-WebhookHub-Event' => $delivery->event->event_key,
            'X-WebhookHub-Delivery-Id' => $delivery->uid,
            'X-WebhookHub-Timestamp' => (string) $timestamp,
            'X-WebhookHub-Signature' => $signature,
            'X-WebhookHub-Idempotency' => $delivery->event->idempotency_key,
        ];

        match ($subscription->auth_type) {
            'bearer' => $headers['Authorization'] = 'Bearer '.($subscription->auth_config['token'] ?? ''),
            'basic' => $headers['Authorization'] = 'Basic '.base64_encode(
                ($subscription->auth_config['username'] ?? '').':'.($subscription->auth_config['password'] ?? '')
            ),
            'apikey' => $headers[$subscription->auth_config['header_name'] ?? 'X-API-Key'] = $subscription->auth_config['api_key'] ?? '',
            default => null,
        };

        return $headers;
    }

    protected function logDelivery(
        WebhookDelivery $delivery,
        array $requestHeaders,
        array $requestBody,
        $response,
        int $durationMs,
        ?string $error = null
    ): void {
        $sanitizedHeaders = $this->sanitizeHeaders($requestHeaders);

        WebhookDeliveryLog::create([
            'delivery_id' => $delivery->id,
            'request_headers' => $sanitizedHeaders,
            'request_body' => $requestBody,
            'response_status' => $response?->status(),
            'response_headers' => $response ? $response->headers() : null,
            'response_body' => $response ? substr($response->body(), 0, 5000) : $error,
            'duration_ms' => $durationMs,
        ]);
    }

    protected function sanitizeHeaders(array $headers): array
    {
        $sensitive = ['Authorization', 'X-API-Key'];

        foreach ($sensitive as $key) {
            if (isset($headers[$key])) {
                $headers[$key] = '***REDACTED***';
            }
        }

        return $headers;
    }
}
