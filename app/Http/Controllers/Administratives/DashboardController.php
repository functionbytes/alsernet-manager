<?php

namespace App\Http\Controllers\Administratives;

use App\Http\Controllers\Controller;
use App\Models\Document\Document;
use App\Models\Enterprise\Enterprise;
use App\Models\Newsletter;
use App\Structure\Elements;

class DashboardController extends Controller
{
    public function dashboard(){

        $now = \Carbon\Carbon::now();

        $totalToday = Document::whereDate('created_at', $now->today())->count();
        $totalWeek = Document::whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()])->count();
        $totalMonth = Document::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();

        $totalPending = Document::whereIn('proccess', ['pending', 'incomplete', 'awaiting_documents'])->count();

        // Estadísticas por estado (para el gráfico/tabla)
        $statistics = Document::select('proccess', \DB::raw('count(*) as total'))
            ->groupBy('proccess')
            ->get();

        // Datos para el gráfico (Document Trends) - Últimos 12 meses
        $monthlyCounts = [];
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $date = $now->copy()->subMonths(11 - $i);
            $monthName = $date->format('M');
            $count = Document::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            $monthlyCounts[] = $count;
            $months[] = $monthName;
        }

        // Documentos recientes (Recent Documents)
        $recentDocuments = Document::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('administratives.views.dashboard.index')->with([
            'totalToday' => $totalToday,
            'totalWeek' => $totalWeek,
            'totalMonth' => $totalMonth,
            'totalPending' => $totalPending,
            'statistics' => $statistics,
            'monthlyCounts' => $monthlyCounts,
            'months' => $months,
            'recentDocuments' => $recentDocuments
        ]);

    }

}
