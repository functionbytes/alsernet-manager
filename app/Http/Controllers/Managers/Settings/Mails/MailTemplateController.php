<?php

namespace App\Http\Controllers\Managers\Settings\Mails;

use App\Http\Controllers\Controller;
use App\Models\Lang;
use App\Models\Mail\MailLayout;
use App\Models\Mail\MailTemplate;
use App\Models\Mail\MailTemplateLang;
use Illuminate\Http\Request;

class MailTemplateController extends Controller
{
    /**
     * Listar todos los templates de email (únicos por key, no por idioma)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $module = $request->input('module');
        $langId = $request->input('lang_id', 1); // Default to first language for preview

        $query = MailTemplate::distinct('key')
            ->select('mail_templates.*')
            ->orderByDesc('updated_at');

        // Búsqueda por nombre, key o descripción
        if ($search) {
            $query->search($search);
        }

        // Filtrar por módulo
        if ($module) {
            $query->module($module);
        }

        // Eager load translations for selected language
        $query->with(['translations' => function ($q) use ($langId) {
            $q->where('lang_id', $langId);
        }]);

        $templates = $query->paginate(15);

        // Obtener módulos únicos para filtro
        $modules = MailTemplate::distinct('module')->pluck('module')->toArray();

        // Obtener idiomas disponibles
        $langs = \App\Models\Lang::available()->get();

        return view('managers.views.mailers.templates.index', [
            'templates' => $templates,
            'search' => $search,
            'module' => $module,
            'langId' => $langId,
            'modules' => $modules,
            'langs' => $langs,
        ]);
    }

    /**
     * Mostrar formulario para crear nuevo template
     */
    public function create(Request $request)
    {
        $template = new MailTemplate;
        $layouts = MailLayout::where('type', 'layout')
            ->where('is_enabled', true)
            ->with('translations')
            ->orderBy('alias')
            ->get();

        // Obtener módulo del request o usar 'documents' como default
        $module = $request->input('module', 'documents');

        // Obtener idioma del request (default: primer idioma disponible)
        $langId = $request->input('lang_id');
        if (! $langId) {
            $defaultLang = \App\Models\Lang::available()->first();
            $langId = $defaultLang?->id;
        }

        // Estructura base para el nuevo template
        $baseContent = MailTemplate::getStructureForModule($module);

        $variables = MailTemplate::defaultVariables($module);

        // Obtener idiomas disponibles
        $langs = \App\Models\Lang::available()->get();

        return view('managers.views.mailers.templates.create', [
            'template' => $template,
            'layouts' => $layouts,
            'module' => $module,
            'langId' => $langId,
            'currentLangId' => $langId,
            'variables' => $variables,
            'baseContent' => $baseContent,
            'langs' => $langs,
        ]);
    }

