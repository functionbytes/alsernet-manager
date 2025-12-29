<?php

namespace App\Http\Controllers\Warehouses\Products;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function all(Request $request)
    {

        $products = Product::all()->take(100);

        return view('warehouses.views.inventaries.barcodes.all')->with([
            'inventaries' => $products,
        ]);

    }

    public function single($uid)
    {

        $product = Product::uid($uid);

        return view('warehouses.views.inventaries.barcodes.all')->with([
            'product' => $product,
        ]);
    }
}
