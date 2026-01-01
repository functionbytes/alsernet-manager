<?php

namespace Modules\Helpdesk\Listeners;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Helpdesk\Events\SlaBreached;

/**
 * Send notification when ticket SLA is breached
 */
class SendSlaBreachNotification
{
    /**
     * Handle the event
     */
    public function handle(SlaBreached $event): void
    {
        $ticket = $event->ticket;
        $timeExceeded = now()->diff($ticket->due_at);

        Log::info('Sending SLA breach notifications', [
            'ticket_id' => $ticket->id,
            'due_at' => $ticket->due_at,
            'time_exceeded' => $timeExceeded->format('%h horas %i minutos'),
        ]);

        $recipients = [];

        if ($ticket->assignedAgent) {
            $recipients[] = $ticket->assignedAgent;
        }

        $managers = User::permission('manage_helpdesk')->get();
        foreach ($managers as $manager) {
            if (!in_array($manager->id, array_column($recipients, 'id'))) {
                $recipients[] = $manager;
            }
        }

        foreach ($recipients as $recipient) {
            Mail::send('emails.helpdesk.sla_breach', [
                'ticket' => $ticket,
                'recipient' => $recipient,
                'timeExceeded' => $timeExceeded,
            ], function ($message) use ($ticket, $recipient) {
                $message->to($recipient->email, $recipient->name)
                    ->subject("SLA EXCEDIDO - Ticket #{$ticket->ticket_number}");
            });

            Log::info('SLA breach notification sent', [
                'ticket_id' => $ticket->id,
                'recipient_id' => $recipient->id,
            ]);
        }
    }
}
