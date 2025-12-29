<?php

namespace Modules\Helpdesk\Models;

use App\Models\Traits\HasUid;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasUid, SoftDeletes;

    protected $table = 'helpdesk_tickets';

    protected $fillable = [
        'uid',
        'ticket_number',
        'subject',
        'description',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_id',
        'category_id',
        'priority_id',
        'status_id',
        'assigned_to',
        'assigned_at',
        'closed_at',
        'closed_by',
        'due_at',
        'sla_policy_id',
        'sla_breached',
        'first_response_at',
        'last_activity_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'closed_at' => 'datetime',
            'due_at' => 'datetime',
            'first_response_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'sla_breached' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /**
     * Customer who created the ticket
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Category of the ticket
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Priority of the ticket
     */
    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class, 'priority_id');
    }

    /**
     * Status of the ticket
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    /**
     * Agent assigned to the ticket
     */
    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * SLA policy applied to the ticket
     */
    public function slaPolicy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class, 'sla_policy_id');
    }

    /**
     * Messages in the ticket
     */
    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class, 'ticket_id');
    }

    /**
     * Assignment history
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(TicketAssignment::class, 'ticket_id');
    }

    /**
     * Ticket history
     */
    public function history(): HasMany
    {
        return $this->hasMany(TicketHistory::class, 'ticket_id');
    }

    /**
     * Scope for open tickets
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereHas('status', function ($q) {
            $q->where('type', '!=', 'closed');
        });
    }

    /**
     * Scope for tickets assigned to a specific user
     */
    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope for overdue tickets
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_at', '<', now())
            ->whereNull('closed_at');
    }

    /**
     * Scope for SLA breach tickets
     */
    public function scopeSlaBreach(Builder $query): Builder
    {
        return $query->where('sla_breached', true);
    }

    /**
     * Generate unique ticket number
     */
    public function generateTicketNumber(): string
    {
        $prefix = 'TKT';
        $date = now()->format('Ymd');
        $sequence = static::whereDate('created_at', today())->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    /**
     * Check if ticket is overdue
     */
    public function isOverdue(): bool
    {
        if (! $this->due_at || $this->closed_at) {
            return false;
        }

        return $this->due_at->isPast();
    }

    /**
     * Calculate due date based on SLA policy
     */
    public function calculateDueDate(): ?Carbon
    {
        if (! $this->slaPolicy) {
            return null;
        }

        $hours = $this->slaPolicy->resolution_time_hours;

        if ($this->slaPolicy->business_hours_only) {
            return $this->calculateBusinessHoursDue($hours);
        }

        return $this->created_at->addHours($hours);
    }

    /**
     * Calculate due date considering business hours only
     */
    protected function calculateBusinessHoursDue(int $hours): Carbon
    {
        $businessHoursPerDay = 8;
        $businessDays = (int) ceil($hours / $businessHoursPerDay);
        $remainingHours = $hours % $businessHoursPerDay;

        $dueDate = $this->created_at->copy();

        for ($i = 0; $i < $businessDays; $i++) {
            $dueDate->addDay();
            while ($dueDate->isWeekend()) {
                $dueDate->addDay();
            }
        }

        $dueDate->addHours($remainingHours);

        return $dueDate;
    }

    /**
     * Mark first response
     */
    public function markFirstResponse(): void
    {
        if (! $this->first_response_at) {
            $this->first_response_at = now();
            $this->save();
        }
    }

    /**
     * Update last activity timestamp
     */
    public function updateLastActivity(): void
    {
        $this->last_activity_at = now();
        $this->save();
    }

    /**
     * Boot method to generate ticket number on creation
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Ticket $ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = $ticket->generateTicketNumber();
            }

            if (empty($ticket->last_activity_at)) {
                $ticket->last_activity_at = now();
            }

            if ($ticket->sla_policy_id && empty($ticket->due_at)) {
                $ticket->due_at = $ticket->calculateDueDate();
            }
        });
    }
}
