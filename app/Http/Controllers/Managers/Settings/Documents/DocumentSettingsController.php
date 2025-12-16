<?php

namespace App\Http\Controllers\Managers\Settings\Documents;

use App\Http\Controllers\Controller;
use App\Models\Document\Document;
use App\Models\Document\DocumentSlaBreach;
use App\Models\Document\DocumentSlaPolicy;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * Controller for managing document settings.
 *
 * Handles document-related system settings including email notifications,
 * SLA policies, and general configuration options.
 */
class DocumentSettingsController extends Controller
{
    /**
     * The settings prefix for document-related settings.
     */
    private const SETTINGS_PREFIX = 'documents.';

    /**
     * Available setting groups/categories.
     *
     * @var array<string, array{label: string, description: string, icon: string}>
     */
    private const SETTING_GROUPS = [
        'general' => [
            'label' => 'General',
            'description' => 'Configuracion general del modulo de documentos',
            'icon' => 'ti ti-settings',
        ],
        'email' => [
            'label' => 'Notificaciones Email',
            'description' => 'Configuracion de envio de emails y recordatorios',
            'icon' => 'ti ti-mail',
        ],
        'sla' => [
            'label' => 'Politicas SLA',
            'description' => 'Configuracion de politicas de nivel de servicio',
            'icon' => 'ti ti-clock',
        ],
        'upload' => [
            'label' => 'Carga de Archivos',
            'description' => 'Configuracion de tipos de archivo y limites',
            'icon' => 'ti ti-upload',
        ],
    ];

    /**
     * Display the document settings dashboard.
     *
     * Retrieves all document settings grouped by category and displays
     * statistics about SLA compliance and document processing.
     *
     * @param  Request  $request  The HTTP request instance
     * @return View The settings dashboard view
     */
    public function index(Request $request): View
    {
        $pageTitle = 'Configuracion de Documentos';
        $breadcrumb = 'Configuracion / Documentos';
        $activeTab = $request->get('tab', 'general');

        // Retrieve settings grouped by category
        $settingGroups = $this->getSettingsGroupedByCategory();

        // Calculate SLA compliance statistics
        $slaStats = $this->calculateSlaStatistics();

        // Calculate document processing statistics
        $documentStats = $this->calculateDocumentStatistics();

        // Get available SLA policies for reference
        $slaPolicies = DocumentSlaPolicy::select('id', 'name', 'active', 'is_default')
            ->orderBy('name')
            ->get();

        return view('managers.views.settings.documents.settings.index', [
            'pageTitle' => $pageTitle,
            'breadcrumb' => $breadcrumb,
            'activeTab' => $activeTab,
            'settingGroups' => $settingGroups,
            'groupMetadata' => self::SETTING_GROUPS,
            'slaStats' => $slaStats,
            'documentStats' => $documentStats,
            'slaPolicies' => $slaPolicies,
        ]);
    }

