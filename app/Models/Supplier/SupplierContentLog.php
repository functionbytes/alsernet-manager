<?php

namespace App\Models\Supplier;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $content_id
 * @property string $action
 * @property string|null $previous_status
 * @property string|null $new_status
 * @property int|null $user_id
 * @property array|null $details
 * @property string|null $ip_address
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\Supplier\SupplierAiContent $content
 * @property-read \App\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContentLog byAction(string $action)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContentLog byUser(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContentLog forContent(int $contentId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContentLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContentLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContentLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContentLog recent()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContentLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContentLog whereContentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContentLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContentLog whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContentLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContentLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContentLog whereNewStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContentLog wherePreviousStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContentLog whereUserId($value)
 *
 * @mixin \Eloquent
 */
class SupplierContentLog extends Model
{
    use HasFactory;

    protected $table = 'supplier_content_logs';

    public const UPDATED_AT = null;

    protected $fillable = [
        'content_id',
        'action',
        'previous_status',
        'new_status',
        'user_id',
        'details',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'created_at' => 'datetime',
            'content_id' => 'integer',
            'user_id' => 'integer',
        ];
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(SupplierAiContent::class, 'content_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeForContent($query, int $contentId)
    {
        return $query->where('content_id', $contentId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function isAutomated(): bool
    {
        return is_null($this->user_id);
    }

    public function wasPerformedBy(?int $userId): bool
    {
        return $this->user_id === $userId;
    }

    public function getStatusChangeDescription(): ?string
    {
        if ($this->previous_status && $this->new_status) {
            return "Changed from {$this->previous_status} to {$this->new_status}";
        }

        return null;
    }
}
