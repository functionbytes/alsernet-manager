<?php

namespace Modules\Mailer\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Lang;
use Illuminate\Http\Request;
use Modules\Mailer\Models\MailerEndpoint;
use Modules\Mailer\Models\MailerTemplate;

class MailerEndpointController extends Controller
{
    /**
     * Display list of email endpoints
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $source = $request->input('source');
        $status = $request->input('status');

        $query = MailerEndpoint::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if ($source) {
            $query->where('source', $source);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $endpoints = $query->orderByDesc('created_at')->paginate(15);
        $sources = MailerEndpoint::distinct('source')->pluck('source');
        $types = MailerEndpoint::distinct('type')->pluck('type');

        return view('mailer::endpoints.index', [
            'endpoints' => $endpoints,
            'sources' => $sources,
            'types' => $types,
            'search' => $search,
            'source' => $source,
            'status' => $status,
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $templates = MailerTemplate::where('is_enabled', true)->get();
        $languages = Lang::available()->get();

        return view('mailer::endpoints.create', [
            'templates' => $templates,
            'languages' => $languages,
        ]);
    }

    /**
     * Store endpoint
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:mailer_endpoints,slug',
            'source' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'mailer_template_id' => 'nullable|exists:mailer_templates,id',
            'lang_id' => 'nullable|exists:langs,id',
            'expected_variables' => 'nullable|array',
            'required_variables' => 'nullable|array',
            'variable_mappings' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $endpoint = MailerEndpoint::create($validated);

        return redirect()->route('manager.settings.mailers.endpoints.edit', $endpoint)
            ->with('success', 'Endpoint de correo creado exitosamente');
    }

    /**
     * Show edit form
     */
    public function edit(MailerEndpoint $emailEndpoint)
    {
        $templates = MailerTemplate::where('is_enabled', true)->get();
        $languages = Lang::available()->get();
        $logs = $emailEndpoint->logs()->latest()->paginate(10);

        return view('mailer::endpoints.edit', [
            'endpoint' => $emailEndpoint,
            'templates' => $templates,
            'languages' => $languages,
            'logs' => $logs,
            'successCount' => $emailEndpoint->successLogs()->count(),
            'failedCount' => $emailEndpoint->failedLogs()->count(),
        ]);
    }

    /**
     * Update endpoint
     */
    public function update(Request $request, MailerEndpoint $emailEndpoint)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:mailer_endpoints,slug,'.$emailEndpoint->id,
            'source' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'mailer_template_id' => 'nullable|exists:mailer_templates,id',
            'lang_id' => 'nullable|exists:langs,id',
            'expected_variables' => 'nullable|array',
            'required_variables' => 'nullable|array',
            'variable_mappings' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $emailEndpoint->update($validated);

        return redirect()->route('manager.settings.mailers.endpoints.edit', $emailEndpoint)
            ->with('success', 'Endpoint actualizado correctamente');
    }

    /**
     * Delete endpoint
     */
    public function destroy(MailerEndpoint $emailEndpoint)
    {
        $emailEndpoint->delete();

        return redirect()->route('manager.settings.mailers.endpoints.index')
            ->with('success', 'Endpoint eliminado correctamente');
    }

    /**
     * Regenerate API token
     */
    public function regenerateToken(MailerEndpoint $emailEndpoint)
    {
        $emailEndpoint->api_token = MailerEndpoint::generateToken();
        $emailEndpoint->save();

        return back()->with('success', 'Token regenerado correctamente');
    }

    /**
     * Get endpoint logs
     */
    public function logs(Request $request, MailerEndpoint $emailEndpoint)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status');
        $period = $request->input('period');

        $query = $emailEndpoint->logs();

        // Search by email
        if ($search) {
            $query->where('recipient_email', 'like', "%{$search}%");
        }

        // Filter by status
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        // Filter by period
        if ($period) {
            $date = match ($period) {
                '24h' => now()->subDay(),
                '7d' => now()->subDays(7),
                '30d' => now()->subDays(30),
                default => null,
            };

            if ($date) {
                $query->where('created_at', '>=', $date);
            }
        }

        $logs = $query->latest()->paginate(20);

        // Statistics
        $allLogs = $emailEndpoint->logs();
        $successCount = $allLogs->where('status', 'success')->count();
        $failedCount = $allLogs->where('status', 'failed')->count();
        $total = $allLogs->count();
        $successRate = $total > 0 ? round(($successCount / $total) * 100, 1) : 0;

        return view('mailer::endpoints.logs', [
            'endpoint' => $emailEndpoint,
            'logs' => $logs,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'period' => $period,
            'successCount' => $successCount,
            'failedCount' => $failedCount,
            'successRate' => $successRate,
        ]);
    }

    /**
     * Show comprehensive documentation for all endpoints
     */
    public function documentation(): \Illuminate\View\View
    {
        $endpoints = MailerEndpoint::with('template', 'language')
            ->orderByDesc('created_at')
            ->get();

        $appUrl = config('app.url');

        return view('mailer::endpoints.documentation', [
            'endpoints' => $endpoints,
            'appUrl' => $appUrl,
        ]);
    }
}
