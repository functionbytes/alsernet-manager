<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;

class PulseController extends Controller
{
    public function dashboard()
    {

        return view('managers.views.pulse.dashboard')->with([
        ]);

    }
}
