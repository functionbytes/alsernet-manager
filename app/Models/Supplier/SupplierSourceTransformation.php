<?php

namespace App\Models\Supplier;

use App\Library\Traits\HasUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierSourceTransformation extends Model
{
    use HasUid;

    protected $fillable = [
        'source_id',
        'name',
        'description',
        'field_name',
        'apply_order',
        'transformation_type',
        'transformation_config',
        'apply_condition',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'transformation_config' => 'array',
            'apply_condition' => 'array',
            'is_enabled' => 'boolean',
            'apply_order' => 'integer',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(SupplierSource::class, 'source_id');
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('apply_order', 'asc');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('transformation_type', $type);
    }

    public function scopeForField($query, string $fieldName)
    {
        return $query->where(function ($q) use ($fieldName) {
            $q->where('field_name', $fieldName)
                ->orWhereNull('field_name');
        });
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('field_name');
    }

    public function isRegexReplace(): bool
    {
        return $this->transformation_type === 'regex_replace';
    }

    public function isRegexExtract(): bool
    {
        return $this->transformation_type === 'regex_extract';
    }

    public function isMapping(): bool
    {
        return $this->transformation_type === 'mapping';
    }

    public function isFormula(): bool
    {
        return $this->transformation_type === 'formula';
    }

    public function isLookup(): bool
    {
        return $this->transformation_type === 'lookup';
    }

    public function isSplit(): bool
    {
        return $this->transformation_type === 'split';
    }

    public function isJoin(): bool
    {
        return $this->transformation_type === 'join';
    }

    public function isFormat(): bool
    {
        return $this->transformation_type === 'format';
    }

    public function isCustomFunction(): bool
    {
        return $this->transformation_type === 'custom_function';
    }

    public function shouldApply(array $data): bool
    {
        if (! $this->is_enabled) {
            return false;
        }

        if (! $this->apply_condition) {
            return true;
        }

        return $this->evaluateCondition($data, $this->apply_condition);
    }

    protected function evaluateCondition(array $data, array $condition): bool
    {
        // Simple condition evaluation
        // TODO: Implement more complex condition logic
        foreach ($condition as $field => $expectedValue) {
            if (! isset($data[$field]) || $data[$field] !== $expectedValue) {
                return false;
            }
        }

        return true;
    }

    public function apply(mixed $value): mixed
    {
        return match ($this->transformation_type) {
            'regex_replace' => $this->applyRegexReplace($value),
            'regex_extract' => $this->applyRegexExtract($value),
            'mapping' => $this->applyMapping($value),
            'formula' => $this->applyFormula($value),
            'lookup' => $this->applyLookup($value),
            'split' => $this->applySplit($value),
            'join' => $this->applyJoin($value),
            'format' => $this->applyFormat($value),
            'custom_function' => $this->applyCustomFunction($value),
            default => $value,
        };
    }

    protected function applyRegexReplace(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $patterns = $this->transformation_config['patterns'] ?? [];
        foreach ($patterns as $pattern) {
            $value = preg_replace($pattern['find'], $pattern['replace'], $value);
        }

        return $value;
    }

    protected function applyRegexExtract(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $pattern = $this->transformation_config['pattern'] ?? null;
        if (! $pattern || ! preg_match($pattern, $value, $matches)) {
            return $value;
        }

        return $matches[1] ?? $value;
    }

    protected function applyMapping(mixed $value): mixed
    {
        $mapping = $this->transformation_config['mapping'] ?? [];
        $default = $this->transformation_config['default'] ?? $value;

        return $mapping[$value] ?? $default;
    }

    protected function applyFormula(mixed $value): mixed
    {
        // TODO: Implement formula evaluation
        return $value;
    }

    protected function applyLookup(mixed $value): mixed
    {
        // TODO: Implement lookup logic
        return $value;
    }

    protected function applySplit(mixed $value): array
    {
        if (! is_string($value)) {
            return (array) $value;
        }

        $delimiter = $this->transformation_config['delimiter'] ?? ',';

        return explode($delimiter, $value);
    }

    protected function applyJoin(mixed $value): string
    {
        if (! is_array($value)) {
            return (string) $value;
        }

        $delimiter = $this->transformation_config['delimiter'] ?? ',';

        return implode($delimiter, $value);
    }

    protected function applyFormat(mixed $value): string
    {
        $format = $this->transformation_config['format'] ?? '%s';

        return sprintf($format, $value);
    }

    protected function applyCustomFunction(mixed $value): mixed
    {
        // TODO: Implement custom function execution with safety checks
        return $value;
    }
}
