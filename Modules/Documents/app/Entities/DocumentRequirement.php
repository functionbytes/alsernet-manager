<?php

namespace Modules\Documents\Entities;

use App\Models\Lang;
use App\Models\Traits\HasUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentRequirement extends Model
{
    use HasUid;

    protected $table = 'document_type_requirements';

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

    public function translations(): HasMany
    {
        return $this->hasMany(DocumentRequirementTranslation::class);
    }

    // Get translation for specific language
    public function translate(?int $langId = null)
    {
        $langId = $langId ?? Lang::getDefaultLangId();

        return $this->translations()->where('lang_id', $langId)->first();
    }

    // Translation methods using database translations
    public function getName(?int $langId = null): string
    {
        $translation = $this->translate($langId);

        return $translation?->name ?? $this->key;
    }

    public function getHelpText(?int $langId = null): string
    {
        $translation = $this->translate($langId);

        return $translation?->help_text ?? '';
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
