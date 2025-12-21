<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Document\StageEmailAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StageEmailActionController extends Controller
{
    /**
     * Display a listing of all stage email action configurations
     */
    public function index(): View
    {
        $configurations = StageEmailAction::ordered()->get()->groupBy('validation_stage');

        return view('managers.settings.stage-email-actions.index', [
            'configurations' => $configurations,
            'availableStages' => StageEmailAction::AVAILABLE_STAGES,
            'availableActions' => StageEmailAction::AVAILABLE_ACTIONS,
        ]);
    }

    /**
     * Show the form for editing configurations for a specific stage
     */
    public function edit(string $stage): View
    {
        if (! array_key_exists($stage, StageEmailAction::AVAILABLE_STAGES)) {
            abort(404, 'Validation stage not found');
        }

        $configurations = StageEmailAction::forStage($stage)->ordered()->get();

        // Ensure all actions have a configuration record
        $configuredActions = $configurations->pluck('email_action')->toArray();
        foreach (array_keys(StageEmailAction::AVAILABLE_ACTIONS) as $action) {
            if (! in_array($action, $configuredActions)) {
                $configurations->push(
                    new StageEmailAction([
                        'validation_stage' => $stage,
                        'email_action' => $action,
                        'is_enabled' => false,
                        'sort_order' => count($configuredActions),
                    ])
                );
            }
        }

        return view('managers.settings.stage-email-actions.edit', [
            'stage' => $stage,
            'stageName' => StageEmailAction::AVAILABLE_STAGES[$stage],
            'configurations' => $configurations,
            'availableActions' => StageEmailAction::AVAILABLE_ACTIONS,
        ]);
    }

    /**
     * Update configurations for a specific stage
     */
    public function update(Request $request, string $stage): RedirectResponse
    {
        if (! array_key_exists($stage, StageEmailAction::AVAILABLE_STAGES)) {
            abort(404, 'Validation stage not found');
        }

        $validated = $request->validate([
            'email_actions' => 'required|array',
            'email_actions.*' => 'boolean',
            'sort_order' => 'array',
            'sort_order.*' => 'integer',
        ]);

        // Update or create configurations
        foreach (StageEmailAction::AVAILABLE_ACTIONS as $action => $label) {
            $isEnabled = $validated['email_actions'][$action] ?? false;
            $sortOrder = $validated['sort_order'][$action] ?? 0;

            StageEmailAction::updateOrCreate(
                [
                    'validation_stage' => $stage,
                    'email_action' => $action,
                ],
                [
                    'is_enabled' => (bool) $isEnabled,
                    'sort_order' => (int) $sortOrder,
                ]
            );
        }

        return redirect()->route('manager.settings.stage-email-actions.index')
            ->with('success', "Configuración de acciones de email para {$stage} actualizada correctamente");
    }
}
