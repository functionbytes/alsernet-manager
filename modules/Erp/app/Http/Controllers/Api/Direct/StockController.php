<?php

namespace Modules\Erp\Http\Controllers\Direct;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * VERSIÓN SQL DIRECTO - GESTIÓN DE STOCK CENTRAL WEB
 *
 * Endpoints compatibles con API Gestión ERP
 * Base URL: /api/direct/stock-central-web
 *
 * Performance: ~15-30ms (vs ~80-120ms Eloquent)
 */
class StockController extends Controller
{
    /**
     * Consultar stock libre (no reservado) de un artículo
     *
     * GET /api/direct/stock-central-web/{idarticulo}
     */
    public function show($idarticulo): JsonResponse
    {
        $startTime = microtime(true);

        try {
            // Usar Query Builder para aplicar automáticamente prefix_schema
            $stock = DB::connection('oracle')
                ->table('stock_central_web')
                ->select('idarticulo', 'unidades')
                ->where('idarticulo', $idarticulo)
                ->first();

            if (!$stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock no encontrado para este artículo'
                ], 404);
            }

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO SQL DIRECTO StockController::show: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'data' => [
                    'idarticulo' => $stock->idarticulo,
                    'unidades' => $stock->unidades
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en StockController::show (SQL Directo)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al consultar stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
