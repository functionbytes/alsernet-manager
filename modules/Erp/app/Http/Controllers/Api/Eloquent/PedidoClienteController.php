<?php

namespace Modules\Erp\Http\Controllers\Eloquent;

use App\Http\Controllers\Controller;
use Modules\Erp\Models\V2\Oracle\Pedido\PedidocliCentral;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * VERSIÓN ELOQUENT - GESTIÓN DE PEDIDOS DE CLIENTE
 *
 * Endpoints compatibles con API Gestión ERP
 * Base URL: /api/eloquent/pedido-cliente
 */
class PedidoClienteController extends Controller
{
    /**
     * Consultar pedido de cliente
     *
     * GET /api/eloquent/pedido-cliente?npedidocli={numero}&serie={serie}
     */
    public function show(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $validated = $request->validate([
                'npedidocli' => 'required|integer',
                'serie' => 'required|integer',
            ]);

            $pedido = PedidocliCentral::with([
                'cliente:idcliente,nombre,apellidos,email,cif',
                'almacen:idalmacen,nombre',
                'lineas.articulo:idarticulo,codigo,descripcion'
            ])
            ->where('npedidocli', $validated['npedidocli'])
            ->where('idseriepedidocli_central', $validated['serie'])
            ->first();

            if (!$pedido) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pedido no encontrado'
                ], 404);
            }

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO ELOQUENT PedidoClienteController::show: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'data' => [
                    'idpedidocli_central' => $pedido->idpedidocli_central,
                    'npedidocli' => $pedido->npedidocli,
                    'idseriepedidocli_central' => $pedido->idseriepedidocli_central,
                    'fpedido' => $pedido->fpedido?->format('Y-m-d'),
                    'fprevista' => $pedido->fprevista?->format('Y-m-d'),
                    'fservido' => $pedido->fservido?->format('Y-m-d'),
                    'estado' => $pedido->estado,
                    'idcliente' => $pedido->idcliente,
                    'cliente' => $pedido->cliente ? [
                        'idcliente' => $pedido->cliente->idcliente,
                        'nombre' => $pedido->cliente->nombre,
                        'apellidos' => $pedido->cliente->apellidos,
                        'email' => $pedido->cliente->email,
                        'cif' => $pedido->cliente->cif,
                    ] : null,
                    'idalmacen' => $pedido->idalmacen,
                    'almacen' => $pedido->almacen ? [
                        'idalmacen' => $pedido->almacen->idalmacen,
                        'nombre' => $pedido->almacen->nombre,
                    ] : null,
                    'observaciones' => $pedido->observaciones,
                    'solicitafactura' => $pedido->solicitafactura,
                    'facturado' => $pedido->facturado,
                    'lineas' => $pedido->lineas->map(function($linea) {
                        return [
                            'idlpedidocli_central' => $linea->idlpedidocli_central,
                            'idarticulo' => $linea->idarticulo,
                            'articulo' => $linea->articulo ? [
                                'codigo' => $linea->articulo->codigo,
                                'descripcion' => $linea->articulo->descripcion,
                            ] : null,
                            'cantidad' => $linea->cantidad,
                            'precio' => $linea->precio,
                            'descuento' => $linea->descuento,
                        ];
                    }),
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error en PedidoClienteController::show (Eloquent)', [
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
     * POST /api/eloquent/pedido-cliente
     */
    public function store(Request $request): JsonResponse
    {
        $startTime = microtime(true);

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

            $pedido = new PedidocliCentral();
            $pedido->idcliente = $validated['idcliente'];
            $pedido->idalmacen = $validated['idalmacen'];
            $pedido->idseriepedidocli_central = $validated['idseriepedidocli_central'];
            $pedido->fpedido = $validated['fpedido'] ?? now();
            $pedido->estado = 1; // Pendiente
            $pedido->observaciones = $validated['observaciones'] ?? null;
            $pedido->solicitafactura = $validated['solicitafactura'] ?? 0;
            $pedido->facturado = 0;
            $pedido->save();

            // Crear líneas del pedido
            foreach ($validated['lineas'] as $lineaData) {
                $pedido->lineas()->create([
                    'idarticulo' => $lineaData['idarticulo'],
                    'cantidad' => $lineaData['cantidad'],
                    'precio' => $lineaData['precio'],
                    'descuento' => $lineaData['descuento'] ?? 0,
                ]);
            }

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO ELOQUENT PedidoClienteController::store: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'message' => 'Pedido creado correctamente',
                'data' => [
                    'idpedidocli_central' => $pedido->idpedidocli_central,
                    'npedidocli' => $pedido->npedidocli,
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error en PedidoClienteController::store (Eloquent)', [
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
