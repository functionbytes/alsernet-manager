<?php

namespace Modules\Helpdesk\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Helpdesk\Events\SlaWarning;

/**
 * Send warning notification when ticket SLA is approaching breach
 */
class SendSlaWarningNotification
{
    /**
     * Handle the event
     */
    public function handle(SlaWarning $event): void
    {
        $ticket = $event->ticket;

        if (!$ticket->assignedAgent) {
            Log::warning('Cannot send SLA warning - no agent assigned', [
                'ticket_id' => $ticket->id,
            ]);

            return;
        }

        $timeRemaining = now()->diff($ticket->due_at);

        Log::info('Sending SLA warning notification', [
            'ticket_id' => $ticket->id,
            'agent_id' => $ticket->assignedAgent->id,
            'due_at' => $ticket->due_at,
            'time_remaining' => $timeRemaining->format('%h horas %i minutos'),
        ]);

        Mail::send('emails.helpdesk.sla_warning', [
            'ticket' => $ticket,
            'agent' => $ticket->assignedAgent,
            'timeRemaining' => $timeRemaining,
        ], function ($message) use ($ticket) {
            $message->to($ticket->assignedAgent->email, $ticket->assignedAgent->name)
                ->subject("SLA próximo a vencer - Ticket #{$ticket->ticket_number}");
        });
    }
}
