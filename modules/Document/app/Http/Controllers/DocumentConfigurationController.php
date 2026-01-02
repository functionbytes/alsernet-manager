<?php

namespace Modules\Document\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Document\Entities\DocumentStorageConfigurationHistory;
use Modules\Mailer\Models\MailerTemplate;

/**
 * OrderConfigurationController
 * Maneja la configuración global de documentos que se aplica a TODOS los tipos
 */
class DocumentConfigurationController extends Controller
{
    public function index()
    {
        return view('documents::settings.index');
    }

    /**
     * Mostrar panel de configuración de almacenamiento
     */
    public function storageSettings()
    {
        $storageSettings = $this->getStorageSettings();

        return view('documents::settings.configurations.storage', [
            'storageSettings' => $storageSettings,
        ]);
    }

    /**
     * Actualizar configuración de almacenamiento
     */
    public function updateStorageSettings(Request $request)
    {
        $validated = $request->validate([
            'default_storage_disk' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            // Validar que el disco existe en la configuración
            $availableDisks = array_keys(config('filesystems.disks'));
            if (! in_array($validated['default_storage_disk'], $availableDisks)) {
                return back()
                    ->withInput()
                    ->with('error', 'El disco seleccionado no está configurado en el sistema');
            }

            // Obtener disco anterior
            $oldDisk = Setting::get('documents.default_storage_disk', 'media');
            $newDisk = $validated['default_storage_disk'];

            // Guardar el disco seleccionado para documentos
            Setting::set('documents.default_storage_disk', $newDisk);

            // Registrar cambio en el historial solo si realmente cambió
            if ($oldDisk !== $newDisk) {
                $diskConfig = config('filesystems.disks.'.$newDisk);
                DocumentStorageConfigurationHistory::create([
                    'old_disk_name' => $oldDisk,
                    'new_disk_name' => $newDisk,
                    'changed_by' => Auth::id(),
                    'driver' => $diskConfig['driver'] ?? 'unknown',
                    'reason' => $validated['reason'] ?? null,
                    'action' => 'update',
                    'metadata' => [
                        'old_driver' => config('filesystems.disks.'.$oldDisk)['driver'] ?? 'unknown',
                        'new_driver' => $diskConfig['driver'] ?? 'unknown',
                    ],
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Disco de almacenamiento actualizado correctamente');
        } catch (\Exception $e) {
            \Log::error('Error updating storage configuration', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la configuración: '.$e->getMessage());
        }
    }

    /**
     * Mostrar panel de configuración global
     */
    public function globalSettings()
    {
        $globalSettings = $this->getGlobalSettings();

        return view('documents::settings.configurations.index', [
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
        $availableTemplates = MailerTemplate::module('documents')
            ->enabled()
            ->select(['id', 'name', 'key'])
            ->with(['translations' => function ($q) {
                $q->where('lang_id', 1)->select('id', 'mailer_template_id', 'lang_id');
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
            $templates = MailerTemplate::module('documents')
                ->enabled()
                ->when($query, function ($q) use ($query) {
                    return $q->search($query);
                })
                ->select(['id', 'name', 'key'])
                ->with(['translations' => function ($q) {
                    $q->where('lang_id', 1)->select('id', 'mailer_template_id', 'lang_id');
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

    /**
     * Obtener configuración de almacenamiento
     */
    private function getStorageSettings(): array
    {
        // Obtener discos disponibles desde la configuración
        $disksConfig = config('filesystems.disks');
        $availableDisks = [];

        foreach ($disksConfig as $diskName => $diskConfig) {
            $driver = $diskConfig['driver'] ?? 'unknown';
            $root = $diskConfig['root'] ?? 'N/A';
            $url = $diskConfig['url'] ?? null;

            // Crear descripción legible
            $description = match ($driver) {
                'local' => 'Almacenamiento local en: '.$root,
                'ftp' => 'Servidor FTP: '.($diskConfig['host'] ?? 'No configurado'),
                'sftp' => 'Servidor SFTP: '.($diskConfig['host'] ?? 'No configurado'),
                's3' => 'Amazon S3: '.($diskConfig['bucket'] ?? 'No configurado'),
                default => 'Driver: '.$driver,
            };

            $availableDisks[$diskName] = [
                'name' => $diskName,
                'driver' => $driver,
                'root' => $root,
                'url' => $url,
                'description' => $description,
            ];
        }

        // Obtener discos personalizados de la base de datos
        $customDisksJson = Setting::get('system.custom_storage_disks', '[]');
        $customDisks = json_decode($customDisksJson, true) ?: [];

        // Agregar discos personalizados a la lista
        foreach ($customDisks as $customDisk) {
            $diskName = $customDisk['name'];
            $driver = $customDisk['driver'];

            $description = match ($driver) {
                'local' => 'Almacenamiento local en: '.($customDisk['root'] ?? 'N/A'),
                'ftp' => 'Servidor FTP: '.($customDisk['host'] ?? 'No configurado'),
                'sftp' => 'Servidor SFTP: '.($customDisk['host'] ?? 'No configurado'),
                's3' => 'Amazon S3: '.($customDisk['bucket'] ?? 'No configurado'),
                default => 'Driver: '.$driver,
            };

            $availableDisks[$diskName] = [
                'name' => $diskName,
                'driver' => $driver,
                'root' => $customDisk['root'] ?? 'N/A',
                'url' => $customDisk['url'] ?? null,
                'description' => $description.' (Personalizado)',
            ];
        }

        // Obtener disco seleccionado actualmente
        $currentDisk = Setting::get('documents.default_storage_disk', 'media');

        return [
            'available_disks' => $availableDisks,
            'current_disk' => $currentDisk,
        ];
    }

    /**
     * Probar conexión a un disco de almacenamiento.
     */
    public function testStorageConnection(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $diskName = $request->input('disk_name');

            if (! $diskName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nombre de disco requerido',
                    'response_time' => 0,
                ], 400);
            }

            // Validar que el disco existe
            $availableDisks = array_keys(config('filesystems.disks'));
            if (! in_array($diskName, $availableDisks)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Disco no configurado',
                    'response_time' => round((microtime(true) - $startTime) * 1000),
                ]);
            }

            $diskConfig = config('filesystems.disks.'.$diskName);
            $driver = $diskConfig['driver'] ?? 'unknown';

            // Prueba según tipo de driver
            $result = match ($driver) {
                'local' => $this->testLocalStorage($diskConfig, $diskName),
                's3' => $this->testS3Connection($diskConfig, $diskName),
                'ftp' => $this->testFtpConnection($diskConfig, $diskName),
                'sftp' => $this->testSftpConnection($diskConfig, $diskName),
                default => ['success' => false, 'message' => "Driver '$driver' no soporta prueba de conexión"],
            };

            $responseTime = round((microtime(true) - $startTime) * 1000);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'response_time' => $responseTime,
                'metadata' => $result['metadata'] ?? null,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error testing storage connection', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al probar conexión: '.$e->getMessage(),
                'response_time' => round((microtime(true) - $startTime) * 1000),
            ], 500);
        }
    }

    /**
     * Probar almacenamiento local.
     */
    private function testLocalStorage(array $config, string $diskName): array
    {
        $root = $config['root'] ?? null;

        if (! $root) {
            return ['success' => false, 'message' => 'Ruta raíz no configurada'];
        }

        if (! is_dir($root)) {
            return ['success' => false, 'message' => "Directorio no existe: $root"];
        }

        if (! is_readable($root)) {
            return ['success' => false, 'message' => "Directorio no es legible: $root"];
        }

        if (! is_writable($root)) {
            return ['success' => false, 'message' => "Directorio no es escribible: $root"];
        }

        return [
            'success' => true,
            'message' => 'Almacenamiento local accesible',
            'metadata' => ['path' => $root],
        ];
    }

    /**
     * Probar conexión a Amazon S3.
     */
    private function testS3Connection(array $config, string $diskName): array
    {
        try {
            $disk = Storage::disk($diskName);
            $disk->exists('.');

            return [
                'success' => true,
                'message' => 'Conexión S3 exitosa',
                'metadata' => ['bucket' => $config['bucket'] ?? 'unknown'],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error en conexión S3: '.$e->getMessage()];
        }
    }

    /**
     * Probar conexión FTP.
     */
    private function testFtpConnection(array $config, string $diskName): array
    {
        try {
            $disk = Storage::disk($diskName);
            // Try to list files from the disk to verify connection
            $disk->listContents('/');

            return [
                'success' => true,
                'message' => 'Conexión FTP exitosa',
                'metadata' => ['host' => $config['host'] ?? 'unknown'],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error en conexión FTP: '.$e->getMessage()];
        }
    }

    /**
     * Probar conexión SFTP.
     */
    private function testSftpConnection(array $config, string $diskName): array
    {
        try {
            $disk = Storage::disk($diskName);
            // Try to list files from the disk to verify connection
            $disk->listContents('/');

            return [
                'success' => true,
                'message' => 'Conexión SFTP exitosa',
                'metadata' => ['host' => $config['host'] ?? 'unknown'],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error en conexión SFTP: '.$e->getMessage()];
        }
    }

    /**
     * Obtener estadísticas de almacenamiento de un disco.
     */
    public function getStorageStats(Request $request, string $diskName): JsonResponse
    {
        try {
            $diskConfig = config('filesystems.disks.'.$diskName);

            if (! $diskConfig) {
                return response()->json([
                    'success' => false,
                    'message' => 'Disco no configurado',
                ], 404);
            }

            $driver = $diskConfig['driver'] ?? 'unknown';
            $stats = null;

            // Obtener estadísticas según tipo de driver
            if ($driver === 'local') {
                $root = $diskConfig['root'] ?? null;
                if ($root && is_dir($root)) {
                    $totalSpace = disk_total_space($root);
                    $freeSpace = disk_free_space($root);
                    $usedSpace = $totalSpace - $freeSpace;

                    $stats = [
                        'total' => $this->formatBytes($totalSpace),
                        'free' => $this->formatBytes($freeSpace),
                        'used' => $this->formatBytes($usedSpace),
                        'percent' => round(($usedSpace / $totalSpace) * 100, 2),
                    ];
                }
            }

            // Para otros drivers, no disponible
            if (! $stats) {
                $stats = [
                    'total' => 'N/A',
                    'free' => 'N/A',
                    'used' => 'N/A',
                    'percent' => null,
                ];
            }

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting storage stats', [
                'disk' => $diskName,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener historial de cambios de configuración de almacenamiento.
     */
    public function getStorageConfigurationHistory(): JsonResponse
    {
        try {
            $history = DocumentStorageConfigurationHistory::with('user')
                ->recent()
                ->limit(10)
                ->get()
                ->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'old_disk' => $record->old_disk_name,
                        'new_disk' => $record->new_disk_name,
                        'driver' => $record->driver,
                        'user' => $record->user?->firstname.' '.$record->user?->lastname,
                        'user_id' => $record->changed_by,
                        'action_label' => $record->action_label,
                        'reason' => $record->reason,
                        'created_at' => $record->created_at,
                        'created_at_relative' => $record->created_at?->diffForHumans(),
                    ];
                });

            return response()->json([
                'success' => true,
                'history' => $history,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting storage configuration history', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Formatear bytes a formato legible.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }
}
