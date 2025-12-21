<?php

namespace App\Models\Validation;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ValidatorGroupConfigurationHistory - Historial de cambios en configuraciones de grupos
 *
 * @property int $id
 * @property int $validator_group_id
 * @property int $configuration_id
 * @property int|null $user_id
 * @property string $configuration_key
 * @property string $configuration_label
 * @property bool $old_value
 * @property bool $new_value
 * @property string $action
 * @property string|null $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ValidatorGroupConfigurationHistory extends Model
{
    protected $table = 'config_histories';

    protected $fillable = [
        'validator_group_id',
        'configuration_id',
        'user_id',
        'configuration_key',
        'configuration_label',
        'old_value',
        'new_value',
        'action',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'old_value' => 'boolean',
            'new_value' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the validator group this history entry belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ValidatorGroup::class, 'validator_group_id');
    }

    /**
     * Get the configuration this history entry is for.
     */
    public function configuration(): BelongsTo
    {
        return $this->belongsTo(ValidatorGroupConfiguration::class, 'configuration_id');
    }

    /**
     * Get the user who made this change.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get human-readable action label.
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'create' => 'Creada',
            'update' => 'Actualizada',
            'delete' => 'Eliminada',
            default => $this->action,
        };
    }

    /**
     * Get formatted change description.
     */
    public function getChangeDescriptionAttribute(): string
    {
        if ($this->action === 'create') {
            return "Habilitada por {$this->user?->firstname} {$this->user?->lastname}";
        }

        $valueText = $this->new_value ? 'Habilitada' : 'Deshabilitada';

        return "$valueText por {$this->user?->firstname} {$this->user?->lastname}";
    }
}
