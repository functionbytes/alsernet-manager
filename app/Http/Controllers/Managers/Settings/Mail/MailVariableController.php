<?php

namespace App\Http\Controllers\Managers\Settings\Mail;

use App\Http\Controllers\Controller;
use App\Models\Lang;
use App\Models\Mail\MailVariable;
use Illuminate\Http\Request;

class MailVariableController extends Controller
{
    /**
     * Display a listing of email variables
     */
    public function index()
    {
        $pageTitle = 'Gestionar Variables de Email';
        $breadcrumb = 'Configuración / Correos / Variables';

        $variables = MailVariable::with('translations')
            ->orderBy('module')
            ->orderBy('category')
            ->orderBy('key')
            ->paginate(20);

        return view('managers.views.settings.mailers.variables.index', compact(
            'pageTitle',
            'breadcrumb',
            'variables',
        ));
    }

    /**
     * Show the form for creating a new variable
     */
    public function create()
    {
        $pageTitle = 'Crear Variable de Email';
        $breadcrumb = 'Configuración / Correos / Variables / Crear';

        $langs = Lang::all();
        $categories = [
            'system' => 'Sistema',
            'customer' => 'Cliente',
            'order' => 'Pedido',
            'document' => 'Documento',
            'general' => 'General',
        ];
        $modules = [
            'core' => 'Core',
            'documents' => 'Documentos',
            'orders' => 'Pedidos',
        ];

        return view('managers.views.settings.mailers.variables.create', compact(
            'pageTitle',
            'breadcrumb',
            'langs',
            'categories',
            'modules',
        ));
    }

    /**
     * Store a newly created variable in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:mail_variables,key|regex:/^[A-Z_]+$/',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|in:system,customer,order,document,general',
            'module' => 'required|string|in:core,documents,orders',
            'is_system' => 'boolean',
            'is_enabled' => 'boolean',
            'translations' => 'required|array',
            'translations.*.lang_id' => 'required|exists:langs,id',
            'translations.*.name' => 'required|string|max:255',
            'translations.*.description' => 'nullable|string',
        ]);

        $translations = $validated['translations'];
        unset($validated['translations']);

        $variable = MailVariable::create($validated);

        // Create translations
        foreach ($translations as $translation) {
            $variable->translations()->create($translation);
        }

        return redirect()
            ->route('manager.settings.mailers.variables.index')
            ->with('success', "Variable '{$variable->key}' creada exitosamente.");
    }

    /**
     * Show the form for editing a variable
     */
    public function edit(MailVariable $variable)
    {
        $pageTitle = 'Editar Variable de Email';
        $breadcrumb = 'Configuración / Correos / Variables / Editar';

        $langs = Lang::all();
        $categories = [
            'system' => 'Sistema',
            'customer' => 'Cliente',
            'order' => 'Pedido',
            'document' => 'Documento',
            'general' => 'General',
        ];
        $modules = [
            'core' => 'Core',
            'documents' => 'Documentos',
            'orders' => 'Pedidos',
        ];

        $variable->load('translations');

        return view('managers.views.settings.mailers.variables.edit', compact(
            'pageTitle',
            'breadcrumb',
            'variable',
            'langs',
            'categories',
            'modules',
        ));
    }

    /**
     * Update the specified variable in storage
     */
    public function update(Request $request, MailVariable $variable)
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:mail_variables,key,'.$variable->id.'|regex:/^[A-Z_]+$/',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|in:system,customer,order,document,general',
            'module' => 'required|string|in:core,documents,orders',
            'is_system' => 'boolean',
            'is_enabled' => 'boolean',
            'translations' => 'required|array',
            'translations.*.lang_id' => 'required|exists:langs,id',
            'translations.*.name' => 'required|string|max:255',
            'translations.*.description' => 'nullable|string',
        ]);

        $translations = $validated['translations'];
        unset($validated['translations']);

        $variable->update($validated);

        // Update or create translations
        foreach ($translations as $translation) {
            $variable->translations()->updateOrCreate(
                ['lang_id' => $translation['lang_id']],
                [
                    'name' => $translation['name'],
                    'description' => $translation['description'] ?? null,
                ]
            );
        }

        return redirect()
            ->route('manager.settings.mailers.variables.index')
            ->with('success', "Variable '{$variable->key}' actualizada exitosamente.");
    }

    /**
     * Remove the specified variable from storage
     */
    public function destroy(MailVariable $variable)
    {
        if ($variable->is_system) {
            return redirect()
                ->route('manager.settings.mailers.variables.index')
                ->with('error', 'No se puede eliminar variables del sistema.');
        }

        $key = $variable->key;
        $variable->delete();

        return redirect()
            ->route('manager.settings.mailers.variables.index')
            ->with('success', "Variable '{$key}' eliminada exitosamente.");
    }

    /**
     * Toggle variable status
     */
    public function toggleStatus(MailVariable $variable)
    {
        $variable->update(['is_enabled' => ! $variable->is_enabled]);

        $status = $variable->is_enabled ? 'habilitada' : 'deshabilitada';

        return response()->json([
            'success' => true,
            'message' => "Variable '{$variable->key}' {$status}.",
            'is_enabled' => $variable->is_enabled,
        ]);
    }

    /**
     * Get variables for a specific module and category
     */
    public function getByModule(Request $request)
    {
        $module = $request->get('module', 'core');
        $category = $request->get('category');

        $query = MailVariable::where('module', $module)
            ->where('is_enabled', true);

        if ($category) {
            $query->where('category', $category);
        }

        $variables = $query->orderBy('category')->orderBy('key')->get();

        return response()->json($variables);
    }

    /**
     * Get variables grouped by category for template editor
     */
    public function getGroupedByCategory(Request $request)
    {
        $module = $request->get('module', 'core');

        $variables = MailVariable::where('module', $module)
            ->orWhere('module', 'core')
            ->where('is_enabled', true)
            ->orderBy('category')
            ->orderBy('key')
            ->get();

        $grouped = [];
        foreach ($variables as $variable) {
            if (!isset($grouped[$variable->category])) {
                $grouped[$variable->category] = [];
            }

            $grouped[$variable->category][] = [
                'key' => $variable->key,
                'name' => $variable->name,
                'description' => $variable->description,
            ];
        }

        return response()->json($grouped);
    }

    /**
     * Get all available variable keys for validation
     */
    public function getAvailableKeys(Request $request)
    {
        $module = $request->get('module', 'core');

        $keys = MailVariable::where('module', $module)
            ->orWhere('module', 'core')
            ->where('is_enabled', true)
            ->pluck('key')
            ->toArray();

        return response()->json([
            'keys' => $keys,
            'count' => count($keys),
        ]);
    }
}
