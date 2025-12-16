<?php

namespace App\Subscriber;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriberListUser extends Model
{
    protected $table = 'subscriber_list_users';

    protected $fillable = [
        'list_id',
        'subscriber_id',
        'created_at',
        'updated_at',
    ];

    public function scopeId($query, $id)
    {
        return $query->where('id', $id)->first();
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo('App\Subscriber\SubscriberList', 'list_id', 'id');
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo('App\Subscriber\Subscriber', 'subscriber_id', 'id');
    }
}
