<?php

namespace App\Mail\Helpdesk;

use App\Models\Helpdesk\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketAssignedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Ticket $ticket,
        public ?User $assignedTo = null,
        public ?User $assignedBy = null
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Ticket #{$this->ticket->ticket_number} asignado a ti - {$this->ticket->subject}",
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
            view: 'emails.helpdesk.ticket-assigned',
            with: [
                'ticket' => $this->ticket,
                'assignedTo' => $this->assignedTo,
                'assignedBy' => $this->assignedBy,
                'ticketUrl' => route('helpdesk.ticket.show', $this->ticket->uid),
                'customer' => $this->ticket->customer,
                'priority' => $this->ticket->priority,
                'category' => $this->ticket->category?->name,
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
