<?php

namespace App\Jobs\Document;

use App\Models\Document\Document;
use App\Models\Document\DocumentAction;
use App\Models\Lang;
use App\Models\Mail\MailTemplate;
use App\Services\Email\TemplateRendererService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendDocumentEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private Document $document,
        private string $templateKey,
        private array $variables,
    ) {
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle(TemplateRendererService $renderer): void
    {
        // Get template with language support
        $template = $this->getTemplate();

        if (! $template) {
            \Log::warning(
                "Email template not found: {$this->templateKey}",
                ['document_id' => $this->document->id]
            );

            return;
        }

        try {
            // Render template with variables and layout
            $html = $renderer->renderEmailTemplate(
                $template,
                $this->variables
            );

            // Send email
            Mail::html($html, function ($message) use ($template) {
                $message
                    ->to($this->document->customer_email)
                    ->subject($template->subject);
            });

            // Log action
            DocumentAction::create([
                'document_id' => $this->document->id,
                'action_type' => "email_sent_{$this->templateKey}",
                'description' => "Email enviado: {$template->subject}",
                'performed_by' => auth('managers')->id(),
                'metadata' => [
                    'template_key' => $this->templateKey,
                    'recipient' => $this->document->customer_email,
                    'template_id' => $template->id,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send document email', [
                'document_id' => $this->document->id,
                'template_key' => $this->templateKey,
                'error' => $e->getMessage(),
            ]);

            // Log failed action
            DocumentAction::create([
                'document_id' => $this->document->id,
                'action_type' => "email_failed_{$this->templateKey}",
                'description' => "Error al enviar email: {$e->getMessage()}",
                'performed_by' => auth('managers')->id(),
                'metadata' => [
                    'template_key' => $this->templateKey,
                    'error' => $e->getMessage(),
                ],
            ]);

            throw $e;
        }
    }

    /**
     * Get template with language support and fallback
     * Supports both template_id:{id} format and key format
     */
    private function getTemplate(): ?MailTemplate
    {
        // Check if templateKey is template_id:N format (configured template)
        if (str_starts_with($this->templateKey, 'template_id:')) {
            $templateId = (int) str_replace('template_id:', '', $this->templateKey);

            // Try to get template by ID
            $template = MailTemplate::find($templateId);

            if ($template && $template->is_enabled && $template->module === 'documents') {
                return $template;
            }

            // Log warning if configured template not found
            if ($templateId) {
                \Log::warning(
                    "Configured email template not found or disabled: ID {$templateId}",
                    ['document_id' => $this->document->id]
                );
            }

            // Extract default key from the configured template ID for better error tracking
            // This helps identify which action failed
        }

        // Get customer language for key-based fallback
        $langId = $this->document->customer?->lang_id
            ?? auth('managers')?->user()?->lang_id
            ?? Lang::first()?->id;

        // Extract default key if using template_id format
        $searchKey = str_starts_with($this->templateKey, 'template_id:')
            ? $this->extractDefaultKeyFromTemplateId()
            : $this->templateKey;

        // Try exact language match
        $template = MailTemplate::where('key', $searchKey)
            ->where('module', 'documents')
            ->where('lang_id', $langId)
            ->where('is_enabled', true)
            ->first();

        // Fallback to default language
        if (! $template) {
            $defaultLang = Lang::first();

            if ($defaultLang) {
                $template = MailTemplate::where('key', $searchKey)
                    ->where('module', 'documents')
                    ->where('lang_id', $defaultLang->id)
                    ->where('is_enabled', true)
                    ->first();
            }
        }

        return $template;
    }

    /**
     * Extract default key from template ID based on configuration
     * Maps template_id back to action type for fallback
     */
    private function extractDefaultKeyFromTemplateId(): string
    {
        // Default mapping of setting keys to template keys
        $settingToKeyMap = [
            'documents.email_template_initial_request_id' => 'document_initial_request',
            'documents.email_template_reminder_id' => 'document_reminder',
            'documents.email_template_missing_docs_id' => 'document_missing_documents',
            'documents.email_template_approval_id' => 'document_approved',
            'documents.email_template_rejection_id' => 'document_rejected',
            'documents.email_template_completion_id' => 'document_completed',
        ];

        $templateId = (int) str_replace('template_id:', '', $this->templateKey);

        // Find which setting contains this template ID
        foreach ($settingToKeyMap as $setting => $defaultKey) {
            $configuredId = \Illuminate\Support\Facades\Setting::get($setting);
            if ($configuredId && (int) $configuredId === $templateId) {
                return $defaultKey;
            }
        }

        // If no match found, return a generic fallback
        return 'document_template';
    }
}
