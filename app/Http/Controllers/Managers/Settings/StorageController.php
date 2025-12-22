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

        return view('managers.views.settings.storage.index', [
            'storageData' => $storageData,
        ]);
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
                return back()
                    ->withInput()
                    ->with('error', 'No pueden existir dos discos con el mismo nombre');
            }

            // Guardar configuración de discos personalizados
            Setting::set('system.custom_storage_disks', json_encode($validated['disks']));

            return redirect()
                ->back()
                ->with('success', 'Configuración de almacenamiento actualizada correctamente');
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
}
