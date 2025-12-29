<?php

namespace Modules\Helpdesk\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Helpdesk\Events\TicketCreated;

/**
 * Send confirmation email to customer when ticket is created
 */
class SendCustomerConfirmation
{
    /**
     * Handle the event
     */
    public function handle(TicketCreated $event): void
    {
        $ticket = $event->ticket;

        Log::info('Sending customer confirmation email', [
            'ticket_id' => $ticket->id,
            'customer_email' => $ticket->customer_email,
        ]);

        Mail::send('emails.helpdesk.ticket_created', ['ticket' => $ticket], function ($message) use ($ticket) {
            $message->to($ticket->customer_email, $ticket->customer_name)
                ->subject("Ticket #{$ticket->ticket_number} - {$ticket->subject}");
        });
    }
}
