<?php

namespace Modules\Mailer\Models;

use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $uid
 * @property int $layout_id
 * @property int $lang_id
 * @property string|null $subject
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Mailer\Models\MailerLayout $layout
 * @property-read \App\Models\Lang $lang
 */
class MailerLayoutLang extends Model
{
    use HasUid;

    protected $table = 'mailer_layout_langs';

    protected $fillable = [
        'uid',
        'layout_id',
        'lang_id',
        'subject',
        'content',
    ];

    /**
     * Relación con EmailLayout
     */
    public function layout(): BelongsTo
    {
        return $this->belongsTo(MailerLayout::class, 'layout_id', 'id');
    }

    /**
     * Relación con Lang
     */
    public function lang(): BelongsTo
    {
        return $this->belongsTo('App\Models\Lang', 'lang_id', 'id');
    }
}
