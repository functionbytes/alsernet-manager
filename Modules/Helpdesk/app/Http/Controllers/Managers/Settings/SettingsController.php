<?php

namespace Modules\Helpdesk\Http\Controllers\Managers\Settings;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController
{
    public function index(): View
    {
        return view('helpdesk::managers.settings.index', [
            'config' => config('helpdesk'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        // Actualizar configuración (opcional, puede ser en BD o config cache)
        return redirect()->route('manager.settings.helpdesk.index')
            ->with('success', 'Helpdesk settings updated successfully');
    }
}
