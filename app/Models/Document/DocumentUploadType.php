<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $key Identifier (automatic, manual)
 * @property string $label Display name
 * @property string|null $description Description of the upload type
 * @property string|null $icon Icon class
 * @property string|null $color Color code
 * @property bool $is_active Whether this upload type is active
 * @property int $order Display order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentUploadType active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentUploadType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentUploadType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentUploadType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentUploadType ordered()
 *
 * @mixin \Eloquent
 */
class DocumentUploadType extends Model
{
    protected $table = 'document_upload_types';

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

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'upload_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    public static function getByKey(string $key): ?self
    {
        return static::where('key', $key)->first();
    }
}
