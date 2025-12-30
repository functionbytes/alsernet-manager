<?php

namespace Modules\Warehouse\Http\Controllers\Warehouses\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Warehouse\Models\Product\Product;

class BarcodeController extends Controller
{
    public function all(Request $request)
    {

        $products = Product::all()->take(100);

        return view('warehouse::inventaries.barcodes.all')->with([
            'inventaries' => $products,
        ]);

    }

    public function single($uid)
    {

        $product = Product::uid($uid);

        return view('warehouse::inventaries.barcodes.all')->with([
            'product' => $product,
        ]);
    }
}
