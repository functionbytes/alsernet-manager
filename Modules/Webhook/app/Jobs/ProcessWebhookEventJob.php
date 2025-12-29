<?php

namespace App\Jobs\Webhook;

use App\Models\Webhook\WebhookDelivery;
use App\Models\Webhook\WebhookEvent;
use App\Models\Webhook\WebhookSubscription;
use App\Services\Webhook\WebhookRuleEngineService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessWebhookEventJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        protected int $eventId
    ) {}

    public function handle(WebhookRuleEngineService $ruleEngine): void
    {
        $event = WebhookEvent::find($this->eventId);

        if (! $event || $event->isProcessed()) {
            return;
        }

        $subscriptions = WebhookSubscription::where('integration_id', $event->integration_id)
            ->where('is_active', true)
            ->get()
            ->filter(fn ($sub) => $sub->isSubscribedTo($event->event_key));

        foreach ($subscriptions as $subscription) {
            if (! $ruleEngine->shouldDeliver($subscription, $event->payload)) {
                Log::info('Event filtered by rules', [
                    'subscription_id' => $subscription->id,
                    'event_id' => $event->id,
                ]);

                continue;
            }

            $transformedPayload = $ruleEngine->transformPayload($subscription, $event->payload);

            $delivery = WebhookDelivery::create([
                'uid' => Str::ulid(),
                'integration_id' => $event->integration_id,
                'subscription_id' => $subscription->id,
                'event_id' => $event->id,
                'status' => 'pending',
                'attempt_count' => 0,
            ]);

            Log::info('Delivery created', [
                'delivery_id' => $delivery->id,
                'subscription_id' => $subscription->id,
                'event_id' => $event->id,
            ]);

            DeliverWebhookJob::dispatch($delivery->id, $transformedPayload)
                ->onQueue('deliveries');
        }

        $event->markAsProcessed();
    }
}
