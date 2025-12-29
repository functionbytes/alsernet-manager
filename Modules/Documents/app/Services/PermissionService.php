<?php

namespace Modules\Documents\Services;

use App\Models\User;
use App\Models\Validation\ValidatorGroup;
use Illuminate\Support\Collection;

/**
 * PermissionService
 *
 * Servicio centralizado para gestionar permisos de documentos
 * combinando Spatie Permission (role-based) con ValidatorGroup (group-based)
 */
class PermissionService
{
    /**
     * Verificar si un usuario tiene acceso a una acción específica de documento
     *
     * @param  string  $action  Nombre de la acción (ej: 'manage', 'send-custom-email')
     * @param  string|null  $profile  Perfil actual (ej: 'administrative', 'manager')
     */
    public function can(User $user, string $action, ?string $profile = null): bool
    {
        // Super-admin SIEMPRE tiene acceso
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Construir el nombre del permiso según el perfil
        $permissionName = $this->buildPermissionName($action, $profile);

        // Verificar permiso Spatie
        if (! $user->can($permissionName)) {
            return false;
        }

        // Si es una acción de email, verificar configuración de ValidatorGroup
        if ($this->isEmailAction($action)) {
            return $this->hasEmailActionAccess($user, $action);
        }

        return true;
    }

    /**
     * Obtener todas las acciones disponibles para un usuario
     */
    public function getAvailableActions(User $user, ?string $profile = null): array
    {
        // Super-admin obtiene TODAS las acciones
        if ($user->hasRole('super-admin')) {
            return $this->getAllActions();
        }

        $available = [];

        // Verificar cada acción
        foreach ($this->getAllActions() as $action) {
            if ($this->can($user, $action, $profile)) {
                $available[] = $action;
            }
        }

        return $available;
    }

    /**
     * Obtener configuración de acciones de email para un usuario
     * Combina permisos Spatie con configuración ValidatorGroup
     */
    public function getEmailActionsConfig(User $user, ?string $profile = null): array
    {
        // Super-admin tiene todo habilitado
        if ($user->hasRole('super-admin')) {
            return [
                'enable_initial_request' => true,
                'enable_missing_docs' => true,
                'enable_reminder' => true,
                'enable_upload_confirmation' => true,
                'enable_approval' => true,
                'enable_rejection' => true,
                'enable_custom_email' => true,
            ];
        }

        // Obtener configuración base de ValidatorGroup
        $groupConfig = ValidatorGroup::getEmailConfigurationsForUser($user);

        // Filtrar por permisos Spatie
        $finalConfig = [];
        foreach ($groupConfig as $action => $enabled) {
            $actionName = str_replace('enable_', '', $action);
            $actionName = str_replace('_', '-', $actionName);

            // Solo habilitar si tiene permiso Y configuración de grupo
            $finalConfig[$action] = $enabled && $this->can($user, $actionName, $profile);
        }

        return $finalConfig;
    }

    /**
     * Verificar si usuario está en un ValidatorGroup activo
     */
    public function isInValidatorGroup(User $user): bool
    {
        return ValidatorGroup::query()
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Obtener grupos de validador a los que pertenece el usuario
     */
    public function getUserValidatorGroups(User $user): Collection
    {
        return ValidatorGroup::query()
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->where('is_active', true)
            ->get();
    }

    // =========================================================================
    // MÉTODOS PRIVADOS
    // =========================================================================

    /**
     * Construir nombre del permiso según perfil y acción
     */
    private function buildPermissionName(string $action, ?string $profile = null): string
    {
        $profile = $profile ?? 'administrative'; // Default profile

        return "{$profile}.documents.{$action}";
    }

    /**
     * Verificar si una acción es de tipo email
     */
    private function isEmailAction(string $action): bool
    {
        $emailActions = [
            'send-custom-email',
            'send-notification',
            'send-reminder',
            'send-missing',
            'send-approval',
            'send-rejection',
            'send-upload-confirmation',
        ];

        return in_array($action, $emailActions);
    }

    /**
     * Verificar acceso a acción de email según ValidatorGroup
     */
    private function hasEmailActionAccess(User $user, string $action): bool
    {
        $groupConfig = ValidatorGroup::getEmailConfigurationsForUser($user);

        // Mapear acción a configuración
        $configKey = match ($action) {
            'send-notification' => 'enable_initial_request',
            'send-custom-email' => 'enable_custom_email',
            'send-reminder' => 'enable_reminder',
            'send-missing' => 'enable_missing_docs',
            'send-approval' => 'enable_approval',
            'send-rejection' => 'enable_rejection',
            'send-upload-confirmation' => 'enable_upload_confirmation',
            default => null,
        };

        return $configKey ? ($groupConfig[$configKey] ?? false) : false;
    }

    /**
     * Obtener lista completa de acciones disponibles
     */
    private function getAllActions(): array
    {
        return [
            'manage',
            'view',
            'create',
            'edit',
            'update',
            'destroy',
            'admin-upload',
            'confirm-upload',
            'delete-single',
            'send-notification',
            'send-custom-email',
            'send-reminder',
            'send-missing',
            'send-approval',
            'send-rejection',
            'send-upload-confirmation',
            'add-note',
            'files',
            'files.get',
            'files.delete',
            'history',
            'import',
            'import-erp',
            'pending',
            'missing-documents',
            'resend-reminder',
            'store',
            'summary',
            'sync.all',
            'sync.by-order',
            'sync.by-order.query',
            'sync.from-erp',
        ];
    }
}
