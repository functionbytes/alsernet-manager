<?php

namespace App\Http\Controllers\Managers\Settings\Webhooks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\Webhooks\StoreIntegrationRequest;
use App\Http\Requests\Managers\Settings\Webhooks\UpdateIntegrationRequest;
use App\Models\Webhook\WebhookApiKey;
use App\Models\Webhook\WebhookIntegration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WebhookIntegrationController extends Controller
{
    public function index(Request $request): View
    {
        $pageTitle = 'Integraciones Webhook';
        $breadcrumb = 'Configuración / Webhooks / Integraciones';

        $search = $request->get('search');
        $status = $request->get('status');
        $plan = $request->get('plan');

        $query = WebhookIntegration::query()
            ->withCount(['events', 'subscriptions', 'deliveries']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('uid', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($plan) {
            $query->where('plan', $plan);
        }

        $integrations = $query->orderByDesc('created_at')->paginate(15);

        return view('managers.views.settings.webhooks.integrations.index', compact(
            'integrations',
            'pageTitle',
            'breadcrumb',
            'search',
            'status',
            'plan'
        ));
    }

    public function create(): View
    {
        $pageTitle = 'Nueva Integración Webhook';
        $breadcrumb = 'Configuración / Webhooks / Integraciones / Crear';

        return view('managers.views.settings.webhooks.integrations.create', compact(
            'pageTitle',
            'breadcrumb'
        ));
    }

    public function store(StoreIntegrationRequest $request): JsonResponse
    {
        try {
            $integration = WebhookIntegration::create([
                'name' => $request->name,
                'status' => 'active',
                'plan' => $request->plan ?? 'free',
                'daily_limit' => $request->daily_limit ?? 1000,
                'allowed_ips' => $request->allowed_ips ? explode(',', $request->allowed_ips) : null,
                'allowed_domains' => $request->allowed_domains ? explode(',', $request->allowed_domains) : null,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Integración creada exitosamente',
                'redirect' => route('manager.settings.webhooks.integrations.edit', $integration),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la integración: '.$e->getMessage(),
            ], 500);
        }
    }

    public function edit(WebhookIntegration $integration): View
    {
        $pageTitle = 'Editar Integración - '.$integration->name;
        $breadcrumb = 'Configuración / Webhooks / Integraciones / Editar';

        $integration->load(['apiKeys', 'events' => function ($query) {
            $query->latest()->limit(10);
        }]);

        $stats = [
            'total_events' => $integration->events()->count(),
            'total_deliveries' => $integration->deliveries()->count(),
            'success_deliveries' => $integration->deliveries()->where('status', 'success')->count(),
            'failed_deliveries' => $integration->deliveries()->where('status', 'failed')->count(),
            'active_subscriptions' => $integration->subscriptions()->where('is_active', true)->count(),
        ];

        return view('managers.views.settings.webhooks.integrations.edit', compact(
            'integration',
            'pageTitle',
            'breadcrumb',
            'stats'
        ));
    }

    public function update(UpdateIntegrationRequest $request, WebhookIntegration $integration): JsonResponse
    {
        try {
            $integration->update([
                'name' => $request->name,
                'status' => $request->status,
                'plan' => $request->plan,
                'daily_limit' => $request->daily_limit,
                'allowed_ips' => $request->allowed_ips ? explode(',', $request->allowed_ips) : null,
                'allowed_domains' => $request->allowed_domains ? explode(',', $request->allowed_domains) : null,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Integración actualizada correctamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: '.$e->getMessage(),
            ], 500);
        }
    }

    public function toggle(WebhookIntegration $integration): JsonResponse
    {
        $newStatus = $integration->status === 'active' ? 'disabled' : 'active';
        $integration->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'status' => $newStatus,
        ]);
    }

    public function destroy(WebhookIntegration $integration): JsonResponse
    {
        try {
            $integration->delete();

            return response()->json([
                'success' => true,
                'message' => 'Integración eliminada correctamente',
                'redirect' => route('manager.settings.webhooks.integrations.index'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: '.$e->getMessage(),
            ], 500);
        }
    }

    public function apiKeys(WebhookIntegration $integration): View
    {
        $pageTitle = 'API Keys - '.$integration->name;
        $breadcrumb = 'Configuración / Webhooks / Integraciones / API Keys';

        $apiKeys = $integration->apiKeys()->latest()->paginate(15);

        return view('managers.views.settings.webhooks.integrations.api-keys', compact(
            'integration',
            'apiKeys',
            'pageTitle',
            'breadcrumb'
        ));
    }

    public function storeApiKey(Request $request, WebhookIntegration $integration): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'permissions' => 'nullable|array',
            'rate_limit_per_minute' => 'nullable|integer|min:1|max:1000',
        ]);

        $rawSecret = Str::random(64);

        $apiKey = $integration->apiKeys()->create([
            'key' => 'whk_'.Str::random(40),
            'secret' => hash('sha256', $rawSecret),
            'name' => $request->name,
            'permissions' => $request->permissions ?? ['inbound', 'outbound'],
            'rate_limit_per_minute' => $request->rate_limit_per_minute ?? 60,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'API Key creada exitosamente',
            'api_key' => [
                'key' => $apiKey->key,
                'secret' => $rawSecret,
            ],
        ]);
    }

    public function revokeApiKey(WebhookApiKey $apiKey): JsonResponse
    {
        $apiKey->update(['revoked_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'API Key revocada correctamente',
        ]);
    }

    public function destroyApiKey(WebhookApiKey $apiKey): JsonResponse
    {
        $apiKey->delete();

        return response()->json([
            'success' => true,
            'message' => 'API Key eliminada correctamente',
        ]);
    }
}
