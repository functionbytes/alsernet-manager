<?php

namespace Modules\Warehouse\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Warehouse\Entities\Warehouse;

class
WarehousesController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = null ?? $request->search;
        $available = null ?? $request->available;

        $warehouses = Warehouse::latest();

        if ($searchKey != null) {
            $warehouses = $warehouses->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($available != null) {
            $warehouses = $warehouses->where('available', $available);
        }

        $warehouses = $warehouses->paginate(paginationNumber());

        return view('warehouse::warehouses.warehouses.index')->with([
            'warehouses' => $warehouses,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function arrange($uid)
    {

        $warehouse = Warehouse::uid($uid);
        $user = app('warehouses');

        return view('warehouse::warehouses.warehouses.arrange')->with([
            'warehouse' => $warehouse,
        ]);

    }

    public function content($uid)
    {

        $warehouse = Warehouse::uid($uid);

        return view('warehouse::warehouses.warehouses.content')->with([
            'warehouse' => $warehouse,
        ]);

    }

    public function destroy($uid)
    {
        $warehouse = Warehouse::uid($uid);
        $warehouse->delete();

        return redirect()->route('warehouses.warehouses.index');
    }
}
