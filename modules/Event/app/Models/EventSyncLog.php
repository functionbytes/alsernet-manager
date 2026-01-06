<?php

namespace Modules\Event\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSyncLog extends Model
{
    use HasFactory;

    protected $table = 'aalv_event_sync_log';

    protected $fillable = [
        'event_id',
        'external_id',
        'source',
        'action',
        'status',
        'payload',
        'error_message',
        'synced_at',
    ];

    protected $casts = [
        'payload' => 'json',
        'synced_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    /**
     * Scope to filter by sync status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by source (laravel or prestashop)
     */
    public function scopeSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope to get latest synced records
     */
    public function scopeLatestSynced($query)
    {
        return $query->where('status', 'synced')->latest('synced_at');
    }
}
