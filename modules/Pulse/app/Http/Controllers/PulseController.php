<?php

namespace Modules\Pulse\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class PulseController extends Controller
{
    /**
     * Display the Pulse dashboard
     */
    public function index(): RedirectResponse
    {
        // Redirect to the Laravel Pulse dashboard
        // Laravel Pulse registers its own routes at the configured path
        return redirect('/pulse');
    }
}
