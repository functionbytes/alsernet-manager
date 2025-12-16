<?php

namespace App\Mail\Helpdesk;

use App\Models\Helpdesk\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketClosedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Ticket $ticket,
        public bool $isCustomerFacing = true
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Ticket #{$this->ticket->ticket_number} ha sido cerrado - {$this->ticket->subject}",
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
            view: 'emails.helpdesk.ticket-closed',
            with: [
                'ticket' => $this->ticket,
                'customer' => $this->ticket->customer,
                'isCustomerFacing' => $this->isCustomerFacing,
                'ticketUrl' => route('helpdesk.ticket.show', $this->ticket->uid),
                'closedAt' => $this->ticket->closed_at,
                'resolutionTime' => $this->ticket->closed_at?->diffInMinutes($this->ticket->created_at),
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