    /**
     * Update an individual setting.
     *
     * Validates the input based on the setting type and updates the value.
     * Logs the change to the activity log for auditing purposes.
     *
     * @param  Request  $request  The HTTP request containing key and value
     * @return JsonResponse JSON response with success/error status
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'key' => ['required', 'string', 'max:255'],
                'value' => ['present'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validacion',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $key = $request->input('key');
            $value = $request->input('value');

            // Validate key format (must start with documents prefix)
            if (! str_starts_with($key, self::SETTINGS_PREFIX)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Clave de configuracion invalida',
                ], 400);
            }

            // Get setting type for validation
            $settingMeta = $this->getSettingMetadata($key);
            $validationType = $settingMeta['type'] ?? 'string';

            // Validate value based on type
            $valueValidationResult = $this->validateSettingValue($value, $validationType, $settingMeta);
            if ($valueValidationResult !== true) {
                return response()->json([
                    'success' => false,
                    'message' => $valueValidationResult,
                ], 422);
            }

            // Process value based on type
            $processedValue = $this->processSettingValue($value, $validationType);

            // Get old value for logging
            $oldValue = Setting::get($key);

            // Update the setting
            Setting::set($key, $processedValue);

            // Log the change
            $this->logSettingChange($key, $oldValue, $processedValue);

            return response()->json([
                'success' => true,
                'message' => 'Configuracion actualizada correctamente',
                'data' => [
                    'key' => $key,
                    'value' => $processedValue,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating document setting', [
                'key' => $request->input('key'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la configuracion: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Batch update multiple settings.
     *
     * Performs transactional updates ensuring all settings are updated
     * or none are (all-or-nothing approach).
     *
     * @param  Request  $request  The HTTP request containing settings array
     * @return RedirectResponse Redirect with flash message
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string', 'max:255'],
            'settings.*.value' => ['present'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Error de validacion en los datos enviados');
        }

        $settings = $request->input('settings', []);

        // Pre-validate all settings before starting transaction
        $validationErrors = [];
        foreach ($settings as $index => $setting) {
            $key = $setting['key'];
            $value = $setting['value'];

            // Validate key format
            if (! str_starts_with($key, self::SETTINGS_PREFIX)) {
                $validationErrors[] = "Clave invalida en posicion {$index}: {$key}";

                continue;
            }

            // Validate value based on type
            $settingMeta = $this->getSettingMetadata($key);
            $validationType = $settingMeta['type'] ?? 'string';
            $valueValidationResult = $this->validateSettingValue($value, $validationType, $settingMeta);

            if ($valueValidationResult !== true) {
                $validationErrors[] = "Error en '{$key}': {$valueValidationResult}";
            }
        }

        if (! empty($validationErrors)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Errores de validacion: '.implode(', ', $validationErrors));
        }

        try {
            DB::beginTransaction();

            $updatedSettings = [];

            foreach ($settings as $setting) {
                $key = $setting['key'];
                $value = $setting['value'];

                $settingMeta = $this->getSettingMetadata($key);
                $validationType = $settingMeta['type'] ?? 'string';

                // Process and save the value
                $processedValue = $this->processSettingValue($value, $validationType);
                $oldValue = Setting::get($key);

                Setting::set($key, $processedValue);

                // Track updated settings for logging
                $updatedSettings[] = [
                    'key' => $key,
                    'old_value' => $oldValue,
                    'new_value' => $processedValue,
                ];
            }

            DB::commit();

            // Log all changes after successful commit
            foreach ($updatedSettings as $change) {
                $this->logSettingChange($change['key'], $change['old_value'], $change['new_value']);
            }

            return redirect()
                ->route('manager.settings.documents.settings.index')
                ->with('success', 'Configuraciones actualizadas correctamente ('.count($updatedSettings).' cambios)');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error in batch document settings update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al guardar las configuraciones: '.$e->getMessage());
        }
    }

    /**
     * Get settings grouped by section.
     *
     * Returns settings organized by section for display in tabs/accordion.
     * Includes section descriptions and metadata.
     *
     * @param  string|null  $section  Optional section filter
     * @return JsonResponse|array Settings grouped by section
     */
    public function getSectionSettings(?string $section = null): JsonResponse|array
    {
        $groupedSettings = $this->getSettingsGroupedByCategory();

        if ($section !== null) {
            if (! isset($groupedSettings[$section])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seccion no encontrada',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'section' => $section,
                    'metadata' => self::SETTING_GROUPS[$section] ?? null,
                    'settings' => $groupedSettings[$section],
                ],
            ]);
        }

        // Return all sections with their metadata
        $result = [];
        foreach ($groupedSettings as $group => $settings) {
            $result[$group] = [
                'metadata' => self::SETTING_GROUPS[$group] ?? [
                    'label' => ucfirst($group),
                    'description' => '',
                    'icon' => 'ti ti-settings',
                ],
                'settings' => $settings,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Reset settings to default values.
     *
     * @param  Request  $request  The HTTP request with optional group filter
     * @return JsonResponse JSON response with success/error status
     */
    public function resetToDefaults(Request $request): JsonResponse
    {
        try {
            $group = $request->input('group');

            $defaultSettings = $this->getDefaultSettings();

            if ($group !== null) {
                $defaultSettings = array_filter($defaultSettings, function ($setting) use ($group) {
                    return ($setting['group'] ?? 'general') === $group;
                });
            }

            DB::beginTransaction();

            foreach ($defaultSettings as $key => $config) {
                $fullKey = self::SETTINGS_PREFIX.$key;
                $oldValue = Setting::get($fullKey);
                Setting::set($fullKey, $config['default']);
                $this->logSettingChange($fullKey, $oldValue, $config['default'], 'reset_to_default');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Configuraciones restauradas a valores por defecto',
                'count' => count($defaultSettings),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error resetting document settings to defaults', [
                'group' => $request->input('group'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar configuraciones: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retrieve all document settings grouped by category.
     *
     * @return array<string, array> Settings grouped by category
     */
    private function getSettingsGroupedByCategory(): array
    {
        // Get all document settings from database
        $settings = Setting::where('key', 'like', self::SETTINGS_PREFIX.'%')
            ->get()
            ->keyBy('key');

        // Get default settings with metadata
        $defaultSettings = $this->getDefaultSettings();

        $grouped = [];

        foreach ($defaultSettings as $key => $config) {
            $fullKey = self::SETTINGS_PREFIX.$key;
            $group = $config['group'] ?? 'general';

            // Get current value from database or use default
            $currentValue = $settings->has($fullKey)
                ? $settings[$fullKey]->value
                : $config['default'];

            $grouped[$group][$key] = [
                'key' => $fullKey,
                'value' => $currentValue,
                'default' => $config['default'],
                'type' => $config['type'],
                'label' => $config['label'],
                'description' => $config['description'] ?? '',
                'options' => $config['options'] ?? null,
                'validation' => $config['validation'] ?? null,
            ];
        }

        return $grouped;
    }

    /**
     * Get default settings configuration.
     *
     * @return array<string, array> Default settings with metadata
     */
    private function getDefaultSettings(): array
    {
        return [
            // General Settings
            'enabled' => [
                'group' => 'general',
                'type' => 'boolean',
                'default' => 'yes',
                'label' => 'Modulo habilitado',
                'description' => 'Habilita o deshabilita el modulo de documentos',
            ],
            'auto_detect_type' => [
                'group' => 'general',
                'type' => 'boolean',
                'default' => 'yes',
                'label' => 'Deteccion automatica de tipo',
                'description' => 'Detecta automaticamente el tipo de documento basandose en los productos',
            ],
            'require_all_documents' => [
                'group' => 'general',
                'type' => 'boolean',
                'default' => 'yes',
                'label' => 'Requerir todos los documentos',
                'description' => 'Requiere que se carguen todos los documentos antes de confirmar',
            ],
            'default_status' => [
                'group' => 'general',
                'type' => 'string',
                'default' => 'pending',
                'label' => 'Estado por defecto',
                'description' => 'Estado inicial de los documentos nuevos',
                'options' => ['pending', 'awaiting_upload', 'in_review', 'approved'],
            ],
            'portal_url' => [
                'group' => 'general',
                'type' => 'string',
                'default' => '',
                'label' => 'URL del portal de documentos',
                'description' => 'URL base para el portal de carga de documentos (usar {uid} como placeholder)',
                'validation' => ['nullable', 'url'],
            ],

            // Email Settings
            'mail_enabled' => [
                'group' => 'email',
                'type' => 'boolean',
                'default' => 'yes',
                'label' => 'Envio de emails habilitado',
                'description' => 'Habilita el envio de notificaciones por email',
            ],
            'mail_upload_notification' => [
                'group' => 'email',
                'type' => 'boolean',
                'default' => 'yes',
                'label' => 'Notificacion de solicitud de carga',
                'description' => 'Envia email cuando se solicita carga de documentos',
            ],
            'mail_upload_confirmation' => [
                'group' => 'email',
                'type' => 'boolean',
                'default' => 'yes',
                'label' => 'Confirmacion de carga',
                'description' => 'Envia email de confirmacion cuando se cargan documentos',
            ],
            'mail_reminder_enabled' => [
                'group' => 'email',
                'type' => 'boolean',
                'default' => 'yes',
                'label' => 'Recordatorios habilitados',
                'description' => 'Envia recordatorios automaticos para documentos pendientes',
            ],
            'mail_reminder_days' => [
                'group' => 'email',
                'type' => 'integer',
                'default' => '3',
                'label' => 'Dias para recordatorio',
                'description' => 'Numero de dias antes de enviar un recordatorio',
                'validation' => ['integer', 'min:1', 'max:30'],
            ],
            'mail_max_reminders' => [
                'group' => 'email',
                'type' => 'integer',
                'default' => '3',
                'label' => 'Maximo de recordatorios',
                'description' => 'Numero maximo de recordatorios a enviar',
                'validation' => ['integer', 'min:1', 'max:10'],
            ],
            'mail_from_name' => [
                'group' => 'email',
                'type' => 'string',
                'default' => config('app.name', 'Alsernet'),
                'label' => 'Nombre del remitente',
                'description' => 'Nombre que aparece como remitente en los emails',
            ],
            'mail_from_address' => [
                'group' => 'email',
                'type' => 'string',
                'default' => config('mail.from.address', ''),
                'label' => 'Email del remitente',
                'description' => 'Direccion de email del remitente',
                'validation' => ['nullable', 'email'],
            ],
            'mail_reply_to' => [
                'group' => 'email',
                'type' => 'string',
                'default' => '',
                'label' => 'Email de respuesta',
                'description' => 'Direccion de email para respuestas',
                'validation' => ['nullable', 'email'],
            ],
            'mail_cc' => [
                'group' => 'email',
                'type' => 'text',
                'default' => '',
                'label' => 'CC (copia)',
                'description' => 'Direcciones de email para copia (separadas por coma)',
            ],
            'mail_bcc' => [
                'group' => 'email',
                'type' => 'text',
                'default' => '',
                'label' => 'BCC (copia oculta)',
                'description' => 'Direcciones de email para copia oculta (separadas por coma)',
            ],

            // SLA Settings
            'sla_enabled' => [
                'group' => 'sla',
                'type' => 'boolean',
                'default' => 'yes',
                'label' => 'Politicas SLA habilitadas',
                'description' => 'Habilita el seguimiento de politicas SLA',
            ],
            'sla_default_policy_id' => [
                'group' => 'sla',
                'type' => 'integer',
                'default' => '0',
                'label' => 'Politica SLA por defecto',
                'description' => 'ID de la politica SLA a aplicar por defecto',
            ],
            'sla_track_breaches' => [
                'group' => 'sla',
                'type' => 'boolean',
                'default' => 'yes',
                'label' => 'Registrar incumplimientos',
                'description' => 'Registra automaticamente los incumplimientos de SLA',
            ],
            'sla_auto_escalate' => [
                'group' => 'sla',
                'type' => 'boolean',
                'default' => 'yes',
                'label' => 'Escalamiento automatico',
                'description' => 'Escala automaticamente cuando se excede el umbral',
            ],
            'sla_escalation_email' => [
                'group' => 'sla',
                'type' => 'string',
                'default' => '',
                'label' => 'Email de escalamiento',
                'description' => 'Direccion de email para notificaciones de escalamiento',
                'validation' => ['nullable', 'email'],
            ],
            'sla_business_hours_only' => [
                'group' => 'sla',
                'type' => 'boolean',
                'default' => 'yes',
                'label' => 'Solo horario laboral',
                'description' => 'Calcula SLA solo en horario laboral',
            ],
            'sla_business_hours_start' => [
                'group' => 'sla',
                'type' => 'string',
                'default' => '09:00',
                'label' => 'Inicio horario laboral',
                'description' => 'Hora de inicio del horario laboral (HH:MM)',
            ],
            'sla_business_hours_end' => [
                'group' => 'sla',
                'type' => 'string',
                'default' => '17:00',
                'label' => 'Fin horario laboral',
                'description' => 'Hora de fin del horario laboral (HH:MM)',
            ],
            'sla_exclude_weekends' => [
                'group' => 'sla',
                'type' => 'boolean',
                'default' => 'yes',
                'label' => 'Excluir fines de semana',
                'description' => 'No cuenta fines de semana para el calculo de SLA',
            ],

            // Upload Settings
            'upload_max_file_size' => [
                'group' => 'upload',
                'type' => 'integer',
                'default' => '10240',
                'label' => 'Tamano maximo de archivo (KB)',
                'description' => 'Tamano maximo permitido por archivo en kilobytes',
                'validation' => ['integer', 'min:1024', 'max:102400'],
            ],
            'upload_max_files' => [
                'group' => 'upload',
                'type' => 'integer',
                'default' => '10',
                'label' => 'Maximo de archivos',
                'description' => 'Numero maximo de archivos por documento',
                'validation' => ['integer', 'min:1', 'max:50'],
            ],
            'upload_allowed_types' => [
                'group' => 'upload',
                'type' => 'json',
                'default' => '["jpg","jpeg","png","pdf","doc","docx"]',
                'label' => 'Tipos de archivo permitidos',
                'description' => 'Extensiones de archivo permitidas (formato JSON)',
            ],
            'upload_image_max_width' => [
                'group' => 'upload',
                'type' => 'integer',
                'default' => '2048',
                'label' => 'Ancho maximo de imagen',
                'description' => 'Ancho maximo de imagenes en pixeles (se redimensionara)',
                'validation' => ['integer', 'min:800', 'max:4096'],
            ],
            'upload_image_max_height' => [
                'group' => 'upload',
                'type' => 'integer',
                'default' => '2048',
                'label' => 'Alto maximo de imagen',
                'description' => 'Alto maximo de imagenes en pixeles (se redimensionara)',
                'validation' => ['integer', 'min:800', 'max:4096'],
            ],
            'upload_compress_images' => [
                'group' => 'upload',
                'type' => 'boolean',
                'default' => 'yes',
                'label' => 'Comprimir imagenes',
                'description' => 'Comprime automaticamente las imagenes cargadas',
            ],
            'upload_image_quality' => [
                'group' => 'upload',
                'type' => 'integer',
                'default' => '85',
                'label' => 'Calidad de imagen',
                'description' => 'Calidad de compresion de imagenes (1-100)',
                'validation' => ['integer', 'min:50', 'max:100'],
            ],
        ];
    }

    /**
     * Get metadata for a specific setting.
     *
     * @param  string  $key  The setting key
     * @return array Setting metadata
     */
    private function getSettingMetadata(string $key): array
    {
        $shortKey = str_replace(self::SETTINGS_PREFIX, '', $key);
        $defaults = $this->getDefaultSettings();

        return $defaults[$shortKey] ?? [
            'type' => 'string',
            'validation' => null,
        ];
    }

    /**
     * Validate a setting value based on its type.
     *
     * @param  mixed  $value  The value to validate
     * @param  string  $type  The setting type
     * @param  array  $metadata  Additional validation metadata
     * @return bool|string True if valid, error message if invalid
     */
    private function validateSettingValue(mixed $value, string $type, array $metadata): bool|string
    {
        $customValidation = $metadata['validation'] ?? null;

        switch ($type) {
            case 'boolean':
                if (! in_array($value, ['yes', 'no', true, false, 1, 0, '1', '0'], true)) {
                    return 'El valor debe ser yes/no o true/false';
                }
                break;

            case 'integer':
                if (! is_numeric($value) || (int) $value != $value) {
                    return 'El valor debe ser un numero entero';
                }
                if ($customValidation) {
                    foreach ($customValidation as $rule) {
                        if (str_starts_with($rule, 'min:')) {
                            $min = (int) str_replace('min:', '', $rule);
                            if ((int) $value < $min) {
                                return "El valor debe ser mayor o igual a {$min}";
                            }
                        }
                        if (str_starts_with($rule, 'max:')) {
                            $max = (int) str_replace('max:', '', $rule);
                            if ((int) $value > $max) {
                                return "El valor debe ser menor o igual a {$max}";
                            }
                        }
                    }
                }
                break;

            case 'json':
                if (! empty($value)) {
                    json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return 'El valor debe ser un JSON valido';
                    }
                }
                break;

            case 'string':
            case 'text':
                if ($customValidation && in_array('email', $customValidation)) {
                    if (! empty($value) && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return 'El valor debe ser una direccion de email valida';
                    }
                }
                if ($customValidation && in_array('url', $customValidation)) {
                    if (! empty($value) && ! filter_var($value, FILTER_VALIDATE_URL)) {
                        return 'El valor debe ser una URL valida';
                    }
                }
                break;
        }

        // Check options if defined
        if (isset($metadata['options']) && ! empty($value)) {
            if (! in_array($value, $metadata['options'])) {
                return 'El valor debe ser uno de: '.implode(', ', $metadata['options']);
            }
        }

        return true;
    }

    /**
     * Process a setting value for storage.
     *
     * @param  mixed  $value  The value to process
     * @param  string  $type  The setting type
     * @return string The processed value
     */
    private function processSettingValue(mixed $value, string $type): string
    {
        switch ($type) {
            case 'boolean':
                if (in_array($value, [true, 1, '1', 'yes'], true)) {
                    return 'yes';
                }

                return 'no';

            case 'integer':
                return (string) (int) $value;

            case 'json':
                if (is_array($value)) {
                    return json_encode($value);
                }

                return (string) $value;

            default:
                return (string) $value;
        }
    }

    /**
     * Log a setting change to the activity log.
     *
     * @param  string  $key  The setting key
     * @param  mixed  $oldValue  The previous value
     * @param  mixed  $newValue  The new value
     * @param  string  $action  The action type (updated, reset_to_default)
     */
    private function logSettingChange(string $key, mixed $oldValue, mixed $newValue, string $action = 'updated'): void
    {
        if ($oldValue === $newValue) {
            return;
        }

        try {
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'key' => $key,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'action' => $action,
                ])
                ->log("Document setting {$action}: {$key}");
        } catch (\Exception $e) {
            Log::warning('Failed to log setting change', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calculate SLA compliance statistics.
     *
     * @return array SLA statistics
     */
    private function calculateSlaStatistics(): array
    {
        try {
            $totalDocuments = Document::whereNotNull('sla_policy_id')->count();
            $totalBreaches = DocumentSlaBreach::count();
            $unresolvedBreaches = DocumentSlaBreach::where('resolved', false)->count();
            $escalatedBreaches = DocumentSlaBreach::where('escalated', true)->count();

            // Calculate compliance rate
            $complianceRate = $totalDocuments > 0
                ? round((($totalDocuments - $totalBreaches) / $totalDocuments) * 100, 2)
                : 100;

            // Get breach statistics by type
            $breachByType = DocumentSlaBreach::select('breach_type', DB::raw('COUNT(*) as count'))
                ->groupBy('breach_type')
                ->pluck('count', 'breach_type')
                ->toArray();

            // Get recent breaches (last 7 days)
            $recentBreaches = DocumentSlaBreach::where('created_at', '>=', now()->subDays(7))->count();

            // Average resolution time (in minutes)
            $avgResolutionTime = DocumentSlaBreach::whereNotNull('resolved_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, resolved_at)) as avg_time')
                ->value('avg_time') ?? 0;

            return [
                'total_documents_with_sla' => $totalDocuments,
                'total_breaches' => $totalBreaches,
                'unresolved_breaches' => $unresolvedBreaches,
                'escalated_breaches' => $escalatedBreaches,
                'compliance_rate' => $complianceRate,
                'breach_by_type' => $breachByType,
                'recent_breaches' => $recentBreaches,
                'avg_resolution_time' => round($avgResolutionTime, 0),
                'active_policies' => DocumentSlaPolicy::where('active', true)->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating SLA statistics', ['error' => $e->getMessage()]);

            return [
                'total_documents_with_sla' => 0,
                'total_breaches' => 0,
                'unresolved_breaches' => 0,
                'escalated_breaches' => 0,
                'compliance_rate' => 100,
                'breach_by_type' => [],
                'recent_breaches' => 0,
                'avg_resolution_time' => 0,
                'active_policies' => 0,
            ];
        }
    }

    /**
     * Calculate document processing statistics.
     *
     * @return array Document statistics
     */
    private function calculateDocumentStatistics(): array
    {
        try {
            $totalDocuments = Document::count();
            $documentsWithMedia = Document::has('media')->count();
            $documentsConfirmed = Document::whereNotNull('confirmed_at')->count();

            // Documents by type
            $documentsByType = Document::select('type', DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();

            // Documents by source
            $documentsBySource = Document::select('source', DB::raw('COUNT(*) as count'))
                ->whereNotNull('source')
                ->groupBy('source')
                ->pluck('count', 'source')
                ->toArray();

            // Recent documents (last 30 days)
            $recentDocuments = Document::where('created_at', '>=', now()->subDays(30))->count();

            // Documents pending upload
            $pendingUpload = Document::whereNull('confirmed_at')
                ->doesntHave('media')
                ->count();

            return [
                'total_documents' => $totalDocuments,
                'documents_with_media' => $documentsWithMedia,
                'documents_confirmed' => $documentsConfirmed,
                'documents_by_type' => $documentsByType,
                'documents_by_source' => $documentsBySource,
                'recent_documents' => $recentDocuments,
                'pending_upload' => $pendingUpload,
                'completion_rate' => $totalDocuments > 0
                    ? round(($documentsWithMedia / $totalDocuments) * 100, 2)
                    : 0,
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating document statistics', ['error' => $e->getMessage()]);

            return [
                'total_documents' => 0,
                'documents_with_media' => 0,
                'documents_confirmed' => 0,
                'documents_by_type' => [],
                'documents_by_source' => [],
                'recent_documents' => 0,
                'pending_upload' => 0,
                'completion_rate' => 0,
            ];
        }
    }
}