    /**
     * Guardar nuevo template
     */
    public function store(Request $request)
    {
        // Validar datos
        $validated = $request->validate([
            'key' => 'required|string',
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'preheader' => 'nullable|string|max:255',
            'content' => 'required|string',
            'layout_id' => 'nullable|exists:mail_layouts,id',
            'module' => 'required|string|in:core,documents,orders,notifications',
            'lang_id' => 'required|exists:langs,id',
            'is_protected' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        try {
            // Validar que no exista ya un template con esta key
            $existing = MailTemplate::where('key', $validated['key'])->first();

            if ($existing) {
                return redirect()
                    ->back()
                    ->with('error', 'Ya existe un template con esta clave (key)')
                    ->withInput();
            }

            // Obtener todos los idiomas disponibles
            $allLangs = \App\Models\Lang::available()->get();

            if ($allLangs->isEmpty()) {
                return redirect()
                    ->back()
                    ->with('error', 'No hay idiomas disponibles en el sistema')
                    ->withInput();
            }

            // Crear UN template principal (sin subject/content, solo metadata)
            $template = MailTemplate::create([
                'key' => $validated['key'],
                'name' => $validated['name'],
                'layout_id' => $validated['layout_id'],
                'module' => $validated['module'],
                'description' => $validated['description'] ?? null,
                'is_enabled' => true,
                'is_protected' => $validated['is_protected'] ?? false,
            ]);

            // Crear traducciones para todos los idiomas
            foreach ($allLangs as $lang) {
                MailTemplateLang::create([
                    'mail_template_id' => $template->id,
                    'lang_id' => $lang->id,
                    'subject' => $validated['subject'],
                    'preheader' => $validated['preheader'] ?? null,
                    'content' => $validated['content'],
                ]);
            }

            return redirect()
                ->route('manager.settings.mailers.templates.edit', [
                    'uid' => $template->uid,
                    'lang_id' => $validated['lang_id'],
                ])
                ->with('success', "Template '{$validated['name']}' creado exitosamente para todos los idiomas (".count($allLangs).' versiones)');
        } catch (\Exception $e) {
            \Log::error('Error creating email template', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validated ?? [],
            ]);

            return redirect()
                ->back()
                ->with('error', 'Error al crear el template: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar formulario para editar template
     */
    public function edit(Request $request, $uid, $translation_uid = null)
    {
        $template = MailTemplate::where('uid', $uid)->firstOrFail();

        // Si viene translation_uid, cargar por UID de traducción
        if ($translation_uid) {
            $translation = MailTemplateLang::where('uid', $translation_uid)
                ->where('mail_template_id', $template->id)
                ->firstOrFail();
            $langId = $translation->lang_id;
        } else {
            // Obtener idioma actual (del request o default a 1)
            $langId = $request->input('lang_id', 1);
            // Obtener la traducción para el idioma actual
            $translation = $template->translate($langId);
        }

        // Si no existe traducción para este idioma, crear una vacía
        if (! $translation) {
            $translation = new EmailTemplateTranslation([
                'mail_template_id' => $template->id,
                'lang_id' => $langId,
                'subject' => '',
                'preheader' => '',
                'content' => '',
            ]);
        }

        $layouts = MailLayout::where('type', 'layout')
            ->where('is_enabled', true)
            ->with('translations')
            ->orderBy('alias')
            ->get();
        $variables = $template->getAvailableVariables();

        // Obtener idiomas disponibles para mostrar selector
        $langs = Lang::available()->get();

        // Obtener otras traducciones para este template
        $otherTranslations = $template->translations()
            ->where('lang_id', '!=', $langId)
            ->with('lang')
            ->get();

        return view('managers.views.mailers.templates.edit', [
            'template' => $template,
            'translation' => $translation,
            'currentLangId' => $langId,
            'layouts' => $layouts,
            'variables' => $variables,
            'langs' => $langs,
            'otherTranslations' => $otherTranslations,
            'otherLangs' => $otherTranslations, // Keep for backwards compatibility
        ]);
    }

    /**
     * Actualizar template
     */
    public function update(Request $request, $uid)
    {
        $template = MailTemplate::where('uid', $uid)->firstOrFail();

        // Validar datos
        $validated = $request->validate([
            'subject' => 'nullable|string|max:255',
            'preheader' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'layout_id' => 'nullable|exists:mail_layouts,id',
            'is_enabled' => 'nullable|boolean',
            'is_protected' => 'nullable|boolean',
            'lang_id' => 'required|exists:langs,id',
            'description' => 'nullable|string',
            'translation_uid' => 'nullable|string',
        ]);

        try {
            // Actualizar metadata del template (name, layout_id, is_enabled, etc.)
            $template->update([
                'layout_id' => $validated['layout_id'],
                'is_enabled' => $validated['is_enabled'] ?? true,
                'is_protected' => $validated['is_protected'] ?? false,
                'description' => $validated['description'] ?? null,
            ]);

            // Obtener la traducción para el idioma actual
            $translation = $template->translate($validated['lang_id']);

            if ($translation) {
                // Actualizar traducción existente
                $translation->update([
                    'subject' => $validated['subject'],
                    'preheader' => $validated['preheader'] ?? null,
                    'content' => $validated['content'],
                ]);
            } else {
                // Crear nueva traducción si no existe
                $translation = EmailTemplateTranslation::create([
                    'mail_template_id' => $template->id,
                    'lang_id' => $validated['lang_id'],
                    'subject' => $validated['subject'],
                    'preheader' => $validated['preheader'] ?? null,
                    'content' => $validated['content'],
                ]);
            }

            return redirect()
                ->route('manager.settings.mailers.templates.edit', [
                    'uid' => $template->uid,
                    'translation_uid' => $translation->uid,
                ])
                ->with('success', 'Template actualizado exitosamente');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al actualizar: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Obtener preview HTML del template (vista completa)
     */
    public function preview(Request $request, $uid)
    {
        $template = MailTemplate::where('uid', $uid)->firstOrFail();

        // Obtener idioma actual (del request o default a 1)
        $langId = $request->input('lang_id', 1);

        // Obtener traducción para el idioma especificado
        $translation = $template->translate($langId);

        if (! $translation) {
            return redirect()->back()->with('error', 'No existe traducción para este idioma');
        }

        // Usar el MISMO servicio que se usa para enviar emails reales
        // Esto garantiza que el preview sea idéntico al email enviado
        $variables = $this->getPreviewVariables($template);
        $html = \App\Services\Email\TemplateRendererService::renderEmailTemplate($template, $variables, $langId);

        return view('managers.views.mailers.templates.preview', [
            'template' => $template,
            'translation' => $translation,
            'html' => $html,
        ]);
    }

    /**
     * Obtener preview en AJAX (para split-panel en vivo)
     */
    public function previewAjax(Request $request, $uid)
    {
        try {
            $template = MailTemplate::where('uid', $uid)->firstOrFail();

            // Obtener idioma actual (del request o default a 1)
            $langId = $request->input('lang_id', 1);

            // Obtener layout_id del request (para live preview sin guardar)
            $overrideLayoutId = $request->input('layout_id');

            // Obtener contenido del editor (live preview)
            $customContent = $request->input('content');

            // Si hay override de layout_id, actualizarlo temporalmente en el template
            $originalLayoutId = $template->layout_id;
            if ($overrideLayoutId !== null) {
                $template->layout_id = $overrideLayoutId;
            }

            // Si hay contenido custom, actualizar la traducción temporalmente
            if ($customContent !== null) {
                $translation = $template->translate($langId);
                if ($translation) {
                    $originalContent = $translation->content;
                    $translation->content = $customContent;
                }
            }

            // Usar el MISMO servicio que se usa para enviar emails reales
            // Esto garantiza que el preview sea idéntico al email enviado
            $variables = $this->getPreviewVariables($template);
            $html = \App\Services\Email\TemplateRendererService::renderEmailTemplate($template, $variables, $langId);

            // Restaurar valores originales
            $template->layout_id = $originalLayoutId;
            if (isset($originalContent) && isset($translation)) {
                $translation->content = $originalContent;
            }

            return response()->json([
                'success' => true,
                'html' => $html,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en previewAjax: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'html' => '<div class="alert alert-danger p-3">Error: '.$e->getMessage().'</div>',
            ], 500);
        }
    }

    /**
     * Obtener variables de ejemplo para el preview
     */
    private function getPreviewVariables(MailTemplate $template): array
    {
        $baseVariables = [
            // Sistema
            'COMPANY_NAME' => config('app.name', 'Alsernet'),
            'SITE_NAME' => config('app.name', 'Alsernet'),
            'SITE_URL' => config('app.url', 'https://example.com'),
            'SUPPORT_EMAIL' => config('mail.support.address', 'soporte@example.com'),
            'SUPPORT_PHONE' => '+34 900 000 000',
            'CONTACT_EMAIL' => config('mail.from.address', 'info@example.com'),
            'CURRENT_YEAR' => date('Y'),
            'CURRENT_DATE' => date('d/m/Y'),
            'CURRENT_DATETIME' => date('d/m/Y H:i'),

            // Cliente (ejemplos)
            'CUSTOMER_NAME' => 'Juan García Pérez',
            'CUSTOMER_FIRSTNAME' => 'Juan',
            'CUSTOMER_LASTNAME' => 'García Pérez',
            'CUSTOMER_EMAIL' => 'juan.garcia@example.com',

            // Pedido (ejemplos)
            'ORDER_ID' => '12345',
            'ORDER_REFERENCE' => 'REF-2024-001',
            'ORDER_DATE' => date('d/m/Y'),

            // Documento (ejemplos)
            'DOCUMENT_TYPE' => 'DNI',
            'DOCUMENT_TYPE_LABEL' => 'Documento de Identidad',
            'DOCUMENT_UID' => 'DOC-ABC123',
            'UPLOAD_LINK' => config('app.url').'/upload/DOC-ABC123',
            'UPLOAD_URL' => config('app.url').'/upload/DOC-ABC123',
            'EXPIRATION_DATE' => date('d/m/Y', strtotime('+3 days')),
            'DEADLINE' => date('d/m/Y', strtotime('+3 days')),

            // Contenido personalizado (para plantillas custom)
            'custom_content' => '<p>Este es un contenido de ejemplo para el correo personalizado.</p>',
            'CUSTOM_CONTENT' => '<p>Este es un contenido de ejemplo para el correo personalizado.</p>',
        ];

        // Agregar variables específicas según el módulo
        if ($template->module === 'documents') {
            $baseVariables['MISSING_DOCUMENTS'] = '<ul style="margin: 10px 0; padding-left: 20px;"><li style="margin: 5px 0;">DNI o Pasaporte</li><li style="margin: 5px 0;">Comprobante de domicilio</li><li style="margin: 5px 0;">Justificante de ingresos</li></ul>';
            $baseVariables['MISSING_DOCUMENTS_LIST'] = $baseVariables['MISSING_DOCUMENTS'];
            $baseVariables['REQUIRED_DOCUMENTS_LIST'] = $baseVariables['MISSING_DOCUMENTS'];
            $baseVariables['DOCUMENT_INSTRUCTIONS'] = 'Por favor, cargue los documentos solicitados en formato PDF o imagen.';

            // Variable NOTES_SECTION con ejemplo de nota del administrador
            $baseVariables['NOTES_SECTION'] = '<div style="background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #ff9800;">
                <p style="margin: 0; font-weight: bold; color: #374151;">Nota adicional del administrador:</p>
                <p style="margin-top: 10px; font-style: italic; color: #555;">"La foto del DNI está borrosa. Por favor, asegúrese de que todos los datos sean legibles. También necesitamos que el comprobante de domicilio no tenga más de 3 meses de antigüedad."</p>
            </div>';
            $baseVariables['NOTES'] = 'La foto del DNI está borrosa. Por favor, asegúrese de que todos los datos sean legibles.';
            $baseVariables['REQUEST_REASON'] = $baseVariables['NOTES'];

            // Variables para emails de recordatorio
            $baseVariables['DAYS_SINCE_REQUEST'] = '5';
            // REMINDER_MESSAGE sin contenedor - la plantilla ya tiene su propio estilo
            $baseVariables['REMINDER_MESSAGE'] = 'Han pasado <strong>5 días</strong> desde que solicitamos su documentación y aún no hemos recibido respuesta. Le recordamos que es importante que nos envíe los documentos lo antes posible para poder continuar con el procesamiento de su pedido.';
        }

        return $baseVariables;
    }

    /**
     * Eliminar template
     */
    public function destroy($uid)
    {
        $template = MailTemplate::where('uid', $uid)->firstOrFail();

        // Verificar si el template está protegido
        if ($template->is_protected) {
            return redirect()
                ->back()
                ->with('error', 'No se puede eliminar un template protegido. Desactiva la protección primero si deseas eliminarlo.');
        }

        try {
            $name = $template->name;

            // Delete all translations first (if not using cascade)
            $template->translations()->delete();

            // Delete the template
            $template->delete();

            return redirect()
                ->route('manager.settings.mailers.templates.index')
                ->with('success', "Template '{$name}' eliminado exitosamente");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al eliminar: '.$e->getMessage());
        }
    }

    /**
     * Renderizar template con header y footer (sistema estilo Mercosan)
     */
    private function renderTemplateWithLayout(MailTemplate $template, ?int $langId = null, ?int $overrideLayoutId = null, ?string $customContent = null): string
    {
        $langId = $langId ?? 1;

        // Obtener la traducción del template para el idioma especificado
        $translation = $template->translate($langId);

        if (! $translation) {
            return '<div class="alert alert-danger p-3">No existe traducción para este idioma</div>';
        }

        // 1. Reemplazar variables en el contenido del template
        // Usar contenido custom si se proporciona (para live preview)
        $content = $customContent ?? $translation->content;
        $variables = $template->getAvailableVariables();

        foreach ($variables as $variable) {
            $placeholder = '{'.$variable['name'].'}';
            $exampleValue = $this->getExampleValue($variable['name']);
            $content = str_replace($placeholder, $exampleValue, $content);
        }

        // 2. Obtener header y footer de los layouts base
        $headerLayout = MailLayout::where('alias', 'mail_template_header')->first();
        $footerLayout = MailLayout::where('alias', 'mail_template_footer')->first();

        $header = '';
        $footer = '';

        if ($headerLayout) {
            $headerTranslation = $headerLayout->translate($langId);
            $header = $headerTranslation ? $headerTranslation->content : '';
        }

        if ($footerLayout) {
            $footerTranslation = $footerLayout->translate($langId);
            $footer = $footerTranslation ? $footerTranslation->content : '';
        }

        // 3. Reemplazar variables globales en header y footer
        $globalVariables = $this->getGlobalVariables($translation);

        $header = $this->replaceVariables($header, $globalVariables);
        $footer = $this->replaceVariables($footer, $globalVariables);

        // 4. Crear preheader HTML si existe
        $preheaderHtml = '';
        if ($translation->preheader) {
            $preheaderHtml = <<<PREHEADER
    <!-- Preheader text (hidden in email but visible in inbox preview) -->
    <div style="display: none; max-height: 0; overflow: hidden; mso-hide: all;" aria-hidden="true">
        {$translation->preheader}
    </div>
    <!-- End Preheader -->
PREHEADER;
        }

        // 5. Determinar qué layout usar (override o el del template)
        $layoutToUse = null;
        if ($overrideLayoutId) {
            $layoutToUse = MailLayout::find($overrideLayoutId);
        } elseif ($template->layout) {
            $layoutToUse = $template->layout;
        }

        // 6. Si hay un layout, usarlo
        if ($layoutToUse) {
            $layoutTranslation = $layoutToUse->translate($langId);
            $layoutContent = $layoutTranslation ? $layoutTranslation->content : $layoutToUse->content;

            // Reemplazar {{ header }} y {{ footer }}
            $layoutContent = str_replace('{{ header }}', $header, $layoutContent);
            $layoutContent = str_replace('{{ footer }}', $footer, $layoutContent);

            // Reemplazar {CONTENT}
            $layoutContent = str_replace('{CONTENT}', $content, $layoutContent);

            return $preheaderHtml.$layoutContent;
        }

        // 7. Si no hay layout personalizado, retornar solo el contenido
        return $preheaderHtml.$content;
    }

    /**
     * Obtener variables globales para header/footer
     */
    private function getGlobalVariables(?MailTemplateLang $translation = null): array
    {
        $subject = $translation ? ($translation->subject ?? 'Email de Alsernet') : 'Email de Alsernet';

        return [
            'LOGO_URL' => config('app.url').'/images/logo.png',
            'SITE_NAME' => config('app.name', 'Alsernet'),
            'SITE_URL' => config('app.url'),
            'SITE_EMAIL' => 'soporte@Alsernet.com',
            'COMPANY_ADDRESS' => 'Calle Principal 123',
            'COMPANY_CITY' => 'Ciudad',
            'COMPANY_COUNTRY' => 'Colombia',
            'COMPANY_PHONE' => '+57 300 123 4567',
            'COMPANY_EMAIL' => 'info@Alsernet.com',
            'CURRENT_YEAR' => date('Y'),
            'CURRENT_MONTH' => date('m'),
            'CURRENT_DAY' => date('d'),
            'RECIPIENT_EMAIL' => 'ejemplo@email.com',
            'mail_SUBJECT' => $subject,
        ];
    }

    /**
     * Reemplazar variables en un texto
     */
    private function replaceVariables(string $text, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $text = str_replace('{'.$key.'}', $value, $text);
        }

        return $text;
    }

    /**
     * Formatear HTML (API endpoint)
     */
    public function formatHtml(Request $request)
    {
        $validated = $request->validate([
            'html' => 'required|string',
        ]);

        try {
            $formatted = $this->beautifyHtml($validated['html']);

            return response()->json([
                'success' => true,
                'formatted' => $formatted,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al formatear HTML',
            ], 500);
        }
    }

    /**
     * Formatear HTML con indentación
     */
    private function beautifyHtml(string $html): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        // Suprimir errores de HTML malformado
        libxml_use_internal_errors(true);

        // Cargar HTML
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Limpiar errores
        libxml_clear_errors();

        // Obtener HTML formateado
        $formatted = $dom->saveHTML();

        // Remover la declaración XML que se agregó
        $formatted = str_replace('<?xml encoding="UTF-8">', '', $formatted);

        return trim($formatted);
    }

    /**
     * Obtener variable con valor de ejemplo
     */
    private function getExampleValue($variableName): string
    {
        $examples = [
            'CUSTOMER_NAME' => 'Juan García',
            'CUSTOMER_EMAIL' => 'juan@example.com',
            'ORDER_ID' => '12345',
            'ORDER_NUMBER' => 'ORD-2025-001',
            'ORDER_DATE' => date('d/m/Y'),
            'ORDER_TOTAL' => '$5,000.00',
            'ORDER_STATUS' => 'Completada',
            'ORDER_REFERENCE' => 'ORD-2025-001',
            'DOCUMENT_TYPE' => 'Cédula de Ciudadanía',
            'UPLOAD_LINK' => 'https://Alsernet.test/upload/doc-12345',
            'EXPIRATION_DATE' => date('d/m/Y', strtotime('+7 days')),
            'SITE_NAME' => 'Alsernet',
            'SITE_URL' => config('app.url'),
            'SITE_EMAIL' => 'info@Alsernet.test',
            'RECIPIENT_NAME' => 'María López',
            'RECIPIENT_EMAIL' => 'maria@example.com',
            'NOTIFICATION_TYPE' => 'Información General',
            'NOTIFICATION_DATE' => date('d/m/Y H:i'),
            'CURRENT_YEAR' => date('Y'),
            'CURRENT_MONTH' => date('m'),
            'CURRENT_DAY' => date('d'),
        ];

        return $examples[$variableName] ?? '{{'.$variableName.'}}';
    }

    /**
     * Obtener variables disponibles en AJAX (alias para variables())
     */
    public function getVariables($uid)
    {
        return $this->variables($uid);
    }

    /**
     * Obtener variables disponibles para el template (usado por edit.blade.php)
     */
    public function variables($uid)
    {
        try {
            $template = MailTemplate::where('uid', $uid)->firstOrFail();

            // Obtener variables desde la base de datos filtradas por el módulo del template
            // Incluye variables del módulo específico + variables core
            $dbVariables = \App\Models\Mail\MailVariable::query()
                ->where('is_enabled', true)
                ->where(function ($query) use ($template) {
                    $query->where('module', $template->module)
                        ->orWhere('module', 'core');
                })
                ->orderBy('category')
                ->orderBy('key')
                ->get();

            // Agrupar variables por categoría
            $grouped = $dbVariables->groupBy('category');

            $variables = [];
            foreach ($grouped as $category => $items) {
                $categoryLabel = ucfirst($category);
                $categoryLabels = [
                    'system' => 'Sistema',
                    'customer' => 'Cliente',
                    'order' => 'Pedido',
                    'document' => 'Documento',
                    'general' => 'General',
                ];

                $variables[] = [
                    'group' => $categoryLabels[$category] ?? $categoryLabel,
                    'items' => $items->map(function ($variable) {
                        return [
                            'name' => $variable->key,
                            'description' => $variable->description ?? $variable->name,
                        ];
                    })->toArray(),
                ];
            }

            return response()->json([
                'success' => true,
                'variables' => $variables,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading variables: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'variables' => [],
            ], 500);
        }
    }

    /**
     * Get variables for a specific module (for create form)
     * Used when creating a new template to load variables based on selected module
     */
    public function variablesByModule(Request $request)
    {
        try {
            $module = $request->query('module');

            if (!$module) {
                return response()->json([
                    'success' => false,
                    'message' => 'Module parameter is required',
                    'variables' => [],
                ], 400);
            }

            // Get variables from database filtered by the specified module
            // Includes variables from the specific module + core variables
            $dbVariables = \App\Models\Mail\MailVariable::query()
                ->where('is_enabled', true)
                ->where(function ($query) use ($module) {
                    $query->where('module', $module)
                        ->orWhere('module', 'core');
                })
                ->orderBy('category')
                ->orderBy('key')
                ->get();

            // Group variables by category
            $grouped = $dbVariables->groupBy('category');

            $variables = [];
            foreach ($grouped as $category => $items) {
                $categoryLabel = ucfirst($category);
                $categoryLabels = [
                    'system' => 'Sistema',
                    'customer' => 'Cliente',
                    'order' => 'Pedido',
                    'document' => 'Documento',
                    'general' => 'General',
                ];

                $variables[] = [
                    'group' => $categoryLabels[$category] ?? $categoryLabel,
                    'items' => $items->map(function ($variable) {
                        return [
                            'name' => $variable->key,
                            'description' => $variable->description ?? $variable->name,
                        ];
                    })->toArray(),
                ];
            }

            return response()->json([
                'success' => true,
                'variables' => $variables,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading variables by module: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'variables' => [],
            ], 500);
        }
    }

    /**
     * Cambiar estado (enabled/disabled)
     */
    public function toggleStatus($uid)
    {
        $template = MailTemplate::where('uid', $uid)->firstOrFail();

        $template->is_enabled = ! $template->is_enabled;
        $template->save();

        $status = $template->is_enabled ? 'Habilitado' : 'Deshabilitado';

        return redirect()
            ->back()
            ->with('success', "Template '{$template->name}' $status");
    }

    /**
     * Enviar email de prueba
     */
    public function sendTest(Request $request, $uid)
    {
        $template = MailTemplate::where('uid', $uid)->firstOrFail();

        $validated = $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            // Obtener traducción (usar idioma 1 como default para emails de prueba)
            $translation = $template->translate(1);

            if (! $translation) {
                return redirect()->back()->with('error', 'No existe traducción para enviar email de prueba');
            }

            // Renderizar el HTML completo con header/footer
            $html = $this->renderTemplateWithLayout($template, 1);

            // Enviar email usando el facade Mail correctamente
            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($translation, $validated, $html) {
                $message->to($validated['test_email'])
                    ->subject($translation->subject)
                    ->html($html);
            });

            return redirect()
                ->back()
                ->with('success', 'Email de prueba enviado a '.$validated['test_email']);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al enviar email: '.$e->getMessage());
        }
    }
}
