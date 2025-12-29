<?php

namespace App\Http\Controllers\Managers\Settings\Suppliers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Supplier\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SupplierCategoriesController extends Controller
{
    /**
     * Display supplier categories management page
     */
    public function index(string $supplierUid): View
    {
        $supplier = Supplier::where('uid', $supplierUid)->firstOrFail();
        $pageTitle = "Categorías: {$supplier->name}";
        $breadcrumb = 'Configuración / Proveedores / Categorías';

        // Get assigned categories with pivot data
        $assignedCategories = $supplier->categories()
            ->withPivot(['uid', 'is_active', 'priority', 'metadata', 'created_at'])
            ->orderByPivot('priority', 'desc')
            ->orderByPivot('created_at', 'desc')
            ->get();

        // Get available categories (not yet assigned)
        $assignedCategoryIds = $assignedCategories->pluck('id')->toArray();
        $availableCategories = Category::where('available', true)
            ->whereNotIn('id', $assignedCategoryIds)
            ->orderBy('title')
            ->get();

        // Get stats
        $stats = [
            'total_assigned' => $assignedCategories->count(),
            'active' => $assignedCategories->where('pivot.is_active', true)->count(),
            'inactive' => $assignedCategories->where('pivot.is_active', false)->count(),
            'total_available' => Category::where('available', true)->count(),
        ];

        return view('managers.views.settings.suppliers.categories.index', compact(
            'supplier',
            'assignedCategories',
            'availableCategories',
            'stats',
            'pageTitle',
            'breadcrumb'
        ));
    }

    /**
     * Assign a category to supplier
     */
    public function store(Request $request, string $supplierUid)
    {
        try {
            $request->validate([
                'category_id' => 'required|exists:categories,id',
                'priority' => 'nullable|integer|min:0|max:100',
            ]);

            $supplier = Supplier::where('uid', $supplierUid)->firstOrFail();

            // Check if already assigned
            if ($supplier->categories()->where('category_id', $request->category_id)->exists()) {
                return back()->with('error', 'Esta categoría ya está asignada a este proveedor');
            }

            // Attach category with pivot data
            $supplier->categories()->attach($request->category_id, [
                'uid' => \Illuminate\Support\Str::ulid(),
                'is_active' => true,
                'priority' => $request->priority ?? 0,
                'metadata' => null,
            ]);

            return back()->with('success', 'Categoría asignada exitosamente');

        } catch (\Exception $e) {
            Log::error('Error assigning category to supplier: '.$e->getMessage());

            return back()->with('error', 'Error al asignar la categoría');
        }
    }

    /**
     * Update category assignment (priority, metadata)
     */
    public function update(Request $request, string $supplierUid, int $categoryId)
    {
        try {
            $request->validate([
                'priority' => 'nullable|integer|min:0|max:100',
            ]);

            $supplier = Supplier::where('uid', $supplierUid)->firstOrFail();

            // Update pivot data
            $supplier->categories()->updateExistingPivot($categoryId, [
                'priority' => $request->priority ?? 0,
            ]);

            return back()->with('success', 'Prioridad actualizada exitosamente');

        } catch (\Exception $e) {
            Log::error('Error updating supplier category: '.$e->getMessage());

            return back()->with('error', 'Error al actualizar la categoría');
        }
    }

    /**
     * Toggle category active status
     */
    public function toggle(string $supplierUid, int $categoryId)
    {
        try {
            $supplier = Supplier::where('uid', $supplierUid)->firstOrFail();
            $category = $supplier->categories()->where('category_id', $categoryId)->firstOrFail();

            $newStatus = ! $category->pivot->is_active;

            $supplier->categories()->updateExistingPivot($categoryId, [
                'is_active' => $newStatus,
            ]);

            return back()->with('success', $newStatus
                ? 'Categoría activada exitosamente'
                : 'Categoría desactivada exitosamente');

        } catch (\Exception $e) {
            Log::error('Error toggling supplier category: '.$e->getMessage());

            return back()->with('error', 'Error al cambiar el estado de la categoría');
        }
    }

    /**
     * Remove category from supplier
     */
    public function destroy(string $supplierUid, int $categoryId)
    {
        try {
            $supplier = Supplier::where('uid', $supplierUid)->firstOrFail();

            // Check if category has prompts
            $hasPrompts = $supplier->prompts()
                ->where('category_id', $categoryId)
                ->exists();

            if ($hasPrompts) {
                return back()->with('error', 'No se puede eliminar esta categoría porque tiene prompts asociados');
            }

            $supplier->categories()->detach($categoryId);

            return back()->with('success', 'Categoría eliminada exitosamente');

        } catch (\Exception $e) {
            Log::error('Error removing category from supplier: '.$e->getMessage());

            return back()->with('error', 'Error al eliminar la categoría');
        }
    }
}
