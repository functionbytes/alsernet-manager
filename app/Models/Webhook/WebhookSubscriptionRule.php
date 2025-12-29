<?php

namespace App\Models\Webhook;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookSubscriptionRule extends Model
{
    protected $fillable = [
        'subscription_id',
        'rule_type',
        'conditions',
        'transform_template',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'transform_template' => 'array',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(WebhookSubscription::class, 'subscription_id');
    }
}
