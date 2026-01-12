<?php

namespace Modules\Event\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventBanner extends Model
{
    use HasFactory;

    protected $connection = 'prestashop';

    protected $table = 'aalv_Alsernet_event_banners';

    protected $fillable = [
        'event_id',
        'banner_id',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
