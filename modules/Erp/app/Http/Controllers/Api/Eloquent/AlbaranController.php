<?php

namespace Modules\Erp\Http\Controllers\Eloquent;

use App\Http\Controllers\Controller;
use Modules\Erp\Models\V2\Oracle\Albaran\AlbarancliCentral;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * VERSIÓN ELOQUENT - GESTIÓN DE ALBARANES DE CLIENTE
 *
 * Endpoints compatibles con API Gestión ERP
 * Base URL: /api/eloquent/albaran-cliente
 */
class AlbaranController extends Controller
{
    /**
     * Consultar albarán de cliente
     *
     * GET /api/eloquent/albaran-cliente/{idalbarancli}
     * GET /api/eloquent/albaran-cliente?nalbarancli={numero}&serie={serie}
     */
    public function show(Request $request, $idalbarancli = null): JsonResponse
    {
        $startTime = microtime(true);

        try {
            // Buscar por ID o por número + serie
            if ($idalbarancli) {
                $albaran = AlbarancliCentral::with([
                    'cliente:idcliente,nombre,apellidos,email,cif',
                    'almacen:idalmacen,nombre',
                    'seriealbarancliCentral:idseriealbarancli_central,codigo,descripcion'
                ])
                ->find($idalbarancli);
            } else {
                $validated = $request->validate([
                    'nalbarancli' => 'required|integer',
                    'serie' => 'required|integer',
                ]);

                $albaran = AlbarancliCentral::with([
                    'cliente:idcliente,nombre,apellidos,email,cif',
                    'almacen:idalmacen,nombre',
                    'seriealbarancliCentral:idseriealbarancli_central,codigo,descripcion'
                ])
                ->where('nalbarancli', $validated['nalbarancli'])
                ->where('idseriealbarancli_central', $validated['serie'])
                ->first();
            }

            if (!$albaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Albarán no encontrado'
                ], 404);
            }

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO ELOQUENT AlbaranController::show: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'data' => [
                    'idalbarancli_central' => $albaran->idalbarancli_central,
                    'nalbarancli' => $albaran->nalbarancli,
                    'idseriealbarancli_central' => $albaran->idseriealbarancli_central,
                    'serie' => $albaran->seriealbarancliCentral ? [
                        'idserie' => $albaran->seriealbarancliCentral->idseriealbarancli_central,
                        'codigo' => $albaran->seriealbarancliCentral->codigo,
                        'descripcion' => $albaran->seriealbarancliCentral->descripcion ?? null
                    ] : null,
                    'falbaran' => $albaran->falbaran?->format('Y-m-d'),
                    'estado' => $albaran->estado,
                    'tipo' => $albaran->tipo,
                    'idcliente' => $albaran->idcliente,
                    'cliente' => $albaran->cliente ? [
                        'idcliente' => $albaran->cliente->idcliente,
                        'nombre' => $albaran->cliente->nombre,
                        'apellidos' => $albaran->cliente->apellidos,
                        'email' => $albaran->cliente->email,
                        'cif' => $albaran->cliente->cif,
                    ] : null,
                    'idalmacen' => $albaran->idalmacen,
                    'almacen' => $albaran->almacen ? [
                        'idalmacen' => $albaran->almacen->idalmacen,
                        'nombre' => $albaran->almacen->nombre,
                    ] : null,
                    'idalmacen_creacion' => $albaran->idalmacen_creacion,
                    'idfacturacli' => $albaran->idfacturacli,
                    'solicita_factura' => $albaran->solicita_factura,
                    'observaciones' => $albaran->observaciones,
                    'email' => $albaran->email,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error en AlbaranController::show (Eloquent)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al consultar albarán',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
