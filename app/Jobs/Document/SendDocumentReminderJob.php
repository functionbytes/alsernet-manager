<?php

namespace App\Jobs\Document;

use App\Models\Document\Document;
use App\Models\Document\DocumentStatus;
use App\Services\Documents\DocumentEmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendDocumentReminderJob implements ShouldQueue
{
    use Queueable;

    private ?Document $document = null;

    private bool $isIndividual = false;

    /**
     * Create a new job instance.
     *
     * Can work in two modes:
     * 1. Individual mode: Pass a Document object for a single document with delay
     * 2. Batch mode: No document passed, processes all eligible documents
     */
    public function __construct(?Document $document = null)
    {
        $this->document = $document;
        $this->isIndividual = $document !== null;
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     *
     * Can work in two modes:
     *
     * INDIVIDUAL MODE (when Document is passed):
     * - Sends reminder for a single document
     * - Used when document request is created (with reminder_days delay)
     * - Checks if document has been uploaded before sending
     *
     * BATCH MODE (when no Document is passed):
     * - Processes all eligible documents for reminder
     * - Runs daily at 09:00 UTC
     * - Finds documents created > reminder_days ago
     * - Resends reminders after 7 days if no upload
     */
    public function handle(DocumentEmailService $emailService): void
    {
        try {
            $enableReminders = setting('documents.enable_reminder', true);

            if (! $enableReminders) {
                Log::info('Document reminders are disabled in settings');

                return;
            }

            if ($this->isIndividual) {
                $this->handleIndividualReminder($emailService);
            } else {
                $this->handleBatchReminders($emailService);
            }
        } catch (\Exception $e) {
            Log::error("Document reminder job failed: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Handle reminder for a single document (scheduled with delay)
     */
    private function handleIndividualReminder(DocumentEmailService $emailService): void
    {
        $document = $this->document->fresh();

        if (! $document) {
            Log::warning('Document not found for individual reminder');

            return;
        }

        // Skip if customer has no email
        if (! $document->customer?->email) {
            Log::warning("Document {$document->id} has no customer email");

            return;
        }

        // Skip if documents have already been uploaded
        if ($document->uploaded_confirmation_sent_at) {
            Log::info("Documents for {$document->id} have been uploaded. Skipping individual reminder.");

            return;
        }

        // Skip if already sent reminder today
        $cacheKey = "document_reminder_{$document->id}_".now()->format('Y-m-d');
        if (Cache::has($cacheKey)) {
            Log::info("Reminder already sent for document {$document->id} today");

            return;
        }

        try {
            // Send the reminder email
            $emailService->sendReminder($document);

            // Update reminder sent timestamp
            $document->update(['reminder_sent_at' => now()]);

            // Mark in cache to prevent duplicates
            Cache::put($cacheKey, true, now()->addDay());

            Log::info('Individual reminder email sent successfully', [
                'document_id' => $document->id,
                'document_uid' => $document->uid,
                'customer_email' => $document->customer?->email,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send individual reminder for document {$document->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Handle batch reminders for all eligible documents (daily job)
     */
    private function handleBatchReminders(DocumentEmailService $emailService): void
    {
        $reminderDays = (int) setting('documents.reminder_days', 7);

        // Get documents that are pending or incomplete
        $pendingStatus = DocumentStatus::where('key', 'pending')->first();
        $incompleteStatus = DocumentStatus::where('key', 'incomplete')->first();

        if (! $pendingStatus) {
            Log::warning('Pending status not found in database');

            return;
        }

        // Find documents that should receive RESEND reminders (after 7 days)
        $documents = Document::query()
            ->whereIn('status_id', [
                $pendingStatus->id,
                $incompleteStatus?->id,
            ])
            ->where('created_at', '<', now()->subDays($reminderDays))
            // Only send reminder if documents haven't been uploaded yet
            ->whereNull('uploaded_confirmation_sent_at')
            // Resend reminder after 7 days if still no upload
            ->where('reminder_sent_at', '<', now()->subDays(7))
            ->with(['customer', 'status', 'slaPolicy'])
            ->get();

        $processedCount = 0;

        foreach ($documents as $document) {
            // Skip if customer has no email
            if (! $document->customer?->email) {
                Log::warning("Document {$document->id} has no customer email");

                continue;
            }

            // Double-check that documents haven't been uploaded since query execution
            if ($document->uploaded_confirmation_sent_at) {
                Log::info("Documents for {$document->id} have been uploaded. Skipping batch reminder.");

                continue;
            }

            // Use cache to prevent duplicate reminders within the same execution
            $cacheKey = "document_reminder_{$document->id}_".now()->format('Y-m-d');
            if (Cache::has($cacheKey)) {
                Log::info("Reminder already sent for document {$document->id} today");

                continue;
            }

            try {
                // Send the reminder email
                $emailService->sendReminder($document);

                // Update reminder sent timestamp
                $document->update(['reminder_sent_at' => now()]);

                // Mark in cache to prevent duplicates
                Cache::put($cacheKey, true, now()->addDay());

                $processedCount++;

                Log::info('Batch reminder email sent successfully for document', [
                    'document_id' => $document->id,
                    'document_uid' => $document->uid,
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send batch reminder for document {$document->id}: {$e->getMessage()}");
                // Continue with next document instead of failing the entire job
            }
        }

        Log::info('Batch document reminder job completed', [
            'total_processed' => $processedCount,
            'reminder_days' => $reminderDays,
        ]);
    }
}
