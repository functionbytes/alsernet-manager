<?php

namespace Modules\Supplier\Models\Source;

use App\Models\User;
use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierSourceConfiguration extends Model
{
    use HasUid;

    protected $fillable = [
        'source_id',
        'config_type',
        'config_data',
        'config_schema_version',
        'is_valid',
        'validation_errors',
        'last_validated_at',
        'is_enabled',
        'priority',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'config_data' => 'array',
            'validation_errors' => 'array',
            'is_valid' => 'boolean',
            'is_enabled' => 'boolean',
            'last_validated_at' => 'datetime',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(SupplierSource::class, 'source_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeValid($query)
    {
        return $query->where('is_valid', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('config_type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    public function validateSchema(): void
    {
        // TODO: Implement schema validation logic
        // This should validate config_data against the schema for the config_type
    }

    public function isConnectionConfig(): bool
    {
        return $this->config_type === 'connection';
    }

    public function isAuthenticationConfig(): bool
    {
        return $this->config_type === 'authentication';
    }

    public function isExtractionConfig(): bool
    {
        return $this->config_type === 'extraction';
    }

    public function isScheduleConfig(): bool
    {
        return $this->config_type === 'schedule';
    }

    public function isRetryConfig(): bool
    {
        return $this->config_type === 'retry';
    }

    public function isProxyConfig(): bool
    {
        return $this->config_type === 'proxy';
    }

    public function isValidationConfig(): bool
    {
        return $this->config_type === 'validation';
    }

    public function markAsInvalid(array $errors): void
    {
        $this->update([
            'is_valid' => false,
            'validation_errors' => $errors,
            'last_validated_at' => now(),
        ]);
    }

    public function markAsValid(): void
    {
        $this->update([
            'is_valid' => true,
            'validation_errors' => [],
            'last_validated_at' => now(),
        ]);
    }
}
