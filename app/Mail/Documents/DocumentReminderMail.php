<?php

namespace App\Mail\Documents;

use App\Models\Document\Document;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(protected Document $document)
    {
    }

    public function build(): self
    {
        $customerName = trim(sprintf(
            '%s %s',
            $this->document->customer_firstname ?? '',
            $this->document->customer_lastname ?? ''
        ));

        $orderReference = $this->document->order_reference ;

        $uploadDeadline = $this->document->created_at
            ? Carbon::parse($this->document->created_at)->addDays(3)->format('d/m/Y')
            : null;

        $uploadPortalTemplate = config('documents.upload_portal_url');
        $uploadUrl = $uploadPortalTemplate
            ? str_replace('{uid}', $this->document->uid, rtrim($uploadPortalTemplate))
            : null;

        return $this->subject('Recordatorio: sube la documentación para tu pedido ' . ($orderReference ? '#'.$orderReference : ''))
            ->view('mailers.documents.reminder')
            ->with([
                'document' => $this->document,
                'customerName' => $customerName,
                'orderReference' => $orderReference,
                'documentType' => $this->document->type,
                'uploadDeadline' => $uploadDeadline,
                'uploadUrl' => $uploadUrl,
                'documentUid' => $this->document->uid,
            ]);
    }
}
