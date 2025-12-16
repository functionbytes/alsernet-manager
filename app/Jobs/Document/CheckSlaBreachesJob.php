<?php

namespace App\Jobs\Document;

use App\Models\Document\Document;
use App\Models\Document\DocumentSlaBreach;
use App\Models\Document\DocumentStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CheckSlaBreachesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     *
     * Checks for SLA breaches on documents and creates breach records
     * for escalation and notifications.
     */
    public function handle(): void
    {
        try {
            // Get all active documents with SLA policies
            $documents = Document::query()
                ->whereIn('status_id', [
                    DocumentStatus::where('key', 'pending')->first()?->id,
                    DocumentStatus::where('key', 'incomplete')->first()?->id,
                    DocumentStatus::where('key', 'awaiting_documents')->first()?->id,
                ])
                ->with(['slaPolicy', 'statusHistories', 'breaches'])
                ->get();

            foreach ($documents as $document) {
                if (! $document->slaPolicy) {
                    continue;
                }

                $this->checkUploadRequestSla($document);
                $this->checkReviewSla($document);
                $this->checkApprovalSla($document);
            }

            Log::info('SLA breach check job completed');
        } catch (\Exception $e) {
            Log::error("SLA breach check job failed: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Check if document exceeded upload request time SLA
     */
    private function checkUploadRequestSla(Document $document): void
    {
        $slaPolicy = $document->slaPolicy;
        $uploadRequestMinutes = $slaPolicy->upload_request_time;

        // Apply document type multiplier
        $typeMultiplier = $slaPolicy->getMultiplierForDocumentType(
            $document->configuration?->type ?? 'general'
        );
        $adjustedMinutes = (int) ($uploadRequestMinutes * $typeMultiplier);

        // Check if breach already exists for this SLA
        $existingBreach = $document->breaches()
            ->where('breach_type', 'upload_request')
            ->where('resolved', false)
            ->first();

        if ($existingBreach) {
            return; // Already recorded
        }

        // Calculate time since document creation
        $minutesElapsed = $document->created_at->diffInMinutes(now());

        if ($minutesElapsed > $adjustedMinutes) {
            $minutesOver = $minutesElapsed - $adjustedMinutes;

            // Create breach record
            $breach = DocumentSlaBreach::create([
                'document_id' => $document->id,
                'sla_policy_id' => $slaPolicy->id,
                'breach_type' => 'upload_request',
                'minutes_over' => $minutesOver,
            ]);

            // Check if should escalate
            if ($slaPolicy->enable_escalation) {
                $escalationThreshold = $slaPolicy->getEscalationThreshold($adjustedMinutes);
                if ($minutesElapsed > $escalationThreshold) {
                    $this->escalateBreach($breach, $slaPolicy, $document);
                }
            }

            Log::warning("SLA breach detected for document {$document->id}: upload_request");
        }
    }

    /**
     * Check if document exceeded review time SLA
     */
    private function checkReviewSla(Document $document): void
    {
        $slaPolicy = $document->slaPolicy;
        $reviewMinutes = $slaPolicy->review_time;

        // Check if breach already exists
        $existingBreach = $document->breaches()
            ->where('breach_type', 'review')
            ->where('resolved', false)
            ->first();

        if ($existingBreach) {
            return;
        }

        // Find when document moved to awaiting_documents status
        $awaitingStatus = $document->statusHistories()
            ->where('to_status_id', DocumentStatus::where('key', 'awaiting_documents')->value('id'))
            ->latest()
            ->first();

        if (! $awaitingStatus) {
            return; // Document hasn't reached this status yet
        }

        $minutesElapsed = $awaitingStatus->created_at->diffInMinutes(now());

        if ($minutesElapsed > $reviewMinutes) {
            $minutesOver = $minutesElapsed - $reviewMinutes;

            // Create breach record
            $breach = DocumentSlaBreach::create([
                'document_id' => $document->id,
                'sla_policy_id' => $slaPolicy->id,
                'breach_type' => 'review',
                'minutes_over' => $minutesOver,
            ]);

            // Check if should escalate
            if ($slaPolicy->enable_escalation) {
                $escalationThreshold = $slaPolicy->getEscalationThreshold($reviewMinutes);
                if ($minutesElapsed > $escalationThreshold) {
                    $this->escalateBreach($breach, $slaPolicy, $document);
                }
            }

            Log::warning("SLA breach detected for document {$document->id}: review");
        }
    }

    /**
     * Check if document exceeded approval time SLA
     */
    private function checkApprovalSla(Document $document): void
    {
        $slaPolicy = $document->slaPolicy;
        $approvalMinutes = $slaPolicy->approval_time;

        // Check if breach already exists
        $existingBreach = $document->breaches()
            ->where('breach_type', 'approval')
            ->where('resolved', false)
            ->first();

        if ($existingBreach) {
            return;
        }

        // Find when document last entered a status that needs approval
        $lastStatusChange = $document->statusHistories()
            ->latest()
            ->first();

        if (! $lastStatusChange) {
            return;
        }

        $minutesElapsed = $lastStatusChange->created_at->diffInMinutes(now());

        if ($minutesElapsed > $approvalMinutes) {
            $minutesOver = $minutesElapsed - $approvalMinutes;

            // Create breach record
            $breach = DocumentSlaBreach::create([
                'document_id' => $document->id,
                'sla_policy_id' => $slaPolicy->id,
                'breach_type' => 'approval',
                'minutes_over' => $minutesOver,
            ]);

            // Check if should escalate
            if ($slaPolicy->enable_escalation) {
                $escalationThreshold = $slaPolicy->getEscalationThreshold($approvalMinutes);
                if ($minutesElapsed > $escalationThreshold) {
                    $this->escalateBreach($breach, $slaPolicy, $document);
                }
            }

            Log::warning("SLA breach detected for document {$document->id}: approval");
        }
    }

    /**
     * Escalate a breach by notifying administrators
     */
    private function escalateBreach(
        DocumentSlaBreach                      $breach,
        \App\Models\Document\DocumentSlaPolicy $slaPolicy,
        Document                               $document
    ): void {
        try {
            $breach->update(['escalated' => true, 'escalated_at' => now()]);

            // Get escalation recipients from policy
            $recipients = $slaPolicy->escalation_recipients ?? [];

            if (! empty($recipients)) {
                // Send notification emails to escalation recipients
                foreach ($recipients as $email) {
                    Log::info("Escalating document {$document->id} SLA breach to {$email}");
                    // Can be expanded to send actual notifications
                }
            }

            Log::info("SLA breach {$breach->id} escalated");
        } catch (\Exception $e) {
            Log::error("Failed to escalate SLA breach {$breach->id}: {$e->getMessage()}");
        }
    }
}
