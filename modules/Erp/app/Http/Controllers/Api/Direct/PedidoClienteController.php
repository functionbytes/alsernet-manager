<?php

namespace Modules\Erp\Http\Controllers\Direct;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * VERSIÓN SQL DIRECTO - GESTIÓN DE PEDIDOS DE CLIENTE
 *
 * Endpoints compatibles con API Gestión ERP
 * Base URL: /api/direct/pedido-cliente
 *
 * Performance: ~30-70ms (vs ~150-300ms Eloquent)
 */
class PedidoClienteController extends Controller
{
    /**
     * Consultar pedido de cliente
     *
     * GET /api/direct/pedido-cliente?npedidocli={numero}&serie={serie}
     */
    public function show(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $validated = $request->validate([
                'npedidocli' => 'required|integer',
                'serie' => 'required|integer',
            ]);

            // OPTIMIZACIÓN: Query en 2 pasos para evitar JOINs costosos en pedidos inexistentes
            // Paso 1: Verificar existencia RÁPIDO (sin JOINs, solo campos necesarios)
            // NOTA: Sin índice en (npedidocli, idseriepedidocli_central) hace FULL TABLE SCAN
            // Recomendación: CREATE INDEX IDX_PEDIDO_NUMERO_SERIE
            $exists = DB::connection('oracle')
                ->table('pedidocli_central')
                ->select('idpedidocli_central', 'idcliente', 'idalmacen', 'estado',
                        'observaciones', 'solicitafactura', 'facturado', 'npedidocli',
                        'idseriepedidocli_central')
                ->selectRaw("TO_CHAR(fpedido, 'YYYY-MM-DD') as fpedido,
                            TO_CHAR(fprevista, 'YYYY-MM-DD') as fprevista,
                            TO_CHAR(fservido, 'YYYY-MM-DD') as fservido")
                ->where('npedidocli', $validated['npedidocli'])
                ->where('idseriepedidocli_central', $validated['serie'])
                ->whereNull('fbaja')
                ->limit(1)  // Optimización: detener búsqueda después del primer resultado
                ->first();

            if (!$exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pedido no encontrado'
                ], 404);
            }

            // Paso 2: SOLO si existe, obtener datos relacionados usando PK (RÁPIDO)
            $cliente = DB::connection('oracle')
                ->table('cliente_cent')
                ->select('idcliente', 'nombre', 'apellidos', 'email', 'cif')
                ->where('idcliente', $exists->idcliente)
                ->first();

            $almacen = DB::connection('oracle')
                ->table('almacen')
                ->select('idalmacen', 'descripcion')
                ->where('idalmacen', $exists->idalmacen)
                ->first();

            // Combinar resultados
            $pedido = (object)[
                'idpedidocli_central' => $exists->idpedidocli_central,
                'npedidocli' => $exists->npedidocli,
                'idseriepedidocli_central' => $exists->idseriepedidocli_central,
                'fpedido' => $exists->fpedido,
                'fprevista' => $exists->fprevista,
                'fservido' => $exists->fservido,
                'estado' => $exists->estado,
                'idcliente' => $exists->idcliente,
                'cliente_nombre' => $cliente->nombre ?? null,
                'cliente_apellidos' => $cliente->apellidos ?? null,
                'cliente_email' => $cliente->email ?? null,
                'cliente_cif' => $cliente->cif ?? null,
                'idalmacen' => $exists->idalmacen,
                'almacen_nombre' => $almacen->descripcion ?? null,
                'observaciones' => $exists->observaciones,
                'solicitafactura' => $exists->solicitafactura,
                'facturado' => $exists->facturado,
            ];

