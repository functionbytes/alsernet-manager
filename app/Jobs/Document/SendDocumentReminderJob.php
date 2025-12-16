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
     * Sends reminder emails to customers who haven't uploaded documents
     * within the configured reminder period.
     */
    public function handle(DocumentEmailService $emailService): void
    {
        try {
            // Get the reminder days setting (default: 3 days)
            $reminderDays = (int) setting('documents.reminder_days', 3);
            $enableReminders = setting('documents.enable_reminder', true);

            if (! $enableReminders) {
                Log::info('Document reminders are disabled in settings');

                return;
            }

            // Get documents that are pending or incomplete and haven't been reminded recently
            $pendingStatus = DocumentStatus::where('key', 'pending')->first();
            $incompleteStatus = DocumentStatus::where('key', 'incomplete')->first();

            if (! $pendingStatus) {
                Log::warning('Pending status not found in database');

                return;
            }

            // Find documents that should receive reminders
            $documents = Document::query()
                ->whereIn('status_id', [
                    $pendingStatus->id,
                    $incompleteStatus?->id,
                ])
                ->where('created_at', '<', now()->subDays($reminderDays))
                ->whereNull('reminder_sent_at') // Only send reminder once
                ->orWhere('reminder_sent_at', '<', now()->subDays(7)) // Resend after 7 days
                ->with(['customer', 'status', 'slaPolicy'])
                ->get();

            foreach ($documents as $document) {
                // Skip if customer has no email
                if (! $document->customer?->email) {
                    Log::warning("Document {$document->id} has no customer email");

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

                    Log::info("Reminder email sent successfully for document {$document->id}");
                } catch (\Exception $e) {
                    Log::error("Failed to send reminder for document {$document->id}: {$e->getMessage()}");
                    // Continue with next document instead of failing the entire job
                }
            }

            Log::info('Document reminder job completed. Processed '.count($documents).' documents.');
        } catch (\Exception $e) {
            Log::error("Document reminder job failed: {$e->getMessage()}");
            throw $e;
        }
    }
}
