<?php

namespace Modules\Documents\Entities;

use App\Library\Traits\HasUid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * DocumentValidationHistory Model
 *
 * Polymorphic model for tracking validation workflow history.
 * Works with any model using HasValidationWorkflow trait.
 *
 * @property int $id
 * @property string $uid
 * @property string $validatable_type
 * @property int $validatable_id
 * @property int $stage_number
 * @property string $validator_group
 * @property int $validator_user_id
 * @property string $action (approved, rejected, returned, reopened)
 * @property string|null $comments
 * @property \Illuminate\Support\Carbon $validated_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class DocumentValidationHistory extends Model
{
    use HasUid;

    protected $fillable = [
        'uid',
        'validatable_type',
        'validatable_id',
        'stage_number',
        'validator_group',
        'validator_user_id',
        'action',
        'comments',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'stage_number' => 'integer',
            'validated_at' => 'datetime',
        ];
    }

    /**
     * Get the owning validatable model (Document, Ticket, etc.)
     */
    public function validatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who performed the validation action
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validator_user_id');
    }

    /**
     * Get the DocumentValidatorGroup for this validation
     */
    public function group(): ?DocumentValidatorGroup
    {
        return DocumentValidatorGroup::findByKey($this->validator_group);
    }

    /**
     * Scope to filter by validatable type
     */
    public function scopeForType($query, string $type)
    {
        return $query->where('validatable_type', $type);
    }

    /**
     * Scope to filter by action
     */
    public function scopeWithAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by validator user
     */
    public function scopeByValidator($query, User|int $user)
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('validator_user_id', $userId);
    }

    /**
     * Scope to filter by validator group
     */
    public function scopeInGroup($query, string $groupKey)
    {
        return $query->where('validator_group', $groupKey);
    }

    /**
     * Get formatted action label
     */
    public function getActionLabel(): string
    {
        return match ($this->action) {
            'approved' => 'Aprobado',
            'rejected' => 'Rechazado',
            'returned' => 'Devuelto',
            'reopened' => 'Reabierto',
            default => ucfirst($this->action),
        };
    }
}
