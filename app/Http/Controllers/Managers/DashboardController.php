<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the generic dashboard
     * The dashboard adapts its content based on user role
     */
    public function dashboard(): View
    {
        $user = Auth::user();

        // Pass user data and role to the view
        // The view will adapt its content based on the user role
        return view('theme.dashboard', [
            'user' => $user,
            'userRole' => $user->roles->first()?->name,
        ]);
    }
}
