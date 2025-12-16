<?php

namespace App\Models\Mail;

use App\Library\Traits\HasUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $uid
 * @property int $email_template_id
 * @property int $lang_id
 * @property string|null $subject
 * @property string|null $preheader
 * @property string|null $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Mail\MailTemplate|null $emailTemplate
 * @property-read \App\Models\Lang|null $lang
 */
class MailTemplateLang extends Model
{
    use HasUid;

    protected $table = 'mail_template_langs';

    protected $fillable = [
        'uid',
        'mail_template_id',
        'lang_id',
        'subject',
        'preheader',
        'content',
    ];

    /**
     * Relación con EmailTemplate
     */
    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(MailTemplate::class, 'mail_template_id', 'id');
    }

    /**
     * Relación con Lang
     */
    public function lang(): BelongsTo
    {
        return $this->belongsTo('App\Models\Lang', 'lang_id', 'id');
    }
}
