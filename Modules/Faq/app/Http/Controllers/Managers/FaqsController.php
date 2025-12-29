<?php

namespace Modules\Faq\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use Modules\Faq\Models\Faq\Faq;
use Illuminate\Http\Request;

class FaqsController extends Controller
{
    public function index(Request $request)
    {
        $faqs = Faq::latest()->paginate($request->get('per_page', 15));
        return view('faq::managers.faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('faq::managers.faqs.create');
    }

    public function store(Request $request)
    {
        // Store logic here
    }

    public function edit($uid)
    {
        $faq = Faq::where('uid', $uid)->firstOrFail();
        return view('faq::managers.faqs.edit', compact('faq'));
    }

    public function update(Request $request)
    {
        // Update logic here
    }

    public function destroy($uid)
    {
        Faq::where('uid', $uid)->delete();
        return redirect()->back();
    }
}
