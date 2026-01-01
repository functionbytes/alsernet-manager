<?php

namespace Modules\Document\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Document\Traits\HasUid;

class DocumentStageEmailAction extends Model
{
    use HasUid;

    protected $table = 'document_stage_email_actions';

    protected $fillable = [
        'uid',
        'validation_stage',
        'email_action',
        'is_enabled',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeForStage($query, string $stage)
    {
        return $query->where('validation_stage', $stage);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('validation_stage')->orderBy('sort_order')->orderBy('email_action');
    }

    /**
     * Get all enabled actions for a specific stage
     */
    public static function getEnabledActionsForStage(string $stage): array
    {
        return self::active()
            ->forStage($stage)
            ->ordered()
            ->pluck('email_action')
            ->toArray();
    }

    /**
     * Check if a specific action is enabled for a stage
     */
    public static function isActionEnabledForStage(string $stage, string $action): bool
    {
        return self::active()
            ->forStage($stage)
            ->where('email_action', $action)
            ->exists();
    }

    /**
     * Get all configurations grouped by stage (for admin panel)
     */
    public static function getAllByStage(): array
    {
        return self::ordered()
            ->get()
            ->groupBy('validation_stage')
            ->toArray();
    }
}
