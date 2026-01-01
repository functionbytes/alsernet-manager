<?php

namespace Modules\Document\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Document\Traits\HasUid;

class DocumentType extends Model
{
    use HasUid;

    protected $table = 'document_types';

    protected $fillable = [
        'uid',
        'slug',
        'label',
        'description',
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

    public function requirements(): HasMany
    {
        return $this->hasMany(DocumentRequirement::class)->orderBy('sort_order');
    }

    public function getTranslationsList()
    {
        $langs = Lang::all();
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
        $langId = $langId ?? Lang::getDefaultLangId();

        return $this->getTranslationsList()->firstWhere('lang_id', $langId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

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

    public function getLabelAttribute(): string
    {
        $value = $this->attributes['label'] ?? null;

        return $value ?: $this->getLabel();
    }

    public function getDescriptionAttribute(): string
    {
        $value = $this->attributes['description'] ?? null;

        return $value ?: $this->getDescription();
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

    public function getRequiredDocuments(): array
    {
        $docs = [];

        foreach ($this->requirements as $requirement) {
            $docs[$requirement->key] = $requirement->getName();
        }

        return $docs;
    }

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

    public static function getByType(string $type): ?self
    {
        return self::getBySlug($type);
    }

    public static function getValidBlockadeTypes(): array
    {
        return self::active()
            ->orderBy('slug')
            ->pluck('slug')
            ->toArray();
    }
}
