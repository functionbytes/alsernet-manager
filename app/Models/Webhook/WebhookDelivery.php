<?php

namespace App\Models\Webhook;

use App\Models\Traits\HasUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookDelivery extends Model
{
    use HasUid;

    protected $fillable = [
        'integration_id',
        'subscription_id',
        'event_id',
        'status',
        'attempt_count',
        'next_retry_at',
        'last_error',
        'last_http_status',
        'last_latency_ms',
    ];

    protected function casts(): array
    {
        return [
            'next_retry_at' => 'datetime',
        ];
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(WebhookIntegration::class, 'integration_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(WebhookSubscription::class, 'subscription_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(WebhookEvent::class, 'event_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WebhookDeliveryLog::class, 'delivery_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRetryable($query)
    {
        return $query->where('status', 'failed')
            ->where('next_retry_at', '<=', now());
    }

    public function scopeDead($query)
    {
        return $query->where('status', 'dead');
    }

    public function incrementAttempt(): void
    {
        $this->increment('attempt_count');
    }

    public function markAsSuccess(int $httpStatus, int $latencyMs): void
    {
        $this->update([
            'status' => 'success',
            'last_http_status' => $httpStatus,
            'last_latency_ms' => $latencyMs,
            'last_error' => null,
        ]);
    }

    public function markAsFailed(string $error, ?int $httpStatus, int $latencyMs): void
    {
        $backoffDelay = $this->subscription->getBackoffDelay($this->attempt_count + 1);

        $status = ($this->attempt_count + 1 >= $this->subscription->max_attempts)
            ? 'dead'
            : 'failed';

        $this->update([
            'status' => $status,
            'last_error' => $error,
            'last_http_status' => $httpStatus,
            'last_latency_ms' => $latencyMs,
            'next_retry_at' => $status === 'failed' ? now()->addSeconds($backoffDelay) : null,
        ]);
    }
}
