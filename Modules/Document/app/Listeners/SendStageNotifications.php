<?php

namespace Modules\Document\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Modules\Document\Enums\ValidationAction;
use Modules\Document\Events\DocumentValidationStageApproved;
use Modules\Document\Services\ValidationPermissionService;

class SendStageNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(DocumentValidationStageApproved $event): void
    {
        Log::info('Document validation stage approved', [
            'document_id' => $event->document->id,
            'stage' => $event->stageKey,
            'stage_number' => $event->stageNumber,
            'approved_by' => $event->approvedBy->id,
            'is_final' => $event->isFinalApproval,
        ]);

        // Route notifications based on stage
        match ($event->stageKey) {
            'documentacion' => $this->notifyDocumentationStage($event),
            'licencias' => $this->notifyLicenciasStage($event),
            'contabilidad' => $this->notifyContabilidadStage($event),
            default => $this->notifyDefault($event),
        };
    }

    private function notifyDocumentationStage(DocumentValidationStageApproved $event): void
    {
        // ✅ Send approval email to customer if permission allows
        if ($this->shouldSendApprovalEmail($event)) {
            $this->sendCustomerApprovalEmail($event, 'approval');
        }

        // Notify next stage validators
        $this->notifyNextStageValidators($event);

        Log::info('Documentation stage notifications sent', [
            'document_id' => $event->document->id,
        ]);
    }

    private function notifyLicenciasStage(DocumentValidationStageApproved $event): void
    {
        // ⚠️ NO customer email for intermediate stages
        // Only internal notification to next stage validators

        // Notify next stage validators
        $this->notifyNextStageValidators($event);

        Log::info('Licencias stage notifications sent (no customer email)', [
            'document_id' => $event->document->id,
        ]);
    }

    private function notifyContabilidadStage(DocumentValidationStageApproved $event): void
    {
        // ✅ Send FINAL approval email to customer
        if ($this->shouldSendApprovalEmail($event)) {
            $this->sendCustomerApprovalEmail($event, 'final_approval');
        }

        // Notify document owner and relevant parties
        $this->notifyDocumentOwner($event);

        Log::info('Contabilidad stage notifications sent (final approval)', [
            'document_id' => $event->document->id,
            'customer_email' => $event->document->customer_email,
        ]);
    }

    /**
     * Default notification handler (for unknown stages)
     */
    private function notifyDefault(DocumentValidationStageApproved $event): void
    {
        Log::warning('Unknown validation stage for notifications', [
            'document_id' => $event->document->id,
            'stage' => $event->stageKey,
        ]);

        // Send generic notification
        $this->notifyNextStageValidators($event);
    }

    /**
     * Send approval email to customer
     */
    private function sendCustomerApprovalEmail(DocumentValidationStageApproved $event, string $emailType): void
    {
        if (empty($event->document->customer_email)) {
            Log::warning('Cannot send approval email: no customer email', [
                'document_id' => $event->document->id,
            ]);

            return;
        }

        // Queue email job
        // TODO(implementation): Implement DocumentMailService to send based on email type
        // Examples:
        // - 'approval': Stage 1 approval
        // - 'final_approval': Stage 3 (final) approval

        Log::info("Queuing {$emailType} email", [
            'document_id' => $event->document->id,
            'customer_email' => $event->document->customer_email,
            'stage' => $event->stageKey,
        ]);

        // Example (uncomment when DocumentMailService is ready):
        // Mail::queue(new DocumentApprovalMail($event->document, $emailType));
    }

    /**
     * Notify validators in the next stage
     */
    private function notifyNextStageValidators(DocumentValidationStageApproved $event): void
    {
        if ($event->isFinalApproval) {
            // No next stage after final approval
            return;
        }

        // Get next stage information
        $nextStageKey = $this->getNextStageKey($event->stageKey);
        if (! $nextStageKey) {
            return;
        }

        Log::info('Notifying next stage validators', [
            'document_id' => $event->document->id,
            'current_stage' => $event->stageKey,
            'next_stage' => $nextStageKey,
        ]);

        // TODO(implementation): Send notification to ValidatorGroup users for next stage
        // Example:
        // ValidatorGroup::findByKey($nextStageKey)
        //     ->getUsers()
        //     ->each(fn($user) => Notification::send($user, new DocumentAwaitingValidation($event->document)))
    }

    /**
     * Notify document owner
     */
    private function notifyDocumentOwner(DocumentValidationStageApproved $event): void
    {
        Log::info('Notifying document owner', [
            'document_id' => $event->document->id,
            'owner_email' => $event->document->customer_email,
        ]);

        // TODO(implementation): Notify document owner/stakeholders about final approval
    }

    /**
     * Determine if approval email should be sent
     */
    private function shouldSendApprovalEmail(DocumentValidationStageApproved $event): bool
    {
        // Check if stage allows sending approval emails
        $permissionService = app(ValidationPermissionService::class);

        return $permissionService->canPerformValidationAction(
            $event->document,
            ValidationAction::SEND_APPROVAL_EMAIL
        );
    }

    /**
     * Get the next stage key
     */
    private function getNextStageKey(string $currentStageKey): ?string
    {
        $stageOrder = [
            'documentacion' => 1,
            'licencias' => 2,
            'contabilidad' => 3,
        ];

        $nextOrder = ($stageOrder[$currentStageKey] ?? 0) + 1;

        return array_search($nextOrder, $stageOrder) ?: null;
    }
}
