<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the manager dashboard
     */
    public function dashboard(): View
    {
        return view('dashboard');
    }
}
