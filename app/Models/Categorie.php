<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $uid
 * @property string $title
 * @property int $available
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Subscriber\SubscriberList> $lists
 * @property-read int|null $lists_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Subscriber\SubscriberList> $subscriberlists
 * @property-read int|null $subscriberlists_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie ascending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie available()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie descending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie uid($uid)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie whereAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Categorie extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'title',
        'created_at',
        'updated_at',
    ];

    public function scopeDescending($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeAscending($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    public function scopeUid($query, $uid)
    {
        return $query->where('uid', $uid)->first();
    }

    public function scopeAvailable($query)
    {
        return $query->where('available', 1);
    }

    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(
            'App\Subscriber\SubscriberList',
            'subscriber_list_categories',
            'categorie_id',
            'list_id'
        );
    }

    public function subscriberlists(): BelongsToMany
    {
        return $this->belongsToMany(
            'App\Subscriber\SubscriberList',
            'categorie_subscriber_list',  // Pivot table name
            'categorie_id',  // Foreign key on the pivot table for this model
            'subscriber_list_id' // Foreign key on the pivot table for the related model
        );
    }

    public function listsByLang($langId)
    {
        return $this->lists()->where('subscriber_lists.lang_id', $langId);
    }
}
