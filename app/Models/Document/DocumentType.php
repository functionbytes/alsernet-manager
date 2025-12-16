<?php

namespace App\Models\Document;

use App\Models\Traits\HasUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use HasUid;

    protected $fillable = [
        'uid',
        'slug',
        'name',
        'icon',
        'color',
        'is_active',
        'sort_order',
        'sla_multiplier',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'sla_multiplier' => 'decimal:2',
        ];
    }

    // Relations
    public function requirements(): HasMany
    {
        return $this->hasMany(DocumentRequirement::class)->orderBy('sort_order');
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
                'label' => trans("documents.types.{$this->slug}.label", [], $locale),
                'description' => trans("documents.types.{$this->slug}.description", [], $locale),
                'instructions' => trans("documents.types.{$this->slug}.instructions", [], $locale),
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    // Translation methods using Laravel's localization
    public function getLabel(): string
    {
        return __("documents.types.{$this->slug}.label");
    }

    public function getDescription(): string
    {
        return __("documents.types.{$this->slug}.description");
    }

    public function getInstructions(): string
    {
        return __("documents.types.{$this->slug}.instructions");
    }

    // Magic getters for backwards compatibility
    public function getLabelAttribute(): string
    {
        return $this->getLabel();
    }

    public function getDescriptionAttribute(): string
    {
        return $this->getDescription();
    }

    public function getInstructionsAttribute(): string
    {
        return $this->getInstructions();
    }

    // Static method to get by slug
    public static function getBySlug(string $slug): ?self
    {
        return self::where('slug', $slug)
            ->with('requirements')
            ->first();
    }

    // Get required documents array for current language
    public function getRequiredDocuments(): array
    {
        $docs = [];

        foreach ($this->requirements as $requirement) {
            $docs[$requirement->key] = $requirement->getName();
        }

        return $docs;
    }

    // For backwards compatibility with old getByType method
    public static function getByType(string $type): ?self
    {
        return self::getBySlug($type);
    }
}
