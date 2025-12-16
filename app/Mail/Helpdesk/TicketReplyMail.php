<?php

namespace App\Mail\Helpdesk;

use App\Models\Helpdesk\Ticket;
use App\Models\Helpdesk\TicketComment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketReplyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Ticket $ticket,
        public TicketComment $comment,
        public bool $isCustomerFacing = true
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Re: Ticket #{$this->ticket->ticket_number} - {$this->ticket->subject}",
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
            view: 'emails.helpdesk.ticket-reply',
            with: [
                'ticket' => $this->ticket,
                'comment' => $this->comment,
                'isInternal' => $this->comment->is_internal,
                'isCustomerFacing' => $this->isCustomerFacing,
                'ticketUrl' => route('helpdesk.ticket.show', $this->ticket->uid),
                'viewUrl' => url('/helpdesk/tickets/'.$this->ticket->id),
                'senderName' => $this->comment->sender_name,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->comment->hasAttachments()) {
            foreach ($this->comment->attachment_urls as $url) {
                $attachments[] = $this->attachmentFromUrl($url);
            }
        }

        return $attachments;
    }

    /**
     * Create an attachment from URL (if local).
     */
    protected function attachmentFromUrl(string $url)
    {
        // Only attach local files for security
        if (! str_starts_with($url, config('app.url'))) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);
        $filePath = public_path(ltrim($path, '/'));

        if (file_exists($filePath)) {
            return Attachment::fromPath($filePath);
        }

        return null;
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

    /**
     * Build headers for email threading.
     */
    protected function buildHeaders(): void
    {
        // Get the original/parent email for threading
        if ($previousMail = $this->ticket->mails()->latest()->first()) {
            $this->withSymfonyMessage(function ($message) use ($previousMail) {
                $headers = $message->getHeaders();

                // RFC 5322 email threading headers
                $headers->addTextHeader('In-Reply-To', $previousMail->message_id);
                $headers->addTextHeader('References', $previousMail->references ?? $previousMail->message_id);
            });
        }
    }
}
