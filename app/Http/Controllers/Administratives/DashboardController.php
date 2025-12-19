<?php

namespace App\Http\Controllers\Administratives;

use App\Http\Controllers\Controller;
use App\Models\Document\Document;
use App\Models\Document\DocumentStatus;
use App\Models\Document\DocumentLoad;
use App\Models\Document\DocumentSync;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private function getDashboardData()
    {
        $now = Carbon::now();

        // Estadísticas generales
        $totalToday = Document::whereDate('created_at', $now->today())->count();
        $totalWeek = Document::whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])->count();
        $totalMonth = Document::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $totalAll = Document::count();

        // Pendientes (usando status_id con key 'pending')
        $pendingStatus = DocumentStatus::where('key', 'pending')->first();
        $totalPending = $pendingStatus ? Document::where('status_id', $pendingStatus->id)->count() : 0;

        // Recibidos
        $receivedStatus = DocumentStatus::where('key', 'received')->first();
        $totalReceived = $receivedStatus ? Document::where('status_id', $receivedStatus->id)->count() : 0;

        // Aprobados
        $approvedStatus = DocumentStatus::where('key', 'approved')->first();
        $totalApproved = $approvedStatus ? Document::where('status_id', $approvedStatus->id)->count() : 0;

        // Rechazados
        $rejectedStatus = DocumentStatus::where('key', 'rejected')->first();
        $totalRejected = $rejectedStatus ? Document::where('status_id', $rejectedStatus->id)->count() : 0;

        // Estadísticas por estado (para el gráfico/tabla)
        $statuses = DocumentStatus::withCount('documents')
            ->where('is_active', true)
            ->orderBy('order')
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
        $recentDocuments = Document::with(['status', 'documentLoad'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return [
            'totalToday' => $totalToday,
            'totalWeek' => $totalWeek,
            'totalMonth' => $totalMonth,
            'totalAll' => $totalAll,
            'totalPending' => $totalPending,
            'totalReceived' => $totalReceived,
            'totalApproved' => $totalApproved,
            'totalRejected' => $totalRejected,
            'statuses' => $statuses,
            'monthlyCounts' => $monthlyCounts,
            'months' => $months,
            'recentDocuments' => $recentDocuments,
        ];
    }

    public function dashboard()
    {
        return view('administratives.views.dashboard.index')->with($this->getDashboardData());
    }

    public function dashboardV1()
    {
        return view('administratives.views.dashboard.v1')->with($this->getDashboardData());
    }

    public function dashboardV2()
    {
        return view('administratives.views.dashboard.v2')->with($this->getDashboardData());
    }

    public function dashboardV3()
    {
        return view('administratives.views.dashboard.v3')->with($this->getDashboardData());
    }

    public function dashboardV4()
    {
        $data = $this->getDashboardData();

        // Add load distribution data for v4
        $loadStats = DocumentLoad::select('document_loads.id', 'document_loads.label', 'document_loads.color')
            ->selectRaw('COUNT(documents.id) as count')
            ->leftJoin('documents', 'documents.load_id', '=', 'document_loads.id')
            ->where('document_loads.is_active', true)
            ->groupBy('document_loads.id', 'document_loads.label', 'document_loads.color')
            ->orderBy('document_loads.order')
            ->get();

        // Add documents without load type
        $documentsWithoutLoad = Document::whereNull('load_id')->count();
        if ($documentsWithoutLoad > 0) {
            $loadStats->push((object)[
                'id' => null,
                'label' => 'Sin método de carga',
                'color' => '#6c757d',
                'count' => $documentsWithoutLoad
            ]);
        }

        $data['loads'] = $loadStats;

        return view('administratives.views.dashboard.v4')->with($data);
    }
}
