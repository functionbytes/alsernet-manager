<?php

namespace Modules\Erp\Http\Controllers\Eloquent;

use App\Http\Controllers\Controller;
use Modules\Erp\Models\V2\Oracle\Otros\Vale;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * VERSIÓN ELOQUENT - GESTIÓN DE VALES
 *
 * Endpoints compatibles con API Gestión ERP
 * Base URL: /api/eloquent/vale
 */
class ValeController extends Controller
{
    /**
     * Consultar vale por ID
     *
     * GET /api/eloquent/vale/{idvale}
     */
    public function show($idvale): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $vale = Vale::with(['cliente:idcliente,nombre,apellidos,email', 'almacen:idalmacen,nombre'])
                ->where('idvale', $idvale)
                ->first();

            if (!$vale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vale no encontrado'
                ], 404);
            }

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO ELOQUENT ValeController::show: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'data' => [
                    'idvale' => $vale->idvale,
                    'importe' => $vale->importe,
                    'estado' => $vale->estado,
                    'tipo' => $vale->tipo,
                    'fvalidez' => $vale->fvalidez?->format('Y-m-d'),
                    'fanulacion' => $vale->fanulacion?->format('Y-m-d'),
                    'idcliente' => $vale->idcliente,
                    'cliente' => $vale->cliente ? [
                        'idcliente' => $vale->cliente->idcliente,
                        'nombre' => $vale->cliente->nombre,
                        'apellidos' => $vale->cliente->apellidos,
                        'email' => $vale->cliente->email,
                    ] : null,
                    'idalmacen' => $vale->idalmacen,
                    'almacen' => $vale->almacen ? [
                        'idalmacen' => $vale->almacen->idalmacen,
                        'nombre' => $vale->almacen->nombre,
                    ] : null,
                    'observaciones' => $vale->observaciones,
                    'tiene_codigo_comprobacion' => $vale->tiene_codigo_comprobacion,
                    'idvale_original' => $vale->idvale_original,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ValeController::show (Eloquent)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al consultar vale',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo vale
     *
     * POST /api/eloquent/vale
     */
    public function store(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $validated = $request->validate([
                'importe' => 'required|numeric|min:0',
                'idcliente' => 'required|integer',
                'idalmacen' => 'required|integer',
                'tipo' => 'nullable|integer',
                'fvalidez' => 'nullable|date',
                'observaciones' => 'nullable|string',
                'tiene_codigo_comprobacion' => 'nullable|boolean',
            ]);

            $vale = new Vale();
            $vale->importe = $validated['importe'];
            $vale->idcliente = $validated['idcliente'];
            $vale->idalmacen = $validated['idalmacen'];
            $vale->tipo = $validated['tipo'] ?? 1;
            $vale->estado = 1; // Activo por defecto
            $vale->fvalidez = $validated['fvalidez'] ?? null;
            $vale->observaciones = $validated['observaciones'] ?? null;
            $vale->tiene_codigo_comprobacion = $validated['tiene_codigo_comprobacion'] ?? 0;
            $vale->save();

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO ELOQUENT ValeController::store: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'message' => 'Vale creado correctamente',
                'data' => [
                    'idvale' => $vale->idvale,
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error en ValeController::store (Eloquent)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear vale',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar vale (anular)
     *
     * PUT /api/eloquent/vale/{idvale}
     */
    public function update(Request $request, $idvale): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $validated = $request->validate([
                'operacion' => 'required|in:anular',
            ]);

            $vale = Vale::where('idvale', $idvale)->first();

            if (!$vale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vale no encontrado'
                ], 404);
            }

            if ($validated['operacion'] === 'anular') {
                if ($vale->fanulacion) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El vale ya está anulado'
                    ], 400);
                }

                $vale->fanulacion = now();
                $vale->estado = 0;
                $vale->save();
            }

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO ELOQUENT ValeController::update: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'message' => 'Vale actualizado correctamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error en ValeController::update (Eloquent)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar vale',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
