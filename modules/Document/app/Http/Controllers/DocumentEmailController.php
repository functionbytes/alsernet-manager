<?php

namespace Modules\Document\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Models\Setting;
use Modules\Document\Entities\Document;
use Modules\Document\Entities\DocumentMail;
use Modules\Document\Jobs\MailTemplateJob;
use Modules\Document\Services\DocumentMailService;
use Modules\Document\Traits\ValidatesDocumentPermissions;

/**
 * Handles all email-related operations for documents
 * Extracted from DocumentsController for better separation of concerns
 *
 * ⚠️ All methods require proper authorization checks via ValidatesDocumentPermissions trait
 */
class DocumentEmailController extends Controller
{
    use ValidatesDocumentPermissions;

    /**
     * Send notification email to customer (initial request)
     */
    public function sendNotificationEmail($uid): JsonResponse
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            // ✅ AUTHORIZATION CHECK: Verify user can send initial request emails
            try {
                $this->authorizeEmailOperation($document, 'send-initial-request');
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para enviar solicitudes iniciales de documentos',
                    'error' => $e->getMessage(),
                ], 403);
            }

            // Verificar si está habilitado en configuración global
            if (Setting::get('documents.enable_initial_request', 'yes') !== 'yes') {
                return response()->json([
                    'success' => false,
                    'message' => 'La solicitud inicial de documentos está deshabilitada en la configuración.',
                ], 403);
            }

            // Validar que el documento tiene email
            $recipient = $document->customer_email;
            if (! $recipient) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo enviar: documento sin email de cliente',
                    'document_email' => $document->customer_email,
                ], 400);
            }

            // Despachar job para enviar email en background
            MailTemplateJob::dispatch($document, 'request');

            return response()->json([
                'success' => true,
                'message' => 'Email de notificación en cola para envío',
                'recipient' => $recipient,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar solicitud: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send reminder email to customer
     */
    public function sendReminderEmail($uid): JsonResponse
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            // ✅ AUTHORIZATION CHECK: Verify user can send reminder emails
            try {
                $this->authorizeEmailOperation($document, 'send-reminders');
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para enviar recordatorios',
                    'error' => $e->getMessage(),
                ], 403);
            }

            // Verificar si está habilitado en configuración global
            if (Setting::get('documents.enable_reminder', 'yes') !== 'yes') {
                return response()->json([
                    'success' => false,
                    'message' => 'Los recordatorios automáticos están deshabilitados en la configuración.',
                ], 403);
            }

            // Verificar que el cliente tiene email
            $recipient = $document->customer_email;
            if (! $recipient) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo enviar: documento sin email de cliente',
                ], 400);
            }

            // Despachar job para enviar email en background
            MailTemplateJob::dispatch($document, 'reminder');

            return response()->json([
                'success' => true,
                'message' => 'Email de recordatorio en cola para envío',
                'recipient' => $recipient,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar email: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resend reminder email
     */
    public function resendReminderEmail($uid): JsonResponse
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            // Verificar que el cliente tiene email
            $recipient = $document->customer_email;
            if (! $recipient) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo enviar: documento sin email de cliente',
                ], 400);
            }

            // Despachar job para enviar email en background
            MailTemplateJob::dispatch($document, 'reminder');

            return response()->json([
                'success' => true,
                'message' => 'Recordatorio reenviado exitosamente',
                'recipient' => $recipient,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reenviar recordatorio: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send email for missing specific documents
     */
    public function sendMissingDocumentsEmail(Request $request, $uid): JsonResponse
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            // ✅ AUTHORIZATION CHECK: Verify user can send missing documents notification
            try {
                $this->authorizeEmailOperation($document, 'send-missing-docs');
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para enviar notificaciones de documentos faltantes',
                    'error' => $e->getMessage(),
                ], 403);
            }

            $missingDocs = $request->input('missing_docs', []);

            if (empty($missingDocs)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes especificar al menos un documento faltante.',
                ], 400);
            }

            $mailService = new DocumentMailService;
            $result = $mailService->sendMissingDocumentsMail($document, $missingDocs);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email de documentos específicos enviado exitosamente',
                    'missing_docs' => $missingDocs,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar email de documentos específicos',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send custom email to customer
     */
    public function sendCustomEmail(Request $request, $uid): JsonResponse
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            // ✅ AUTHORIZATION CHECK: Verify user can send custom emails
            try {
                $this->authorizeEmailOperation($document, 'send-custom-email');
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para enviar correos personalizados',
                    'error' => $e->getMessage(),
                ], 403);
            }

            $subject = $request->input('subject');
            $message = $request->input('message');

            if (! $subject || ! $message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes proporcionar asunto y mensaje.',
                ], 400);
            }

            $mailService = new DocumentMailService;
            $result = $mailService->sendCustomMail($document, $subject, $message);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email personalizado enviado exitosamente',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar email personalizado',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send upload confirmation email
     */
    public function sendUploadConfirmationEmail(Request $request, $uid): JsonResponse
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            // ✅ AUTHORIZATION CHECK: Verify user can send upload confirmation emails
            try {
                $this->authorizeEmailOperation($document, 'send-upload-confirmation');
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para enviar confirmaciones de subida',
                    'error' => $e->getMessage(),
                ], 403);
            }

            $message = $request->input('message', '');

            $mailService = new DocumentMailService;
            $result = $mailService->sendUploadConfirmationMail($document, $message);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email de confirmación de subida enviado exitosamente',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar email de confirmación',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send approval email
     */
    public function sendApprovalEmail(Request $request, $uid): JsonResponse
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            // ✅ AUTHORIZATION CHECK: Verify user can send approval emails
            try {
                $this->authorizeEmailOperation($document, 'send-approval');
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para enviar notificaciones de aprobación',
                    'error' => $e->getMessage(),
                ], 403);
            }

            $mailService = new DocumentMailService;
            $result = $mailService->sendApprovalMail($document);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email de aprobación enviado exitosamente',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar email de aprobación',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send rejection email
     */
    public function sendRejectionEmail(Request $request, $uid): JsonResponse
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            $reason = $request->input('reason', 'No especificado');
            $rejectedDocs = $request->input('rejected_docs', []);

            $mailService = new DocumentMailService;
            $result = $mailService->sendRejectionMail($document, $reason, $rejectedDocs);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email de rechazo enviado exitosamente',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar email de rechazo',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get email history for a document
     */
    public function emailHistory($uid)
    {
        $document = Document::findByUid($uid);

        if (! $document) {
            return response()->json([
                'success' => false,
                'message' => 'Documento no encontrado.',
            ], 404);
        }

        $emailHistory = DocumentMail::where('document_id', $document->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'emails' => $emailHistory,
        ]);
    }

    /**
     * Preview email content
     */
    public function emailPreview($mailUid)
    {
        $mail = DocumentMail::where('uid', $mailUid)->first();

        if (! $mail) {
            return response()->json([
                'success' => false,
                'message' => 'Email no encontrado.',
            ], 404);
        }

        return view('documents::emails.preview', compact('mail'));
    }

    /**
     * Send email based on document status
     * Private helper method used internally
     */
    private function sendEmailBasedOnStatus(Document $document, string $statusKey, Request $request): ?array
    {
        $mailService = new DocumentMailService;

        switch ($statusKey) {
            case 'pending':
            case 'awaiting_documents':
                // Enviar solicitud inicial o recordatorio
                $result = $mailService->sendInitialRequestMail($document);

                return [
                    'type' => 'initial_request',
                    'label' => $statusKey === 'pending' ? 'Solicitud inicial' : 'Recordatorio',
                    'recipient' => $document->customer_email,
                    'sent' => $result,
                ];

            case 'received':
                // Enviar confirmación de subida
                $result = $mailService->sendUploadConfirmationMail($document, '');

                return [
                    'type' => 'upload_confirmation',
                    'label' => 'Confirmación de subida',
                    'recipient' => $document->customer_email,
                    'sent' => $result,
                ];

            case 'incomplete':
                // Enviar email de documentos específicos
                $missingDocs = $request->input('missing_docs', []);
                if (empty($missingDocs)) {
                    $missingDocs = $document->getMissingDocuments();
                }
                $result = $mailService->sendMissingDocumentsMail($document, $missingDocs);

                return [
                    'type' => 'missing_documents',
                    'label' => 'Documentos específicos',
                    'recipient' => $document->customer_email,
                    'sent' => $result,
                    'missing_docs' => $missingDocs,
                ];

            case 'approved':
                // Enviar notificación de aprobación
                $result = $mailService->sendApprovalMail($document);

                return [
                    'type' => 'approval',
                    'label' => 'Notificación de aprobación',
                    'recipient' => $document->customer_email,
                    'sent' => $result,
                ];

            case 'rejected':
                // Enviar notificación de rechazo
                $reason = $request->input('rejection_reason', 'No especificado');
                $rejectedDocs = $request->input('rejected_docs', []);
                $result = $mailService->sendRejectionMail($document, $reason, $rejectedDocs);

                return [
                    'type' => 'rejection',
                    'label' => 'Notificación de rechazo',
                    'recipient' => $document->customer_email,
                    'sent' => $result,
                    'reason' => $reason,
                    'rejected_docs' => $rejectedDocs,
                ];

            default:
                return null;
        }
    }
}
