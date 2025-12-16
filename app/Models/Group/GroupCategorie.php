<?php

namespace App\Models\Group;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $group_id
 * @property int $category_id
 * @property-read \App\Models\Group\Group $groups
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupCategorie newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupCategorie newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupCategorie query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupCategorie whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupCategorie whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupCategorie whereId($value)
 * @mixin \Eloquent
 */
class GroupCategorie extends Model
{
    use HasFactory;

    protected $table = 'ticket_groups_categories';

    protected $fillable = [
        'group_id',
        'categorie_id',
    ];

    public function groups()
    {
        return $this->belongsTo('App\Models\Group\Group', 'group_id', 'id');
    }

}
