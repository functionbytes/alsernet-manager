<?php

namespace Modules\Erp\Http\Controllers\Direct;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * VERSIÓN SQL DIRECTO - GESTIÓN DE BONOS DE PROMOCIÓN
 *
 * Endpoints compatibles con API Gestión ERP
 * Base URL: /api/direct/bono
 *
 * Performance: ~20-40ms (vs ~80-120ms Eloquent)
 */
class BonoController extends Controller
{
    /**
     * Consultar datos de un bono
     *
     * GET /api/direct/bono/{idbono}?codigo_verificacion={codigo}&importe_venta={importe}&origen={origen}
     */
    public function show(Request $request, $idbono): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $validated = $request->validate([
                'codigo_verificacion' => 'nullable|string',
                'importe_venta' => 'nullable|numeric',
                'origen' => 'nullable|in:web,gestion',
            ]);

            // Usar Query Builder para aplicar automáticamente prefix_schema
            $bono = DB::connection('oracle')
                ->table('bono_promocion')
                ->selectRaw("idbono_promocion, idtbono_promocion, tipo, estado, importe,
                            importeminimoventa, idalmacen_creacion,
                            TO_CHAR(fecha, 'YYYY-MM-DD') as fecha,
                            TO_CHAR(fvalidez_desde, 'YYYY-MM-DD') as fvalidez_desde,
                            TO_CHAR(fvalidez_hasta, 'YYYY-MM-DD') as fvalidez_hasta,
                            idcatalogo_consumo")
                ->where('idbono_promocion', $idbono)
                ->first();

            if (!$bono) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bono no encontrado'
                ], 404);
            }

            // Nota: La tabla BONO_PROMOCION no tiene campo codigo_verificacion

            // Validar importe mínimo de venta si se proporciona
            if ($request->filled('importe_venta')) {
                if ($bono->importeminimoventa && $request->importe_venta < $bono->importeminimoventa) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El bono no cumple con el importe de venta minimo'
                    ], 400);
                }
            }

            // Determinar estado extendido
            $estado_extendido = $this->getEstadoExtendido($bono);

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO SQL DIRECTO BonoController::show: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'data' => [
                    'idbono_promocion' => $bono->idbono_promocion,
                    'idtbono_promocion' => $bono->idtbono_promocion,
                    'tipo' => $bono->tipo,
                    'descripcion_tipo' => $this->getDescripcionTipo($bono->tipo),
                    'estado_extendido' => $estado_extendido['codigo'],
                    'descripcion_estado_extendido' => $estado_extendido['descripcion'],
                    'importe' => $bono->importe,
                    'importeminimoventa' => $bono->importeminimoventa,
                    'idalmacen_creacion' => $bono->idalmacen_creacion,
                    'fecha' => $bono->fecha,
                    'fvalidez_desde' => $bono->fvalidez_desde,
                    'fvalidez_hasta' => $bono->fvalidez_hasta,
                    'idcatalogo_consumo' => $bono->idcatalogo_consumo,
                    'idcatalogo_web_consumo' => $bono->idcatalogo_consumo,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error en BonoController::show (SQL Directo)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al consultar bono',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar bono (anular, recargar, consumir)
     *
     * PUT /api/direct/bono/{idbono}?origen={origen}
     */
    public function update(Request $request, $idbono): JsonResponse
    {
        $startTime = microtime(true);

        DB::connection('oracle')->beginTransaction();

        try {
            $validated = $request->validate([
                'operacion' => 'required|in:0,1,2', // 0=anular, 1=recargar, 2=consumir
                'codigo_verificacion' => 'nullable|string',
                'importe_venta' => 'nullable|numeric',
                'importe_inicial_tarjeta_regalo' => 'nullable|numeric',
                'origen' => 'nullable|in:web,gestion',
            ]);

            // Obtener bono actual usando Query Builder
            $bono = DB::connection('oracle')
                ->table('bono_promocion')
                ->select('idbono_promocion', 'tipo', 'estado', 'importe',
                        'importeminimoventa', 'fvalidez_hasta')
                ->where('idbono_promocion', $idbono)
                ->first();

            if (!$bono) {
                DB::connection('oracle')->rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Bono no encontrado'
                ], 404);
            }

            $operacion = (int) $validated['operacion'];

            // Validaciones y actualización según operación
            switch ($operacion) {
                case 0: // Anular
                    if ($bono->estado == 2) {
                        DB::connection('oracle')->rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'No se permite anular un bono ya consumido'
                        ], 400);
                    }
                    if ($bono->estado == 0) {
                        DB::connection('oracle')->rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'No se permite anular un bono ya anulado'
                        ], 400);
                    }

                    DB::connection('oracle')
                        ->table('bono_promocion')
                        ->where('idbono_promocion', $idbono)
                        ->update(['estado' => 0]);
                    break;

                case 1: // Recargar
                    if ($bono->tipo != 4) { // 4 = tarjeta regalo
                        DB::connection('oracle')->rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'No se permite cargar un bono que no sea de tipo tarjeta regalo'
                        ], 400);
                    }
                    if ($bono->estado != 1) {
                        DB::connection('oracle')->rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'No se permite cargar una tarjeta regalo no activa'
                        ], 400);
                    }

                    // Recargar importe
                    if ($request->filled('importe_inicial_tarjeta_regalo')) {
                        $nuevoImporte = $bono->importe + $request->importe_inicial_tarjeta_regalo;
                        DB::connection('oracle')
                            ->table('bono_promocion')
                            ->where('idbono_promocion', $idbono)
                            ->update(['importe' => $nuevoImporte]);
                    }
                    break;

                case 2: // Consumir
                    if ($bono->estado != 1) {
                        DB::connection('oracle')->rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'No se permite consumir un bono que no se encuentre activo'
                        ], 400);
                    }

                    // Nota: codigo_verificacion no existe en esta tabla

                    // Validar importe mínimo
                    if ($request->filled('importe_venta')) {
                        if ($bono->importeminimoventa && $request->importe_venta < $bono->importeminimoventa) {
                            DB::connection('oracle')->rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => 'El bono no cumple con el importe de venta minimo'
                            ], 400);
                        }
                    }

                    DB::connection('oracle')
                        ->table('bono_promocion')
                        ->where('idbono_promocion', $idbono)
                        ->update([
                            'estado' => 2,
                            'fconsumo' => DB::raw('SYSDATE')
                        ]);
                    break;
            }

            DB::connection('oracle')->commit();

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO SQL DIRECTO BonoController::update: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'message' => 'Bono actualizado correctamente'
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
            Log::error('Error en BonoController::update (SQL Directo)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar bono',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estado extendido del bono
     */
    private function getEstadoExtendido($bono): array
    {
        // Estado 0 = anulado, 1 = activo, 2 = consumido
        if ($bono->estado == 0) {
            return ['codigo' => 0, 'descripcion' => 'anulado'];
        }

        if ($bono->estado == 2) {
            return ['codigo' => 2, 'descripcion' => 'consumido'];
        }

        // Verificar caducidad
        if ($bono->fvalidez_hasta && strtotime($bono->fvalidez_hasta) < time()) {
            return ['codigo' => 3, 'descripcion' => 'caducado'];
        }

        return ['codigo' => 1, 'descripcion' => 'activo'];
    }

    /**
     * Obtener descripción del tipo de bono
     */
    private function getDescripcionTipo($tipo): string
    {
        $tipos = [
            1 => 'Bono fidelización',
            2 => 'Bono promoción por catálogo',
            3 => 'Bono promoción',
            4 => 'Tarjeta regalo',
        ];

        return $tipos[$tipo] ?? 'Desconocido';
    }
}
