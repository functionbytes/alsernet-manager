<?php

namespace App\Models\Helpdesk;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversationRead extends Model
{
    use HasFactory;

    protected $connection = 'helpdesk';

    protected $table = 'helpdesk_conversation_reads';

    public $timestamps = false;

    protected $fillable = [
        'conversation_item_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Get the conversation item this read receipt belongs to
     */
    public function item()
    {
        return $this->belongsTo(ConversationItem::class, 'conversation_item_id');
    }

    /**
     * Get the user who read the message
     * Note: User model is in the default connection, not helpdesk
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id')
            ->setConnection(config('database.default'));
    }
}
