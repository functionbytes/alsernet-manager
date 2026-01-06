<?php

namespace Modules\Document\Services;

use Illuminate\Support\Facades\Auth;
use Modules\Document\Entities\Document;
use Modules\Document\Entities\DocumentAction;
use Modules\Document\Entities\DocumentNote;
use Modules\Document\Entities\DocumentValidationHistory;

class DocumentActionService
{
    public static function logInitialRequestEmail(Document $document, string $email, ?string $message = null): DocumentAction
    {
        return DocumentAction::logAction(
            documentId: $document->id,
            actionType: 'email_initial_request',
            actionName: 'Correo de Solicitud Inicial Enviado',
            description: $message ?? "Se envió correo de solicitud inicial a {$email}",
            metadata: [
                'email' => $email,
                'message' => $message,
            ],
            performedByType: 'system'
        );
    }

    public static function logReminderEmail(Document $document, string $email, ?string $message = null): DocumentAction
    {
        return DocumentAction::logAction(
            documentId: $document->id,
            actionType: 'email_reminder',
            actionName: 'Recordatorio Enviado',
            description: $message ?? "Se envió recordatorio a {$email}",
            metadata: [
                'email' => $email,
                'message' => $message,
            ],
            performedByType: 'system'
        );
    }

    public static function logMissingDocumentsEmail(Document $document, string $email, array $missingDocs, ?string $message = null): DocumentAction
    {
        return DocumentAction::logAction(
            documentId: $document->id,
            actionType: 'email_missing_documents',
            actionName: 'Solicitud de Documentos Específicos Enviada',
            description: $message ?? 'Se envió solicitud de '.count($missingDocs)." documentos específicos a {$email}",
            metadata: [
                'email' => $email,
                'missing_documents' => $missingDocs,
                'message' => $message,
            ],
            performedByType: 'system'
        );
    }

    public static function logUploadConfirmation(Document $document, ?int $adminId = null): DocumentAction
    {
        return DocumentAction::logAction(
            documentId: $document->id,
            actionType: 'upload_confirmed',
            actionName: 'Carga de Documentos Confirmada',
            description: 'Los documentos fueron confirmados como cargados correctamente',
            performedBy: $adminId,
            performedByType: $adminId ? 'admin' : 'system'
        );
    }

    public static function logDocumentUpload(Document $document, array $uploadedFiles): DocumentAction
    {
        return DocumentAction::logAction(
            documentId: $document->id,
            actionType: 'documents_uploaded',
            actionName: 'Documentos Cargados',
            description: 'Se cargaron '.count($uploadedFiles).' documento(s)',
            metadata: [
                'files_count' => count($uploadedFiles),
                'files' => $uploadedFiles,
            ],
            performedByType: 'customer'
        );
    }

    public static function logStatusChange(Document $document, string $oldStatus, string $newStatus, ?int $adminId = null): DocumentAction
    {
        return DocumentAction::logAction(
            documentId: $document->id,
            actionType: 'status_changed',
            actionName: 'Estado Modificado',
            description: "Estado cambió de '{$oldStatus}' a '{$newStatus}'",
            metadata: [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ],
            performedBy: $adminId,
            performedByType: $adminId ? 'admin' : 'system'
        );
    }

    /**
     * Registrar carga de documentos por parte del administrador
     */
    public static function logAdminDocumentUpload(Document $document, array $uploadedFiles, int $adminId): DocumentAction
    {
        return DocumentAction::logAction(
            documentId: $document->id,
            actionType: 'admin_documents_uploaded',
            actionName: 'Documentos Cargados por Administrador',
            description: 'El administrador cargó '.count($uploadedFiles).' documento(s)',
            metadata: [
                'files_count' => count($uploadedFiles),
                'files' => $uploadedFiles,
            ],
            performedBy: $adminId,
            performedByType: 'admin'
        );
    }

    public static function logDocumentDeletion(Document $document, string $fileName, int $adminId): DocumentAction
    {
        return DocumentAction::logAction(
            documentId: $document->id,
            actionType: 'document_deleted',
            actionName: 'Documento Eliminado',
            description: "El archivo '{$fileName}' fue eliminado",
            metadata: [
                'file_name' => $fileName,
            ],
            performedBy: $adminId,
            performedByType: 'admin'
        );
    }

