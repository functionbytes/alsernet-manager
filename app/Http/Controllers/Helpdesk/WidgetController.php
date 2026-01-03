<?php

namespace App\Http\Controllers\Helpdesk;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class WidgetController extends Controller
{
    public function index(): View
    {
        return view('helpdesk.widget');
    }

    public function launcherDemo(): View
    {
        return view('helpdesk.launcher-demo');
    }

    public function settings()
    {
        return response()->json([]);
    }
}
