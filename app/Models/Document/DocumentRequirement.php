<?php

namespace App\Models\Document;

use App\Models\Traits\HasUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRequirement extends Model
{
    use HasUid;

    protected $fillable = [
        'uid',
        'document_type_id',
        'key',
        'is_required',
        'accepts_multiple',
        'max_file_size',
        'allowed_extensions',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'accepts_multiple' => 'boolean',
            'max_file_size' => 'integer',
            'allowed_extensions' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    // Get translations using barryvdh/laravel-translation-manager
    public function getTranslationsList()
    {
        $langs = \App\Models\Lang::all();
        $translations = [];

        foreach ($langs as $lang) {
            $locale = $lang->locale ?? $lang->code;
            $translations[] = (object) [
                'id' => null,
                'lang_id' => $lang->id,
                'name' => trans("documents.requirements.{$this->key}.name", [], $locale),
                'help_text' => trans("documents.requirements.{$this->key}.help_text", [], $locale),
            ];
        }

        return collect($translations);
    }

    // Get translation for specific language
    public function translate(?int $langId = null)
    {
        $langId = $langId ?? \App\Models\Lang::getDefaultLangId();

        return $this->getTranslationsList()->firstWhere('lang_id', $langId);
    }

    // Translation methods using Laravel's localization
    public function getName(): string
    {
        return __("documents.requirements.{$this->key}.name");
    }

    public function getHelpText(): string
    {
        return __("documents.requirements.{$this->key}.help_text");
    }

    // Magic getter for backwards compatibility
    public function getNameAttribute(): string
    {
        return $this->getName();
    }

    public function getHelpTextAttribute(): string
    {
        return $this->getHelpText();
    }
}
