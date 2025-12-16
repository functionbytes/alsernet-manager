<?php

namespace App\Subscriber;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriberListCategorie extends Model
{
    protected $table = 'subscriber_list_categories';

    protected $fillable = [
        'categorie_id',
        'list_id',
        'created_at',
        'updated_at',
    ];

    public function scopeId($query, $id)
    {
        return $query->where('id', $id)->first();
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo('App\Subscriber\SubscriberList', 'list_id');
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo('App\Subscriber\SubscriberCategorie', 'categorie_id');
    }
}
