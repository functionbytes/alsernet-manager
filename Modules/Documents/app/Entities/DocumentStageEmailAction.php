<?php

namespace Modules\Documents\Entities;

use App\Models\Traits\HasUid;
use Illuminate\Database\Eloquent\Model;

/**
 * StageEmailAction
 *
 * Stores which email actions are enabled/disabled for each validation stage.
 * This allows administrators to configure which email options (approval notifications,
 * rejection notifications, custom emails, etc.) are available at each stage
 * (documentacion, licencias, contabilidad) without modifying code.
 *
 * Example:
 * - validation_stage: 'documentacion', email_action: 'aprobacion', is_enabled: true
 * - validation_stage: 'licencias', email_action: 'confirmacion_archivos', is_enabled: false
 * - validation_stage: 'contabilidad', email_action: 'rechazo', is_enabled: true
 */
class StageEmailAction extends Model
{
    use HasUid;

    // Email action types
    public const ACTION_SOLICITUD_DOCUMENTOS = 'solicitud_documentos';

    public const ACTION_CONFIRMACION_ARCHIVOS = 'confirmacion_archivos';

    public const ACTION_APROBACION = 'aprobacion';

    public const ACTION_RECHAZO = 'rechazo';

    public const ACTION_CORREO_PERSONALIZADO = 'correo_personalizado';

    // Validation stages
    public const STAGE_DOCUMENTACION = 'documentacion';

    public const STAGE_LICENCIAS = 'licencias';

    public const STAGE_CONTABILIDAD = 'contabilidad';

    public const AVAILABLE_ACTIONS = [
        self::ACTION_SOLICITUD_DOCUMENTOS => 'Solicitud de documentos',
        self::ACTION_CONFIRMACION_ARCHIVOS => 'Confirmación de archivos subidos',
        self::ACTION_APROBACION => 'Notificación de aprobación',
        self::ACTION_RECHAZO => 'Notificación de rechazo',
        self::ACTION_CORREO_PERSONALIZADO => 'Correo personalizado',
    ];

    public const AVAILABLE_STAGES = [
        self::STAGE_DOCUMENTACION => 'Documentación',
        self::STAGE_LICENCIAS => 'Licencias',
        self::STAGE_CONTABILIDAD => 'Contabilidad',
    ];

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
