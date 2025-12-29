<?php

namespace App\Models\Webhook;

use Illuminate\Database\Eloquent\Model;

class WebhookEventCatalog extends Model
{
    protected $table = 'webhook_event_catalog';

    protected $fillable = [
        'key',
        'version',
        'description',
        'json_schema',
        'example_payload',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'json_schema' => 'array',
            'example_payload' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
