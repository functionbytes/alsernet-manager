<?php

namespace App\Mail\Documents;

use App\Models\Document\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentMissingNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        protected Document $document,
        protected array $missingDocs,
        protected ?string $notes = null
    ) {}

    /**
     * Build the message.
     */
    public function build(): self
    {
        $customerName = trim(sprintf(
            '%s %s',
            $this->document->customer_firstname ?? '',
            $this->document->customer_lastname ?? ''
        ));

        $orderReference = $this->document->order_reference;

        $uploadPortalTemplate = config('documents.upload_portal_url');
        $uploadUrl = $uploadPortalTemplate
            ? str_replace('{uid}', $this->document->uid, rtrim($uploadPortalTemplate))
            : null;

        return $this->subject('Documentación pendiente para tu pedido ' . ($orderReference ? '#'.$orderReference : ''))
            ->view('mailers.documents.missing')
            ->with([
                'document' => $this->document,
                'customerName' => $customerName ?: ($this->document->customer_email ?? 'Cliente'),
                'orderReference' => $orderReference,
                'missingDocs' => $this->missingDocs,
                'notes' => $this->notes,
                'uploadUrl' => $uploadUrl,
                'documentUid' => $this->document->uid,
            ]);
    }
}
