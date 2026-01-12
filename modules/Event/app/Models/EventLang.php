<?php

namespace Modules\Event\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventLang extends Model
{
    use HasFactory;

    protected $connection = 'prestashop';

    protected $table = 'aalv_Alsernet_event_manager_lang';

    protected $fillable = [
        'id_event',
        'id_lang',
        'title',
        'special',
        'url_special',
        'title_special',
        'buttom_all',
        'buttom_one',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event');
    }
}
