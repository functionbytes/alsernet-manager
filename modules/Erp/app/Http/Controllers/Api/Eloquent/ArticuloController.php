<?php

namespace Modules\Erp\Http\Controllers\Eloquent;

use App\Http\Controllers\Controller;
use Modules\Erp\Models\V2\Oracle\Articulo\Articulo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * VERSIÓN ELOQUENT - GESTIÓN DE ARTÍCULOS
 *
 * Endpoints compatibles con API Gestión ERP
 * Base URL: /api/eloquent/articulo
 */
class ArticuloController extends Controller
{
    /**
     * Obtener todos los artículos con filtros, búsqueda y paginación
     *
     * GET /api/eloquent/articulo
     * GET /api/eloquent/articulo?per_page=50&page=1
     * GET /api/eloquent/articulo?search=texto&sort_by=codigo&sort_order=asc
     * GET /api/eloquent/articulo?estado=1&idmarca=5&idmodelo=10
     *
     * Parámetros:
     * - per_page: Registros por página (default 50, máximo 500)
     * - page: Número de página (default 1)
     * - search: Buscar por código, codbar o descripción
     * - estado: Filtrar por estado (0 o 1)
     * - idmarca: Filtrar por marca
     * - idmodelo: Filtrar por modelo
     * - idsubfamilia: Filtrar por subfamilia
     * - sort_by: Campo para ordenar (idarticulo, codigo, descripcion, preciomedio, estado)
     * - sort_order: Orden (asc o desc, default asc)
     */
    public function index(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $perPage = $request->input('per_page', 50);
            $perPage = min($perPage, 500);

            $search = $request->input('search');
            $estado = $request->input('estado');
            $idmarca = $request->input('idmarca');
            $idmodelo = $request->input('idmodelo');
            $idsubfamilia = $request->input('idsubfamilia');
            $sortBy = $request->input('sort_by', 'idarticulo');
            $sortOrder = strtolower($request->input('sort_order', 'asc'));

            // Validar sort_order
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'asc';
            }

            // Campos permitidos para ordenar
            $sortByAllowed = ['idarticulo', 'codigo', 'descripcion', 'preciomedio', 'estado', 'codbar'];
            if (!in_array($sortBy, $sortByAllowed)) {
                $sortBy = 'idarticulo';
            }

            // Construir query
            $query = Articulo::query();

            // Filtros
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('codigo', 'LIKE', "%{$search}%")
                      ->orWhere('codbar', 'LIKE', "%{$search}%")
                      ->orWhere('descripcion', 'LIKE', "%{$search}%");
                });
            }

            if ($estado !== null) {
                $query->where('estado', $estado);
            }

            if ($idmarca) {
                $query->where('idmarca', $idmarca);
            }

            if ($idmodelo) {
                $query->where('idmodelo', $idmodelo);
            }

            if ($idsubfamilia) {
                $query->where('idsubfamilia', $idsubfamilia);
            }

            // Ordenamiento
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $articulos = $query->paginate($perPage);

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO ELOQUENT ArticuloController::index: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'data' => $articulos->map(function ($articulo) {
                    return [
                        'idarticulo' => $articulo->idarticulo,
                        'codigo' => $articulo->codigo,
                        'codbar' => $articulo->codbar,
                        'descripcion' => $articulo->descripcion,
                        'estado' => $articulo->estado,
                        'preciomedio' => $articulo->preciomedio,
                        'precioultcompra' => $articulo->precioultcompra,
                        'idmarca' => $articulo->idmarca,
                        'idmodelo' => $articulo->idmodelo,
                        'idsubfamilia' => $articulo->idsubfamilia,
                        'rutaimagen' => $articulo->rutaimagen,
                        'observaciones' => $articulo->observaciones,
                    ];
                })->toArray(),
                'pagination' => [
                    'total' => $articulos->total(),
                    'count' => $articulos->count(),
                    'per_page' => $articulos->perPage(),
                    'current_page' => $articulos->currentPage(),
                    'total_pages' => $articulos->lastPage(),
                    'has_more' => $articulos->hasMorePages(),
                ],
                'filters_applied' => [
                    'search' => $search,
                    'estado' => $estado,
                    'idmarca' => $idmarca,
                    'idmodelo' => $idmodelo,
                    'idsubfamilia' => $idsubfamilia,
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ArticuloController::index (Eloquent)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al consultar artículos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Consultar artículo por ID
     *
     * GET /api/eloquent/articulo/{idarticulo}
     */
    public function show($idarticulo, Request $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $articulo = Articulo::where('idarticulo', $idarticulo)->first();

            if (!$articulo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Artículo no encontrado'
                ], 404);
            }

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO ELOQUENT ArticuloController::show: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'data' => [
                    'idarticulo' => $articulo->idarticulo,
                    'codigo' => $articulo->codigo,
                    'codbar' => $articulo->codbar,
                    'descripcion' => $articulo->descripcion,
                    'estado' => $articulo->estado,
                    'preciomedio' => $articulo->preciomedio,
                    'precioultcompra' => $articulo->precioultcompra,
                    'idmarca' => $articulo->idmarca,
                    'idmodelo' => $articulo->idmodelo,
                    'idsubfamilia' => $articulo->idsubfamilia,
                    'rutaimagen' => $articulo->rutaimagen,
                    'observaciones' => $articulo->observaciones,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error en ArticuloController::show (Eloquent)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al consultar artículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
