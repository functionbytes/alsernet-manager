<?php

namespace Modules\Erp\Http\Controllers\Direct;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * VERSIÓN SQL DIRECTO - GESTIÓN DE CLIENTES
 *
 * Endpoints compatibles con API Gestión ERP
 * Base URL: /api/direct/clientes
 *
 * Performance: ~50-100ms (vs ~200-300ms Eloquent)
 */
class ClienteController extends Controller
{
    /**
     * Buscar clientes por múltiples criterios
     *
     * GET /api/direct/clientes?{params}
     */
    public function index(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            // Usar Query Builder para aplicar automáticamente prefix_schema
            $query = DB::connection('oracle')
                ->table('cliente_cent')
                ->selectRaw("idcliente, nombre, apellidos, cif, email, codigo_internet,
                            idtarjeta, idcategoria_cliente, ididioma,
                            TO_CHAR(fcreacion, 'YYYY-MM-DD') as fcreacion,
                            TO_CHAR(fbaja, 'YYYY-MM-DD') as fbaja,
                            TO_CHAR(faceptacion_lopd, 'YYYY-MM-DD') as faceptacion_lopd,
                            no_informacion_comercial_lopd,
                            no_datos_a_terceros_lopd,
                            tiene_interes_legitimo_lopd");

            // Aplicar filtros (ORDENADOS POR PERFORMANCE - índices primero)

            // ✅ ÓPTIMO: Usa PK_CLIENTE_CENT (indexado)
            if ($request->filled('idcliente_gestion')) {
                $query->where('idcliente', $request->idcliente_gestion);
            }

            // ✅ ÓPTIMO: Usa IDX_CLIENTE_CENT_CIF (indexado) ~20ms
            if ($request->filled('dni')) {
                $query->where('cif', $request->dni);
            }

            // ⚠️ LENTO: EMAIL no tiene índice - Full table scan ~3 segundos
            // RECOMENDACIÓN: Usar 'dni' (CIF) en lugar de 'email' cuando sea posible
            // Optimización: agregar LIMIT 1 si solo se busca por email
            $useLimit = false;
            if ($request->filled('email')) {
                $query->where('email', $request->email);
                $useLimit = true;  // EMAIL es único, podemos usar LIMIT 1
            }

            if ($request->filled('apellidos')) {
                $query->whereRaw('UPPER(apellidos) LIKE ?', ['%' . strtoupper($request->apellidos) . '%']);
                $useLimit = false;  // Múltiples filtros, no usar LIMIT
            }

            if ($request->filled('fnacimiento')) {
                $query->whereRaw('TRUNC(fnacimiento) = TO_DATE(?, \'YYYY-MM-DD\')', [$request->fnacimiento]);
                $useLimit = false;
            }

            if ($request->filled('faceptacion_lopd_desde')) {
                $query->whereRaw('faceptacion_lopd >= TO_DATE(?, \'YYYY-MM-DD\')', [$request->faceptacion_lopd_desde]);
                $useLimit = false;
            }

            if ($request->filled('faceptacion_lopd_hasta')) {
                $query->whereRaw('faceptacion_lopd <= TO_DATE(?, \'YYYY-MM-DD\')', [$request->faceptacion_lopd_hasta]);
                $useLimit = false;
            }

            if ($request->filled('fbaja_desde')) {
                $query->whereRaw('fbaja >= TO_DATE(?, \'YYYY-MM-DD\')', [$request->fbaja_desde]);
                $useLimit = false;
            }

            if ($request->filled('fbaja_hasta')) {
                $query->whereRaw('fbaja <= TO_DATE(?, \'YYYY-MM-DD\')', [$request->fbaja_hasta]);
                $useLimit = false;  // Si hay rangos de fecha, no usar LIMIT
            }

            // Optimización: si solo busca por email (campo único), usar LIMIT 1
            if ($useLimit) {
                $query->limit(1);
            }

            $clientes = $query->get()->toArray();

            if (empty($clientes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron clientes'
                ], 404);
            }

            // Obtener catálogos y cuotas para cada cliente
            $result = array_map(function($cliente) {
                return $this->enrichClienteData($cliente);
            }, $clientes);

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO SQL DIRECTO ClienteController::index: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'data' => $result,
                'total' => count($result)
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ClienteController::index (SQL Directo)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al buscar clientes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear o actualizar cliente
     *
     * POST /api/direct/clientes
     */
    public function store(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        DB::connection('oracle')->beginTransaction();

        try {
            $validated = $request->validate([
                'idcliente_gestion' => 'nullable|integer',
                'cliente_nombre' => 'required|string',
                'cliente_apellidos' => 'required|string',
                'cliente_cif' => 'required|string',
                'cliente_email' => 'required|email',
                'cliente_faceptacion_lopd' => 'required|date',
                'cliente_no_info_comercial' => 'required|boolean',
                'cliente_no_datos_a_terceros' => 'required|boolean',
                'cliente_idcatalogo' => 'nullable|string',
            ]);

            // Validaciones de negocio
            if (empty($validated['cliente_faceptacion_lopd'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'ERROR 20404: No es posible insertar un cliente que no ha aceptado LOPD',
                    'code' => 20404
                ], 400);
            }

            if (empty($validated['cliente_idcatalogo'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'ERROR 20403: El cliente debe tener al menos un catálogo',
                    'code' => 20403
                ], 400);
            }

            $idcliente = null;

            // Actualizar o Insertar
            if ($request->filled('idcliente_gestion')) {
                // UPDATE
                $sql = "
                    UPDATE cliente_cent SET
                        nombre = :nombre,
                        apellidos = :apellidos,
                        cif = :cif,
                        email = :email,
                        faceptacion_lopd = TO_DATE(:faceptacion_lopd, 'YYYY-MM-DD HH24:MI:SS'),
                        no_informacion_comercial_lopd = :no_info,
                        no_datos_a_terceros_lopd = :no_terceros
                    WHERE idcliente = :idcliente
                ";

                DB::connection('oracle')->update($sql, [
                    'nombre' => $request->cliente_nombre,
                    'apellidos' => $request->cliente_apellidos,
                    'cif' => $request->cliente_cif,
                    'email' => $request->cliente_email,
                    'faceptacion_lopd' => $request->cliente_faceptacion_lopd,
                    'no_info' => $request->cliente_no_info_comercial ? 1 : 0,
                    'no_terceros' => $request->cliente_no_datos_a_terceros ? 1 : 0,
                    'idcliente' => $request->idcliente_gestion
                ]);

                $idcliente = $request->idcliente_gestion;

            } else {
                // INSERT - Obtener nuevo ID de secuencia
                $nextId = DB::connection('oracle')->selectOne("SELECT SEQ_CLIENTE_CENT.NEXTVAL as nextval FROM DUAL");
                $idcliente = $nextId->nextval;

                $sql = "
                    INSERT INTO cliente_cent (
                        idcliente, nombre, apellidos, cif, email,
                        faceptacion_lopd, no_informacion_comercial_lopd, no_datos_a_terceros_lopd,
                        fcreacion
                    ) VALUES (
                        :idcliente, :nombre, :apellidos, :cif, :email,
                        TO_DATE(:faceptacion_lopd, 'YYYY-MM-DD HH24:MI:SS'),
                        :no_info, :no_terceros, SYSDATE
                    )
                ";

                DB::connection('oracle')->insert($sql, [
                    'idcliente' => $idcliente,
                    'nombre' => $request->cliente_nombre,
                    'apellidos' => $request->cliente_apellidos,
                    'cif' => $request->cliente_cif,
                    'email' => $request->cliente_email,
                    'faceptacion_lopd' => $request->cliente_faceptacion_lopd,
                    'no_info' => $request->cliente_no_info_comercial ? 1 : 0,
                    'no_terceros' => $request->cliente_no_datos_a_terceros ? 1 : 0,
                ]);
            }

            // Insertar catálogos
            if ($request->filled('cliente_idcatalogo')) {
                $catalogos = explode(',', $request->cliente_idcatalogo);
                foreach ($catalogos as $idcatalogo) {
                    $idcatalogo = trim($idcatalogo);

                    // Verificar si ya existe
                    $existe = DB::connection('oracle')->selectOne(
                        "SELECT COUNT(*) as total FROM clientecatalogo_cent
                         WHERE idcliente = :idcliente AND idcatalogo = :idcatalogo",
                        ['idcliente' => $idcliente, 'idcatalogo' => $idcatalogo]
                    );

                    if ($existe->total == 0) {
                        $nextCatId = DB::connection('oracle')->selectOne("SELECT SEQ_CLIENTECATALOGO_CENT.NEXTVAL as nextval FROM DUAL");

                        DB::connection('oracle')->insert(
                            "INSERT INTO clientecatalogo_cent (idclientecatalogo, idcliente, idcatalogo, estado, fsuscripcion)
                             VALUES (:idcc, :idcliente, :idcatalogo, 1, SYSDATE)",
                            [
                                'idcc' => $nextCatId->nextval,
                                'idcliente' => $idcliente,
                                'idcatalogo' => $idcatalogo
                            ]
                        );
                    }
                }
            }

            DB::connection('oracle')->commit();

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO SQL DIRECTO ClienteController::store: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'idcliente' => $idcliente
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
            Log::error('Error en ClienteController::store (SQL Directo)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear/actualizar cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar LOPD por email
     *
     * PUT /api/direct/clientes
     */
    public function updateLopd(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cliente_email' => 'required|email',
                'cliente_faceptacion_lopd' => 'required|date',
                'cliente_no_info_comercial' => 'required|boolean',
                'cliente_no_datos_a_terceros' => 'required|boolean',
            ]);

            $sql = "
                UPDATE cliente_cent SET
                    faceptacion_lopd = TO_DATE(:faceptacion_lopd, 'YYYY-MM-DD HH24:MI:SS'),
                    no_informacion_comercial_lopd = :no_info,
                    no_datos_a_terceros_lopd = :no_terceros
                WHERE email = :email
            ";

            $affected = DB::connection('oracle')->update($sql, [
                'faceptacion_lopd' => $request->cliente_faceptacion_lopd,
                'no_info' => $request->cliente_no_info_comercial ? 1 : 0,
                'no_terceros' => $request->cliente_no_datos_a_terceros ? 1 : 0,
                'email' => $request->cliente_email
            ]);

            if ($affected === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'LOPD actualizado correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ClienteController::updateLopd (SQL Directo)', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar LOPD',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enriquecer datos del cliente con catálogos y cuotas
     */
    private function enrichClienteData($cliente): array
    {
        // Obtener catálogos usando Query Builder
        $catalogos = DB::connection('oracle')
            ->table('clientecatalogo_cent')
            ->selectRaw("idcatalogo, TO_CHAR(fsuscripcion, 'YYYY-MM-DD') as fsuscripcion, estado")
            ->where('idcliente', $cliente->idcliente)
            ->whereNull('fbaja')
            ->get()
            ->toArray();

        // Obtener cuotas usando Query Builder (con try-catch por si la tabla no existe)
        try {
            $cuotas = DB::connection('oracle')
                ->table('clientecuota_cent as cc')
                ->leftJoin('articulo as a', 'cc.idarticulo', '=', 'a.idarticulo')
                ->selectRaw("TO_CHAR(cc.fcontratacion, 'YYYY-MM-DD') as fcontratacion,
                            TO_CHAR(cc.ffinservicio, 'YYYY-MM-DD') as ffinservicio,
                            cc.estado,
                            a.idarticulo, a.codigo, a.descripcion")
                ->where('cc.idcliente', $cliente->idcliente)
                ->whereNull('cc.fbaja')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            // Si la tabla no existe o no es accesible, retornar array vacío
            Log::warning("Error al obtener cuotas del cliente {$cliente->idcliente}: " . $e->getMessage());
            $cuotas = [];
        }

        return [
            'idcliente' => $cliente->idcliente,
            'nombre' => $cliente->nombre,
            'apellidos' => $cliente->apellidos,
            'cif' => $cliente->cif,
            'email' => $cliente->email,
            'codigo_internet' => $cliente->codigo_internet,
            'idtarjeta' => $cliente->idtarjeta,
            'idcategoria_cliente' => $cliente->idcategoria_cliente,
            'ididioma' => $cliente->ididioma,
            'fcreacion' => $cliente->fcreacion,
            'fbaja' => $cliente->fbaja,
            'faceptacion_lopd' => $cliente->faceptacion_lopd,
            'no_informacion_comercial_lopd' => $cliente->no_informacion_comercial_lopd,
            'no_datos_a_terceros_lopd' => $cliente->no_datos_a_terceros_lopd,
            'tiene_interes_legitimo_lopd' => $cliente->tiene_interes_legitimo_lopd,
            'cliente_catalogo' => array_map(fn($cat) => [
                'idcatalogo' => $cat->idcatalogo,
                'fsuscripcion' => $cat->fsuscripcion,
                'estado' => $cat->estado
            ], $catalogos),
            'cliente_cuota' => array_map(fn($cuota) => [
                'fcontratacion' => $cuota->fcontratacion,
                'ffinservicio' => $cuota->ffinservicio,
                'estado' => $cuota->estado,
                'articulo' => [
                    'idarticulo' => $cuota->idarticulo,
                    'codigo' => $cuota->codigo,
                    'descripcion' => $cuota->descripcion,
                ]
            ], $cuotas),
            'cantidad_albaranes' => 0, // TODO: Calcular
        ];
    }
}
