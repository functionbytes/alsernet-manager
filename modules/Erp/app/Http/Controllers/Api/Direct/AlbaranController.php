<?php

namespace Modules\Erp\Http\Controllers\Direct;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * VERSIÓN SQL DIRECTO - GESTIÓN DE ALBARANES DE CLIENTE
 *
 * Endpoints compatibles con API Gestión ERP
 * Base URL: /api/direct/albaran-cliente
 *
 * Performance: ~15-35ms (vs ~100-150ms Eloquent)
 */
class AlbaranController extends Controller
{
    /**
     * Consultar albarán de cliente
     *
     * GET /api/direct/albaran-cliente/{idalbarancli}
     * GET /api/direct/albaran-cliente?nalbarancli={numero}&serie={serie}
     */
    public function show(Request $request, $idalbarancli = null): JsonResponse
    {
        $startTime = microtime(true);

        try {
            // Usar Query Builder para aplicar automáticamente prefix_schema
            $query = DB::connection('oracle')
                ->table('albarancli_central as a')
                ->leftJoin('cliente_cent as c', 'a.idcliente', '=', 'c.idcliente')
                ->leftJoin('almacen as alm', 'a.idalmacen', '=', 'alm.idalmacen')
                ->leftJoin('seriealbarancli_central as s', 'a.idseriealbarancli_central', '=', 's.idseriealbarancli_central')
                ->selectRaw("
                    a.idalbarancli_central,
                    a.nalbarancli,
                    a.idseriealbarancli_central,
                    s.descripcion as serie_descripcion,
                    s.descripcioncorta as serie_descripcion_corta,
                    TO_CHAR(a.falbaran, 'YYYY-MM-DD') as falbaran,
                    a.estado,
                    a.tipo,
                    a.idcliente,
                    c.nombre as cliente_nombre,
                    c.apellidos as cliente_apellidos,
                    c.email as cliente_email,
                    c.cif as cliente_cif,
                    a.idalmacen,
                    alm.descripcion as almacen_descripcion,
                    a.idalmacen_creacion,
                    a.idfacturacli,
                    a.solicita_factura,
                    a.observaciones,
                    a.email
                ")
                ->whereNull('a.fbaja');

            if ($idalbarancli) {
                // Buscar por ID
                $query->where('a.idalbarancli_central', $idalbarancli);
            } else {
                // Buscar por número + serie
                $validated = $request->validate([
                    'nalbarancli' => 'required|integer',
                    'serie' => 'required|integer',
                ]);

                $query->where('a.nalbarancli', $validated['nalbarancli'])
                      ->where('a.idseriealbarancli_central', $validated['serie']);
            }

            $albaran = $query->first();

            if (!$albaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Albarán no encontrado'
                ], 404);
            }

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO SQL DIRECTO AlbaranController::show: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'data' => [
                    'idalbarancli_central' => $albaran->idalbarancli_central,
                    'nalbarancli' => $albaran->nalbarancli,
                    'idseriealbarancli_central' => $albaran->idseriealbarancli_central,
                    'serie' => $albaran->idseriealbarancli_central ? [
                        'idserie' => $albaran->idseriealbarancli_central,
                        'descripcion' => $albaran->serie_descripcion,
                        'descripcion_corta' => $albaran->serie_descripcion_corta
                    ] : null,
                    'falbaran' => $albaran->falbaran,
                    'estado' => $albaran->estado,
                    'tipo' => $albaran->tipo,
                    'idcliente' => $albaran->idcliente,
                    'cliente' => $albaran->idcliente ? [
                        'idcliente' => $albaran->idcliente,
                        'nombre' => $albaran->cliente_nombre,
                        'apellidos' => $albaran->cliente_apellidos,
                        'email' => $albaran->cliente_email,
                        'cif' => $albaran->cliente_cif,
                    ] : null,
                    'idalmacen' => $albaran->idalmacen,
                    'almacen' => $albaran->idalmacen ? [
                        'idalmacen' => $albaran->idalmacen,
                        'descripcion' => $albaran->almacen_descripcion,
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
            Log::error('Error en AlbaranController::show (SQL Directo)', [
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
