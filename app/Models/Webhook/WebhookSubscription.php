<?php

namespace App\Models\Webhook;

use App\Models\Traits\HasUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookSubscription extends Model
{
    use HasUid;

    protected $fillable = [
        'integration_id',
        'name',
        'url',
        'is_active',
        'subscribed_events',
        'auth_type',
        'auth_config',
        'signing_secret',
        'timeout_ms',
        'max_attempts',
        'backoff_policy',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'subscribed_events' => 'array',
            'auth_config' => 'array',
            'backoff_policy' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->signing_secret)) {
                $model->signing_secret = \Illuminate\Support\Str::random(64);
            }
            if (empty($model->backoff_policy)) {
                $model->backoff_policy = [60, 300, 900, 3600, 21600, 86400];
            }
        });
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(WebhookIntegration::class, 'integration_id');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(WebhookSubscriptionRule::class, 'subscription_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class, 'subscription_id');
    }

    public function isSubscribedTo(string $eventKey): bool
    {
        return in_array($eventKey, $this->subscribed_events ?? []);
    }

    public function getBackoffDelay(int $attemptCount): int
    {
        $policy = $this->backoff_policy ?? [60, 300, 900, 3600, 21600, 86400];
        $index = min($attemptCount - 1, count($policy) - 1);

        return $policy[$index] ?? 86400;
    }
}
