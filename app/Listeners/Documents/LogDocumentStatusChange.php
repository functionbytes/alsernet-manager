<?php

namespace App\Listeners\Documents;

use App\Events\Document\DocumentStatusChanged;
use App\Models\Document\DocumentStatusHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogDocumentStatusChange
{
    /**
     * Handle the event.
     *
     * Logs document status changes to:
     * 1. document_status_histories - Status change history
     * 2. document_status_transition_logs - Which transition rule was used
     */
    public function handle(DocumentStatusChanged $event): void
    {
        try {
            // Log to document_status_histories (main history)
            $statusHistory = DocumentStatusHistory::create([
                'document_id' => $event->document->id,
                'from_status_id' => $event->fromStatus->id,
                'to_status_id' => $event->toStatus->id,
                'changed_by' => Auth::id(),
                'reason' => $event->reason,
                'metadata' => [
                    'document_uid' => $event->document->uid,
                    'from_status_key' => $event->fromStatus->key,
                    'to_status_key' => $event->toStatus->key,
                ],
            ]);

            // Find and log the transition used
            $transition = \App\Models\Document\DocumentStatusTransition::where('from_status_id', $event->fromStatus->id)
                ->where('to_status_id', $event->toStatus->id)
                ->active()
                ->first();

            if ($transition) {
                \App\Models\Document\DocumentStatusTransitionLog::create([
                    'document_id' => $event->document->id,
                    'transition_id' => $transition->id,
                    'from_status_id' => $event->fromStatus->id,
                    'to_status_id' => $event->toStatus->id,
                    'performed_by' => Auth::id(),
                    'reason' => $event->reason,
                    'metadata' => [
                        'document_uid' => $event->document->uid,
                        'transition_name' => "{$event->fromStatus->key} → {$event->toStatus->key}",
                    ],
                ]);
            }

            Log::info('Document status change logged', [
                'document_uid' => $event->document->uid,
                'from_status' => $event->fromStatus->key,
                'to_status' => $event->toStatus->key,
                'changed_by' => Auth::id(),
                'transition_id' => $transition?->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log document status change', [
                'document_uid' => $event->document->uid ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
