<?php

namespace Modules\Document\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentStorageConfigurationHistory extends Model
{
    protected $table = 'document_storage_config_histories';

    protected $fillable = [
        'old_disk_name',
        'new_disk_name',
        'changed_by',
        'driver',
        'reason',
        'action',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who made the change.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'changed_by');
    }

    /**
     * Get recent configuration changes.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Filter by disk name.
     */
    public function scopeForDisk($query, string $diskName)
    {
        return $query->where(function ($q) use ($diskName) {
            $q->where('old_disk_name', $diskName)
                ->orWhere('new_disk_name', $diskName);
        });
    }

    /**
     * Filter by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('changed_by', $userId);
    }

    /**
     * Get a human-readable action label.
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'create' => 'Creada',
            'update' => 'Actualizada',
            'delete' => 'Eliminada',
            default => ucfirst($this->action),
        };
    }

    /**
     * Get a human-readable change description.
     */
    public function getChangeDescriptionAttribute(): string
    {
        if ($this->action === 'create') {
            return "Disco '{$this->new_disk_name}' configurado por {$this->user?->firstname}";
        }

        if ($this->action === 'delete') {
            return "Disco '{$this->old_disk_name}' eliminado";
        }

        return "Cambio de '{$this->old_disk_name}' a '{$this->new_disk_name}'";
    }
}
