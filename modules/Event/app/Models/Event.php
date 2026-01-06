<?php

namespace Modules\Event\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Event extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    // Note: In production, this should connect to the remote PrestaShop database via 'prestashop' connection
    // For now using default connection for development
    protected $table = 'aalv_Alsernet_event_manager';

    protected static $recordEvents = ['deleted', 'updated', 'created'];

    protected $fillable = [
        'uid',
        'title',
        'color_flag',
        'filter_flag',
        'management_flag',
        'priority_flag',
        'color_buttom',
        'hover_buttom',
        'cms',
        'featured',
        'amazing',
        'available',
        'completed',
        'iva',
        'processing',
        'processed',
        'banners_unique',
        'banners',
        'banners_backup',
        'start_at',
        'end_at',
        'observations',
        'external_id',
        'sync_status',
        'sync_timestamp',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'amazing' => 'boolean',
        'available' => 'boolean',
        'completed' => 'boolean',
        'processing' => 'boolean',
        'processed' => 'boolean',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'sync_timestamp' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "Event has been {$eventName}");
    }

    public function scopeDescending($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeAscending($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->orderBy('created_at', 'asc');
    }

    public function scopeId($query, $id): ?Model
    {
        return $query->where('id', $id)->first();
    }

    public function scopeUid($query, $uid): ?Model
    {
        return $query->where('uid', $uid)->first();
    }

    public function categories(): HasMany
    {
        return $this->hasMany(EventCategory::class, 'event_id');
    }

    public function banners(): HasMany
    {
        return $this->hasMany(EventBanner::class, 'event_id');
    }

    public function langs(): HasMany
    {
        return $this->hasMany(EventLang::class, 'id_event');
    }

    public function observations(): HasMany
    {
        return $this->hasMany(EventObservation::class, 'event_id');
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(EventSyncLog::class, 'event_id');
    }
}
