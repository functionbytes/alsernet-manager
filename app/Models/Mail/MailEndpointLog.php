<?php

namespace App\Models\Mail;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailEndpointLog extends Model
{
    protected $fillable = [
        'mail_endpoint_id',
        'payload',
        'status',
        'error_message',
        'recipient_email',
        'mail_subject',
        'sent_at',
        'job_id',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the associated email endpoint
     */
    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(MailEndpoint::class, 'mail_endpoint_id');
    }
}
