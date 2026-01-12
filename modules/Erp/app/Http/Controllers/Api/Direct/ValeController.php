<?php

namespace Modules\Erp\Http\Controllers\Direct;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * VERSIÓN SQL DIRECTO - GESTIÓN DE VALES
 *
 * Endpoints compatibles con API Gestión ERP
 * Base URL: /api/direct/vale
 *
 * Performance: ~15-30ms (vs ~80-120ms Eloquent)
 */
class ValeController extends Controller
{
    /**
     * Consultar vale por ID
     *
     * GET /api/direct/vale/{idvale}
     */
    public function show($idvale): JsonResponse
    {
        $startTime = microtime(true);

        try {
            // Usar Query Builder para aplicar automáticamente prefix_schema
            $vale = DB::connection('oracle')
                ->table('vale as v')
                ->leftJoin('cliente_cent as c', 'v.idcliente', '=', 'c.idcliente')
                ->leftJoin('almacen as a', 'v.idalmacen', '=', 'a.idalmacen')
                ->selectRaw("
                    v.idvale,
                    v.importe,
                    v.estado,
                    v.tipo,
                    TO_CHAR(v.fvalidez, 'YYYY-MM-DD') as fvalidez,
                    TO_CHAR(v.fanulacion, 'YYYY-MM-DD') as fanulacion,
                    v.idcliente,
                    c.nombre as cliente_nombre,
                    c.apellidos as cliente_apellidos,
                    c.email as cliente_email,
                    v.idalmacen,
                    a.descripcion as almacen_descripcion,
                    v.observaciones,
                    v.tiene_codigo_comprobacion,
                    v.idvale_original
                ")
                ->where('v.idvale', $idvale)
                ->first();

            if (!$vale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vale no encontrado'
                ], 404);
            }

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO SQL DIRECTO ValeController::show: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'data' => [
                    'idvale' => $vale->idvale,
                    'importe' => $vale->importe,
                    'estado' => $vale->estado,
                    'tipo' => $vale->tipo,
                    'fvalidez' => $vale->fvalidez,
                    'fanulacion' => $vale->fanulacion,
                    'idcliente' => $vale->idcliente,
                    'cliente' => $vale->idcliente ? [
                        'idcliente' => $vale->idcliente,
                        'nombre' => $vale->cliente_nombre,
                        'apellidos' => $vale->cliente_apellidos,
                        'email' => $vale->cliente_email,
                    ] : null,
                    'idalmacen' => $vale->idalmacen,
                    'almacen' => $vale->idalmacen ? [
                        'idalmacen' => $vale->idalmacen,
                        'descripcion' => $vale->almacen_descripcion,
                    ] : null,
                    'observaciones' => $vale->observaciones,
                    'tiene_codigo_comprobacion' => $vale->tiene_codigo_comprobacion,
                    'idvale_original' => $vale->idvale_original,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ValeController::show (SQL Directo)', [
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
     * POST /api/direct/vale
     */
    public function store(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        DB::connection('oracle')->beginTransaction();

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

            // Obtener siguiente ID de la secuencia
            $nextId = DB::connection('oracle')->selectOne("SELECT SEQ_VALE.NEXTVAL as nextval FROM DUAL");
            $idvale = $nextId->nextval;

            $sql = "
                INSERT INTO vale (
                    idvale, importe, estado, idcliente, idalmacen, tipo,
                    fvalidez, observaciones, tiene_codigo_comprobacion
                ) VALUES (
                    :idvale, :importe, :estado, :idcliente, :idalmacen, :tipo,
                    :fvalidez, :observaciones, :tiene_codigo_comprobacion
                )
            ";

            $bindings = [
                'idvale' => $idvale,
                'importe' => $validated['importe'],
                'estado' => 1, // Activo
                'idcliente' => $validated['idcliente'],
                'idalmacen' => $validated['idalmacen'],
                'tipo' => $validated['tipo'] ?? 1,
                'fvalidez' => $validated['fvalidez'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'tiene_codigo_comprobacion' => $validated['tiene_codigo_comprobacion'] ?? 0,
            ];

            DB::connection('oracle')->insert($sql, $bindings);
            DB::connection('oracle')->commit();

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO SQL DIRECTO ValeController::store: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'message' => 'Vale creado correctamente',
                'data' => [
                    'idvale' => $idvale,
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::connection('oracle')->rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::connection('oracle')->rollBack();
            Log::error('Error en ValeController::store (SQL Directo)', [
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
     * PUT /api/direct/vale/{idvale}
     */
    public function update(Request $request, $idvale): JsonResponse
    {
        $startTime = microtime(true);

        DB::connection('oracle')->beginTransaction();

        try {
            $validated = $request->validate([
                'operacion' => 'required|in:anular',
            ]);

            // Verificar que el vale existe
            $sqlSelect = "SELECT idvale, fanulacion FROM vale WHERE idvale = :idvale";
            $vale = DB::connection('oracle')->selectOne($sqlSelect, ['idvale' => $idvale]);

            if (!$vale) {
                DB::connection('oracle')->rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Vale no encontrado'
                ], 404);
            }

            if ($validated['operacion'] === 'anular') {
                if ($vale->fanulacion) {
                    DB::connection('oracle')->rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'El vale ya está anulado'
                    ], 400);
                }

                $sqlUpdate = "UPDATE vale SET fanulacion = SYSDATE, estado = 0 WHERE idvale = :idvale";
                DB::connection('oracle')->update($sqlUpdate, ['idvale' => $idvale]);
            }

            DB::connection('oracle')->commit();

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO SQL DIRECTO ValeController::update: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'message' => 'Vale actualizado correctamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::connection('oracle')->rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::connection('oracle')->rollBack();
            Log::error('Error en ValeController::update (SQL Directo)', [
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
