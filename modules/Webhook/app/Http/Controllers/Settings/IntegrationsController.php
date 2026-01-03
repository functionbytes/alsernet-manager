<?php

namespace Modules\Webhook\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Webhook\Http\Requests\Managers\Settings\StoreIntegrationRequest;
use Modules\Webhook\Http\Requests\Managers\Settings\UpdateIntegrationRequest;
use Modules\Webhook\Models\WebhookIntegration;

class IntegrationsController extends Controller
{
    /**
     * Display a listing of integrations.
     */
    public function index(Request $request)
    {
        $integrations = WebhookIntegration::query()
            ->withCount('subscriptions')
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->input('plan'), fn ($q, $plan) => $q->where('plan', $plan))
            ->latest()
            ->paginate(20);

        return view('webhook::settings.integrations.index', compact('integrations'));
    }

    /**
     * Show the form for creating a new integration.
     */
    public function create()
    {
        return view('webhook::settings.integrations.create');
    }

    /**
     * Store a newly created integration.
     */
    public function store(StoreIntegrationRequest $request)
    {
        $integration = WebhookIntegration::create($request->validated());

        return redirect()
            ->route('manager.settings.webhooks.integrations.show', $integration->uid)
            ->with('success', 'Integration created successfully.');
    }

    /**
     * Display the specified integration.
     */
    public function show(string $uid)
    {
        $integration = WebhookIntegration::where('uid', $uid)
            ->with(['subscriptions'])
            ->firstOrFail();

        return view('webhook::settings.integrations.show', compact('integration'));
    }

    /**
     * Show the form for editing the specified integration.
     */
    public function edit(string $uid)
    {
        $integration = WebhookIntegration::where('uid', $uid)->firstOrFail();

        return view('webhook::settings.integrations.edit', compact('integration'));
    }

    /**
     * Update the specified integration.
     */
    public function update(UpdateIntegrationRequest $request, string $uid)
    {
        $integration = WebhookIntegration::where('uid', $uid)->firstOrFail();
        $integration->update($request->validated());

        return redirect()
            ->route('manager.settings.webhooks.integrations.show', $integration->uid)
            ->with('success', 'Integration updated successfully.');
    }

    /**
     * Remove the specified integration.
     */
    public function destroy(string $uid)
    {
        $integration = WebhookIntegration::where('uid', $uid)->firstOrFail();
        $integration->delete();

        return redirect()
            ->route('manager.settings.webhooks.integrations.index')
            ->with('success', 'Integration deleted successfully.');
    }

    /**
     * Activate an integration.
     */
    public function activate(string $uid)
    {
        $integration = WebhookIntegration::where('uid', $uid)->firstOrFail();
        $integration->update(['status' => 'active']);

        return back()->with('success', 'Integration activated successfully.');
    }

    /**
     * Suspend an integration.
     */
    public function suspend(string $uid)
    {
        $integration = WebhookIntegration::where('uid', $uid)->firstOrFail();
        $integration->update(['status' => 'suspended']);

        return back()->with('success', 'Integration suspended successfully.');
    }

    /**
     * Disable an integration.
     */
    public function disable(string $uid)
    {
        $integration = WebhookIntegration::where('uid', $uid)->firstOrFail();
        $integration->update(['status' => 'disabled']);

        return back()->with('success', 'Integration disabled successfully.');
    }
}
