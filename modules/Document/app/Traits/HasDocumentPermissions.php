<?php

namespace Modules\Document\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Document\Entities\DocumentValidatorGroup;

/**
 * Trait HasDocumentPermissions
 *
 * Este trait agrega funcionalidad de permisos del módulo Document al modelo User
 * Sigue el patrón de Spatie Permission pero scoped al módulo Document
 */
trait HasDocumentPermissions
{
    /**
     * Document Validator Groups that the user belongs to
     */
    public function documentValidatorGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            DocumentValidatorGroup::class,
            'document_validator_group_user',
            'user_id',
            'validator_group_id'
        )->withPivot('priority', 'created_at');
    }

    /**
     * Check if user has a permission within a specific document validator group
     *
     * @param  string  $permissionName  The permission to check (e.g., 'approve-documents')
     * @param  DocumentValidatorGroup|string  $group  The group model or key
     * @return bool
     */
    public function hasPermissionInGroup(string $permissionName, $group): bool
    {
        $groupModel = $group instanceof DocumentValidatorGroup
            ? $group
            : DocumentValidatorGroup::findByKey($group);

        if (! $groupModel) {
            return false;
        }

        // Check if user belongs to the group
        if (! $groupModel->hasUser($this)) {
            return false;
        }

        // Check if the group has the permission
        return $groupModel->hasPermission($permissionName);
    }

    /**
     * Check if user can perform an action in any of their document groups
     *
     * @param  string  $action  The action/permission to check
     * @return bool
     */
    public function canInDocumentModule(string $action): bool
    {
        return $this->documentValidatorGroups()
            ->where('is_active', true)
            ->get()
            ->contains(function ($group) use ($action) {
                return $group->can($action);
            });
    }

    /**
     * Get all permissions the user has across all their document validator groups
     *
     * @return array Array of permissions with their details and associated groups
     */
    public function getDocumentGroupPermissions(): array
    {
        $permissions = [];

        foreach ($this->documentValidatorGroups()->where('is_active', true)->get() as $group) {
            foreach ($group->permissions()->where('is_active', true)->get() as $permission) {
                if (! isset($permissions[$permission->name])) {
                    $permissions[$permission->name] = [
                        'name' => $permission->name,
                        'label' => $permission->label,
                        'category' => $permission->category,
                        'groups' => [],
                    ];
                }
                $permissions[$permission->name]['groups'][] = $group->name;
            }
        }

        return array_values($permissions);
    }

    /**
     * Check if user belongs to any document validator group
     *
     * @return bool
     */
    public function belongsToAnyDocumentGroup(): bool
    {
        return $this->documentValidatorGroups()->where('is_active', true)->exists();
    }

    /**
     * Get the user's primary document validator groups
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function primaryDocumentGroups()
    {
        return $this->documentValidatorGroups()
            ->where('is_active', true)
            ->wherePivot('priority', 'primary')
            ->get();
    }

    /**
     * Get the user's backup document validator groups
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function backupDocumentGroups()
    {
        return $this->documentValidatorGroups()
            ->where('is_active', true)
            ->wherePivot('priority', 'backup')
            ->get();
    }

    /**
     * Check if user has permission in any of their groups
     * Useful for authorization in controllers
     *
     * @param  string  $permission  The permission name
     * @return bool
     */
    public function hasDocumentPermission(string $permission): bool
    {
        return $this->canInDocumentModule($permission);
    }

    // =========================================================================
    // COMPONENT VISIBILITY HELPERS - Para usar en vistas Blade
    // =========================================================================

    /**
     * Check if a component should be visible based on document permissions
     * Useful for showing/hiding UI components in views
     *
     * @param  string  $componentName  Name of the component (e.g., 'email-actions', 'document-management')
     * @return bool
     */
    public function canViewDocumentComponent(string $componentName): bool
    {
        return match ($componentName) {
            // Componentes de Email
            'email-actions' => $this->canInDocumentModule('send-initial-request') ||
                              $this->canInDocumentModule('send-reminders') ||
                              $this->canInDocumentModule('send-missing-docs') ||
                              $this->canInDocumentModule('send-approval') ||
                              $this->canInDocumentModule('send-rejection'),

            // Gestión de Documentos
            'document-management' => $this->canInDocumentModule('edit-documents') ||
                                   $this->canInDocumentModule('create-documents'),

            // Subida de Documentos
            'document-upload' => $this->canInDocumentModule('upload-documents'),

            // Validación
            'validation-workflow' => $this->canInDocumentModule('approve-documents') ||
                                   $this->canInDocumentModule('reject-documents'),

            // Notas
            'document-notes' => $this->canInDocumentModule('add-notes') ||
                              $this->canInDocumentModule('edit-notes'),

            // Adjuntos
            'additional-attachments' => $this->canInDocumentModule('upload-attachments') ||
                                       $this->canInDocumentModule('delete-attachments'),

            // Historial
            'action-history' => $this->canInDocumentModule('view-all-documents'),
            'email-history' => $this->canInDocumentModule('view-all-documents'),
            'status-timeline' => $this->canInDocumentModule('view-all-documents'),

            // Información del Documento
            'products-list' => $this->canInDocumentModule('view-all-documents'),
            'order-details' => $this->canInDocumentModule('view-all-documents'),
            'customer-information' => $this->canInDocumentModule('view-all-documents'),

            // Sincronización
            'sync-operations' => $this->canInDocumentModule('import-documents') ||
                                $this->canInDocumentModule('sync-erp') ||
                                $this->canInDocumentModule('sync-api'),

            // Por defecto, no visible
            default => false,
        };
    }

    /**
     * Check if user can perform an action on a specific document component
     *
     * @param  string  $componentName  The component name
     * @param  string  $action  The action (view, edit, delete, etc.)
     * @return bool
     */
    public function canActionDocumentComponent(string $componentName, string $action = 'view'): bool
    {
        return match ($componentName) {
            // Email Actions
            'email-actions' => match ($action) {
                'send-initial-request' => $this->canInDocumentModule('send-initial-request'),
                'send-reminder' => $this->canInDocumentModule('send-reminders'),
                'send-missing-docs' => $this->canInDocumentModule('send-missing-docs'),
                'send-approval' => $this->canInDocumentModule('send-approval'),
                'send-rejection' => $this->canInDocumentModule('send-rejection'),
                'send-custom' => $this->canInDocumentModule('send-custom-email'),
                default => false,
            },

            // Document Management
            'document-management' => match ($action) {
                'edit' => $this->canInDocumentModule('edit-documents'),
                'create' => $this->canInDocumentModule('create-documents'),
                'delete' => $this->canInDocumentModule('delete-documents'),
                default => false,
            },

            // Document Upload
            'document-upload' => match ($action) {
                'upload' => $this->canInDocumentModule('upload-documents'),
                'delete' => $this->canInDocumentModule('delete-documents'),
                default => false,
            },

            // Validation
            'validation-workflow' => match ($action) {
                'approve' => $this->canInDocumentModule('approve-documents'),
                'reject' => $this->canInDocumentModule('reject-documents'),
                default => false,
            },

            // Notes
            'document-notes' => match ($action) {
                'add' => $this->canInDocumentModule('add-notes'),
                'edit' => $this->canInDocumentModule('edit-notes'),
                'delete' => $this->canInDocumentModule('delete-notes'),
                default => false,
            },

            // Attachments
            'additional-attachments' => match ($action) {
                'upload' => $this->canInDocumentModule('upload-attachments'),
                'delete' => $this->canInDocumentModule('delete-attachments'),
                default => false,
            },

            default => false,
        };
    }

    /**
     * Get all allowed component actions for this user
     * Useful for disabling buttons/UI elements
     *
     * @return array
     */
    public function getAllowedComponentActions(): array
    {
        $actions = [];

        // Collect all allowed permissions
        $permissions = $this->getDocumentGroupPermissions();

        foreach ($permissions as $permission) {
            $actions[$permission['name']] = true;
        }

        return $actions;
    }
}
