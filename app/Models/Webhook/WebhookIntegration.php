<?php

namespace App\Models\Webhook;

use App\Models\Traits\HasUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookIntegration extends Model
{
    use HasUid;

    protected $fillable = [
        'name',
        'status',
        'plan',
        'daily_limit',
        'allowed_ips',
        'allowed_domains',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'allowed_ips' => 'array',
            'allowed_domains' => 'array',
        ];
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(WebhookApiKey::class, 'integration_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(WebhookEvent::class, 'integration_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(WebhookSubscription::class, 'integration_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class, 'integration_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasReachedDailyLimit(): bool
    {
        $todayEvents = $this->events()
            ->whereDate('received_at', today())
            ->count();

        return $todayEvents >= $this->daily_limit;
    }
}
