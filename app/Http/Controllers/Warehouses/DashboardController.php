<?php

namespace App\Http\Controllers\Warehouses;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function dashboard()
    {

        return view('warehouses.views.dashboard.index')->with([
        ]);

    }
}
