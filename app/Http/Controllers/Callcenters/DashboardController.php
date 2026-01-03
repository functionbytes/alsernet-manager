<?php

namespace App\Http\Controllers\Callcenters;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the callcenter dashboard
     */
    public function dashboard(): View
    {
        return view('dashboard');
    }
}
