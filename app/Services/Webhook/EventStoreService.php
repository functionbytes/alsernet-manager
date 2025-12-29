<?php

namespace App\Services\Webhook;

use App\Models\Webhook\WebhookEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EventStoreService
{
    public function storeEvent(array $data): WebhookEvent
    {
        $idempotencyKey = $data['idempotency_key'] ?? null;

        if ($idempotencyKey) {
            $existing = WebhookEvent::where('idempotency_key', $idempotencyKey)->first();

            if ($existing) {
                Log::info('Idempotent request detected', [
                    'idempotency_key' => $idempotencyKey,
                    'event_id' => $existing->id,
                ]);

                return $existing;
            }
        }

        return WebhookEvent::create([
            'integration_id' => $data['integration_id'],
            'event_key' => $data['event_key'],
            'event_version' => $data['event_version'] ?? 'v1',
            'external_event_id' => $data['external_event_id'] ?? null,
            'idempotency_key' => $idempotencyKey ?? Str::uuid(),
            'payload' => $data['payload'],
            'payload_hash' => WebhookEvent::generateHash($data['payload']),
            'received_at' => now(),
        ]);
    }
}
