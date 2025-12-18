<?php

namespace App\Services\Systems;

use Illuminate\Support\Facades\Log;

/**
 * Route File Watcher Service
 *
 * Monitors route files for changes (additions, deletions, modifications)
 * and automatically triggers synchronization when changes are detected.
 */
class RouteFileWatcherService
{
    /**
     * Files to monitor for changes
     */
    protected array $routeFiles = [
        'routes/managers.php',
        'routes/callcenters.php',
        'routes/shops.php',
        'routes/warehouses.php',
        'routes/administratives.php',
        'routes/returns.php',
    ];

    /**
     * Cache file to store file hashes
     */
    protected string $cacheFile;

    /**
     * Service instance
     */
    protected RouteSyncService $syncService;

    public function __construct(RouteSyncService $syncService)
    {
        $this->syncService = $syncService;
        $this->cacheFile = storage_path('app/route-monitor-cache.json');
    }

    /**
     * Start monitoring route files for changes
     *
     * This method runs continuously and checks for file modifications
     */
    public function startWatching(int $interval = 5)
    {
        Log::info('🔍 Route file watcher started', [
            'interval' => $interval . ' seconds',
            'monitored_files' => count($this->routeFiles),
        ]);

        $this->output("🔍 Route file watcher started (checking every {$interval}s)");
        $this->output('Press Ctrl+C to stop monitoring');
        $this->output(str_repeat('-', 60));

        while (true) {
            try {
                $changes = $this->detectChanges();

                if (!empty($changes['added']) || !empty($changes['modified']) || !empty($changes['deleted'])) {
                    $this->handleChanges($changes);
                }

                sleep($interval);
            } catch (\Exception $e) {
                Log::error('Error in route watcher', [
                    'error' => $e->getMessage(),
                ]);
                $this->output("❌ Error: {$e->getMessage()}", 'error');
                sleep($interval);
            }
        }
    }

    /**
     * Detect changes in route files
     */
    public function detectChanges(): array
    {
        $changes = [
            'added' => [],
            'modified' => [],
            'deleted' => [],
        ];

        $currentHashes = $this->getFileHashes();
        $previousHashes = $this->loadCachedHashes();

        // Detect added and modified files
        foreach ($currentHashes as $file => $hash) {
            if (!isset($previousHashes[$file])) {
                $changes['added'][] = $file;
            } elseif ($previousHashes[$file] !== $hash) {
                $changes['modified'][] = $file;
            }
        }

        // Detect deleted files
        foreach ($previousHashes as $file => $hash) {
            if (!isset($currentHashes[$file])) {
                $changes['deleted'][] = $file;
            }
        }

        // Save current hashes for next comparison
        if (!empty($changes['added']) || !empty($changes['modified']) || !empty($changes['deleted'])) {
            $this->saveCachedHashes($currentHashes);
        }

        return $changes;
    }

    /**
     * Get hashes of all route files
     */
    protected function getFileHashes(): array
    {
        $hashes = [];

        foreach ($this->routeFiles as $file) {
            $path = base_path($file);

            if (file_exists($path)) {
                $hashes[$file] = $this->calculateFileHash($path);
            }
        }

        return $hashes;
    }

    /**
     * Calculate hash of a file (content + mtime)
     *
     * Uses both content hash and modification time for redundancy
     */
    protected function calculateFileHash(string $path): string
    {
        // Combine content hash with modification time
        // This catches both content changes and timestamp changes
        $content = file_get_contents($path);
        $mtime = filemtime($path);

        return md5($content . '|' . $mtime);
    }

    /**
     * Load previously cached file hashes
     */
    protected function loadCachedHashes(): array
    {
        if (!file_exists($this->cacheFile)) {
            return [];
        }

        $cached = json_decode(file_get_contents($this->cacheFile), true);
        return is_array($cached) ? $cached : [];
    }

    /**
     * Save file hashes to cache
     */
    protected function saveCachedHashes(array $hashes): void
    {
        file_put_contents($this->cacheFile, json_encode($hashes, JSON_PRETTY_PRINT));
    }

    /**
     * Handle detected changes by syncing routes
     */
    protected function handleChanges(array $changes): void
    {
        $timestamp = now()->format('Y-m-d H:i:s');

        $this->output("\n⚠️  Route file changes detected at {$timestamp}");

        if (!empty($changes['added'])) {
            $this->output('✅ Added files: ' . implode(', ', $changes['added']));
        }

        if (!empty($changes['modified'])) {
            $this->output('⟳ Modified files: ' . implode(', ', $changes['modified']));
        }

        if (!empty($changes['deleted'])) {
            $this->output('❌ Deleted files: ' . implode(', ', $changes['deleted']));
        }

        $this->output('Syncing routes with database...');

        // Execute sync
        try {
            $result = $this->syncService->syncAllRoutes();

            $this->displaySyncResults($result);

            Log::info('Route files changed - Auto-sync completed', [
                'detected_changes' => count(array_filter($changes)),
                'routes_added' => count($result['added']),
                'routes_updated' => count($result['updated']),
                'routes_deleted' => count($result['deleted']),
            ]);
        } catch (\Exception $e) {
            $this->output("❌ Sync failed: {$e->getMessage()}", 'error');
            Log::error('Auto-sync failed', [
                'error' => $e->getMessage(),
            ]);
        }

        $this->output(str_repeat('-', 60));
    }

    /**
     * Display sync results
     */
    protected function displaySyncResults(array $result): void
    {
        $this->output("\n📊 Sync Results:");
        $this->output("   Total routes processed: {$result['total']}");

        if (!empty($result['added'])) {
            $this->output("   ✅ Added: " . count($result['added']));
            foreach ($result['added'] as $route) {
                $this->output("      • {$route}");
            }
        }

        if (!empty($result['updated'])) {
            $this->output("   ⟳ Updated: " . count($result['updated']));
        }

        if (!empty($result['deleted'])) {
            $this->output("   ❌ Deleted: " . count($result['deleted']));
            foreach ($result['deleted'] as $route) {
                $this->output("      • {$route}");
            }
        }

        if (empty($result['added']) && empty($result['updated']) && empty($result['deleted'])) {
            $this->output("   ℹ️  No route changes detected");
        }
    }

    /**
     * Add a file to watch list
     */
    public function addFileToWatch(string $file): self
    {
        if (!in_array($file, $this->routeFiles)) {
            $this->routeFiles[] = $file;
        }

        return $this;
    }

    /**
     * Get all monitored files
     */
    public function getMonitoredFiles(): array
    {
        return $this->routeFiles;
    }

    /**
     * Output a message (can be overridden for testing)
     */
    protected function output(string $message, string $type = 'info'): void
    {
        if (php_sapi_name() === 'cli') {
            // Format output for CLI
            match ($type) {
                'error' => fwrite(STDERR, "\033[31m{$message}\033[0m\n"),
                'success' => fwrite(STDOUT, "\033[32m{$message}\033[0m\n"),
                'warning' => fwrite(STDOUT, "\033[33m{$message}\033[0m\n"),
                default => fwrite(STDOUT, "{$message}\n"),
            };
        }
    }
}
