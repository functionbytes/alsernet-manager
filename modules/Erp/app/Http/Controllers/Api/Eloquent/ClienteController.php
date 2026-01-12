<?php

namespace Modules\Erp\Http\Controllers\Eloquent;

use App\Http\Controllers\Controller;
use Modules\Erp\Models\V2\Oracle\Cliente\ClienteCent;
use Modules\Erp\Models\V2\Oracle\Cliente\ClientecatalogoCent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * VERSIÓN ELOQUENT - GESTIÓN DE CLIENTES
 *
 * Endpoints compatibles con API Gestión ERP
 * Base URL: /api/eloquent/clientes
 */
class ClienteController extends Controller
{
    /**
     * Buscar clientes por múltiples criterios
     *
     * GET /api/eloquent/clientes?{params}
     *
     * Parámetros opcionales:
     * - dni, apellidos, idcliente_gestion, idclienteweb, email, telefono1
     * - fnacimiento, faceptacion_lopd_desde, faceptacion_lopd_hasta
     * - fbaja_desde, fbaja_hasta
     */
    public function index(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            // Nota: Eliminadas relaciones 'catalogos' y 'cuotas' que no existen en el modelo
            $query = ClienteCent::query();

            // Filtros
            if ($request->filled('idcliente_gestion')) {
                $query->where('idcliente', $request->idcliente_gestion);
            }

            if ($request->filled('dni')) {
                $query->where('cif', $request->dni);
            }

            if ($request->filled('email')) {
                $query->where('email', $request->email);
            }

            if ($request->filled('apellidos')) {
                $query->where('apellidos', 'like', '%' . $request->apellidos . '%');
            }

            if ($request->filled('telefono1')) {
                // Buscar en tabla de teléfonos relacionada
                $query->whereHas('telefonos', function($q) use ($request) {
                    $q->where('telefono', $request->telefono1);
                });
            }

            if ($request->filled('fnacimiento')) {
                $query->whereDate('fnacimiento', $request->fnacimiento);
            }

            if ($request->filled('faceptacion_lopd_desde')) {
                $query->where('faceptacion_lopd', '>=', $request->faceptacion_lopd_desde);
            }

            if ($request->filled('faceptacion_lopd_hasta')) {
                $query->where('faceptacion_lopd', '<=', $request->faceptacion_lopd_hasta);
            }

            if ($request->filled('fbaja_desde')) {
                $query->where('fbaja', '>=', $request->fbaja_desde);
            }

            if ($request->filled('fbaja_hasta')) {
                $query->where('fbaja', '<=', $request->fbaja_hasta);
            }

            $clientes = $query->get();

            if ($clientes->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron clientes'
                ], 404);
            }

            $response = $clientes->map(function($cliente) {
                return $this->formatClienteResponse($cliente);
            });

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO ELOQUENT ClienteController::index: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'data' => $response,
                'total' => $clientes->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ClienteController::index (Eloquent)', [
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
     * POST /api/eloquent/clientes
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
                'cliente_idcatalogo' => 'nullable|string', // IDs separados por coma
            ]);

            // Validar LOPD
            if (empty($validated['cliente_faceptacion_lopd'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'ERROR 20404: No es posible insertar un cliente que no ha aceptado LOPD',
                    'code' => 20404
                ], 400);
            }

            // Validar catálogo
            if (empty($validated['cliente_idcatalogo'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'ERROR 20403: El cliente debe tener al menos un catálogo',
                    'code' => 20403
                ], 400);
            }

            // Buscar o crear cliente
            if (!empty($request->idcliente_gestion)) {
                $cliente = ClienteCent::find($request->idcliente_gestion);
            } else {
                $cliente = new ClienteCent();
            }

            // Actualizar datos
            $cliente->nombre = $request->cliente_nombre;
            $cliente->apellidos = $request->cliente_apellidos;
            $cliente->cif = $request->cliente_cif;
            $cliente->email = $request->cliente_email;
            $cliente->faceptacion_lopd = $request->cliente_faceptacion_lopd;
            $cliente->no_informacion_comercial_lopd = $request->cliente_no_info_comercial;
            $cliente->no_datos_a_terceros_lopd = $request->cliente_no_datos_a_terceros;

            // Campos opcionales
            if ($request->filled('cliente_percontacto')) {
                $cliente->percontacto = $request->cliente_percontacto;
            }
            if ($request->filled('cliente_observaciones')) {
                $cliente->observaciones = $request->cliente_observaciones;
            }
            if ($request->filled('cliente_idioma')) {
                $cliente->ididioma = $request->cliente_idioma;
            }
            if ($request->filled('cliente_genero')) {
                $cliente->genero = $request->cliente_genero;
            }
            if ($request->filled('cliente_fnacimiento')) {
                $cliente->fnacimiento = $request->cliente_fnacimiento;
            }

            $cliente->save();

            // Sincronizar catálogos
            if ($request->filled('cliente_idcatalogo')) {
                $catalogos = explode(',', $request->cliente_idcatalogo);
                foreach ($catalogos as $idcatalogo) {
                    ClientecatalogoCent::firstOrCreate([
                        'idcliente' => $cliente->idcliente,
                        'idcatalogo' => trim($idcatalogo),
                    ], [
                        'estado' => 1,
                        'fsuscripcion' => now()
                    ]);
                }
            }

            DB::connection('oracle')->commit();

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO ELOQUENT ClienteController::store: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'idcliente' => $cliente->idcliente
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
            Log::error('Error en ClienteController::store (Eloquent)', [
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
     * PUT /api/eloquent/clientes
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

            $cliente = ClienteCent::where('email', $request->cliente_email)->first();

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            $cliente->faceptacion_lopd = $request->cliente_faceptacion_lopd;
            $cliente->no_informacion_comercial_lopd = $request->cliente_no_info_comercial;
            $cliente->no_datos_a_terceros_lopd = $request->cliente_no_datos_a_terceros;
            $cliente->save();

            return response()->json([
                'success' => true,
                'message' => 'LOPD actualizado correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ClienteController::updateLopd (Eloquent)', [
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
     * Formatear respuesta de cliente según formato API original
     */
    private function formatClienteResponse($cliente): array
    {
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
            'fcreacion' => $cliente->fcreacion?->format('Y-m-d'),
            'fbaja' => $cliente->fbaja?->format('Y-m-d'),
            'faceptacion_lopd' => $cliente->faceptacion_lopd?->format('Y-m-d'),
            'no_informacion_comercial_lopd' => $cliente->no_informacion_comercial_lopd,
            'no_datos_a_terceros_lopd' => $cliente->no_datos_a_terceros_lopd,
            'tiene_interes_legitimo_lopd' => $cliente->tiene_interes_legitimo_lopd,
            // NOTA: catalogos y cuotas no están cargados (relaciones eliminadas por no existir en modelo)
            'cliente_catalogo' => [],
            'cliente_cuota' => [],
            'cantidad_albaranes' => 0, // TODO: Calcular desde albaranes
        ];
    }
}
