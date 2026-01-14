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
use Modules\Helpdesk\Events\SlaBreached;

/**
 * Job to check and mark SLA breaches for tickets
 */
class CheckSlaBreaches implements ShouldQueue
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
    public function handle(SlaService $slaService): void
    {
        try {
            Log::info("CheckSlaBreaches job started at " . now());

            $breachedTickets = Ticket::query()
                ->where('sla_breached', false)
                ->where('due_at', '<', now())
                ->get();

            $breachCount = 0;

            foreach ($breachedTickets as $ticket) {
                try {
                    $ticket->update(['sla_breached' => true]);

                    event(new SlaBreached($ticket));

                    Log::warning("SLA breached for ticket #{$ticket->id} - Subject: {$ticket->subject}", [
                        'ticket_id' => $ticket->id,
                        'due_at' => $ticket->due_at,
                        'breached_at' => now(),
                    ]);

                    $breachCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to process SLA breach for ticket #{$ticket->id}: {$e->getMessage()}", [
                        'ticket_id' => $ticket->id,
                        'exception' => $e,
                    ]);
                }
            }

            Log::info("CheckSlaBreaches job completed at " . now() . " - Total breaches: {$breachCount}");
        } catch (\Exception $e) {
            Log::error("CheckSlaBreaches job failed: {$e->getMessage()}", [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
