<?php

namespace Modules\Faq\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Faq\Models\Faq\FaqCategorie;

class CategoriesController extends Controller
{
    public function index(Request $request)
    {
        $categories = FaqCategorie::latest()->paginate($request->get('per_page', 15));

        return view('faq::theme.faqs.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('faq::theme.faqs.categories.create');
    }

    public function store(Request $request)
    {
        // Store logic here
    }

    public function edit($uid)
    {
        $category = FaqCategorie::where('uid', $uid)->firstOrFail();

        return view('faq::theme.faqs.categories.edit', compact('category'));
    }

    public function update(Request $request)
    {
        // Update logic here
    }

    public function destroy($uid)
    {
        FaqCategorie::where('uid', $uid)->delete();

        return redirect()->back();
    }
}
