<?php

namespace App\Mail\Helpdesk;

use App\Models\Helpdesk\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketSlaWarningMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Ticket $ticket,
        public string $slaTerm = 'first_response', // first_response, next_response, or resolution
        public int $minutesRemaining = 0
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $termLabel = match ($this->slaTerm) {
            'next_response' => 'próxima respuesta',
            'resolution' => 'resolución',
            default => 'primera respuesta',
        };

        return new Envelope(
            subject: "⚠️ SLA en riesgo - Ticket #{$this->ticket->ticket_number} (Plazo de {$termLabel})",
            from: config('mail.from.address'),
            replyTo: [config('helpdesk.email.support_email', 'support@example.com')],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.helpdesk.ticket-sla-warning',
            with: [
                'ticket' => $this->ticket,
                'slaTerm' => $this->slaTerm,
                'minutesRemaining' => $this->minutesRemaining,
                'hoursRemaining' => ceil($this->minutesRemaining / 60),
                'ticketUrl' => route('helpdesk.ticket.show', $this->ticket->uid),
                'customer' => $this->ticket->customer,
                'assignee' => $this->ticket->assignee,
                'slaTermLabel' => match ($this->slaTerm) {
                    'next_response' => 'próxima respuesta',
                    'resolution' => 'resolución',
                    default => 'primera respuesta',
                },
                'dueAt' => match ($this->slaTerm) {
                    'next_response' => $this->ticket->sla_next_response_due_at,
                    'resolution' => $this->ticket->sla_resolution_due_at,
                    default => $this->ticket->sla_first_response_due_at,
                },
                'isBreached' => match ($this->slaTerm) {
                    'next_response' => $this->ticket->sla_next_response_breached,
                    'resolution' => $this->ticket->sla_resolution_breached,
                    default => $this->ticket->sla_first_response_breached,
                },
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Set the job queue.
     */
    public function onQueue(string $queue = 'default'): static
    {
        return parent::onQueue('mail');
    }

    /**
     * Set the job tries.
     */
    public function tries(): int
    {
        return 3;
    }
}
