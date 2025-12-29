<?php

namespace App\Models\Supplier;

use app\Library\Traits\HasUid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SupplierCredential Model
 *
 * Securely stores encrypted credentials for automation sources.
 *
 * @property int $id
 * @property string $uid ULID unique identifier
 * @property int|null $supplier_id FK to suppliers
 * @property int|null $source_id FK to supplier_sources
 * @property string $credential_type Type: ftp, api, oauth, basic_auth, proxy
 * @property string $name Identifier name
 * @property string $credentials Encrypted JSON credentials
 * @property \Illuminate\Support\Carbon|null $expires_at Expiration date
 * @property \Illuminate\Support\Carbon|null $last_used_at Last time used
 * @property bool $is_valid If credentials are valid
 * @property string|null $validation_error Last validation error
 * @property int|null $created_by FK to users
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Supplier\Supplier|null $supplier
 * @property-read \App\Models\Supplier\SupplierSource|null $source
 * @property-read \App\Models\User|null $creator
 */
class SupplierCredential extends Model
{
    use HasUid;

    protected $fillable = [
        'supplier_id',
        'source_id',
        'credential_type',
        'name',
        'credentials',
        'expires_at',
        'last_used_at',
        'is_valid',
        'validation_error',
        'created_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_valid' => 'boolean',
        'credentials' => 'encrypted:json',
    ];

    /**
     * Relationship to supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Relationship to source
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(SupplierSource::class, 'source_id');
    }

    /**
     * Relationship to user who created the credentials
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scope: Filter by credential type
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('credential_type', $type);
    }

    /**
     * Scope: Filter by supplier
     */
    public function scopeForSupplier(Builder $query, int $supplierId): Builder
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope: Filter by source
     */
    public function scopeForSource(Builder $query, int $sourceId): Builder
    {
        return $query->where('source_id', $sourceId);
    }

    /**
     * Scope: Filter valid credentials
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->where('is_valid', true);
    }

    /**
     * Scope: Filter invalid credentials
     */
    public function scopeInvalid(Builder $query): Builder
    {
        return $query->where('is_valid', false);
    }

    /**
     * Scope: Filter expiring credentials
     */
    public function scopeExpiringSoon(Builder $query, int $days = 7): Builder
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays($days))
            ->where('expires_at', '>', now());
    }

    /**
     * Scope: Filter expired credentials
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    /**
     * Check if credentials are valid
     */
    public function isValid(): bool
    {
        return $this->is_valid && ! $this->isExpired();
    }

    /**
     * Check if credentials are expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if credentials expire soon
     */
    public function expiresSoon(int $days = 7): bool
    {
        return $this->expires_at
            && $this->expires_at->isFuture()
            && $this->expires_at->diffInDays(now()) <= $days;
    }

    /**
     * Mark credentials as used
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Mark credentials as invalid
     */
    public function markAsInvalid(string $error): void
    {
        $this->update([
            'is_valid' => false,
            'validation_error' => $error,
        ]);
    }

    /**
     * Mark credentials as valid
     */
    public function markAsValid(): void
    {
        $this->update([
            'is_valid' => true,
            'validation_error' => null,
        ]);
    }

    /**
     * Get credentials as array
     */
    public function getCredentialsArray(): array
    {
        return $this->credentials ?? [];
    }

    /**
     * Get a specific credential value
     */
    public function getCredentialValue(string $key, mixed $default = null): mixed
    {
        $credentials = $this->getCredentialsArray();

        return $credentials[$key] ?? $default;
    }
}
