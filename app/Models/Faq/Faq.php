<?php

namespace App\Models\Faq;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $uid
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property int $available
 * @property int $category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Faq\FaqCategorie $categorie
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq ascending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq available()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq descending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq id($id)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq uid($uid)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Faq extends Model
{
    use HasFactory  , LogsActivity;

    protected $table = "faqs";

    protected static $recordEvents = ['deleted','updated','created'];

    protected $fillable = [
        'uid',
        'title',
        'slug',
        'description',
        'available',
        'category_id',
        'created_at',
        'updated_at'
    ];

    public function getActivitylogOptions(): LogOptions
    {

        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logFillable()
            ->setDescriptionForEvent(fn(string $eventName) => "This model has been {$eventName}");

    }

    public function scopeAvailable($query)
    {
        return $query->where('available', 1);
    }

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
        return $query->where('id' ,$id)->first();
    }

    public function scopeUid($query ,$uid)
    {
        return $query->where('uid', $uid)->first();
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo('App\Models\Faq\FaqCategorie','category_id','id');
    }

}
