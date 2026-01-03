<?php

namespace Modules\Warehouse\Http\Controllers;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function dashboard()
    {

        return view('warehouse::warehouses.dashboard.index')->with([
        ]);

    }
}
