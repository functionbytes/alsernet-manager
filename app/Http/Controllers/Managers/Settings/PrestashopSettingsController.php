<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PrestashopSettingsController extends Controller
{
    /**
     * Mostrar dashboard de configuración PrestaShop
     */
    public function index()
    {
        $settings = Setting::getPrestashopSettings();
        $stats = Setting::getPrestashopStats();

        return view('managers.views.settings.prestashop.index', compact('settings', 'stats'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit()
    {
        $settings = Setting::getPrestashopSettings();

        return view('managers.views.settings.prestashop.edit', compact('settings'));
    }

    /**
     * Actualizar configuración
     */
    public function update(Request $request)
    {
        $rules = Setting::getPrestashopRules();
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Setting::setPrestashopSettings($request->all());

        return redirect()->route('manager.settings.prestashop.index')
            ->with('success', 'Configuración de PrestaShop actualizada correctamente');
    }

    /**
     * Verificar conexión a la base de datos PrestaShop
     */
    public function checkConnection()
    {
        try {
            $settings = Setting::getPrestashopSettings();

            // Intentar conectar a la BD de PrestaShop
            $conn = @mysqli_connect(
                $settings['prestashop_db_host'],
                $settings['prestashop_db_username'],
                $settings['prestashop_db_password'],
                $settings['prestashop_db_database'],
                (int)$settings['prestashop_db_port']
            );

            if ($conn) {
                mysqli_close($conn);
                Setting::updatePrestashopSyncStatus('online');

                return response()->json([
                    'success' => true,
                    'status' => 'online',
                    'message' => 'Conexión a PrestaShop establecida correctamente'
                ]);
            } else {
                Setting::updatePrestashopSyncStatus('offline');

                return response()->json([
                    'success' => false,
                    'status' => 'offline',
                    'message' => 'No se pudo conectar a la base de datos de PrestaShop: ' . mysqli_connect_error()
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error verificando conexión PrestaShop: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al verificar conexión: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Activar/Desactivar servicio
     */
    public function toggleActive(Request $request)
    {
        $isEnabled = $request->input('enabled');

        if ($isEnabled === null) {
            $current = Setting::get('prestashop_enabled', 'no');
            $isEnabled = $current === 'yes' ? 'no' : 'yes';
        } else {
            $isEnabled = $isEnabled ? 'yes' : 'no';
        }

        Setting::set('prestashop_enabled', $isEnabled);

        return response()->json([
            'success' => true,
            'enabled' => $isEnabled === 'yes',
            'message' => $isEnabled === 'yes' ? 'PrestaShop habilitado' : 'PrestaShop deshabilitado'
        ]);
    }

    /**
     * Resetear estadísticas
     */
    public function resetStats()
    {
        Setting::resetPrestashopStats();

        return response()->json([
            'success' => true,
            'message' => 'Estadísticas reseteadas correctamente'
        ]);
    }

    /**
     * Obtener estadísticas en tiempo real
     */
    public function getStats()
    {
        $stats = Setting::getPrestashopStats();

        if (!$stats) {
            return response()->json([
                'success' => false,
                'message' => 'Configuración no encontrada'
            ], 404);
        }

        $lastCheck = $stats['last_sync_check'] ?? null;
        $lastCheckDate = $lastCheck ? \Carbon\Carbon::parse($lastCheck) : null;

        return response()->json([
            'success' => true,
            'data' => [
                'total_syncs' => $stats['total_syncs'] ?? 0,
                'failed_syncs' => $stats['failed_syncs'] ?? 0,
                'success_rate' => $stats['success_rate'] ?? 100,
                'last_check' => $lastCheckDate?->diffForHumans() ?? null,
                'last_status' => $stats['last_sync_status'] ?? null,
                'enabled' => $stats['enabled'] ?? false,
            ]
        ]);
    }

    /**
     * Test de sincronización
     */
    public function testSync()
    {
        try {
            $settings = Setting::getPrestashopSettings();

            // Intentar conectar y contar órdenes
            $conn = @mysqli_connect(
                $settings['prestashop_db_host'],
                $settings['prestashop_db_username'],
                $settings['prestashop_db_password'],
                $settings['prestashop_db_database'],
                (int)$settings['prestashop_db_port']
            );

            if (!$conn) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo conectar a la base de datos de PrestaShop'
                ], 500);
            }

            // Contar órdenes pendientes (ejemplo)
            $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM ps_orders WHERE valid = 1");
            $row = mysqli_fetch_assoc($result);
            $pendingOrders = $row['total'] ?? 0;

            mysqli_close($conn);

            return response()->json([
                'success' => true,
                'message' => 'Sincronización funcionando correctamente',
                'pending_orders' => $pendingOrders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en sincronización: ' . $e->getMessage()
            ], 500);
        }
    }
}