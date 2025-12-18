<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Services\Systems\SystemInfoService;

class SystemInfoController extends Controller
{
    private SystemInfoService $systemInfoService;

    public function __construct(SystemInfoService $systemInfoService)
    {
        $this->systemInfoService = $systemInfoService;
    }

    /**
     * Display the system information panel
     */
    public function index()
    {
        $systemInfo = $this->systemInfoService->getAllSystemInfo();

        return view('managers.views.settings.system.info.index', [
            'environment' => $systemInfo['environment'],
            'server' => $systemInfo['server'],
            'extensions' => $systemInfo['php_extensions'],
            'packages' => $systemInfo['composer_packages'],
        ]);
    }

    /**
     * Get system information for AJAX requests
     */
    public function api()
    {
        return response()->json($this->systemInfoService->getAllSystemInfo());
    }
}
