<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ErpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ErpSettingsController extends Controller
{
    protected ErpService $erpService;

    public function __construct(ErpService $erpService)
    {
        $this->erpService = $erpService;
    }

    /**
     * Mostrar dashboard de configuración ERP
     */
    public function index()
    {
        $settings = Setting::getErpSettings();

        // Obtener estadísticas
        $stats = $this->erpService->getStats();

        // Verificar última conexión
        $lastCheck = $settings['last_connection_check'] ?? null;
        $lastCheckDate = $lastCheck ? \Carbon\Carbon::parse($lastCheck) : null;
        $needsCheck = !$lastCheckDate || $lastCheckDate->diffInMinutes(now()) > 5;

        return view('managers.views.settings.erp.index', compact('settings', 'stats', 'needsCheck'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit()
    {
        $settings = Setting::getErpSettings();

        return view('managers.views.settings.erp.edit', compact('settings'));
    }

    /**
     * Actualizar configuración
     */
    public function update(Request $request)
    {
        $rules = Setting::getErpRules();
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Setting::setErpSettings($request->all());

        // Limpiar cache del servicio
        $this->erpService->clearCache();

        return redirect()->route('manager.settings.erp.index')
            ->with('success', 'Configuración del ERP actualizada correctamente');
    }

    /**
     * Verificar conexión con el ERP
     */
    public function checkConnection()
    {
        try {
            $result = $this->erpService->checkConnection();

            Setting::updateErpConnectionStatus($result['status']);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Error verificando conexión ERP: ' . $e->getMessage());

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
        $isActive = $request->input('active');

        if ($isActive === null) {
            $current = Setting::get('erp_is_active', 'no');
            $isActive = $current === 'yes' ? 'no' : 'yes';
        } else {
            $isActive = $isActive ? 'yes' : 'no';
        }

        Setting::set('erp_is_active', $isActive);

        return response()->json([
            'success' => true,
            'is_active' => $isActive === 'yes',
            'message' => $isActive === 'yes' ? 'Servicio ERP activado' : 'Servicio ERP desactivado'
        ]);
    }

    /**
     * Limpiar cache del ERP
     */
    public function clearCache()
    {
        try {
            $this->erpService->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Cache del ERP limpiado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resetear estadísticas
     */
    public function resetStats()
    {
        Setting::resetErpStats();

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
        $stats = Setting::getErpStats();

        if (!$stats) {
            return response()->json([
                'success' => false,
                'message' => 'Configuración no encontrada'
            ], 404);
        }

        $lastCheck = $stats['last_connection_check'] ?? null;
        $lastCheckDate = $lastCheck ? \Carbon\Carbon::parse($lastCheck) : null;

        return response()->json([
            'success' => true,
            'data' => [
                'total_requests' => $stats['total_requests'] ?? 0,
                'failed_requests' => $stats['failed_requests'] ?? 0,
                'success_rate' => $stats['success_rate'] ?? 100,
                'last_check' => $lastCheckDate?->diffForHumans() ?? null,
                'last_status' => $stats['last_connection_status'] ?? null,
                'is_active' => $stats['is_active'] ?? false,
            ]
        ]);
    }

    /**
     * Test de sincronización
     */
    public function testSync()
    {
        try {
            $result = $this->erpService->getCambiosPendientes(10, 0);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sincronización funcionando correctamente',
                    'pending_changes' => $result['count'] ?? 0
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo conectar al servicio de sincronización'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en sincronización: ' . $e->getMessage()
            ], 500);
        }
    }
}
