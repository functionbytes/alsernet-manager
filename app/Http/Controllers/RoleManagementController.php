<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class RoleManagementController extends Controller
{
    public function index(): View
    {
        return view('roles.index');
    }

    public function edit($user): View
    {
        return view('roles.edit');
    }

    public function update(Request $request, $user): RedirectResponse
    {
        return back();
    }

    public function mappings(): View
    {
        return view('roles.mappings');
    }

    public function updateMapping(Request $request, $mapping): RedirectResponse
    {
        return back();
    }

    public function updateRoute(Request $request, $route): RedirectResponse
    {
        return back();
    }
}
