<?php

namespace Modules\Helpdesk\Models;

use App\Models\Traits\HasUid;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketHistory extends Model
{
    use HasUid;

    protected $table = 'helpdesk_ticket_history';

    public $timestamps = false;

    protected $fillable = [
        'uid',
        'ticket_id',
        'user_id',
        'action',
        'field',
        'old_value',
        'new_value',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Ticket this history entry belongs to
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    /**
     * User who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope for filtering by action
     */
    public function scopeByAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for filtering by ticket
     */
    public function scopeForTicket(Builder $query, int $ticketId): Builder
    {
        return $query->where('ticket_id', $ticketId);
    }

    /**
     * Boot method to set created_at timestamp
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (TicketHistory $history) {
            if (empty($history->created_at)) {
                $history->created_at = now();
            }
        });
    }
}
