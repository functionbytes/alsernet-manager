<?php

namespace Modules\Supplier\Entities;

use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uid
 * @property int|null $supplier_id
 * @property int|null $category_id
 * @property int|null $source_id
 * @property string $scope
 * @property string $label
 * @property string $prompt_template
 * @property string $output_language
 * @property string $tone
 * @property int $priority
 * @property bool $seo_focus
 * @property array|null $required_sections
 * @property int $version
 * @property bool $is_default
 * @property bool $is_active
 * @property bool $is_template
 * @property string|null $template_category
 * @property int|null $cloned_from_template_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Supplier\Supplier|null $supplier
 * @property-read \App\Models\Supplier\SupplierSource|null $source
 * @property-read \App\Models\Supplier\SupplierPrompt|null $templateSource
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Supplier\SupplierPrompt> $clonedPrompts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Supplier\SupplierAiContent> $contents
 * @property-read int|null $contents_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt byPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt byScope(string $scope)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt defaults()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt forCategory(int $categoryId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt forSource(int $sourceId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt forSupplier(int $supplierId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt global()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt uid(string $uid)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt whereOutputLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt wherePromptTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt whereRequiredSections($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt whereScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt whereSeoFocus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt whereTone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierPrompt whereVersion($value)
 *
 * @mixin \Eloquent
 */
class SupplierPrompt extends Model
{
    use HasFactory, HasUid;

    protected $table = 'supplier_prompts';

    protected $fillable = [
        'uid',
        'supplier_id',
        'category_id',
        'source_id',
        'scope',
        'label',
        'prompt_template',
        'output_language',
        'tone',
        'priority',
        'seo_focus',
        'required_sections',
        'version',
        'is_default',
        'is_active',
        'is_template',
        'template_category',
        'cloned_from_template_id',
    ];

    protected function casts(): array
    {
        return [
            'required_sections' => 'array',
            'seo_focus' => 'boolean',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'is_template' => 'boolean',
            'priority' => 'integer',
            'version' => 'integer',
            'supplier_id' => 'integer',
            'category_id' => 'integer',
            'source_id' => 'integer',
            'cloned_from_template_id' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $prompt) {
            if (is_null($prompt->uid)) {
                $prompt->uid = (string) Str::ulid();
            }
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(SupplierSource::class, 'source_id');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(SupplierAiContent::class, 'prompt_id');
    }

    /**
     * Template source - if this prompt was cloned from a template
     */
    public function templateSource(): BelongsTo
    {
        return $this->belongsTo(SupplierPrompt::class, 'cloned_from_template_id');
    }

    /**
     * Prompts that were cloned from this template
     */
    public function clonedPrompts(): HasMany
    {
        return $this->hasMany(SupplierPrompt::class, 'cloned_from_template_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefaults($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    public function scopeByScope($query, string $scope)
    {
        return $query->where('scope', $scope);
    }

    public function scopeGlobal($query)
    {
        return $query->where('scope', 'global');
    }

    public function scopeForSupplier($query, int $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeForCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeForSource($query, int $sourceId)
    {
        return $query->where('source_id', $sourceId);
    }

    public function scopeUid($query, string $uid)
    {
        return $query->where('uid', $uid);
    }

    /**
     * Filter only templates
     */
    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    /**
     * Filter templates by category
     */
    public function scopeTemplateCategory($query, string $category)
    {
        return $query->templates()->where('template_category', $category);
    }

    /**
     * Filter non-template prompts
     */
    public function scopeNotTemplates($query)
    {
        return $query->where('is_template', false);
    }

    /**
     * Clone this template into a new prompt
     *
     * @param  array  $overrides  Fields to override (supplier_id, category_id, etc.)
     */
    public function cloneFromTemplate(array $overrides = []): self
    {
        // Prepare base data from template
        $data = [
            'label' => $this->label,
            'prompt_template' => $this->prompt_template,
            'scope' => $this->scope,
            'content_type' => $this->content_type ?? 'description',
            'output_language' => $this->output_language,
            'tone' => $this->tone,
            'seo_focus' => $this->seo_focus,
            'priority' => $this->priority,
            'version' => 1,
            'is_active' => true,
            'is_template' => false, // Cloned prompts are NOT templates
            'cloned_from_template_id' => $this->id,
            'required_sections' => $this->required_sections,
            'notes' => $this->notes ?? null,
        ];

        // Merge with overrides
        $data = array_merge($data, $overrides);

        // Generate new UID
        $data['uid'] = (string) Str::ulid();

        // Create new prompt
        return self::create($data);
    }

    /**
     * Resolve the best matching prompt based on priority system.
     *
     * Priority order (highest to lowest):
     * 1. Prompt specific to SOURCE + SUPPLIER + CATEGORY
     * 2. Prompt specific to SUPPLIER + CATEGORY
     * 3. Prompt specific to SOURCE + SUPPLIER
     * 4. Prompt specific to SUPPLIER
     * 5. Prompt specific to CATEGORY
     * 6. Global default prompt
     */
    public static function resolvePrompt(
        ?int $supplierId = null,
        ?int $categoryId = null,
        ?int $sourceId = null
    ): ?self {
        $query = self::query()->active()->byPriority();

        if ($supplierId && $categoryId && $sourceId) {
            $prompt = (clone $query)
                ->where('supplier_id', $supplierId)
                ->where('category_id', $categoryId)
                ->where('source_id', $sourceId)
                ->where('scope', 'source')
                ->first();
            if ($prompt) {
                return $prompt;
            }
        }

        if ($supplierId && $categoryId) {
            $prompt = (clone $query)
                ->where('supplier_id', $supplierId)
                ->where('category_id', $categoryId)
                ->where('scope', 'supplier')
                ->first();
            if ($prompt) {
                return $prompt;
            }
        }

        if ($supplierId && $sourceId) {
            $prompt = (clone $query)
                ->where('supplier_id', $supplierId)
                ->where('source_id', $sourceId)
                ->where('scope', 'source')
                ->first();
            if ($prompt) {
                return $prompt;
            }
        }

        if ($supplierId) {
            $prompt = (clone $query)
                ->where('supplier_id', $supplierId)
                ->where('scope', 'supplier')
                ->first();
            if ($prompt) {
                return $prompt;
            }
        }

        if ($categoryId) {
            $prompt = (clone $query)
                ->where('category_id', $categoryId)
                ->where('scope', 'category')
                ->first();
            if ($prompt) {
                return $prompt;
            }
        }

        return (clone $query)
            ->where('scope', 'global')
            ->where('is_default', true)
            ->first();
    }

    public function render(array $variables = []): string
    {
        $template = $this->prompt_template;

        foreach ($variables as $key => $value) {
            $template = str_replace('{{'.$key.'}}', $value, $template);
        }

        return $template;
    }

    public function createNewVersion(): self
    {
        $newPrompt = $this->replicate();
        $newPrompt->version = $this->version + 1;
        $newPrompt->uid = (string) Str::ulid();
        $newPrompt->save();

        return $newPrompt;
    }
}
