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
        'label',
        'icon',
        'color',
        'is_active',
        'sort_order',
        'sla_multiplier',
        'validation_stages',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'sla_multiplier' => 'decimal:2',
            'validation_stages' => 'array',
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

    /**
     * Get validation workflow stages for this document type
     * Returns configured stages or default ['documentacion'] if not set
     *
     * Supports two formats:
     * - Old: ['documentacion', 'licencias'] - simple array
     * - New: [{'key': 'documentacion', 'order': 1, 'conditions': {...}}] - ordered with conditions
     *
     * @return array<string> Array of ValidatorGroup keys in order
     */
    public function getValidationStages(): array
    {
        if (empty($this->validation_stages) || ! is_array($this->validation_stages)) {
            return ['documentacion']; // Default fallback
        }

        // Check if it's the new format (array of objects with 'key' property)
        if (isset($this->validation_stages[0]) && is_array($this->validation_stages[0]) && isset($this->validation_stages[0]['key'])) {
            // New format: sort by order and extract keys
            $stages = collect($this->validation_stages)
                ->sortBy('order')
                ->pluck('key')
                ->values()
                ->toArray();

            return $stages;
        }

        // Old format: simple array of keys
        return $this->validation_stages;
    }

    /**
     * Get full validation stage configuration with conditions
     * Returns array of stage objects with key, order, and conditions
     *
     * @return array<array{key: string, order: int, conditions: array}>
     */
    public function getValidationStagesWithConditions(): array
    {
        if (empty($this->validation_stages) || ! is_array($this->validation_stages)) {
            return [
                [
                    'key' => 'documentacion',
                    'order' => 1,
                    'conditions' => [],
                ],
            ];
        }

        // Check if it's the new format
        if (isset($this->validation_stages[0]) && is_array($this->validation_stages[0]) && isset($this->validation_stages[0]['key'])) {
            // New format: sort by order
            return collect($this->validation_stages)
                ->sortBy('order')
                ->values()
                ->toArray();
        }

        // Old format: convert to new format
        return collect($this->validation_stages)
            ->map(function ($key, $index) {
                return [
                    'key' => $key,
                    'order' => $index + 1,
                    'conditions' => [],
                ];
            })
            ->values()
            ->toArray();
    }

    // For backwards compatibility with old getByType method
    public static function getByType(string $type): ?self
    {
        return self::getBySlug($type);
    }
}
