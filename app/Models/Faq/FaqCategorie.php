<?php

namespace App\Models\Faq;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uid
 * @property string $title
 * @property string|null $slug
 * @property int $available
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqCategorie ascending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqCategorie available()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqCategorie descending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqCategorie id($id)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqCategorie newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqCategorie newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqCategorie query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqCategorie slug($slug)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqCategorie uid($uid)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqCategorie whereAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqCategorie whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqCategorie whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqCategorie whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqCategorie whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqCategorie whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaqCategorie whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FaqCategorie extends Model
{
    use HasFactory;

    protected $table = 'faq_categories';

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

    public function faqs(): HasMany
    {
        return $this->hasMany('App\Models\Faq\Instruction','categorie_id');
    }

}
