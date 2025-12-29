<?php

namespace App\Http\Controllers\Managers\Settings\Webhooks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\Webhooks\StoreSubscriptionRequest;
use App\Http\Requests\Managers\Settings\Webhooks\UpdateSubscriptionRequest;
use App\Jobs\Webhook\DeliverWebhookJob;
use App\Models\Webhook\WebhookIntegration;
use App\Models\Webhook\WebhookSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WebhookSubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $pageTitle = 'Suscripciones Webhook';
        $breadcrumb = 'Configuración / Webhooks / Suscripciones';

        $search = $request->get('search');
        $integrationId = $request->get('integration_id');
        $isActive = $request->get('is_active');

        $query = WebhookSubscription::query()
            ->with('integration')
            ->withCount('deliveries');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%");
            });
        }

        if ($integrationId) {
            $query->where('integration_id', $integrationId);
        }

        if ($isActive !== null && $isActive !== '') {
            $query->where('is_active', (bool) $isActive);
        }

        $subscriptions = $query->orderByDesc('created_at')->paginate(15);
        $integrations = WebhookIntegration::where('status', 'active')->get();

        return view('managers.views.settings.webhooks.subscriptions.index', compact(
            'subscriptions',
            'integrations',
            'pageTitle',
            'breadcrumb',
            'search',
            'integrationId',
            'isActive'
        ));
    }

    public function create(): View
    {
        $pageTitle = 'Nueva Suscripción Webhook';
        $breadcrumb = 'Configuración / Webhooks / Suscripciones / Crear';

        $integrations = WebhookIntegration::where('status', 'active')->get();
        $eventCatalog = \App\Models\Webhook\WebhookEventCatalog::where('is_active', true)->get();

        return view('managers.views.settings.webhooks.subscriptions.create', compact(
            'pageTitle',
            'breadcrumb',
            'integrations',
            'eventCatalog'
        ));
    }

    public function store(StoreSubscriptionRequest $request): JsonResponse
    {
        try {
            $subscription = WebhookSubscription::create([
                'integration_id' => $request->integration_id,
                'name' => $request->name,
                'url' => $request->url,
                'is_active' => $request->boolean('is_active', true),
                'subscribed_events' => $request->subscribed_events ?? [],
                'auth_type' => $request->auth_type ?? 'none',
                'auth_config' => $request->auth_config ?? [],
                'signing_secret' => Str::random(64),
                'timeout_ms' => $request->timeout_ms ?? 10000,
                'max_attempts' => $request->max_attempts ?? 6,
                'backoff_policy' => [60, 300, 900, 3600, 21600, 86400],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Suscripción creada exitosamente',
                'redirect' => route('manager.settings.webhooks.subscriptions.edit', $subscription),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la suscripción: '.$e->getMessage(),
            ], 500);
        }
    }

    public function edit(WebhookSubscription $subscription): View
    {
        $pageTitle = 'Editar Suscripción - '.$subscription->name;
        $breadcrumb = 'Configuración / Webhooks / Suscripciones / Editar';

        $subscription->load(['integration', 'deliveries' => function ($query) {
            $query->latest()->limit(20);
        }]);

        $integrations = WebhookIntegration::where('status', 'active')->get();
        $eventCatalog = \App\Models\Webhook\WebhookEventCatalog::where('is_active', true)->get();

        $stats = [
            'total_deliveries' => $subscription->deliveries()->count(),
            'success' => $subscription->deliveries()->where('status', 'success')->count(),
            'failed' => $subscription->deliveries()->where('status', 'failed')->count(),
            'dead' => $subscription->deliveries()->where('status', 'dead')->count(),
        ];

        return view('managers.views.settings.webhooks.subscriptions.edit', compact(
            'subscription',
            'integrations',
            'eventCatalog',
            'stats',
            'pageTitle',
            'breadcrumb'
        ));
    }

    public function update(UpdateSubscriptionRequest $request, WebhookSubscription $subscription): JsonResponse
    {
        try {
            $subscription->update([
                'integration_id' => $request->integration_id,
                'name' => $request->name,
                'url' => $request->url,
                'is_active' => $request->boolean('is_active'),
                'subscribed_events' => $request->subscribed_events ?? [],
                'auth_type' => $request->auth_type,
                'auth_config' => $request->auth_config ?? [],
                'timeout_ms' => $request->timeout_ms,
                'max_attempts' => $request->max_attempts,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Suscripción actualizada correctamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: '.$e->getMessage(),
            ], 500);
        }
    }

    public function toggle(WebhookSubscription $subscription): JsonResponse
    {
        $subscription->update(['is_active' => ! $subscription->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'is_active' => $subscription->is_active,
        ]);
    }

    public function test(WebhookSubscription $subscription): JsonResponse
    {
        $testPayload = [
            'test' => true,
            'message' => 'Test webhook from WebhookHub',
            'timestamp' => now()->toIso8601String(),
            'subscription' => [
                'id' => $subscription->uid,
                'name' => $subscription->name,
            ],
        ];

        DeliverWebhookJob::dispatch($subscription->id, $testPayload)
            ->onQueue('deliveries');

        return response()->json([
            'success' => true,
            'message' => 'Webhook de prueba enviado. Revisa los logs de entrega.',
        ]);
    }

    public function rotateSecret(WebhookSubscription $subscription): JsonResponse
    {
        $newSecret = Str::random(64);
        $subscription->update(['signing_secret' => $newSecret]);

        return response()->json([
            'success' => true,
            'message' => 'Signing secret rotado correctamente',
            'new_secret' => $newSecret,
        ]);
    }

    public function destroy(WebhookSubscription $subscription): JsonResponse
    {
        try {
            $subscription->delete();

            return response()->json([
                'success' => true,
                'message' => 'Suscripción eliminada correctamente',
                'redirect' => route('manager.settings.webhooks.subscriptions.index'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: '.$e->getMessage(),
            ], 500);
        }
    }
}
