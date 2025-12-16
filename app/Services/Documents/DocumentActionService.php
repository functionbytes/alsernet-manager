<?php

namespace App\Services\Documents;

use App\Models\Document\Document;
use App\Models\Document\DocumentAction;
use App\Models\Document\DocumentNote;

class DocumentActionService
{
    /**
     * Registrar que se envió un correo de notificación inicial
     */
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

    /**
     * Registrar que se envió un recordatorio
     */
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

    /**
     * Registrar que se envió un correo de documentos específicos
     */
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

    /**
     * Registrar que se confirmó la carga de documentos
     */
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

    /**
     * Registrar que se cargaron documentos
     */
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

    /**
     * Registrar un cambio de estado
     */
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

    /**
     * Registrar eliminación de documento
     */
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

    /**
     * Obtener historial de un documento
     */
    public static function getDocumentHistory(Document $document)
    {
        return DocumentAction::getDocumentHistory($document->id);
    }

    /**
     * Agregar una nota al documento
     */
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

    /**
     * Obtener notas del documento
     */
    public static function getDocumentNotes(Document $document, bool $onlyInternal = false)
    {
        return DocumentNote::getDocumentNotes($document->id, $onlyInternal);
    }

    /**
     * Registrar que se envió un correo personalizado
     */
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
}
