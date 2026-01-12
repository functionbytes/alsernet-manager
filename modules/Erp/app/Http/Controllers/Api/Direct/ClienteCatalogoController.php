<?php

namespace Modules\Erp\Http\Controllers\Direct;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * VERSIÓN SQL DIRECTO - GESTIÓN DE CLIENTE CATÁLOGO
 *
 * Endpoints compatibles con API Gestión ERP
 * Base URL: /api/direct/clientecatalogo
 *
 * Performance: ~30-60ms (vs ~100-150ms Eloquent)
 */
class ClienteCatalogoController extends Controller
{
    /**
     * Consultar catálogos de un cliente por idcliente
     *
     * GET /api/direct/cliente-catalogo?idcliente={id}
     */
    public function index(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $validated = $request->validate([
                'idcliente' => 'required|integer',
            ]);

            // Usar Query Builder para aplicar automáticamente prefix_schema
            $catalogos = DB::connection('oracle')
                ->table('clientecatalogo_cent')
                ->selectRaw("
                    idclientecatalogo,
                    idcliente,
                    idcatalogo,
                    estado,
                    TO_CHAR(fsuscripcion, 'YYYY-MM-DD') as fsuscripcion
                ")
                ->where('idcliente', $validated['idcliente'])
                ->whereNull('fbaja')
                ->orderBy('fsuscripcion', 'desc')
                ->get()
                ->toArray();

            if (empty($catalogos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron catálogos'
                ], 404);
            }

            $result = array_map(function($catalogo) {
                return [
                    'idclientecatalogo' => $catalogo->idclientecatalogo,
                    'idcliente' => $catalogo->idcliente,
                    'idcatalogo' => $catalogo->idcatalogo,
                    'estado' => $catalogo->estado,
                    'fsuscripcion' => $catalogo->fsuscripcion,
                ];
            }, $catalogos);

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO SQL DIRECTO ClienteCatalogoController::index: " . round($totalTime * 1000, 2) . "ms ===");

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
            Log::error('Error en ClienteCatalogoController::index (SQL Directo)', [
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
     * POST /api/direct/clientecatalogo
     */
    public function store(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        DB::connection('oracle')->beginTransaction();

        try {
            $validated = $request->validate([
                'cliente_email' => 'required|email',
                'cliente_idcatalogo' => 'required|string', // IDs separados por coma
            ]);

            // Buscar clientes por email
            $sqlCliente = "SELECT idcliente FROM cliente_cent WHERE email = :email AND fbaja IS NULL";
            $clientes = DB::connection('oracle')->select($sqlCliente, [
                'email' => $validated['cliente_email']
            ]);

            if (empty($clientes)) {
                DB::connection('oracle')->rollBack();
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

                    // Verificar si ya existe
                    $sqlExiste = "
                        SELECT COUNT(*) as total
                        FROM clientecatalogo_cent
                        WHERE idcliente = :idcliente
                          AND idcatalogo = :idcatalogo
                    ";

                    $existe = DB::connection('oracle')->selectOne($sqlExiste, [
                        'idcliente' => $cliente->idcliente,
                        'idcatalogo' => $idcatalogo
                    ]);

                    if ($existe->total == 0) {
                        // Obtener nuevo ID de secuencia
                        $nextId = DB::connection('oracle')->selectOne(
                            "SELECT SEQ_CLIENTECATALOGO_CENT.NEXTVAL as nextval FROM DUAL"
                        );

                        // Insertar nueva suscripción
                        $sqlInsert = "
                            INSERT INTO clientecatalogo_cent (
                                idclientecatalogo, idcliente, idcatalogo, estado, fsuscripcion, fcreacion
                            ) VALUES (
                                :idclientecatalogo, :idcliente, :idcatalogo, 1, SYSDATE, SYSDATE
                            )
                        ";

                        DB::connection('oracle')->insert($sqlInsert, [
                            'idclientecatalogo' => $nextId->nextval,
                            'idcliente' => $cliente->idcliente,
                            'idcatalogo' => $idcatalogo
                        ]);

                        $suscripciones++;
                    }
                }
            }

            DB::connection('oracle')->commit();

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO SQL DIRECTO ClienteCatalogoController::store: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'message' => 'Suscripciones creadas correctamente',
                'suscripciones' => $suscripciones
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
            Log::error('Error en ClienteCatalogoController::store (SQL Directo)', [
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
     * DELETE /api/direct/clientecatalogo
     */
    public function destroy(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        DB::connection('oracle')->beginTransaction();

        try {
            $validated = $request->validate([
                'cliente_email' => 'required|email',
                'cliente_idcatalogo' => 'required|string', // IDs separados por coma
            ]);

            // Buscar clientes por email
            $sqlCliente = "SELECT idcliente FROM cliente_cent WHERE email = :email AND fbaja IS NULL";
            $clientes = DB::connection('oracle')->select($sqlCliente, [
                'email' => $validated['cliente_email']
            ]);

            if (empty($clientes)) {
                DB::connection('oracle')->rollBack();
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

                    // Soft delete (marcar fbaja)
                    $sqlDelete = "
                        UPDATE clientecatalogo_cent
                        SET fbaja = SYSDATE,
                            fmodificacion = SYSDATE
                        WHERE idcliente = :idcliente
                          AND idcatalogo = :idcatalogo
                          AND fbaja IS NULL
                    ";

                    $affected = DB::connection('oracle')->update($sqlDelete, [
                        'idcliente' => $cliente->idcliente,
                        'idcatalogo' => $idcatalogo
                    ]);

                    $desuscripciones += $affected;
                }
            }

            DB::connection('oracle')->commit();

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO SQL DIRECTO ClienteCatalogoController::destroy: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'message' => 'Desuscripciones realizadas correctamente',
                'desuscripciones' => $desuscripciones
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
            Log::error('Error en ClienteCatalogoController::destroy (SQL Directo)', [
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
