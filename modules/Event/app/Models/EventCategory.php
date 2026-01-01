<?php

namespace Modules\Event\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventCategory extends Model
{
    use HasFactory;

    protected $connection = 'prestashop';

    protected $table = 'aalv_Alsernet_event_manager_categories';

    protected $fillable = [
        'event_id',
        'category_id',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
