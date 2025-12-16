<?php

namespace App\Mail\Documents;

use App\Models\Document\Document;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentUploadedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(protected Document $document)
    {
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        $customer = $this->document->customer;
        $order = $this->document->order;

        $customerName = trim(sprintf(
            '%s %s',
            $this->document->customer_firstname ?? $customer?->firstname ?? '',
            $this->document->customer_lastname ?? $customer?->lastname ?? ''
        ));

        $orderReference = $this->document->order_reference ?? $order?->reference ?? $this->document->order_id;

        $uploadedAt = $this->document->confirmed_at
            ? Carbon::parse($this->document->confirmed_at)
                ->timezone(config('app.timezone', 'UTC'))
                ->format('d/m/Y H:i')
            : null;

        return $this->subject('Confirmación de recepción de documentos ' . ($orderReference ? '#'.$orderReference : ''))
            ->view('mailers.documents.uploaded')
            ->with([
                'document' => $this->document,
                'customerName' => $customerName ?: $customer?->email,
                'orderReference' => $orderReference,
                'documentType' => $this->document->type,
                'uploadedAt' => $uploadedAt,
            ]);
    }
}