    public static function getDocumentHistory(Document $document)
    {
        return DocumentAction::getDocumentHistory($document->id);
    }

    public static function addNote(Document $document, int $adminId, string $content, bool $isInternal = true): DocumentNote
    {
        $note = DocumentNote::addNote($document->id, $adminId, $content, $isInternal);

        // También registrar como acción
        DocumentAction::logAction(
            documentId: $document->id,
            actionType: 'note_added',
            actionName: 'Nota Agregada',
            description: $isInternal ? 'Nota interna agregada' : 'Nota agregada',
            metadata: [
                'note_id' => $note->id,
                'is_internal' => $isInternal,
            ],
            performedBy: $adminId,
            performedByType: 'admin'
        );

        return $note;
    }

    public static function getDocumentNotes(Document $document, bool $onlyInternal = false)
    {
        return DocumentNote::getDocumentNotes($document->id, $onlyInternal);
    }

    public static function logCustomEmail(Document $document, string $email, string $subject, string $content, ?string $message = null, ?int $adminId = null): DocumentAction
    {
        return DocumentAction::logAction(
            documentId: $document->id,
            actionType: 'email_custom',
            actionName: 'Correo Personalizado Enviado',
            description: $message ?? "Se envió correo personalizado a {$email}",
            metadata: [
                'email' => $email,
                'subject' => $subject,
                'content_preview' => substr($content, 0, 150).'...',
                'content_length' => strlen($content),
                'message' => $message,
            ],
            performedBy: $adminId,
            performedByType: 'admin'
        );
    }

    /**
     * Approve current validation stage and advance to next stage
     */
    public function approveStage(Document $document, ?string $comments = null, ?int $assignedUserId = null): Document
    {
        $user = Auth::user();
        $currentGroup = $document->current_validator_group;

        // Create validation history record
        DocumentValidationHistory::create([
            'document_id' => $document->id,
            'stage_number' => $document->current_stage,
            'validator_group' => $currentGroup,
            'validator_user_id' => $user->id,
            'action' => 'approved',
            'comments' => $comments,
            'validated_at' => now(),
        ]);

        // Log action
        DocumentAction::logAction(
            documentId: $document->id,
            actionType: 'stage_approved',
            actionName: 'Etapa Aprobada',
            description: "Etapa {$document->current_stage} aprobada por {$user->full_name}",
            metadata: [
                'stage_number' => $document->current_stage,
                'validator_group' => $currentGroup,
                'assigned_user_id' => $assignedUserId,
                'comments' => $comments,
            ],
            performedBy: $user->id,
            performedByType: 'validator'
        );

        // Check if this is the last stage
        if ($document->current_stage >= $document->total_stages) {
            // All stages completed - mark as approved
            $document->update([
                'validation_status' => 'approved',
                'current_stage' => $document->total_stages,
            ]);
        } else {
            // Advance to next stage
            $stages = $document->getValidationWorkflowStages();
            $nextStageKey = $stages[$document->current_stage] ?? null;

            $document->update([
                'current_stage' => $document->current_stage + 1,
                'current_validator_group' => $nextStageKey,
                'assigned_user_id' => $assignedUserId,
                'validation_status' => 'in_validation',
            ]);
        }

        return $document->fresh();
    }

    /**
     * Reject current validation stage
     */
    public function rejectStage(Document $document, string $reason): Document
    {
        $user = Auth::user();

        // Create validation history record
        DocumentValidationHistory::create([
            'document_id' => $document->id,
            'stage_number' => $document->current_stage,
            'validator_group' => $document->current_validator_group,
            'validator_user_id' => $user->id,
            'action' => 'rejected',
            'comments' => $reason,
            'validated_at' => now(),
        ]);

        // Log action
        DocumentAction::logAction(
            documentId: $document->id,
            actionType: 'stage_rejected',
            actionName: 'Etapa Rechazada',
            description: "Etapa {$document->current_stage} rechazada por {$user->full_name}",
            metadata: [
                'stage_number' => $document->current_stage,
                'validator_group' => $document->current_validator_group,
                'reason' => $reason,
            ],
            performedBy: $user->id,
            performedByType: 'validator'
        );

        // Reset to pending status for re-submission
        $document->update([
            'validation_status' => 'rejected',
        ]);

        return $document->fresh();
    }
}
