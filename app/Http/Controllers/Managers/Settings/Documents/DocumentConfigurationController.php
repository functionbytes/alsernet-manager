<?php

namespace App\Http\Controllers\Managers\Settings\Documents;

use App\Http\Controllers\Controller;
use App\Models\Mail\MailTemplate;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * OrderConfigurationController
 * Maneja la configuración global de documentos que se aplica a TODOS los tipos
 */
class DocumentConfigurationController extends Controller
{
    public function index()
    {
        return view('managers.views.settings.documents.index');
    }

    /**
     * Mostrar panel de configuración global
     */
    public function globalSettings()
    {
        $globalSettings = $this->getGlobalSettings();

        return view('managers.views.settings.documents.configurations.index', [
            'globalSettings' => $globalSettings,
        ]);
    }

    /**
     * Actualizar configuración global
     */
    public function updateGlobalSettings(Request $request)
    {
        $validated = $request->validate([
            'enable_initial_request' => 'boolean',
            'enable_reminder' => 'boolean',
            'reminder_days' => 'required|integer|min:1|max:90',
            'enable_missing_docs' => 'boolean',
            'enable_custom_email' => 'boolean',
            'enable_upload_confirmation' => 'boolean',
            'enable_approval' => 'boolean',
            'enable_rejection' => 'boolean',
            'mail_template_initial_request_id' => 'nullable|integer|exists:mail_templates,id',
            'mail_template_reminder_id' => 'nullable|integer|exists:mail_templates,id',
            'mail_template_missing_docs_id' => 'nullable|integer|exists:mail_templates,id',
            'mail_template_custom_email_id' => 'nullable|integer|exists:mail_templates,id',
            'mail_template_upload_confirmation_id' => 'nullable|integer|exists:mail_templates,id',
            'mail_template_approval_id' => 'nullable|integer|exists:mail_templates,id',
            'mail_template_rejection_id' => 'nullable|integer|exists:mail_templates,id',
        ]);

        try {
            Setting::set('documents.enable_initial_request', $request->boolean('enable_initial_request') ? 'yes' : 'no');
            Setting::set('documents.enable_reminder', $request->boolean('enable_reminder') ? 'yes' : 'no');
            Setting::set('documents.reminder_days', (string) $validated['reminder_days']);
            Setting::set('documents.enable_missing_docs', $request->boolean('enable_missing_docs') ? 'yes' : 'no');
            Setting::set('documents.enable_custom_email', $request->boolean('enable_custom_email') ? 'yes' : 'no');
            Setting::set('documents.enable_upload_confirmation', $request->boolean('enable_upload_confirmation') ? 'yes' : 'no');
            Setting::set('documents.enable_approval', $request->boolean('enable_approval') ? 'yes' : 'no');
            Setting::set('documents.enable_rejection', $request->boolean('enable_rejection') ? 'yes' : 'no');

            // Save template IDs
            Setting::set('documents.mail_template_initial_request_id', (string) ($validated['mail_template_initial_request_id'] ?? ''));
            Setting::set('documents.mail_template_reminder_id', (string) ($validated['mail_template_reminder_id'] ?? ''));
            Setting::set('documents.mail_template_missing_docs_id', (string) ($validated['mail_template_missing_docs_id'] ?? ''));
            Setting::set('documents.mail_template_custom_email_id', (string) ($validated['mail_template_custom_email_id'] ?? ''));
            Setting::set('documents.mail_template_upload_confirmation_id', (string) ($validated['mail_template_upload_confirmation_id'] ?? ''));
            Setting::set('documents.mail_template_approval_id', (string) ($validated['mail_template_approval_id'] ?? ''));
            Setting::set('documents.mail_template_rejection_id', (string) ($validated['mail_template_rejection_id'] ?? ''));

            return redirect()
                ->back()
                ->with('success', 'Configuración global actualizada correctamente');
        } catch (\Exception $e) {
            \Log::error('Error updating document configuration', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la configuración: '.$e->getMessage());
        }
    }

    /**
     * Obtener configuración global de documentos
     */
    public function getGlobalSettings(): array
    {
        // Get all available templates for Select2
        $availableTemplates = MailTemplate::module('documents')
            ->enabled()
            ->select(['id', 'name', 'key'])
            ->with(['translations' => function ($q) {
                $q->where('lang_id', 1)->select('id', 'mail_template_id', 'lang_id');
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($template) {
                $translation = $template->translations->first();

                return [
                    'id' => (int) $template->id,
                    'text' => sprintf(
                        '%s [Lang: %s]',
                        $template->name,
                        'Default'
                    ),
                    'name' => $template->name,
                    'key' => $template->key,
                ];
            })
            ->toArray();

        return [
            'enable_initial_request' => Setting::get('documents.enable_initial_request', 'yes') === 'yes',
            'enable_reminder' => Setting::get('documents.enable_reminder', 'yes') === 'yes',
            'reminder_days' => (int) Setting::get('documents.reminder_days', '7'),
            'enable_missing_docs' => Setting::get('documents.enable_missing_docs', 'yes') === 'yes',
            'enable_custom_email' => Setting::get('documents.enable_custom_email', 'no') === 'yes',
            'enable_upload_confirmation' => Setting::get('documents.enable_upload_confirmation', 'yes') === 'yes',
            'enable_approval' => Setting::get('documents.enable_approval', 'yes') === 'yes',
            'enable_rejection' => Setting::get('documents.enable_rejection', 'yes') === 'yes',
            'mail_template_initial_request_id' => Setting::get('documents.mail_template_initial_request_id'),
            'mail_template_reminder_id' => Setting::get('documents.mail_template_reminder_id'),
            'mail_template_missing_docs_id' => Setting::get('documents.mail_template_missing_docs_id'),
            'mail_template_custom_email_id' => Setting::get('documents.mail_template_custom_email_id'),
            'mail_template_upload_confirmation_id' => Setting::get('documents.mail_template_upload_confirmation_id'),
            'mail_template_approval_id' => Setting::get('documents.mail_template_approval_id'),
            'mail_template_rejection_id' => Setting::get('documents.mail_template_rejection_id'),
            'available_templates' => $availableTemplates,
        ];
    }

    /**
     * Search email templates for Select2 AJAX
     */
    public function searchTemplates(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');

            // Search templates from documents module that are enabled
            $templates = MailTemplate::module('documents')
                ->enabled()
                ->when($query, function ($q) use ($query) {
                    return $q->search($query);
                })
                ->select(['id', 'name', 'key'])
                ->with(['translations' => function ($q) {
                    $q->where('lang_id', 1)->select('id', 'mail_template_id', 'lang_id');
                }])
                ->orderBy('name')
                ->limit(50)
                ->get()
                ->map(function ($template) {
                    return [
                        'id' => (int) $template->id,
                        'text' => sprintf(
                            '%s [Lang: %s]',
                            $template->name,
                            'Default'
                        ),
                        'name' => $template->name,
                        'key' => $template->key,
                    ];
                })
                ->values()
                ->toArray();

            return response()->json([
                'results' => $templates,
                'pagination' => ['more' => false],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error searching email templates', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'results' => [],
                'error' => 'Error al buscar templates: '.$e->getMessage(),
            ], 500);
        }
    }
}
