<?php

namespace Modules\Helpdesk\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Helpdesk\Events\TicketAssigned;

/**
 * Notify agent when a ticket is assigned to them
 */
class NotifyAgentOfAssignment
{
    /**
     * Handle the event
     */
    public function handle(TicketAssigned $event): void
    {
        $ticket = $event->ticket;
        $agent = $event->agent;

        Log::info('Notifying agent of ticket assignment', [
            'ticket_id' => $ticket->id,
            'agent_id' => $agent->id,
            'agent_email' => $agent->email,
        ]);

        Mail::send('emails.helpdesk.ticket_assigned', [
            'ticket' => $ticket,
            'agent' => $agent,
        ], function ($message) use ($ticket, $agent) {
            $message->to($agent->email, $agent->name)
                ->subject("Ticket asignado #{$ticket->ticket_number} - {$ticket->subject}");
        });
    }
}
