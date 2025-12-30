<?php

namespace Modules\Document\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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

    public function validatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validator_user_id');
    }

    public function group(): ?DocumentValidatorGroup
    {
        return DocumentValidatorGroup::findByKey($this->validator_group);
    }

    public function scopeForType($query, string $type)
    {
        return $query->where('validatable_type', $type);
    }

    public function scopeWithAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByValidator($query, User|int $user)
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('validator_user_id', $userId);
    }

    public function scopeInGroup($query, string $groupKey)
    {
        return $query->where('validator_group', $groupKey);
    }

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
