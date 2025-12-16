<?php

namespace App\Http\Controllers\Managers\Campaigns\Products;

use App\Exports\Managers\ProductExport;
use App\Exports\Managers\ProductKardexExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function generateInventary(Request $request)
    {

        return Excel::download(new ProductExport, 'Reporte inventario.xlsx');

    }

    public function generateKardex(Request $request)
    {
        return Excel::download(new ProductKardexExport, 'Reporte kardex.xlsx');

    }
}
