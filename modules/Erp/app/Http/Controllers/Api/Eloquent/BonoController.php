<?php

namespace Modules\Erp\Http\Controllers\Eloquent;

use App\Http\Controllers\Controller;
use Modules\Erp\Models\V2\Oracle\Promocion\BonoPromocion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * VERSIÓN ELOQUENT - GESTIÓN DE BONOS DE PROMOCIÓN
 *
 * Endpoints compatibles con API Gestión ERP
 * Base URL: /api/eloquent/bono
 */
class BonoController extends Controller
{
    /**
     * Consultar datos de un bono
     *
     * GET /api/eloquent/bono/{idbono}?codigo_verificacion={codigo}&importe_venta={importe}&origen={origen}
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

            $bono = BonoPromocion::with('tbono_promocion', 'catalogo_consumo')
                ->find($idbono);

            if (!$bono) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bono no encontrado'
                ], 404);
            }

            // Validar código de verificación si se proporciona
            if ($request->filled('codigo_verificacion')) {
                if ($bono->codigo_verificacion !== $request->codigo_verificacion) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El codigo de verificacion no es correcto'
                    ], 400);
                }
            }

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
            Log::info("=== TIEMPO ELOQUENT BonoController::show: " . round($totalTime * 1000, 2) . "ms ===");

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
                    'fecha' => $bono->fecha?->format('Y-m-d'),
                    'fvalidez_desde' => $bono->fvalidez_desde?->format('Y-m-d'),
                    'fvalidez_hasta' => $bono->fvalidez_hasta?->format('Y-m-d'),
                    'codigo_verificacion' => $bono->codigo_verificacion,
                    'idcatalogo_consumo' => $bono->idcatalogo_consumo,
                    'idcatalogo_web_consumo' => $bono->catalogo_consumo ? $bono->catalogo_consumo->idcatalogo : null,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error en BonoController::show (Eloquent)', [
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
     * PUT /api/eloquent/bono/{idbono}?origen={origen}
     */
    public function update(Request $request, $idbono): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $validated = $request->validate([
                'operacion' => 'required|in:0,1,2', // 0=anular, 1=recargar, 2=consumir
                'codigo_verificacion' => 'nullable|string',
                'importe_venta' => 'nullable|numeric',
                'importe_inicial_tarjeta_regalo' => 'nullable|numeric',
                'origen' => 'nullable|in:web,gestion',
            ]);

            $bono = BonoPromocion::find($idbono);

            if (!$bono) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bono no encontrado'
                ], 404);
            }

            $operacion = (int) $validated['operacion'];

            // Validaciones según operación
            switch ($operacion) {
                case 0: // Anular
                    if ($bono->estado == 2) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No se permite anular un bono ya consumido'
                        ], 400);
                    }
                    if ($bono->estado == 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No se permite anular un bono ya anulado'
                        ], 400);
                    }
                    $bono->estado = 0;
                    break;

                case 1: // Recargar
                    if ($bono->tipo != 4) { // 4 = tarjeta regalo
                        return response()->json([
                            'success' => false,
                            'message' => 'No se permite cargar un bono que no sea de tipo tarjeta regalo'
                        ], 400);
                    }
                    if ($bono->estado != 1) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No se permite cargar una tarjeta regalo no activa'
                        ], 400);
                    }
                    // Recargar importe
                    if ($request->filled('importe_inicial_tarjeta_regalo')) {
                        $bono->importe += $request->importe_inicial_tarjeta_regalo;
                    }
                    break;

                case 2: // Consumir
                    if ($bono->estado != 1) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No se permite consumir un bono que no se encuentre activo'
                        ], 400);
                    }

                    // Validar código de verificación
                    if ($request->filled('codigo_verificacion')) {
                        if ($bono->codigo_verificacion !== $request->codigo_verificacion) {
                            return response()->json([
                                'success' => false,
                                'message' => 'El codigo de verificacion no es correcto'
                            ], 400);
                        }
                    }

                    // Validar importe mínimo
                    if ($request->filled('importe_venta')) {
                        if ($bono->importeminimoventa && $request->importe_venta < $bono->importeminimoventa) {
                            return response()->json([
                                'success' => false,
                                'message' => 'El bono no cumple con el importe de venta minimo'
                            ], 400);
                        }
                    }

                    $bono->estado = 2;
                    $bono->fconsumo = now();
                    break;
            }

            $bono->save();

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO ELOQUENT BonoController::update: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'message' => 'Bono actualizado correctamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error en BonoController::update (Eloquent)', [
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
        if ($bono->fvalidez_hasta && $bono->fvalidez_hasta < now()) {
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
