<?php

namespace App\Models\Webhook;

use App\Models\Traits\HasUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookEvent extends Model
{
    use HasUid;

    protected $fillable = [
        'integration_id',
        'event_key',
        'event_version',
        'external_event_id',
        'idempotency_key',
        'payload',
        'payload_hash',
        'received_at',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(WebhookIntegration::class, 'integration_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class, 'event_id');
    }

    public static function generateHash(array $payload): string
    {
        return hash('sha256', json_encode($payload));
    }

    public function isProcessed(): bool
    {
        return $this->processed_at !== null;
    }

    public function markAsProcessed(): void
    {
        $this->update(['processed_at' => now()]);
    }
}
