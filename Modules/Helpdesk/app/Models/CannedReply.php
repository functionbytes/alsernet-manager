<?php

namespace Modules\Helpdesk\Models;

use App\Models\Traits\HasUid;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CannedReply extends Model
{
    use HasUid, SoftDeletes;

    protected $table = 'helpdesk_canned_replies';

    protected $fillable = [
        'uid',
        'title',
        'slug',
        'content',
        'content_html',
        'category_id',
        'is_active',
        'usage_count',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'usage_count' => 'integer',
        ];
    }

    /**
     * Category this canned reply belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * User who created this canned reply
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for active canned replies
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
