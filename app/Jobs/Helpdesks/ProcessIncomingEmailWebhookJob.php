<?php

namespace App\Jobs\Helpdesks;

use App\Models\Helpdesk\Customer;
use App\Models\Helpdesk\Ticket;
use App\Models\Helpdesk\TicketComment;
use App\Models\Helpdesk\TicketMail;
use App\Models\Helpdesk\TicketStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessIncomingEmailWebhookJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected array $emailData
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->processIncomingEmail();
        } catch (\Exception $e) {
            Log::error('Error processing email webhook: '.$e->getMessage(), [
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * Process the incoming email from webhook.
     */
    protected function processIncomingEmail(): void
    {
        // Find or create ticket
        $ticket = $this->findOrCreateTicket($this->emailData);

        if (! $ticket) {
            Log::warning('Could not create ticket for email: '.$this->emailData['subject']);

            return;
        }

        // Save attachments if any
        if (! empty($this->emailData['attachments'])) {
            $this->emailData['attachments'] = $this->processAttachments($this->emailData['attachments']);
        }

        // Create TicketMail record
        $ticketMail = TicketMail::createFromInbound($this->emailData, $ticket);

        // Create TicketComment for timeline
        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'author_id' => $ticket->customer_id,
            'body' => $this->emailData['body_text'] ?? '',
            'html_body' => $this->emailData['body_html'] ?? null,
            'is_internal' => false,
        ]);

        // Link mail to comment
        $ticketMail->update(['ticket_comment_id' => $comment->id]);

        // Update last message timestamp
        $ticket->update(['last_message_at' => now()]);

        Log::info("Webhook email processed for ticket #{$ticket->ticket_number}");
    }

    /**
     * Find existing ticket or create new one for email.
     */
    protected function findOrCreateTicket(array $parsed): ?Ticket
    {
        // Try to find by Message-ID threading first
        if (! empty($parsed['in_reply_to'])) {
            $existingMail = TicketMail::where('message_id', $parsed['in_reply_to'])->first();
            if ($existingMail) {
                return $existingMail->ticket;
            }
        }

        // Try to find by ticket number in subject
        if (preg_match('/#(TCK-\d{4}-\d{5})/', $parsed['subject'], $matches)) {
            $ticket = Ticket::where('ticket_number', $matches[1])->first();
            if ($ticket) {
                return $ticket;
            }
        }

        // Find or create customer by email
        $fromEmail = $this->extractEmailAddress($parsed['from']);
        $customer = Customer::where('email', $fromEmail)->first();

        if (! $customer) {
            $fromName = $this->extractEmailName($parsed['from']);
            $customer = Customer::create([
                'email' => $fromEmail,
                'name' => $fromName ?: $fromEmail,
            ]);
            Log::info("Created new customer from webhook: {$fromEmail}");
        }

        // Create new ticket
        $ticket = Ticket::create([
            'customer_id' => $customer->id,
            'subject' => $parsed['subject'] ?? 'Sin asunto',
            'description' => $parsed['body_text'] ?? $parsed['body_html'],
            'source' => 'email_webhook',
            'status_id' => TicketStatus::where('is_default', true)->first()?->id ?? 1,
            'priority' => $this->detectPriority($parsed['subject'] ?? ''),
        ]);

        Log::info("Created new ticket #{$ticket->ticket_number} from webhook");

        return $ticket;
    }

    /**
     * Process and save attachments.
     */
    protected function processAttachments(array $attachments): array
    {
        $savedAttachments = [];

        foreach ($attachments as $attachment) {
            try {
                $filename = $attachment['filename'] ?? 'attachment';
                $content = $attachment['content'] ?? null;

                if (! $content) {
                    continue;
                }

                $filePath = $this->saveAttachment($filename, $content);

                if ($filePath) {
                    $savedAttachments[] = [
                        'filename' => $filename,
                        'url' => asset('storage/'.$filePath),
                        'size' => strlen($content),
                        'mime' => $attachment['mime'] ?? 'application/octet-stream',
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('Error processing attachment: '.$e->getMessage());
            }
        }

        return $savedAttachments;
    }

    /**
     * Save attachment to storage.
     */
    protected function saveAttachment(string $filename, string $content): ?string
    {
        try {
            $path = 'helpdesk/attachments/'.date('Y/m/d').'/'.md5(uniqid()).'_'.$filename;

            Storage::disk('local')->put($path, $content);

            return $path;
        } catch (\Exception $e) {
            Log::error('Error saving attachment: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Extract email address from name+email format.
     */
    protected function extractEmailAddress(string $from): string
    {
        if (preg_match('/<(.+?)>/', $from, $matches)) {
            return $matches[1];
        }

        return trim($from);
    }

    /**
     * Extract name from name+email format.
     */
    protected function extractEmailName(string $from): ?string
    {
        if (preg_match('/^(.+?)\s*</', $from, $matches)) {
            return trim($matches[1], ' "\'');
        }

        return null;
    }

    /**
     * Detect priority from subject keywords.
     */
    protected function detectPriority(string $subject): string
    {
        $subject = strtolower($subject);

        if (str_contains($subject, 'urgent') || str_contains($subject, 'crítico')) {
            return 'high';
        }

        if (str_contains($subject, 'baja') || str_contains($subject, 'low')) {
            return 'low';
        }

        return 'normal';
    }
}
