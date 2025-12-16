<?php

namespace App\Services\Documents;

use App\Jobs\Document\SendDocumentEmailJob;
use App\Models\Document\Document;
use App\Models\Document\DocumentConfiguration;
use App\Models\Setting;

class DocumentEmailService
{
    /**
     * Send initial document request email
     */
    public function sendInitialRequest(Document $document): void
    {
        if (Setting::get('documents.enable_initial_request') !== 'yes') {
            return;
        }

        $templateKey = $this->resolveTemplateKey(
            'documents.email_template_initial_request_id',
            'document_initial_request',
            $document
        );

        $variables = $this->buildVariables($document);
        $variables['INITIAL_REQUEST_MESSAGE'] = Setting::get('documents.initial_request_message', '');

        SendDocumentEmailJob::dispatch(
            $document,
            $templateKey,
            $variables
        );
    }

    /**
     * Send reminder email for pending documents
     */
    public function sendReminder(Document $document): void
    {
        if (Setting::get('documents.enable_reminder') !== 'yes') {
            return;
        }

        $templateKey = $this->resolveTemplateKey(
            'documents.email_template_reminder_id',
            'document_reminder',
            $document
        );

        $variables = $this->buildVariables($document);
        $variables['DAYS_SINCE_REQUEST'] = $document->created_at->diffInDays();
        $variables['REMINDER_MESSAGE'] = Setting::get('documents.reminder_message', '');

        SendDocumentEmailJob::dispatch(
            $document,
            $templateKey,
            $variables
        );
    }

    /**
     * Send request for missing documents
     */
    public function sendMissingDocumentsRequest(Document $document, array $missingDocs, string $reason = ''): void
    {
        if (Setting::get('documents.enable_missing_docs') !== 'yes') {
            return;
        }

        $templateKey = $this->resolveTemplateKey(
            'documents.email_template_missing_docs_id',
            'document_missing_documents',
            $document
        );

        $variables = $this->buildVariables($document);
        $variables['MISSING_DOCUMENTS'] = $this->buildMissingDocumentsList($document, $missingDocs);
        $variables['REQUEST_REASON'] = $reason ?: Setting::get('documents.missing_docs_message', '');

        SendDocumentEmailJob::dispatch(
            $document,
            $templateKey,
            $variables
        );
    }

    /**
     * Send approval email
     */
    public function sendApprovalEmail(Document $document): void
    {
        if (Setting::get('documents.enable_approval') !== 'yes') {
            return;
        }

        $templateKey = $this->resolveTemplateKey(
            'documents.email_template_approval_id',
            'document_approved',
            $document
        );

        $variables = $this->buildVariables($document);
        $variables['NEXT_STEPS'] = 'Su documentación está completa y su orden será procesada en breve.';

        SendDocumentEmailJob::dispatch(
            $document,
            $templateKey,
            $variables
        );
    }

    /**
     * Send rejection email
     */
    public function sendRejectionEmail(Document $document, string $reason): void
    {
        if (Setting::get('documents.enable_rejection') !== 'yes') {
            return;
        }

        $templateKey = $this->resolveTemplateKey(
            'documents.email_template_rejection_id',
            'document_rejected',
            $document
        );

        $variables = $this->buildVariables($document);
        $variables['REJECTION_REASON'] = $reason;
        $variables['REQUIRED_DOCUMENTS_LIST'] = $this->buildRequiredDocumentsList($document);

        SendDocumentEmailJob::dispatch(
            $document,
            $templateKey,
            $variables
        );
    }

    /**
     * Send completion email
     */
    public function sendCompletionEmail(Document $document): void
    {
        if (Setting::get('documents.enable_completion') !== 'yes') {
            return;
        }

        $templateKey = $this->resolveTemplateKey(
            'documents.email_template_completion_id',
            'document_completed',
            $document
        );

        $variables = $this->buildVariables($document);
        $variables['NEXT_STEPS'] = 'Su pedido está listo para ser entregado.';

        SendDocumentEmailJob::dispatch(
            $document,
            $templateKey,
            $variables
        );
    }

    /**
     * Send upload confirmation email (sent when client uploads documents)
     */
    public function sendUploadConfirmation(Document $document): void
    {
        if (Setting::get('documents.enable_upload_confirmation') !== 'yes') {
            return;
        }

        $templateKey = $this->resolveTemplateKey(
            'documents.email_template_upload_confirmation_id',
            'document_upload_confirmation',
            $document
        );

        $variables = $this->buildVariables($document);
        $variables['UPLOADED_DOCUMENTS_COUNT'] = $document->media()->count();

        SendDocumentEmailJob::dispatch(
            $document,
            $templateKey,
            $variables
        );
    }

    /**
     * Build base variables for all emails
     */
    private function buildVariables(Document $document): array
    {
        return [
            'CUSTOMER_NAME' => $document->customer_name ?? 'Estimado cliente',
            'CUSTOMER_EMAIL' => $document->customer_email,
            'ORDER_ID' => $document->order_id,
            'ORDER_REFERENCE' => $document->order_reference,
            'DOCUMENT_TYPE_LABEL' => ucfirst(str_replace('_', ' ', $document->document_type)),
            'UPLOAD_LINK' => route('helpdesk.documents.upload', ['token' => $document->upload_token]),
            'EXPIRATION_DATE' => $document->expiration_date?->format('d/m/Y'),
            'COMPANY_NAME' => config('app.name'),
            'SUPPORT_EMAIL' => config('mail.from.address'),
            'SUPPORT_PHONE' => config('documents.support_phone', ''),
            'REQUIRED_DOCUMENTS_LIST' => $this->buildRequiredDocumentsList($document),
        ];
    }

    /**
     * Build HTML list of required documents
     */
    private function buildRequiredDocumentsList(Document $document): string
    {
        $config = DocumentConfiguration::getByType($document->document_type);
        if (! $config) {
            return '';
        }

        $items = array_map(
            fn ($doc) => "<li>$doc</li>",
            $config->required_documents ?? []
        );

        return '<ul>'.implode('', $items).'</ul>';
    }

    /**
     * Build HTML list of missing documents
     */
    private function buildMissingDocumentsList(Document $document, array $missingKeys): string
    {
        $config = DocumentConfiguration::getByType($document->document_type);
        if (! $config) {
            return '';
        }

        $items = array_map(
            fn ($key) => "<li>{$config->required_documents[$key]}</li>",
            array_filter($missingKeys, fn ($key) => isset($config->required_documents[$key]))
        );

        return '<ul>'.implode('', $items).'</ul>';
    }

    /**
     * Resolve the template key to use
     * Returns the configured template ID or falls back to the default key
     *
     * @param  string  $settingKey  Setting key containing template_id
     * @param  string  $defaultKey  Default template key if not configured
     * @param  Document  $document  Document instance
     * @return string Template key or template_id:{id} format
     */
    private function resolveTemplateKey(string $settingKey, string $defaultKey, Document $document): string
    {
        // Get configured template ID from settings
        $configuredTemplateId = Setting::get($settingKey);

        // If no template configured, use default key
        if (! $configuredTemplateId) {
            return $defaultKey;
        }

        // Return template_id format for job to process
        return 'template_id:'.$configuredTemplateId;
    }
}
