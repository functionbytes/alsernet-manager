<?php

namespace App\Http\Controllers\Helpdesk;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class WidgetController extends Controller
{
    /**
     * Display the LiveChat widget
     */
    public function index(): View
    {
        $isPreview = request()->has('settingsPreview');

        return view('helpdesk.widget.index', [
            'isPreview' => $isPreview,
        ]);
    }

    /**
     * Display the launcher demo
     */
    public function launcherDemo(): View
    {
        return view('helpdesk.widget.launcher-demo');
    }

    /**
     * Get widget settings for API
     */
    public function settings(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'settings' => [
                'enabled' => true,
                'position' => 'bottom-right',
                'theme' => 'light',
                'showLauncher' => true,
                'welcomeMessage' => '¡Hola! ¿En qué podemos ayudarte?',
            ],
        ]);
    }
}
