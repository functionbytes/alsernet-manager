<?php

namespace Modules\Helpdesk\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Helpdesk\Events\TicketClosed;

/**
 * Update ticket data and notify customer when ticket is closed
 */
class UpdateTicketOnClose
{
    /**
     * Handle the event
     */
    public function handle(TicketClosed $event): void
    {
        $ticket = $event->ticket;

        $ticket->update([
            'closed_at' => now(),
            'closed_by' => auth()->id(),
        ]);

        Log::info('Ticket closed and updated', [
            'ticket_id' => $ticket->id,
            'closed_at' => $ticket->closed_at,
            'closed_by' => $ticket->closed_by,
        ]);

        Mail::send('emails.helpdesk.ticket_closed', [
            'ticket' => $ticket,
        ], function ($message) use ($ticket) {
            $message->to($ticket->customer_email, $ticket->customer_name)
                ->subject("Ticket cerrado #{$ticket->ticket_number} - {$ticket->subject}");
        });

        Log::info('Ticket closed notification sent to customer', [
            'ticket_id' => $ticket->id,
            'customer_email' => $ticket->customer_email,
        ]);
    }
}
