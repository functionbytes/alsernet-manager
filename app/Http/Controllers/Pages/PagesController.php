<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class PagesController extends Controller
{
    /**
     * Show the home page (dashboard)
     */
    public function home(): RedirectResponse
    {
        return redirect()->route('manager.index');
    }
}
