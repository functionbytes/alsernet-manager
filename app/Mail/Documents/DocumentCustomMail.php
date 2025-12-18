<?php

namespace App\Mail\Documents;

use App\Models\Document\Document;
use App\Models\Mail\MailTemplate;
use App\Services\Mails\MailTemplateRendererService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * DocumentCustomMail
 *
 * Mailable para correos personalizados sobre documentos
 * Soporta tanto contenido personalizado como plantillas de BD
 *
 * Uso:
 * - Con contenido personalizado: new DocumentCustomMail($document, $subject, $content)
 * - Con template BD: new DocumentCustomMail($document, null, null, $emailTemplate)
 */
class DocumentCustomMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $document;

    protected $emailSubject;

    protected $emailContent;

    protected $emailTemplate;

    protected $templateVariables = [];

    /**
     * Create a new message instance.
     *
     * @param  string|null  $subject  - Asunto (si null, usa template)
     * @param  string|null  $content  - Contenido personalizado (si null, usa template)
     * @param  MailTemplate|null  $emailTemplate  - Template de BD
     */
    public function __construct(
        Document $document,
        ?string $subject = null,
        ?string $content = null,
        ?MailTemplate $emailTemplate = null
    ) {
        $this->document = $document;
        $this->emailSubject = $subject;
        $this->emailContent = $content;
        $this->emailTemplate = $emailTemplate;

        // Preparar variables para reemplazo
        $this->prepareVariables();
    }

    /**
     * Preparar variables para reemplazo en templates
     */
    protected function prepareVariables(): void
    {
        $customerName = trim($this->document->customer_firstname.' '.$this->document->customer_lastname);

        $this->templateVariables = [
            'CUSTOMER_NAME' => $customerName ?: 'Cliente',
            'CUSTOMER_EMAIL' => $this->document->customer_email ?? '',
            'ORDER_ID' => $this->document->order_id ?? '',
            'ORDER_REFERENCE' => $this->document->order_id ?? '',
            'DOCUMENT_TYPE' => $this->document->document_type ?? 'Documento',
            'UPLOAD_LINK' => route('order.document.upload', ['order' => $this->document->order_id]) ?? '',
            'EXPIRATION_DATE' => $this->document->expiration_date ?? '',
        ];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Determinar si usar template de BD o contenido personalizado
        if ($this->emailTemplate instanceof MailTemplate) {
            return $this->buildFromTemplate();
        } else {
            return $this->buildFromCustomContent();
        }
    }

    /**
     * Construir email desde template de BD
     *
     * @return $this
     */
    private function buildFromTemplate(): self
    {
        // Renderizar template con variables
        $htmlContent = MailTemplateRendererService::renderEmailTemplate(
            $this->emailTemplate,
            $this->templateVariables
        );

        // Obtener asunto (puede tener variables también)
        $subject = MailTemplateRendererService::replaceVariables(
            $this->emailTemplate->subject,
            $this->templateVariables
        );

        return $this->subject($subject)
            ->html($htmlContent);
    }

    /**
     * Construir email desde contenido personalizado (legacy)
     *
     * @return $this
     */
    private function buildFromCustomContent(): self
    {
        // Reemplazar variables en contenido personalizado
        $content = $this->emailContent;

        if ($content) {
            $content = MailTemplateRendererService::replaceVariables(
                $content,
                $this->templateVariables
            );
        }

        return $this->subject($this->emailSubject)
            ->view('mailers.documents.custom')
            ->with([
                'document' => $this->document,
                'content' => $content,
                'customerName' => $this->templateVariables['CUSTOMER_NAME'],
            ]);
    }

    /**
     * Set email template from BD
     *
     * @return $this
     */
    public function setTemplate(MailTemplate $template): self
    {
        $this->emailTemplate = $template;

        return $this;
    }

    /**
     * Set additional variables
     *
     * @return $this
     */
    public function setVariables(array $variables): self
    {
        $this->templateVariables = array_merge($this->templateVariables, $variables);

        return $this;
    }
}
