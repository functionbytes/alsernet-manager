<?php

namespace Modules\Erp\Http\Controllers\Direct;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * VERSIÓN SQL DIRECTO - GESTIÓN DE ARTÍCULOS
 *
 * Endpoints compatibles con API Gestión ERP
 * Base URL: /api/direct/articulo
 *
 * Performance: ~10-25ms (vs ~80-120ms Eloquent)
 */
class ArticuloController extends Controller
{
    /**
     * Obtener todos los artículos con filtros, búsqueda y paginación
     *
     * GET /api/direct/articulo
     * GET /api/direct/articulo?per_page=50&page=1
     * GET /api/direct/articulo?search=texto&sort_by=codigo&sort_order=asc
     * GET /api/direct/articulo?estado=1&idmarca=5&idmodelo=10
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
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 50);
            $perPage = min($perPage, 500);

            $search = $request->input('search');
            $estado = $request->input('estado');
            $idmarca = $request->input('idmarca');
            $idmodelo = $request->input('idmodelo');
            $idsubfamilia = $request->input('idsubfamilia');
            $sortBy = $request->input('sort_by', 'a.idarticulo');
            $sortOrder = strtolower($request->input('sort_order', 'asc'));

            // Validar sort_order
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'asc';
            }

            // Campos permitidos para ordenar
            $sortByAllowed = ['a.idarticulo', 'a.codigo', 'a.descripcion', 'a.preciomedio', 'a.estado', 'a.codbar'];
            if (!in_array($sortBy, $sortByAllowed)) {
                $sortBy = 'a.idarticulo';
            }

            $offset = ($page - 1) * $perPage;

            // Construir query base
            $baseQuery = function () {
                return DB::connection('oracle')
                    ->table('articulo as a')
                    ->leftJoin('marca as m', 'a.idmarca', '=', 'm.idmarca')
                    ->leftJoin('modelo as mo', 'a.idmodelo', '=', 'mo.idmodelo')
                    ->leftJoin('subfamilia_cl as sf', 'a.idsubfamilia', '=', 'sf.idsubfamilia_cl')
                    ->whereNull('a.fbaja');
            };

            // Aplicar filtros
            $applyFilters = function ($query) use ($search, $estado, $idmarca, $idmodelo, $idsubfamilia) {
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('a.codigo', 'LIKE', "%{$search}%")
                          ->orWhere('a.codbar', 'LIKE', "%{$search}%")
                          ->orWhere('a.descripcion', 'LIKE', "%{$search}%");
                    });
                }

                if ($estado !== null) {
                    $query->where('a.estado', $estado);
                }

                if ($idmarca) {
                    $query->where('a.idmarca', $idmarca);
                }

                if ($idmodelo) {
                    $query->where('a.idmodelo', $idmodelo);
                }

                if ($idsubfamilia) {
                    $query->where('a.idsubfamilia', $idsubfamilia);
                }

                return $query;
            };

            // Obtener total
            $totalQuery = $baseQuery();
            $totalQuery = $applyFilters($totalQuery);
            $total = $totalQuery->count();

            // Obtener datos paginados
            $query = $baseQuery();
            $query = $applyFilters($query);

            $articulos = $query
                ->select(
                    'a.idarticulo',
                    'a.codigo',
                    'a.codbar',
                    'a.descripcion',
                    'a.estado',
                    'a.preciomedio',
                    'a.precioultcompra',
                    'a.idmarca',
                    'm.descripcion as marca_descripcion',
                    'a.idmodelo',
                    'mo.descripcion as modelo_descripcion',
                    'a.idsubfamilia',
                    'sf.descripcion as subfamilia_descripcion',
                    'a.rutaimagen',
                    'a.observaciones'
                )
                ->orderBy($sortBy, $sortOrder)
                ->offset($offset)
                ->limit($perPage)
                ->get();

            $totalPages = ceil($total / $perPage);
            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO SQL DIRECTO ArticuloController::index: " . round($totalTime * 1000, 2) . "ms ===");

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
                        'marca' => $articulo->idmarca ? [
                            'idmarca' => $articulo->idmarca,
                            'descripcion' => $articulo->marca_descripcion
                        ] : null,
                        'idmodelo' => $articulo->idmodelo,
                        'modelo' => $articulo->idmodelo ? [
                            'idmodelo' => $articulo->idmodelo,
                            'descripcion' => $articulo->modelo_descripcion
                        ] : null,
                        'idsubfamilia' => $articulo->idsubfamilia,
                        'subfamilia' => $articulo->idsubfamilia ? [
                            'idsubfamilia' => $articulo->idsubfamilia,
                            'descripcion' => $articulo->subfamilia_descripcion
                        ] : null,
                        'rutaimagen' => $articulo->rutaimagen,
                        'observaciones' => $articulo->observaciones,
                    ];
                })->toArray(),
                'pagination' => [
                    'total' => $total,
                    'count' => $articulos->count(),
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'has_more' => $page < $totalPages,
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
            Log::error('Error en ArticuloController::index (SQL Directo)', [
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
     * GET /api/direct/articulo/{idarticulo}
     */
    public function show($idarticulo, Request $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            // Usar Query Builder para aplicar automáticamente prefix_schema
            $articulo = DB::connection('oracle')
                ->table('articulo as a')
                ->leftJoin('marca as m', 'a.idmarca', '=', 'm.idmarca')
                ->leftJoin('modelo as mo', 'a.idmodelo', '=', 'mo.idmodelo')
                ->leftJoin('subfamilia_cl as sf', 'a.idsubfamilia', '=', 'sf.idsubfamilia_cl')
                ->select(
                    'a.idarticulo',
                    'a.codigo',
                    'a.codbar',
                    'a.descripcion',
                    'a.estado',
                    'a.preciomedio',
                    'a.precioultcompra',
                    'a.idmarca',
                    'm.descripcion as marca_descripcion',
                    'a.idmodelo',
                    'mo.descripcion as modelo_descripcion',
                    'a.idsubfamilia',
                    'sf.descripcion as subfamilia_descripcion',
                    'a.rutaimagen',
                    'a.observaciones'
                )
                ->where('a.idarticulo', $idarticulo)
                ->whereNull('a.fbaja')
                ->first();

            if (!$articulo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Artículo no encontrado'
                ], 404);
            }

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO SQL DIRECTO ArticuloController::show: " . round($totalTime * 1000, 2) . "ms ===");

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
                    'marca' => $articulo->idmarca ? [
                        'idmarca' => $articulo->idmarca,
                        'descripcion' => $articulo->marca_descripcion
                    ] : null,
                    'idmodelo' => $articulo->idmodelo,
                    'modelo' => $articulo->idmodelo ? [
                        'idmodelo' => $articulo->idmodelo,
                        'descripcion' => $articulo->modelo_descripcion
                    ] : null,
                    'idsubfamilia' => $articulo->idsubfamilia,
                    'subfamilia' => $articulo->idsubfamilia ? [
                        'idsubfamilia' => $articulo->idsubfamilia,
                        'descripcion' => $articulo->subfamilia_descripcion
                    ] : null,
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
            Log::error('Error en ArticuloController::show (SQL Directo)', [
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
