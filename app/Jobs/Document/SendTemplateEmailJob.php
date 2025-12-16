<?php

namespace App\Jobs\Document;

use App\Models\Document\Document;
use App\Models\Document\DocumentAction;
use App\Services\Documents\DocumentEmailTemplateService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendTemplateEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private Document $document,
        private string $emailType,
        private array $emailData = [],
        private ?int $adminId = null,
    ) {
        $this->onQueue('emails');
        // Capture admin ID when job is dispatched (before execution in queue)
        if ($this->adminId === null && auth()->check()) {
            $this->adminId = auth()->id();
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $result = match ($this->emailType) {
                'initial_request' => DocumentEmailTemplateService::sendInitialRequest($this->document),
                'reminder' => DocumentEmailTemplateService::sendReminder($this->document),
                'missing_documents' => DocumentEmailTemplateService::sendMissingDocuments(
                    $this->document,
                    $this->emailData['missing_docs'] ?? [],
                    $this->emailData['notes'] ?? null
                ),
                'upload_confirmation' => DocumentEmailTemplateService::sendUploadConfirmation($this->document, $this->emailData['notes'] ?? null),
                'approval' => DocumentEmailTemplateService::sendApprovalEmail($this->document, $this->emailData['notes'] ?? null),
                'rejection' => DocumentEmailTemplateService::sendRejectionEmail($this->document, $this->emailData['reason'] ?? null),
                'completion' => DocumentEmailTemplateService::sendCompletionEmail($this->document, $this->emailData['notes'] ?? null),
                'custom' => DocumentEmailTemplateService::sendCustomEmail(
                    $this->document,
                    $this->emailData['subject'] ?? '',
                    $this->emailData['content'] ?? ''
                ),
                default => false,
            };

            if ($result) {
                $this->logSuccess();
            } else {
                $this->logFailure('Email service returned false');
            }
        } catch (\Exception $e) {
            $this->logFailure($e->getMessage());
            throw $e;
        }
    }

    /**
     * Log successful email send
     */
    private function logSuccess(): void
    {
        try {
            $actionNames = [
                'initial_request' => 'Email de solicitud inicial enviado',
                'reminder' => 'Email de recordatorio enviado',
                'missing_documents' => 'Email de documentos faltantes enviado',
                'upload_confirmation' => 'Email de confirmación de carga enviado',
                'approval' => 'Email de aprobación enviado',
                'rejection' => 'Email de rechazo enviado',
                'completion' => 'Email de finalización enviado',
                'custom' => 'Correo personalizado enviado',
            ];

            DocumentAction::create([
                'document_id' => $this->document->id,
                'action_type' => "email_sent_{$this->emailType}",
                'action_name' => $actionNames[$this->emailType] ?? "Email enviado: {$this->emailType}",
                'description' => "Email enviado: {$this->emailType}",
                'performed_by' => $this->adminId,
                'performed_by_type' => 'system',
                'metadata' => [
                    'email_type' => $this->emailType,
                    'recipient' => $this->document->customer_email,
                ],
            ]);

            \Log::info('Document email sent successfully', [
                'document_uid' => $this->document->uid,
                'email_type' => $this->emailType,
                'recipient' => $this->document->customer_email,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log email action', [
                'document_uid' => $this->document->uid,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log failed email send
     */
    private function logFailure(string $errorMessage): void
    {
        try {
            $actionNames = [
                'initial_request' => 'Fallo al enviar email de solicitud inicial',
                'reminder' => 'Fallo al enviar email de recordatorio',
                'missing_documents' => 'Fallo al enviar email de documentos faltantes',
                'upload_confirmation' => 'Fallo al enviar email de confirmación',
                'approval' => 'Fallo al enviar email de aprobación',
                'rejection' => 'Fallo al enviar email de rechazo',
                'completion' => 'Fallo al enviar email de finalización',
                'custom' => 'Fallo al enviar correo personalizado',
            ];

            DocumentAction::create([
                'document_id' => $this->document->id,
                'action_type' => "email_failed_{$this->emailType}",
                'action_name' => $actionNames[$this->emailType] ?? "Fallo al enviar email: {$this->emailType}",
                'description' => "Error al enviar email: {$errorMessage}",
                'performed_by' => $this->adminId,
                'performed_by_type' => 'system',
                'metadata' => [
                    'email_type' => $this->emailType,
                    'error' => $errorMessage,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log failed email action', [
                'document_uid' => $this->document->uid,
                'error' => $e->getMessage(),
            ]);
        }

        \Log::error('Failed to send document email', [
            'document_uid' => $this->document->uid,
            'email_type' => $this->emailType,
            'error' => $errorMessage,
        ]);
    }
}
