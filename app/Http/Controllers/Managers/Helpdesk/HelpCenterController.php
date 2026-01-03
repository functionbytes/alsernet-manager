<?php

namespace App\Http\Controllers\Managers\Helpdesk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HelpCenterController extends Controller
{
    public function apiWidget(Request $request)
    {
        return response()->json([]);
    }

    public function apiArticle($id)
    {
        return response()->json([]);
    }
}
