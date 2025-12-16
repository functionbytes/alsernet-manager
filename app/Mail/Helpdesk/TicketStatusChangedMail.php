<?php

namespace App\Mail\Helpdesk;

use App\Models\Helpdesk\Ticket;
use App\Models\Helpdesk\TicketStatus;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketStatusChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Ticket $ticket,
        public TicketStatus $oldStatus,
        public TicketStatus $newStatus,
        public ?User $changedBy = null
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Ticket #{$this->ticket->ticket_number} cambió de estado a {$this->newStatus->name}",
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
            view: 'emails.helpdesk.ticket-status-changed',
            with: [
                'ticket' => $this->ticket,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
                'changedBy' => $this->changedBy,
                'ticketUrl' => route('helpdesk.ticket.show', $this->ticket->uid),
                'customer' => $this->ticket->customer,
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
