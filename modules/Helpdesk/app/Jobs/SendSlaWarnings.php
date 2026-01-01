<?php

namespace Modules\Helpdesk\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Helpdesk\Models\Ticket;
use Modules\Helpdesk\Services\SlaService;
use Modules\Helpdesk\Services\NotificationService;
use Modules\Helpdesk\Events\SlaWarning;

/**
 * Job to send warnings for tickets approaching SLA deadline
 */
class SendSlaWarnings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(SlaService $slaService, NotificationService $notificationService): void
    {
        try {
            Log::info("SendSlaWarnings job started at " . now());

            $warningWindow = now()->addMinutes(30);
            $thresholdPercent = config('helpdesk.sla.warning_threshold_percent', 80);

            $approachingTickets = Ticket::query()
                ->where('sla_breached', false)
                ->whereBetween('due_at', [now(), $warningWindow])
                ->whereNotNull('due_at')
                ->get();

            $warningCount = 0;

            foreach ($approachingTickets as $ticket) {
                try {
                    if (!$ticket->created_at || !$ticket->due_at) {
                        continue;
                    }

                    $totalTime = $ticket->created_at->diffInSeconds($ticket->due_at);
                    $usedTime = $ticket->created_at->diffInSeconds(now());
                    $percentUsed = $totalTime > 0 ? ($usedTime / $totalTime) * 100 : 0;

                    if ($percentUsed >= $thresholdPercent) {
                        event(new SlaWarning($ticket, $percentUsed));

                        $notificationService->sendSlaWarning($ticket, $percentUsed);

                        Log::warning("SLA warning for ticket #{$ticket->id} - {$percentUsed}% time used", [
                            'ticket_id' => $ticket->id,
                            'subject' => $ticket->subject,
                            'due_at' => $ticket->due_at,
                            'percent_used' => round($percentUsed, 2),
                            'threshold' => $thresholdPercent,
                        ]);

                        $warningCount++;
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to send SLA warning for ticket #{$ticket->id}: {$e->getMessage()}", [
                        'ticket_id' => $ticket->id,
                        'exception' => $e,
                    ]);
                }
            }

            Log::info("SendSlaWarnings job completed at " . now() . " - Total warnings sent: {$warningCount}");
        } catch (\Exception $e) {
            Log::error("SendSlaWarnings job failed: {$e->getMessage()}", [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
