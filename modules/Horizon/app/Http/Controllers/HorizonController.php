<?php

namespace Modules\Horizon\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Horizon\Services\HorizonStatsService;
use Spatie\Health\Facades\Health;

class HorizonController extends Controller
{
    public function __construct(private HorizonStatsService $statsService)
    {
    }

    /**
     * Display Horizon dashboard
     */
    public function index()
    {
        $this->authorize('view-horizon');

        $stats = $this->statsService->getOverallStats();

        $pageTitle = 'Monitoreo de colas';
        $breadcrumb = 'Configuración / Horizon';

        return view('horizon::settings.index', compact(
            'stats',
            'pageTitle',
            'breadcrumb'
        ));
    }

    /**
     * Display active jobs
     */
    public function jobs()
    {
        $this->authorize('view-horizon');

        $jobs = $this->statsService->getRecentJobs();

        $pageTitle = 'Trabajos activos';
        $breadcrumb = 'Configuración / Horizon / Trabajos activos';

        return view('horizon::settings.jobs', compact(
            'jobs',
            'pageTitle',
            'breadcrumb'
        ));
    }

    /**
     * Display failed jobs
     */
    public function failed()
    {
        $this->authorize('view-horizon');

        $failedJobs = $this->statsService->getFailedJobs();
        $failedCount = count($failedJobs);

        $pageTitle = 'Trabajos fallidos';
        $breadcrumb = 'Configuración / Horizon / Trabajos fallidos';

        return view('horizon::settings.failed', compact(
            'failedJobs',
            'failedCount',
            'pageTitle',
            'breadcrumb'
        ));
    }

    /**
     * Display metrics and charts
     */
    public function metrics()
    {
        $this->authorize('view-horizon');

        $metrics = $this->statsService->getMetrics();
        $throughput = $this->statsService->getThroughputData();

        $pageTitle = 'Métricas';
        $breadcrumb = 'Configuración / Horizon / Métricas';

        return view('horizon::settings.metrics', compact(
            'metrics',
            'throughput',
            'pageTitle',
            'breadcrumb'
        ));
    }
}
