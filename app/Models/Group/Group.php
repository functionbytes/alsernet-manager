<?php

namespace App\Models\Group;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uid
 * @property string $slug
 * @property string $title
 * @property int $available
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Group\GroupCategorie> $categorie
 * @property-read int|null $categorie_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ticket\TicketCategorie> $categories
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Group\GroupUser> $user
 * @property-read int|null $user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group ascending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group available()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group descending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group id($id)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group slug($slug)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group uid($uid)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Group extends Model
{
    use HasFactory;

    protected $table = 'ticket_groups';

    protected $fillable = [
        'uid',
        'title',
        'slug',
        'available',
        'created_at',
        'updated_at'
    ];

    public function scopeDescending($query)
{
    return $query->orderBy('created_at', 'desc');
}

public function scopeAscending($query)
{
    return $query->orderBy('created_at', 'asc');
}

    public function scopeId($query ,$id)
    {
        return $query->where('id', $id)->first();
    }

    public function scopeSlug($query ,$slug)
    {
        return $query->where('slug', $slug)->first();
    }

    public function scopeUid($query ,$uid)
    {
        return $query->where('uid', $uid)->first();
    }

    public function scopeAvailable($query)
    {
        return $query->where('available', 1);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'ticket_groups_users', 'group_id', 'user_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Ticket\TicketCategorie', 'ticket_groups_categories', 'group_id', 'category_id');
    }

    public function user()
    {
        return $this->hasMany('App\Models\Group\GroupUser', 'group_id');
    }

    public function categorie()
    {
        return $this->hasMany('App\Models\Group\GroupCategorie', 'group_id');
    }

}
