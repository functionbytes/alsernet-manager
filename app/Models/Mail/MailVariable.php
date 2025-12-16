<?php

namespace App\Models\Mail;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MailVariable extends Model
{
    protected $fillable = [
        'uid',
        'key',
        'name',
        'description',
        'example_value',
        'category',
        'module',
        'is_system',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_enabled' => 'boolean',
        ];
    }

    /**
     * Get translations for this variable
     */
    public function translations(): HasMany
    {
        return $this->hasMany(MailVariableLang::class, 'mail_variable_id');
    }

    /**
     * Get translation for a specific language
     */
    public function translate(int $langId): ?MailVariableLang
    {
        return $this->translations()
            ->where('lang_id', $langId)
            ->first();
    }

    /**
     * Boot method to generate UID on creation
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->uid) {
                $model->uid = (string) Str::uuid();
            }
        });
    }
}
