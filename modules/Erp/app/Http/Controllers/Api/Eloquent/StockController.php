<?php

namespace Modules\Erp\Http\Controllers\Eloquent;

use App\Http\Controllers\Controller;
use Modules\Erp\Models\V2\Oracle\Otros\StockCentralWeb;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * VERSIÓN ELOQUENT - GESTIÓN DE STOCK CENTRAL WEB
 *
 * Endpoints compatibles con API Gestión ERP
 * Base URL: /api/eloquent/stock-central-web
 */
class StockController extends Controller
{
    /**
     * Consultar stock libre (no reservado) de un artículo
     *
     * GET /api/eloquent/stock-central-web/{idarticulo}
     */
    public function show($idarticulo): JsonResponse
    {
        $startTime = microtime(true);

        try {

            $stock = StockCentralWeb::where('idarticulo', $idarticulo)->first();

            if (!$stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock no encontrado para este artículo'
                ], 404);
            }

            $totalTime = microtime(true) - $startTime;
            Log::info("=== TIEMPO ELOQUENT StockController::show: " . round($totalTime * 1000, 2) . "ms ===");

            return response()->json([
                'success' => true,
                'data' => [
                    'idarticulo' => $stock->idarticulo,
                    'unidades' => $stock->unidades
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en StockController::show (Eloquent)', [
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
