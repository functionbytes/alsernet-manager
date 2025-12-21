<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function dashboard()
    {
        return view('managers.views.dashboard.index')->with([
        ]);

    }
}
