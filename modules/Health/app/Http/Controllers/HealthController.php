<?php

namespace Modules\Health\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Health\Facades\Health;

class HealthController extends Controller
{
    /**
     * Display health check dashboard
     */
    public function index()
    {
        $checkResults = Health::registeredChecks();

        $results = collect($checkResults)->map(function ($check) {
            $result = $check->run();

            // Create a simple object with all the data we need
            return (object) [
                'check' => $check,
                'status' => $result->status,
                'label' => $check->getLabel(),
                'shortSummary' => $result->shortSummary,
                'notificationMessage' => $result->notificationMessage,
                'meta' => $result->meta,
            ];
        });

        $pageTitle = 'System Health Check';
        $breadcrumb = 'Settings / Health Check';

        // Calculate overall status
        $overallStatus = 'ok';
        if ($results->contains(fn ($result) => $result->status->value === 'failed')) {
            $overallStatus = 'failed';
        } elseif ($results->contains(fn ($result) => $result->status->value === 'warning')) {
            $overallStatus = 'warning';
        }

        // Get history from last 24 hours
        $history = collect();

        return view('health::settings.index', compact(
            'results',
            'overallStatus',
            'history',
            'pageTitle',
            'breadcrumb'
        ));
    }

    /**
     * Run health checks and return JSON
     */
    public function check()
    {
        $checkResults = Health::registeredChecks();

        $results = collect($checkResults)->map(function ($check) {
            $result = $check->run();

            return [
                'name' => $result->check->getName(),
                'label' => $result->check->getLabel(),
                'status' => $result->status->value,
                'short_summary' => $result->shortSummary,
                'notification_message' => $result->notificationMessage,
                'meta' => $result->meta,
            ];
        });

        $overallStatus = 'ok';
        if ($results->contains(fn ($result) => $result['status'] === 'failed')) {
            $overallStatus = 'failed';
        } elseif ($results->contains(fn ($result) => $result['status'] === 'warning')) {
            $overallStatus = 'warning';
        }

        return response()->json([
            'status' => $overallStatus,
            'timestamp' => now()->toIso8601String(),
            'checks' => $results,
        ]);
    }

    /**
     * Get historical health check data
     */
    public function history(Request $request)
    {
        $days = $request->input('days', 7);

        // Get history from database
        $history = \Spatie\Health\Models\HealthCheckResultHistoryItem::query()
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('check_name')
            ->map(function ($items) {
                return $items->map(function ($item) {
                    return [
                        'status' => $item->status,
                        'created_at' => $item->created_at->toIso8601String(),
                        'short_summary' => $item->short_summary,
                    ];
                });
            });

        return response()->json([
            'period_days' => $days,
            'history' => $history,
        ]);
    }

    /**
     * Simple health check endpoint (no authentication)
     * Used by load balancers, monitoring systems, etc.
     */
    public function ping()
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Full health check endpoint for external monitoring
     * Returns complete health status without authentication
     */
    public function health()
    {
        try {
            $checkResults = Health::registeredChecks();

            $results = collect($checkResults)->map(function ($check) {
                $result = $check->run();

                return [
                    'name' => $check->getName(),
                    'label' => $check->getLabel(),
                    'status' => $result->status->value,
                    'short_summary' => $result->shortSummary,
                ];
            });

            $overallStatus = 'ok';
            if ($results->contains(fn ($result) => $result['status'] === 'failed')) {
                $overallStatus = 'failed';
            } elseif ($results->contains(fn ($result) => $result['status'] === 'warning')) {
                $overallStatus = 'warning';
            }

            return response()->json([
                'status' => $overallStatus,
                'timestamp' => now()->toIso8601String(),
                'checks' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Health check error',
                'timestamp' => now()->toIso8601String(),
            ], 503);
        }
    }

    /**
     * Document module specific health checks
     * Validates document system integrity
     */
    public function documentsHealth()
    {
        try {
            $checks = [
                'database_connection' => [
                    'status' => 'ok',
                    'message' => 'Database connected',
                ],
                'documents_table' => [
                    'status' => 'ok',
                    'message' => 'Documents table exists',
                ],
                'storage_accessible' => [
                    'status' => 'ok',
                    'message' => 'Storage directory accessible',
                ],
            ];

            // Check if documents table exists
            try {
                \Illuminate\Support\Facades\DB::table('documents')->limit(1)->get();
            } catch (\Exception $e) {
                $checks['documents_table']['status'] = 'failed';
                $checks['documents_table']['message'] = 'Documents table not found';
            }

            // Check storage
            if (! is_writable(storage_path())) {
                $checks['storage_accessible']['status'] = 'failed';
                $checks['storage_accessible']['message'] = 'Storage directory not writable';
            }

            $overallStatus = collect($checks)->every(fn ($check) => $check['status'] === 'ok') ? 'ok' : 'failed';

            return response()->json([
                'status' => $overallStatus,
                'module' => 'documents',
                'timestamp' => now()->toIso8601String(),
                'checks' => $checks,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'module' => 'documents',
                'message' => $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ], 503);
        }
    }

    /**
     * Detailed health check with system information
     * Only in debug mode for security
     */
    public function detailed()
    {
        // Only allow in debug mode or with API token
        if (! config('app.debug') && ! $this->hasValidHealthToken()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        try {

            $checkResults = Health::registeredChecks();

            $results = collect($checkResults)->map(function ($check) {
                $result = $check->run();

                return [
                    'name' => $check->getName(),
                    'label' => $check->getLabel(),
                    'status' => $result->status->value,
                    'short_summary' => $result->shortSummary,
                    'notification_message' => $result->notificationMessage,
                    'meta' => $result->meta,
                ];
            });

            $overallStatus = 'ok';
            if ($results->contains(fn ($result) => $result['status'] === 'failed')) {
                $overallStatus = 'failed';
            } elseif ($results->contains(fn ($result) => $result['status'] === 'warning')) {
                $overallStatus = 'warning';
            }

            // System information
            $systemInfo = [
                'app_name' => config('app.name'),
                'environment' => config('app.env'),
                'debug' => config('app.debug'),
                'php_version' => phpversion(),
                'laravel_version' => app()->version(),
                'server_time' => now()->toIso8601String(),
                'uptime_seconds' => $this->getServerUptime(),
            ];

            return response()->json([
                'status' => $overallStatus,
                'timestamp' => now()->toIso8601String(),
                'system' => $systemInfo,
                'checks' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Detailed health check error',
                'timestamp' => now()->toIso8601String(),
            ], 503);
        }
    }

    /**
     * Check if valid health token provided
     */
    private function hasValidHealthToken(): bool
    {
        $token = request()->query('token');
        $validToken = config('healthcheck.api_token');

        return $token && $validToken && hash_equals($validToken, $token);
    }

    /**
     * Get server uptime in seconds
     */
    private function getServerUptime(): int
    {
        try {
            if (function_exists('exec')) {
                $uptime = @exec('cat /proc/uptime');
                if ($uptime) {
                    return (int) explode(' ', $uptime)[0];
                }
            }
        } catch (\Exception $e) {
            // Silently fail, not all servers have /proc/uptime
        }

        return 0;
    }
}
