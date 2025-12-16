<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentStatusTransitionLog extends Model
{
    protected $table = 'document_status_transition_logs';

    protected $fillable = [
        'document_id',
        'transition_id',
        'from_status_id',
        'to_status_id',
        'performed_by',
        'reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * Relation: Document being transitioned
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    /**
     * Relation: The transition definition used
     */
    public function transition(): BelongsTo
    {
        return $this->belongsTo(DocumentStatusTransition::class, 'transition_id');
    }

    /**
     * Relation: From status
     */
    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(DocumentStatus::class, 'from_status_id');
    }

    /**
     * Relation: To status
     */
    public function toStatus(): BelongsTo
    {
        return $this->belongsTo(DocumentStatus::class, 'to_status_id');
    }

    /**
     * Relation: User who performed the transition
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'performed_by');
    }

    /**
     * Scope: Get transition logs for a document
     */
    public function scopeForDocument($query, int $documentId)
    {
        return $query->where('document_id', $documentId);
    }

    /**
     * Scope: Get recent transition logs
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
