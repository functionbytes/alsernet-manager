<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * StorageController
 * Maneja la configuración global de discos de almacenamiento del sistema
 */
class StorageController extends Controller
{
    /**
     * Mostrar panel de gestión de almacenamiento
     */
    public function index()
    {
        $storageData = $this->getStorageData();
        $statistics = $this->getStorageStatistics($storageData);

        return view('theme.views.settings.storage.index', [
            'storageData' => $storageData,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Calcular estadísticas de almacenamiento
     */
    private function getStorageStatistics(array $storageData): array
    {
        $systemCount = count($storageData['system_disks']);
        $customCount = count($storageData['custom_disks']);
        $customConfigCount = count(array_filter($storageData['custom_disks'], fn ($disk) => $disk['from_config'] ?? false));
        $customDbCount = $customCount - $customConfigCount;

        $driverCounts = [
            'local' => 0,
            'ftp' => 0,
            'sftp' => 0,
            's3' => 0,
        ];

        foreach ($storageData['custom_disks'] as $disk) {
            $driver = $disk['driver'] ?? 'unknown';
            if (isset($driverCounts[$driver])) {
                $driverCounts[$driver]++;
            }
        }

        return [
            'total_disks' => $systemCount + $customCount,
            'system_disks' => $systemCount,
            'custom_disks_total' => $customCount,
            'custom_config' => $customConfigCount,
            'custom_db' => $customDbCount,
            'driver_counts' => $driverCounts,
        ];
    }

    /**
     * Actualizar configuración de almacenamiento
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'disks' => 'required|array',
            'disks.*.name' => 'required|string|max:50',
            'disks.*.driver' => 'required|string|in:local,ftp,sftp,s3',
            'disks.*.root' => 'required_if:disks.*.driver,local|string',
            'disks.*.url' => 'nullable|string',
            'disks.*.host' => 'required_if:disks.*.driver,ftp,sftp|string',
            'disks.*.username' => 'required_if:disks.*.driver,ftp,sftp|string',
            'disks.*.password' => 'nullable|string',
            'disks.*.port' => 'nullable|integer',
            'disks.*.bucket' => 'required_if:disks.*.driver,s3|string',
            'disks.*.key' => 'required_if:disks.*.driver,s3|string',
            'disks.*.secret' => 'required_if:disks.*.driver,s3|string',
            'disks.*.region' => 'required_if:disks.*.driver,s3|string',
        ]);

        try {
            // Validar que no se dupliquen nombres de disco
            $diskNames = array_column($validated['disks'], 'name');
            if (count($diskNames) !== count(array_unique($diskNames))) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'No pueden existir dos discos con el mismo nombre',
                    ], 422);
                }

                return back()
                    ->withInput()
                    ->with('error', 'No pueden existir dos discos con el mismo nombre');
            }

            // Guardar configuración de discos personalizados
            Setting::set('system.custom_storage_disks', json_encode($validated['disks']));

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Configuración de almacenamiento actualizada correctamente',
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Configuración de almacenamiento actualizada correctamente');
        } catch (\Exception $e) {
            \Log::error('Error updating storage configuration', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error al actualizar la configuración: '.$e->getMessage(),
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la configuración: '.$e->getMessage());
        }
    }

    /**
     * Obtener datos de almacenamiento
     */
    private function getStorageData(): array
    {
        // Obtener discos configurados en config/filesystems.php
        $systemDisks = $this->getSystemDisks();

        // Discos core que no se muestran como personalizados
        $coreDisks = ['local', 'public'];

        // Obtener discos del config que NO son core (media, ftp, network_shared, etc)
        $configDisks = [];
        $disksConfig = config('filesystems.disks');

        foreach ($disksConfig as $diskName => $diskConfig) {
            // Saltar discos core
            if (in_array($diskName, $coreDisks)) {
                continue;
            }

            $driver = $diskConfig['driver'] ?? 'unknown';

            // Crear estructura similar a custom disks
            $configDisk = [
                'name' => $diskName,
                'driver' => $driver,
                'from_config' => true, // Flag para identificar que viene del config
            ];

            // Agregar campos según el driver
            if ($driver === 'local') {
                $configDisk['root'] = $diskConfig['root'] ?? '';
                $configDisk['url'] = $diskConfig['url'] ?? '';
            } elseif ($driver === 'ftp' || $driver === 'sftp') {
                $configDisk['host'] = $diskConfig['host'] ?? '';
                $configDisk['port'] = $diskConfig['port'] ?? '';
                $configDisk['username'] = $diskConfig['username'] ?? '';
                $configDisk['password'] = '********'; // No mostrar password real
            } elseif ($driver === 's3') {
                $configDisk['bucket'] = $diskConfig['bucket'] ?? '';
                $configDisk['region'] = $diskConfig['region'] ?? '';
                $configDisk['key'] = $diskConfig['key'] ?? '';
                $configDisk['secret'] = '********'; // No mostrar secret real
            }

            $configDisks[] = $configDisk;
        }

        // Obtener discos personalizados de la base de datos
        $customDisksJson = Setting::get('system.custom_storage_disks', '[]');
        $customDisks = json_decode($customDisksJson, true) ?: [];

        // Marcar custom disks como no provenientes del config
        foreach ($customDisks as &$disk) {
            $disk['from_config'] = false;
        }

        // Combinar discos del config con custom disks
        $allCustomDisks = array_merge($configDisks, $customDisks);

        return [
            'system_disks' => $systemDisks,
            'custom_disks' => $allCustomDisks,
            'driver_options' => [
                'local' => 'Almacenamiento Local',
                'ftp' => 'FTP',
                'sftp' => 'SFTP',
                's3' => 'Amazon S3',
            ],
        ];
    }

    /**
     * Obtener discos del sistema desde config
     */
    private function getSystemDisks(): array
    {
        // Solo estos discos se consideran "del sistema" (core de Laravel)
        $coreDisks = ['local', 'public'];

        $disksConfig = config('filesystems.disks');
        $systemDisks = [];

        foreach ($disksConfig as $diskName => $diskConfig) {
            // Solo incluir discos core
            if (! in_array($diskName, $coreDisks)) {
                continue;
            }

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

            $systemDisks[] = [
                'name' => $diskName,
                'driver' => $driver,
                'root' => $root,
                'url' => $url,
                'description' => $description,
                'editable' => false, // Los discos del sistema no son editables desde la UI
            ];
        }

        return $systemDisks;
    }

    /**
     * Mostrar formulario de creación de disco
     */
    public function create()
    {
        $storageData = $this->getStorageData();

        return view('theme.views.settings.storage.create', [
            'driverOptions' => $storageData['driver_options'],
        ]);
    }

    /**
     * Guardar un nuevo disco personalizado
     */
    public function store(Request $request)
    {
        // Validación
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'driver' => 'required|string|in:local,ftp,sftp,s3',
            'root' => 'required_if:driver,local|nullable|string',
            'url' => 'nullable|string',
            'host' => 'required_if:driver,ftp,sftp|nullable|string',
            'username' => 'required_if:driver,ftp,sftp|nullable|string',
            'password' => 'nullable|string',
            'port' => 'nullable|integer',
            'bucket' => 'required_if:driver,s3|nullable|string',
            'key' => 'required_if:driver,s3|nullable|string',
            'secret' => 'required_if:driver,s3|nullable|string',
            'region' => 'required_if:driver,s3|nullable|string',
        ]);

        try {
            // Construir objeto de disco
            $diskData = [
                'name' => $validated['name'],
                'driver' => $validated['driver'],
            ];

            // Agregar campos específicos según el driver
            if ($validated['driver'] === 'local') {
                $diskData['root'] = $validated['root'];
                $diskData['url'] = $validated['url'] ?? null;

                // Validar y preparar el directorio local
                $validation = $this->validateAndPrepareLocalDisk($validated['root']);
                if (! $validation['success']) {
                    return back()
                        ->withInput()
                        ->with('error', $validation['message']);
                }
            } elseif ($validated['driver'] === 'ftp' || $validated['driver'] === 'sftp') {
                $diskData['host'] = $validated['host'];
                $diskData['username'] = $validated['username'];
                $diskData['password'] = $validated['password'] ?? null;
                $diskData['port'] = $validated['port'] ?? null;
            } elseif ($validated['driver'] === 's3') {
                $diskData['bucket'] = $validated['bucket'];
                $diskData['region'] = $validated['region'];
                $diskData['key'] = $validated['key'];
                $diskData['secret'] = $validated['secret'] ?? null;
            }

            // Obtener discos existentes solo de BD
            $customDisksJson = Setting::get('system.custom_storage_disks', '[]');
            $existingDisks = json_decode($customDisksJson, true) ?: [];

            // Validar que el nombre no esté duplicado
            $diskNames = array_column($existingDisks, 'name');
            if (in_array($validated['name'], $diskNames)) {
                return back()
                    ->withInput()
                    ->with('error', 'Ya existe un disco con ese nombre');
            }

            // Validar que el nombre no exista en los discos del config
            $configDisks = config('filesystems.disks', []);
            if (isset($configDisks[$validated['name']])) {
                return back()
                    ->withInput()
                    ->with('error', 'Ya existe un disco del sistema con ese nombre');
            }

            // Agregar nuevo disco
            $existingDisks[] = $diskData;

            // Guardar
            Setting::set('system.custom_storage_disks', json_encode($existingDisks));

            return redirect()
                ->route('manager.settings.storage.index')
                ->with('success', 'Disco de almacenamiento creado correctamente');
        } catch (\Exception $e) {
            \Log::error('Error creating storage disk', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error al crear el disco: '.$e->getMessage());
        }
    }

    /**
     * Mostrar formulario de edición de un disco
     */
    public function edit(int $index)
    {
        $storageData = $this->getStorageData();

        // Verificar que el índice existe
        if (! isset($storageData['custom_disks'][$index])) {
            return redirect()
                ->route('manager.settings.storage.index')
                ->with('error', 'El disco solicitado no existe');
        }

        $disk = $storageData['custom_disks'][$index];
        $isFromConfig = $disk['from_config'] ?? false;

        return view('theme.views.settings.storage.edit', [
            'disk' => $disk,
            'diskIndex' => $index,
            'isFromConfig' => $isFromConfig,
            'driverOptions' => $storageData['driver_options'],
        ]);
    }

    /**
     * Actualizar un disco específico
     */
    public function updateDisk(Request $request, int $index)
    {
        $storageData = $this->getStorageData();

        // Verificar que el índice existe
        if (! isset($storageData['custom_disks'][$index])) {
            return redirect()
                ->route('manager.settings.storage.index')
                ->with('error', 'El disco solicitado no existe');
        }

        $disk = $storageData['custom_disks'][$index];
        $isFromConfig = $disk['from_config'] ?? false;

        // Validación
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'driver' => 'required|string|in:local,ftp,sftp,s3',
            'root' => 'required_if:driver,local|nullable|string',
            'url' => 'nullable|string',
            'host' => 'required_if:driver,ftp,sftp|nullable|string',
            'username' => 'required_if:driver,ftp,sftp|nullable|string',
            'password' => 'nullable|string',
            'port' => 'nullable|integer',
            'bucket' => 'required_if:driver,s3|nullable|string',
            'key' => 'required_if:driver,s3|nullable|string',
            'secret' => 'nullable|string',
            'region' => 'required_if:driver,s3|nullable|string',
        ]);

        try {
            // Construir objeto de disco
            $diskData = [
                'name' => $validated['name'],
                'driver' => $validated['driver'],
            ];

            // Agregar campos específicos según el driver
            if ($validated['driver'] === 'local') {
                $diskData['root'] = $validated['root'];
                $diskData['url'] = $validated['url'] ?? null;

                // Validar y preparar el directorio local
                $validation = $this->validateAndPrepareLocalDisk($validated['root']);
                if (! $validation['success']) {
                    return back()
                        ->withInput()
                        ->with('error', $validation['message']);
                }
            } elseif ($validated['driver'] === 'ftp' || $validated['driver'] === 'sftp') {
                $diskData['host'] = $validated['host'];
                $diskData['username'] = $validated['username'];
                $diskData['password'] = $validated['password'] ?? $disk['password'] ?? null;
                $diskData['port'] = $validated['port'] ?? null;
            } elseif ($validated['driver'] === 's3') {
                $diskData['bucket'] = $validated['bucket'];
                $diskData['region'] = $validated['region'];
                $diskData['key'] = $validated['key'];
                $diskData['secret'] = $validated['secret'] ?? $disk['secret'] ?? null;
            }

            // Obtener discos existentes solo de BD
            $customDisksJson = Setting::get('system.custom_storage_disks', '[]');
            $existingDisks = json_decode($customDisksJson, true) ?: [];

            if ($isFromConfig) {
                // Si es del config, agregarlo como nuevo disco en BD
                $existingDisks[] = $diskData;
            } else {
                // Si es de BD, actualizarlo
                $realIndex = array_search($disk['name'], array_column($existingDisks, 'name'));
                if ($realIndex !== false) {
                    $existingDisks[$realIndex] = $diskData;
                } else {
                    $existingDisks[] = $diskData;
                }
            }

            // Validar nombres duplicados
            $diskNames = array_column($existingDisks, 'name');
            if (count($diskNames) !== count(array_unique($diskNames))) {
                return back()
                    ->withInput()
                    ->with('error', 'No pueden existir dos discos con el mismo nombre');
            }

            // Guardar
            Setting::set('system.custom_storage_disks', json_encode($existingDisks));

            return redirect()
                ->route('manager.settings.storage.index')
                ->with('success', 'Disco actualizado correctamente');
        } catch (\Exception $e) {
            \Log::error('Error updating storage disk', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el disco: '.$e->getMessage());
        }
    }

    /**
     * Eliminar un disco personalizado
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'disk_name' => 'required|string',
        ]);

        try {
            $customDisksJson = Setting::get('system.custom_storage_disks', '[]');
            $customDisks = json_decode($customDisksJson, true) ?: [];

            // Filtrar el disco a eliminar
            $customDisks = array_filter($customDisks, function ($disk) use ($validated) {
                return $disk['name'] !== $validated['disk_name'];
            });

            // Re-indexar el array
            $customDisks = array_values($customDisks);

            // Guardar
            Setting::set('system.custom_storage_disks', json_encode($customDisks));

            return redirect()
                ->back()
                ->with('success', 'Disco de almacenamiento eliminado correctamente');
        } catch (\Exception $e) {
            \Log::error('Error deleting storage disk', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->with('error', 'Error al eliminar el disco: '.$e->getMessage());
        }
    }

    /**
     * Validar y preparar un disco de almacenamiento local
     * Crea el directorio si no existe y verifica permisos
     */
    private function validateAndPrepareLocalDisk(string $rootPath): array
    {
        // Limpiar la ruta
        $rootPath = rtrim($rootPath, '/');

        // Verificar si es una ruta absoluta
        if (! str_starts_with($rootPath, '/')) {
            return [
                'success' => false,
                'message' => 'La ruta debe ser absoluta (debe comenzar con /)',
            ];
        }

        // Intentar crear el directorio si no existe
        if (! is_dir($rootPath)) {
            try {
                if (! mkdir($rootPath, 0755, true)) {
                    return [
                        'success' => false,
                        'message' => "No se pudo crear el directorio: {$rootPath}",
                    ];
                }

                // Crear .gitignore para excluir archivos del control de versiones
                $gitignorePath = $rootPath.'/.gitignore';
                file_put_contents($gitignorePath, "*\n!.gitignore\n!.gitkeep\n");

                // Crear .gitkeep para mantener el directorio en git
                $gitkeepPath = $rootPath.'/.gitkeep';
                file_put_contents($gitkeepPath, '');

                \Log::info('Storage directory created', [
                    'path' => $rootPath,
                    'permissions' => '0755',
                ]);
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => "Error al crear el directorio: {$e->getMessage()}",
                ];
            }
        }

        // Verificar que el directorio es legible
        if (! is_readable($rootPath)) {
            return [
                'success' => false,
                'message' => "El directorio no es legible: {$rootPath}. Verifica los permisos.",
            ];
        }

        // Verificar que el directorio es escribible
        if (! is_writable($rootPath)) {
            return [
                'success' => false,
                'message' => "El directorio no es escribible: {$rootPath}. Verifica los permisos.",
            ];
        }

        return [
            'success' => true,
            'message' => 'Directorio configurado correctamente',
            'path' => $rootPath,
        ];
    }
}
