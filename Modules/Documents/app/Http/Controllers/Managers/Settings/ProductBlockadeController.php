<?php

namespace Modules\Documents\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Documents\Entities\DocumentProductBlockade;
use Modules\Documents\Entities\DocumentType;

class ProductBlockadeController extends Controller
{
    /**
     * Display product blockades configuration page
     */
    public function index()
    {
        $lastSync = Setting::get('product_blockades_last_sync');
        $syncCount = Setting::get('product_blockades_sync_count', 0);
        $totalBlockades = DocumentProductBlockade::count();
        $currentLabels = Setting::get('product_blockade_labels', 'DNI,ESCOPETA,RIFLE,CORTA');

        // Get document types for blockade types
        $documentTypes = DocumentType::where('is_active', true)
            ->orderBy('label')
            ->get();

        // Get statistics by document type
        $blockadesByType = DocumentProductBlockade::selectRaw('document_type_id, COUNT(*) as count')
            ->groupBy('document_type_id')
            ->get()
            ->keyBy('document_type_id');

        // Get unique products count
        $uniqueProducts = DocumentProductBlockade::selectRaw('COUNT(DISTINCT COALESCE(product_id, product_attribute_id)) as count')
            ->value('count') ?? 0;

        // Get recent blockades with product details
        $recentBlockades = DocumentProductBlockade::with('documentType')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Add count to each document type
        foreach ($documentTypes as $type) {
            $type->blockades_count = $blockadesByType->get($type->id)->count ?? 0;
        }

        return view('documents::managers.settings.blockades.index', [
            'lastSync' => $lastSync ? \Carbon\Carbon::parse($lastSync)->diffForHumans() : 'Nunca',
            'syncCount' => (int) $syncCount,
            'totalBlockades' => $totalBlockades,
            'uniqueProducts' => $uniqueProducts,
            'currentLabels' => $currentLabels,
            'documentTypes' => $documentTypes,
            'recentBlockades' => $recentBlockades,
        ]);
    }

    /**
     * Sync product blockades from external MySQL database
     */
    public function sync(Request $request): JsonResponse
    {
        try {
            $fresh = $request->input('fresh', false);

            // Execute artisan command
            $exitCode = Artisan::call('migrate:product-blockades', [
                '--fresh' => $fresh,
            ]);

            $output = Artisan::output();

            // Save last sync info
            Setting::set('product_blockades_last_sync', now());
            Setting::set('product_blockades_sync_count', (int) Setting::get('product_blockades_sync_count', 0) + 1);

            return response()->json([
                'success' => $exitCode === 0,
                'message' => $exitCode === 0
                    ? 'Sincronización de bloqueos completada exitosamente'
                    : 'Sincronización completada con errores',
                'output' => $output,
                'last_sync' => now()->format('Y-m-d H:i:s'),
                'total_blockades' => DocumentProductBlockade::count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error syncing product blockades: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al sincronizar bloqueos: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get product blockades sync status
     */
    public function status(): JsonResponse
    {
        $lastSync = Setting::get('product_blockades_last_sync');
        $syncCount = Setting::get('product_blockades_sync_count', 0);
        $totalBlockades = DocumentProductBlockade::count();

        return response()->json([
            'success' => true,
            'last_sync' => $lastSync ? \Carbon\Carbon::parse($lastSync)->diffForHumans() : 'Nunca',
            'sync_count' => (int) $syncCount,
            'total_blockades' => $totalBlockades,
        ]);
    }

    /**
     * Create a new product blockade manually
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'source_id' => 'required|integer',
            'product_id' => 'nullable|integer|required_without:product_attribute_id',
            'product_attribute_id' => 'nullable|integer|required_without:product_id',
            'document_type_id' => 'required|integer|exists:document_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Check if already exists
            $exists = DocumentProductBlockade::where('source_id', $request->source_id)
                ->where('document_type_id', $request->document_type_id)
                ->when($request->product_id, fn ($q) => $q->where('product_id', $request->product_id))
                ->when($request->product_attribute_id, fn ($q) => $q->where('product_attribute_id', $request->product_attribute_id))
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este bloqueo ya existe para este producto',
                ], 409);
            }

            $blockade = DocumentProductBlockade::create([
                'source_id' => $request->source_id,
                'product_id' => $request->product_id,
                'product_attribute_id' => $request->product_attribute_id,
                'document_type_id' => $request->document_type_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bloqueo creado exitosamente',
                'blockade' => $blockade->load('documentType'),
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating product blockade: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el bloqueo: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store multiple product blockades at once
     */
    public function storeBulk(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'blockades' => 'required|array|min:1',
            'blockades.*.source_id' => 'required|integer',
            'blockades.*.product_id' => 'nullable|integer|required_without:blockades.*.product_attribute_id',
            'blockades.*.product_attribute_id' => 'nullable|integer|required_without:blockades.*.product_id',
            'blockades.*.document_type_id' => 'required|integer|exists:document_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $created = 0;
            $skipped = 0;
            $errors = [];

            foreach ($request->blockades as $index => $blockadeData) {
                // Check if blockade already exists
                $exists = DocumentProductBlockade::where('document_type_id', $blockadeData['document_type_id'])
                    ->where('source_id', $blockadeData['source_id'])
                    ->when($blockadeData['product_id'] ?? null, fn ($q) => $q->where('product_id', $blockadeData['product_id']))
                    ->when($blockadeData['product_attribute_id'] ?? null, fn ($q) => $q->where('product_attribute_id', $blockadeData['product_attribute_id']))
                    ->exists();

                if ($exists) {
                    $skipped++;
                    $errors[] = 'Fila '.($index + 1).': Bloqueo duplicado';

                    continue;
                }

                DocumentProductBlockade::create([
                    'source_id' => $blockadeData['source_id'],
                    'product_id' => $blockadeData['product_id'] ?? null,
                    'product_attribute_id' => $blockadeData['product_attribute_id'] ?? null,
                    'document_type_id' => $blockadeData['document_type_id'],
                ]);

                $created++;
            }

            $message = "{$created} bloqueo(s) creado(s) exitosamente";
            if ($skipped > 0) {
                $message .= ", {$skipped} omitido(s) por duplicado";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'created' => $created,
                'skipped' => $skipped,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating bulk blockades: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear los bloqueos: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save product blockade labels configuration
     */
    public function saveLabels(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'labels' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Etiquetas inválidas',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            Setting::set('product_blockade_labels', $request->labels);

            return response()->json([
                'success' => true,
                'message' => 'Etiquetas guardadas exitosamente',
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving blockade labels: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar las etiquetas: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a product blockade
     */
    public function destroy(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:document_product_blockades,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'ID inválido',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $blockade = DocumentProductBlockade::findOrFail($request->id);
            $blockade->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bloqueo eliminado exitosamente',
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting product blockade: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el bloqueo: '.$e->getMessage(),
            ], 500);
        }
    }
}
