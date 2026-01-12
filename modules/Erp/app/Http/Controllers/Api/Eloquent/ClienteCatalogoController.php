<?php

namespace Modules\Erp\Http\Controllers\Eloquent;

use App\Http\Controllers\Controller;
use Modules\Erp\Models\V2\Oracle\Cliente\ClienteCent;
use Modules\Erp\Models\V2\Oracle\Cliente\ClientecatalogoCent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * VERSIÓN ELOQUENT - GESTIÓN DE CLIENTE CATÁLOGO
 *
 * Endpoints compatibles con API Gestión ERP
 * Base URL: /api/eloquent/clientecatalogo
 */
class ClienteCatalogoController extends Controller
{
    /**
     * Consultar catálogos de un cliente por idcliente
     *
     * GET /api/eloquent/cliente-catalogo?idcliente={id}
     */
    public function index(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $validated = $request->validate([
                'idcliente' => 'required|integer',
            ]);

            $catalogos = ClientecatalogoCent::where('idcliente', $validated['idcliente'])
                ->whereNull('fbaja')
                ->get();

            if ($catalogos->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron catálogos'
                ], 404);
            }

            $result = $catalogos->map(function($catalogo) {
                return [
                    'idclientecatalogo' => $catalogo->idclientecatalogo,
                    'idcliente' => $catalogo->idcliente,
                    'idcatalogo' => $catalogo->idcatalogo,
                    'estado' => $catalogo->estado,
                    'fsuscripcion' => $catalogo->fsuscripcion?->format('Y-m-d'),
                ];
            });

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO ELOQUENT ClienteCatalogoController::index: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error en ClienteCatalogoController::index (Eloquent)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al buscar catálogos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Suscribir a catálogos a los clientes con determinado email
     *
     * POST /api/eloquent/clientecatalogo
     */
    public function store(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $validated = $request->validate([
                'cliente_email' => 'required|email',
                'cliente_idcatalogo' => 'required|string', // IDs separados por coma
            ]);

            // Buscar clientes por email
            $clientes = ClienteCent::where('email', $validated['cliente_email'])->get();

            if ($clientes->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            // Procesar catálogos
            $catalogos = explode(',', $validated['cliente_idcatalogo']);
            $suscripciones = 0;

            foreach ($clientes as $cliente) {
                foreach ($catalogos as $idcatalogo) {
                    $idcatalogo = trim($idcatalogo);

                    // Usar firstOrCreate para evitar duplicados
                    ClientecatalogoCent::firstOrCreate([
                        'idcliente' => $cliente->idcliente,
                        'idcatalogo' => $idcatalogo,
                    ], [
                        'estado' => 1,
                        'fsuscripcion' => now()
                    ]);

                    $suscripciones++;
                }
            }

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO ELOQUENT ClienteCatalogoController::store: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'message' => 'Suscripciones creadas correctamente',
                'suscripciones' => $suscripciones
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error en ClienteCatalogoController::store (Eloquent)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear suscripciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desuscribir de catálogos a los clientes con determinado email
     *
     * DELETE /api/eloquent/clientecatalogo
     */
    public function destroy(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $validated = $request->validate([
                'cliente_email' => 'required|email',
                'cliente_idcatalogo' => 'required|string', // IDs separados por coma
            ]);

            // Buscar clientes por email
            $clientes = ClienteCent::where('email', $validated['cliente_email'])->get();

            if ($clientes->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            // Procesar catálogos
            $catalogos = explode(',', $validated['cliente_idcatalogo']);
            $desuscripciones = 0;

            foreach ($clientes as $cliente) {
                foreach ($catalogos as $idcatalogo) {
                    $idcatalogo = trim($idcatalogo);

                    // Soft delete
                    $affected = ClientecatalogoCent::where('idcliente', $cliente->idcliente)
                        ->where('idcatalogo', $idcatalogo)
                        ->whereNull('fbaja')
                        ->delete();

                    $desuscripciones += $affected;
                }
            }

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO ELOQUENT ClienteCatalogoController::destroy: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'message' => 'Desuscripciones realizadas correctamente',
                'desuscripciones' => $desuscripciones
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error en ClienteCatalogoController::destroy (Eloquent)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar suscripciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