            // Consultar líneas del pedido usando Query Builder
            $lineas = DB::connection('oracle')
                ->table('lpedidocli_central as l')
                ->leftJoin('articulo as a', 'l.idarticulo', '=', 'a.idarticulo')
                ->select('l.idlpedidocli_central', 'l.idarticulo',
                        'a.codigo as articulo_codigo',
                        'a.descripcion as articulo_descripcion',
                        'l.cantidad', 'l.precio', 'l.descuento')
                ->where('l.idpedidocli_central', $pedido->idpedidocli_central)
                ->whereNull('l.fbaja')
                ->orderBy('l.idlpedidocli_central')
                ->get()
                ->toArray();

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO SQL DIRECTO PedidoClienteController::show: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'data' => [
                    'idpedidocli_central' => $pedido->idpedidocli_central,
                    'npedidocli' => $pedido->npedidocli,
                    'idseriepedidocli_central' => $pedido->idseriepedidocli_central,
                    'fpedido' => $pedido->fpedido,
                    'fprevista' => $pedido->fprevista,
                    'fservido' => $pedido->fservido,
                    'estado' => $pedido->estado,
                    'idcliente' => $pedido->idcliente,
                    'cliente' => $pedido->idcliente ? [
                        'idcliente' => $pedido->idcliente,
                        'nombre' => $pedido->cliente_nombre,
                        'apellidos' => $pedido->cliente_apellidos,
                        'email' => $pedido->cliente_email,
                        'cif' => $pedido->cliente_cif,
                    ] : null,
                    'idalmacen' => $pedido->idalmacen,
                    'almacen' => $pedido->idalmacen ? [
                        'idalmacen' => $pedido->idalmacen,
                        'nombre' => $pedido->almacen_nombre,
                    ] : null,
                    'observaciones' => $pedido->observaciones,
                    'solicitafactura' => $pedido->solicitafactura,
                    'facturado' => $pedido->facturado,
                    'lineas' => array_map(function($linea) {
                        return [
                            'idlpedidocli_central' => $linea->idlpedidocli_central,
                            'idarticulo' => $linea->idarticulo,
                            'articulo' => $linea->idarticulo ? [
                                'codigo' => $linea->articulo_codigo,
                                'descripcion' => $linea->articulo_descripcion,
                            ] : null,
                            'cantidad' => $linea->cantidad,
                            'precio' => $linea->precio,
                            'descuento' => $linea->descuento,
                        ];
                    }, $lineas),
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error en PedidoClienteController::show (SQL Directo)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al consultar pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo pedido de cliente
     *
     * POST /api/direct/pedido-cliente
     */
    public function store(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        DB::connection('oracle')->beginTransaction();

        try {
            $validated = $request->validate([
                'idcliente' => 'required|integer',
                'idalmacen' => 'required|integer',
                'idseriepedidocli_central' => 'required|integer',
                'fpedido' => 'nullable|date',
                'observaciones' => 'nullable|string',
                'solicitafactura' => 'nullable|boolean',
                'lineas' => 'required|array|min:1',
                'lineas.*.idarticulo' => 'required|integer',
                'lineas.*.cantidad' => 'required|numeric|min:0',
                'lineas.*.precio' => 'required|numeric|min:0',
                'lineas.*.descuento' => 'nullable|numeric|min:0|max:100',
            ]);

            // Obtener siguiente ID del pedido
            $nextIdPedido = DB::connection('oracle')->selectOne("SELECT SEQ_PEDIDOCLI_CENTRAL.NEXTVAL as nextval FROM DUAL");
            $idpedidocli = $nextIdPedido->nextval;

            // Obtener siguiente número de pedido
            $nextNumPedido = DB::connection('oracle')->selectOne("SELECT NVL(MAX(npedidocli), 0) + 1 as nextnum FROM pedidocli_central WHERE idseriepedidocli_central = :serie", [
                'serie' => $validated['idseriepedidocli_central']
            ]);
            $npedidocli = $nextNumPedido->nextnum;

            // Insertar cabecera del pedido
            $sqlPedido = "
                INSERT INTO pedidocli_central (
                    idpedidocli_central, npedidocli, idcliente, idalmacen,
                    idseriepedidocli_central, fpedido, estado, observaciones,
                    solicitafactura, facturado, fcreacion, fmodificacion
                ) VALUES (
                    :idpedidocli, :npedidocli, :idcliente, :idalmacen,
                    :serie, :fpedido, :estado, :observaciones,
                    :solicitafactura, :facturado, SYSDATE, SYSDATE
                )
            ";

            DB::connection('oracle')->insert($sqlPedido, [
                'idpedidocli' => $idpedidocli,
                'npedidocli' => $npedidocli,
                'idcliente' => $validated['idcliente'],
                'idalmacen' => $validated['idalmacen'],
                'serie' => $validated['idseriepedidocli_central'],
                'fpedido' => $validated['fpedido'] ?? date('Y-m-d'),
                'estado' => 1, // Pendiente
                'observaciones' => $validated['observaciones'] ?? null,
                'solicitafactura' => $validated['solicitafactura'] ?? 0,
                'facturado' => 0,
            ]);

            // Insertar líneas del pedido
            foreach ($validated['lineas'] as $lineaData) {
                $nextIdLinea = DB::connection('oracle')->selectOne("SELECT SEQ_LPEDIDOCLI_CENTRAL.NEXTVAL as nextval FROM DUAL");

                $sqlLinea = "
                    INSERT INTO lpedidocli_central (
                        idlpedidocli_central, idpedidocli_central, idarticulo,
                        cantidad, precio, descuento, fcreacion, fmodificacion
                    ) VALUES (
                        :idlinea, :idpedidocli, :idarticulo,
                        :cantidad, :precio, :descuento, SYSDATE, SYSDATE
                    )
                ";

                DB::connection('oracle')->insert($sqlLinea, [
                    'idlinea' => $nextIdLinea->nextval,
                    'idpedidocli' => $idpedidocli,
                    'idarticulo' => $lineaData['idarticulo'],
                    'cantidad' => $lineaData['cantidad'],
                    'precio' => $lineaData['precio'],
                    'descuento' => $lineaData['descuento'] ?? 0,
                ]);
            }

            DB::connection('oracle')->commit();

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO SQL DIRECTO PedidoClienteController::store: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'message' => 'Pedido creado correctamente',
                'data' => [
                    'idpedidocli_central' => $idpedidocli,
                    'npedidocli' => $npedidocli,
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
            Log::error('Error en PedidoClienteController::store (SQL Directo)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
