<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentSync extends Model
{
    protected $table = 'document_syncs';

    protected $fillable = [
        'key',
        'label',
        'description',
        'icon',
        'color',
        'is_active',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    /**
     * Get documents with this sync type
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'sync_id');
    }

    /**
     * Scope for active syncs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Find by key
     */
    public static function findByKey(string $key): ?self
    {
        return self::where('key', $key)->first();
    }
}
