<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;

class PagesController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function home()
    {
        return view('dashboard');
    }
}
