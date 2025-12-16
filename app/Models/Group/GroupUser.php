<?php

namespace App\Models\Group;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property-read \App\Models\Group\Group $groups
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupUser whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupUser whereUserId($value)
 * @mixin \Eloquent
 */
class GroupUser extends Model
{
    use HasFactory;

    protected $table = 'ticket_groups_users';

    protected $fillable = [
        'group_id',
        'user_id',
    ];

    public function groups()
    {
        return $this->belongsTo('App\Models\Group\Group', 'group_id', 'id');
    }

}
