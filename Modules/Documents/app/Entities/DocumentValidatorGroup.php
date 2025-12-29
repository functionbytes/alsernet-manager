<?php

namespace Modules\Documents\Entities;

use app\Library\Traits\HasUid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class DocumentValidatorGroup extends Model
{
    use HasUid;

    protected $table = 'document_validator_groups';

    protected $fillable = [
        'name',
        'key',
        'description',
        'assignment_mode',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Auto-increment sort_order for new groups
        static::creating(function (self $group) {
            if (is_null($group->sort_order)) {
                $maxOrder = static::max('sort_order') ?? 0;
                $group->sort_order = $maxOrder + 1;
            }
        });

        // Ensure only one default group
        static::saving(function (self $group) {
            if ($group->is_default && $group->isDirty('is_default')) {
                static::where('id', '!=', $group->id ?? 0)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get the users that belong to the validator group.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'validator_group_user',
            'validator_group_id',
            'user_id'
        )->withPivot('priority', 'created_at');
    }

    /**
     * Get users with primary priority.
     */
    public function primaryUsers(): BelongsToMany
    {
        return $this->users()->wherePivot('priority', 'primary');
    }

    /**
     * Get users with backup priority.
     */
    public function backupUsers(): BelongsToMany
    {
        return $this->users()->wherePivot('priority', 'backup');
    }

    /**
     * Get configurations for this validator group.
     */
    public function configurations(): HasMany
    {
        return $this->hasMany(DocumentValidatorGroupConfiguration::class);
    }

    /**
     * Get configuration change history for this validator group.
     */
    public function configurationHistory(): HasMany
    {
        return $this->hasMany(DocumentValidatorGroupConfigurationHistory::class);
    }

    // =========================================================================
    // STATIC FINDERS
    // =========================================================================

    /**
     * Find the default group.
     */
    public static function findDefault(): ?self
    {
        return static::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Find a group by its unique key.
     */
    public static function findByKey(string $key): ?self
    {
        return static::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all active groups ordered by sort_order.
     */
    public static function getActiveOrdered(): Collection
    {
        return static::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get groups by their keys in order.
     *
     * @param  array<string>  $keys
     */
    public static function getByKeysInOrder(array $keys): Collection
    {
        $groups = static::query()
            ->whereIn('key', $keys)
            ->where('is_active', true)
            ->get()
            ->keyBy('key');

        // Return in the order of keys provided
        return collect($keys)
            ->map(fn (string $key) => $groups->get($key))
            ->filter();
    }

    // =========================================================================
    // ASSIGNMENT LOGIC
    // =========================================================================

    /**
     * Get the next user for assignment based on assignment mode.
     *
     * @param  string|null  $entityType  Optional entity type for load balancing
     */
    public function getNextUser(?string $entityType = null): ?User
    {
        $users = $this->primaryUsers()->get();

        if ($users->isEmpty()) {
            $users = $this->backupUsers()->get();
        }

        if ($users->isEmpty()) {
            return null;
        }

        return match ($this->assignment_mode) {
            'round_robin' => $this->getNextUserRoundRobin($users),
            'load_balanced' => $this->getNextUserLoadBalanced($users, $entityType),
            default => $users->first(),
        };
    }

    /**
     * Get next user using round robin based on pivot created_at.
     */
    protected function getNextUserRoundRobin(Collection $users): User
    {
        return $users->sortBy(function (User $user) {
            return $user->pivot->created_at ?? now();
        })->first();
    }

    /**
     * Get next user using load balancing.
     * The entity using this must implement countPendingAssignments().
     */
    protected function getNextUserLoadBalanced(Collection $users, ?string $entityType = null): User
    {
        if ($entityType && class_exists($entityType)) {
            return $users->sortBy(function (User $user) use ($entityType) {
                if (method_exists($entityType, 'countPendingForUser')) {
                    return $entityType::countPendingForUser($user->id);
                }

                return 0;
            })->first();
        }

        // Fallback to first user if no entity type provided
        return $users->first();
    }

    /**
     * Check if a user belongs to this group.
     */
    public function hasUser(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $this->users()->where('users.id', $userId)->exists();
    }

    /**
     * Check if a user can validate for this group.
     */
    public function canUserValidate(User|int $user): bool
    {
        return $this->is_active && $this->hasUser($user);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope to filter by key.
     */
    public function scopeKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Scope to get only active groups.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get groups ordered by their sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // =========================================================================
    // UTILITIES
    // =========================================================================

    /**
     * Reorder groups based on an array of IDs.
     *
     * @param  array<int>  $ids
     */
    public static function reorder(array $ids): void
    {
        foreach ($ids as $order => $id) {
            static::where('id', $id)->update(['sort_order' => $order + 1]);
        }
    }

    /**
     * Get the route key name for route model binding.
     * Uses 'uid' for public-facing routes.
     */
    public function getRouteKeyName(): string
    {
        return 'uid';
    }

    /**
     * Get email action configurations for a specific user based on their groups.
     * Returns merged configurations from all groups the user belongs to.
     * If a user belongs to multiple groups, the most permissive setting wins (OR logic).
     *
     * @return array<string, bool>
     */
    public static function getEmailConfigurationsForUser(User|int $user): array
    {
        $userId = $user instanceof User ? $user->id : $user;

        // Get all active groups the user belongs to
        $userGroups = static::query()
            ->whereHas('users', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->where('is_active', true)
            ->with(['configurations' => function ($q) {
                $q->where('category', 'email_actions')
                    ->where('is_active', true);
            }])
            ->get();

        // If user doesn't belong to any group, return default (all disabled)
        if ($userGroups->isEmpty()) {
            return [
                'enable_initial_request' => false,
                'enable_missing_docs' => false,
                'enable_reminder' => false,
                'enable_upload_confirmation' => false,
                'enable_approval' => false,
                'enable_rejection' => false,
                'enable_custom_email' => false,
            ];
        }

        // Merge configurations from all groups (OR logic - most permissive wins)
        $mergedConfig = [];
        foreach ($userGroups as $group) {
            foreach ($group->configurations as $config) {
                // If any group enables an action, it's enabled
                if (! isset($mergedConfig[$config->key]) || ! $mergedConfig[$config->key]) {
                    $mergedConfig[$config->key] = (bool) $config->value;
                } else {
                    // Use OR logic: if either is true, keep true
                    $mergedConfig[$config->key] = $mergedConfig[$config->key] || (bool) $config->value;
                }
            }
        }

        // Ensure all expected keys exist with defaults
        return array_merge([
            'enable_initial_request' => false,
            'enable_missing_docs' => false,
            'enable_reminder' => false,
            'enable_upload_confirmation' => false,
            'enable_approval' => false,
            'enable_rejection' => false,
            'enable_custom_email' => false,
        ], $mergedConfig);
    }
}
