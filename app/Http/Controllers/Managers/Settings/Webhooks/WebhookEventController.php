<?php

namespace App\Http\Controllers\Managers\Settings\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\Webhook\ProcessWebhookEventJob;
use App\Models\Webhook\WebhookEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebhookEventController extends Controller
{
    public function index(Request $request): View
    {
        $pageTitle = 'Eventos Webhook';
        $breadcrumb = 'Configuración / Webhooks / Eventos';

        $query = WebhookEvent::with('integration')
            ->orderByDesc('received_at');

        if ($request->filled('event_key')) {
            $query->where('event_key', $request->event_key);
        }

        if ($request->filled('integration_id')) {
            $query->where('integration_id', $request->integration_id);
        }

        $events = $query->paginate(25);

        return view('managers.views.settings.webhooks.events.index', compact(
            'events',
            'pageTitle',
            'breadcrumb'
        ));
    }

    public function show(WebhookEvent $event): View
    {
        $pageTitle = 'Detalle de Evento - '.$event->uid;
        $breadcrumb = 'Configuración / Webhooks / Eventos / Detalle';

        $event->load(['integration', 'deliveries']);

        return view('managers.views.settings.webhooks.events.show', compact(
            'event',
            'pageTitle',
            'breadcrumb'
        ));
    }

    public function replay(WebhookEvent $event): JsonResponse
    {
        ProcessWebhookEventJob::dispatch($event->id)
            ->onQueue('webhooks');

        return response()->json([
            'success' => true,
            'message' => 'Evento programado para reprocesamiento',
        ]);
    }
}
