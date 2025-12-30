<?php

namespace Modules\Document\Policies;

use Modules\Document\Entities\Document;
use App\Models\User;
use Modules\Document\Services\PermissionService;

/**
 * DocumentPolicy
 *
 * Policy para autorización de acciones sobre documentos
 * Utiliza PermissionService para combinar Spatie Permission + ValidatorGroup
 */
class DocumentPolicy
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    /**
     * Verificar si usuario puede ver listado de documentos
     */
    public function viewAny(User $user, ?string $profile = null): bool
    {
        return $this->permissionService->can($user, 'view', $profile);
    }

    /**
     * Verificar si usuario puede ver un documento específico
     */
    public function view(User $user, Document $document, ?string $profile = null): bool
    {
        return $this->permissionService->can($user, 'view', $profile);
    }

    /**
     * Verificar si usuario puede crear documentos
     */
    public function create(User $user, ?string $profile = null): bool
    {
        return $this->permissionService->can($user, 'create', $profile);
    }

    /**
     * Verificar si usuario puede gestionar un documento
     */
    public function manage(User $user, Document $document, ?string $profile = null): bool
    {
        // Super-admin siempre puede
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Verificar permiso base
        if (! $this->permissionService->can($user, 'manage', $profile)) {
            return false;
        }

        // Verificar si está en grupo validador activo
        return $this->permissionService->isInValidatorGroup($user);
    }

    /**
     * Verificar si usuario puede actualizar un documento
     */
    public function update(User $user, Document $document, ?string $profile = null): bool
    {
        return $this->permissionService->can($user, 'update', $profile);
    }

    /**
     * Verificar si usuario puede eliminar un documento
     */
    public function delete(User $user, Document $document, ?string $profile = null): bool
    {
        return $this->permissionService->can($user, 'destroy', $profile);
    }

    /**
     * Verificar si usuario puede subir archivos como admin
     */
    public function adminUpload(User $user, Document $document, ?string $profile = null): bool
    {
        return $this->permissionService->can($user, 'admin-upload', $profile);
    }

    /**
     * Verificar si usuario puede confirmar subida
     */
    public function confirmUpload(User $user, Document $document, ?string $profile = null): bool
    {
        return $this->permissionService->can($user, 'confirm-upload', $profile);
    }

    /**
     * Verificar si usuario puede enviar email personalizado
     */
    public function sendCustomEmail(User $user, Document $document, ?string $profile = null): bool
    {
        // Esta acción requiere verificación especial de email actions
        return $this->permissionService->can($user, 'send-custom-email', $profile);
    }

    /**
     * Verificar si usuario puede enviar notificación inicial
     */
    public function sendNotification(User $user, Document $document, ?string $profile = null): bool
    {
        return $this->permissionService->can($user, 'send-notification', $profile);
    }

    /**
     * Verificar si usuario puede enviar recordatorio
     */
    public function sendReminder(User $user, Document $document, ?string $profile = null): bool
    {
        return $this->permissionService->can($user, 'send-reminder', $profile);
    }

    /**
     * Verificar si usuario puede enviar solicitud de documentos faltantes
     */
    public function sendMissing(User $user, Document $document, ?string $profile = null): bool
    {
        return $this->permissionService->can($user, 'send-missing', $profile);
    }

    /**
     * Verificar si usuario puede enviar aprobación
     */
    public function sendApproval(User $user, Document $document, ?string $profile = null): bool
    {
        return $this->permissionService->can($user, 'send-approval', $profile);
    }

    /**
     * Verificar si usuario puede enviar rechazo
     */
    public function sendRejection(User $user, Document $document, ?string $profile = null): bool
    {
        return $this->permissionService->can($user, 'send-rejection', $profile);
    }

    /**
     * Verificar si usuario puede enviar confirmación de subida
     */
    public function sendUploadConfirmation(User $user, Document $document, ?string $profile = null): bool
    {
        return $this->permissionService->can($user, 'send-upload-confirmation', $profile);
    }

    /**
     * Verificar si usuario puede agregar notas
     */
    public function addNote(User $user, Document $document, ?string $profile = null): bool
    {
        return $this->permissionService->can($user, 'add-note', $profile);
    }

    /**
     * Verificar si usuario puede acceder a archivos
     */
    public function accessFiles(User $user, Document $document, ?string $profile = null): bool
    {
        return $this->permissionService->can($user, 'files', $profile);
    }

    /**
     * Verificar si usuario puede ver historial
     */
    public function viewHistory(User $user, Document $document, ?string $profile = null): bool
    {
        return $this->permissionService->can($user, 'history', $profile);
    }

    /**
     * Verificar si usuario puede importar documentos
     */
    public function import(User $user, ?string $profile = null): bool
    {
        return $this->permissionService->can($user, 'import', $profile);
    }

    /**
     * Verificar si usuario puede sincronizar desde ERP
     */
    public function syncFromErp(User $user, ?string $profile = null): bool
    {
        return $this->permissionService->can($user, 'sync.from-erp', $profile);
    }

    /**
     * Verificar si usuario puede aprobar etapa de workflow
     */
    public function approveStage(User $user, Document $document, ?string $profile = null): bool
    {
        // Verificar que esté en workflow activo
        if (! $document->total_stages || $document->validation_status !== 'in_validation') {
            return false;
        }

        // Verificar si es super-admin
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Verificar si el usuario pertenece al grupo validador actual
        $currentGroup = $document->current_validator_group;
        if (! $currentGroup) {
            return false;
        }

        $validatorGroup = \Modules\Document\Validations\\ValidatorGroup::findByKey($currentGroup);
        if (! $validatorGroup) {
            return false;
        }

        return $validatorGroup->canUserValidate($user);
    }

    /**
     * Verificar si usuario puede rechazar etapa de workflow
     */
    public function rejectStage(User $user, Document $document, ?string $profile = null): bool
    {
        // Usar la misma lógica que approveStage
        return $this->approveStage($user, $document, $profile);
    }
}
