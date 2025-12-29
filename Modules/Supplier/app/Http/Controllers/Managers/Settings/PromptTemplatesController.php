<?php

namespace Modules\Supplier\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Modules\Supplier\Entities\Supplier;
use Modules\Supplier\Entities\SupplierPrompt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PromptTemplatesController extends Controller
{
    /**
     * Display prompt templates library
     */
    public function index(Request $request): View
    {
        $pageTitle = 'Biblioteca de Plantillas de Prompts';
        $breadcrumb = 'Configuración / Proveedores / Plantillas';

        // Get filter parameters
        $category = $request->get('category');
        $contentType = $request->get('content_type');

        // Build query
        $query = SupplierPrompt::templates()
            ->orderBy('template_category')
            ->orderBy('content_type')
            ->orderBy('label');

        if ($category) {
            $query->where('template_category', $category);
        }

        if ($contentType) {
            $query->where('content_type', $contentType);
        }

        $templates = $query->get();

        // Get available categories and content types for filters
        $categories = SupplierPrompt::templates()
            ->distinct()
            ->pluck('template_category')
            ->filter()
            ->sort()
            ->values();

        $contentTypes = SupplierPrompt::templates()
            ->distinct()
            ->pluck('content_type')
            ->filter()
            ->sort()
            ->values();

        // Get stats
        $stats = [
            'total_templates' => SupplierPrompt::templates()->count(),
            'total_cloned' => SupplierPrompt::notTemplates()->whereNotNull('cloned_from_template_id')->count(),
            'categories_count' => SupplierPrompt::templates()->distinct()->count('template_category'),
        ];

        // Get suppliers and categories for clone modal
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $allCategories = Category::where('available', true)->orderBy('title')->get();

        return view('managers.views.settings.suppliers.templates.index', compact(
            'templates',
            'categories',
            'contentTypes',
            'stats',
            'suppliers',
            'allCategories',
            'pageTitle',
            'breadcrumb'
        ));
    }

    /**
     * Show create template form
     */
    public function create(): View
    {
        $pageTitle = 'Crear Plantilla de Prompt';
        $breadcrumb = 'Configuración / Proveedores / Plantillas / Crear';

        return view('managers.views.settings.suppliers.templates.create', compact(
            'pageTitle',
            'breadcrumb'
        ));
    }

    /**
     * Store new template
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'label' => 'required|string|max:255',
                'template_category' => 'nullable|string|max:50',
                'content_type' => 'required|string|in:description,short_description,title,seo_title,seo_description,seo_keywords,metadata,features,specifications,benefits',
                'prompt_template' => 'required|string',
                'output_language' => 'required|string|max:10',
                'tone' => 'required|string|max:50',
                'seo_focus' => 'nullable|boolean',
                'priority' => 'nullable|integer|min:0|max:100',
                'notes' => 'nullable|string',
            ]);

            SupplierPrompt::create([
                'uid' => \Illuminate\Support\Str::ulid(),
                'label' => $request->label,
                'template_category' => $request->template_category,
                'content_type' => $request->content_type,
                'prompt_template' => $request->prompt_template,
                'scope' => 'global', // Templates are always global
                'output_language' => $request->output_language,
                'tone' => $request->tone,
                'seo_focus' => $request->seo_focus ?? false,
                'priority' => $request->priority ?? 0,
                'version' => 1,
                'is_active' => true,
                'is_template' => true, // Mark as template
                'notes' => $request->notes,
            ]);

            return redirect()
                ->route('manager.settings.suppliers.templates.index')
                ->with('success', 'Plantilla creada exitosamente');

        } catch (\Exception $e) {
            Log::error('Error creating template: '.$e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Error al crear la plantilla');
        }
    }

    /**
     * Show edit template form
     */
    public function edit(string $templateUid): View
    {
        $template = SupplierPrompt::where('uid', $templateUid)
            ->where('is_template', true)
            ->firstOrFail();

        $pageTitle = 'Editar Plantilla: '.$template->label;
        $breadcrumb = 'Configuración / Proveedores / Plantillas / Editar';

        return view('managers.views.settings.suppliers.templates.edit', compact(
            'template',
            'pageTitle',
            'breadcrumb'
        ));
    }

    /**
     * Update template
     */
    public function update(Request $request, string $templateUid)
    {
        try {
            $request->validate([
                'label' => 'required|string|max:255',
                'template_category' => 'nullable|string|max:50',
                'content_type' => 'required|string|in:description,short_description,title,seo_title,seo_description,seo_keywords,metadata,features,specifications,benefits',
                'prompt_template' => 'required|string',
                'output_language' => 'required|string|max:10',
                'tone' => 'required|string|max:50',
                'seo_focus' => 'nullable|boolean',
                'priority' => 'nullable|integer|min:0|max:100',
                'notes' => 'nullable|string',
            ]);

            $template = SupplierPrompt::where('uid', $templateUid)
                ->where('is_template', true)
                ->firstOrFail();

            $template->update([
                'label' => $request->label,
                'template_category' => $request->template_category,
                'content_type' => $request->content_type,
                'prompt_template' => $request->prompt_template,
                'output_language' => $request->output_language,
                'tone' => $request->tone,
                'seo_focus' => $request->seo_focus ?? false,
                'priority' => $request->priority ?? 0,
                'notes' => $request->notes,
            ]);

            return redirect()
                ->route('manager.settings.suppliers.templates.index')
                ->with('success', 'Plantilla actualizada exitosamente');

        } catch (\Exception $e) {
            Log::error('Error updating template: '.$e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la plantilla');
        }
    }

    /**
     * Delete template
     */
    public function destroy(string $templateUid)
    {
        try {
            $template = SupplierPrompt::where('uid', $templateUid)
                ->where('is_template', true)
                ->firstOrFail();

            // Check if template has cloned prompts
            $clonedCount = $template->clonedPrompts()->count();

            if ($clonedCount > 0) {
                return back()->with('error', "No se puede eliminar esta plantilla porque tiene {$clonedCount} prompt(s) creado(s) desde ella");
            }

            $template->delete();

            return back()->with('success', 'Plantilla eliminada exitosamente');

        } catch (\Exception $e) {
            Log::error('Error deleting template: '.$e->getMessage());

            return back()->with('error', 'Error al eliminar la plantilla');
        }
    }

    /**
     * Clone a template into a new prompt
     */
    public function clone(Request $request, string $templateUid)
    {
        try {
            $request->validate([
                'supplier_id' => 'nullable|integer|exists:suppliers,id',
                'category_id' => 'nullable|integer|exists:categories,id',
                'scope' => 'required|string|in:global,supplier,category,supplier_category,source',
                'label' => 'required|string|max:255',
                'priority' => 'nullable|integer|min:0|max:100',
            ]);

            $template = SupplierPrompt::where('uid', $templateUid)
                ->where('is_template', true)
                ->firstOrFail();

            // Prepare overrides
            $overrides = [
                'label' => $request->label,
                'scope' => $request->scope,
                'priority' => $request->priority ?? 0,
            ];

            // Add supplier_id and category_id based on scope
            if (in_array($request->scope, ['supplier', 'supplier_category'])) {
                if (! $request->supplier_id) {
                    return back()->with('error', 'El proveedor es requerido para este scope');
                }
                $overrides['supplier_id'] = $request->supplier_id;
            }

            if (in_array($request->scope, ['category', 'supplier_category'])) {
                if (! $request->category_id) {
                    return back()->with('error', 'La categoría es requerida para este scope');
                }
                $overrides['category_id'] = $request->category_id;
            }

            // Clone template
            $newPrompt = $template->cloneFromTemplate($overrides);

            return back()->with('success', "Prompt creado exitosamente desde plantilla: {$newPrompt->label}");

        } catch (\Exception $e) {
            Log::error('Error cloning template: '.$e->getMessage());

            return back()->with('error', 'Error al clonar la plantilla');
        }
    }
}
