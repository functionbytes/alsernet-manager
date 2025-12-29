<?php

namespace App\Models\Webhook;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WebhookApiKey extends Model
{
    protected $fillable = [
        'integration_id',
        'key',
        'secret',
        'name',
        'permissions',
        'rate_limit_per_minute',
        'revoked_at',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'revoked_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->key)) {
                $model->key = 'whk_'.Str::random(40);
            }
            if (empty($model->secret)) {
                $rawSecret = Str::random(64);
                $model->secret = hash('sha256', $rawSecret);
            }
        });
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(WebhookIntegration::class, 'integration_id');
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function validateSecret(string $providedSecret): bool
    {
        return hash_equals($this->secret, hash('sha256', $providedSecret));
    }

    public function touchLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
