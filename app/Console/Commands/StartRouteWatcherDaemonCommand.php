<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class StartRouteWatcherDaemonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'routes:daemon
                            {--interval=5 : Check interval in seconds}
                            {--stop : Stop the daemon}
                            {--status : Show daemon status}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Start/stop route watcher as a background daemon process';

    /**
     * Daemon PID file location
     */
    protected string $pidFile;

    /**
     * Log file location
     */
    protected string $logFile;

    public function __construct()
    {
        parent::__construct();
        $this->pidFile = storage_path('route-watcher.pid');
        $this->logFile = storage_path('logs/route-watcher.log');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('stop')) {
            return $this->stopDaemon();
        }

        if ($this->option('status')) {
            return $this->showStatus();
        }

        return $this->startDaemon();
    }

    /**
     * Start the daemon
     */
    protected function startDaemon(): int
    {
        // Check if already running
        if ($this->isDaemonRunning()) {
            $pid = trim(file_get_contents($this->pidFile));
            $this->warn("⚠️  Route watcher daemon is already running (PID: {$pid})");
            $this->line('Run: php artisan routes:daemon --stop');
            return self::FAILURE;
        }

        $this->info('🚀 Starting route watcher daemon...');

        // Get interval
        $interval = (int) $this->option('interval');
        if ($interval < 1 || $interval > 60) {
            $this->error('❌ Interval must be between 1 and 60 seconds');
            return self::FAILURE;
        }

        try {
            // Start process in background
            $command = "php artisan routes:watch --interval={$interval}";

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows
                pclose(popen("start /B {$command}", "r"));
            } else {
                // Unix/Linux/macOS
                $command = "{$command} >> {$this->logFile} 2>&1 &";
                shell_exec($command);
            }

            // Wait a moment for process to start
            sleep(2);

            // Get process info (Unix/Linux only)
            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                $output = shell_exec("ps aux | grep 'routes:watch' | grep -v grep");

                if ($output) {
                    $lines = explode("\n", trim($output));
                    foreach ($lines as $line) {
                        if (str_contains($line, 'routes:watch')) {
                            $parts = preg_split('/\s+/', $line);
                            $pid = $parts[1] ?? null;

                            if ($pid && is_numeric($pid)) {
                                file_put_contents($this->pidFile, $pid);

                                Log::info('Route watcher daemon started', ['pid' => $pid, 'interval' => $interval]);

                                $this->info("✅ Route watcher daemon started successfully!");
                                $this->newLine();
                                $this->line("<fg=green>PID: {$pid}</>");
                                $this->line("<fg=green>Interval: {$interval} seconds</>");
                                $this->line("<fg=cyan>Log file: {$this->logFile}</>");
                                $this->newLine();
                                $this->line('Commands:');
                                $this->line('  • Stop daemon: php artisan routes:daemon --stop');
                                $this->line('  • Show status: php artisan routes:daemon --status');
                                $this->line('  • View logs: tail -f ' . $this->logFile);

                                return self::SUCCESS;
                            }
                        }
                    }
                }
            } else {
                // Windows
                $this->info("✅ Route watcher daemon started in background");
                $this->line("<fg=cyan>Log file: {$this->logFile}</>");
                $this->line("Tip: Monitor logs with: type {$this->logFile}");
                return self::SUCCESS;
            }

            $this->error('❌ Could not start daemon');
            return self::FAILURE;
        } catch (\Exception $e) {
            Log::error('Failed to start route watcher daemon', ['error' => $e->getMessage()]);
            $this->error("❌ Error: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    /**
     * Stop the daemon
     */
    protected function stopDaemon(): int
    {
        if (!$this->isDaemonRunning()) {
            $this->warn('⚠️  Route watcher daemon is not running');
            return self::FAILURE;
        }

        try {
            $pid = trim(file_get_contents($this->pidFile));

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows
                shell_exec("taskkill /PID {$pid} /F");
            } else {
                // Unix/Linux/macOS
                shell_exec("kill {$pid}");
            }

            sleep(1);

            if ($this->isDaemonRunning()) {
                $this->error("❌ Failed to stop daemon (PID: {$pid})");
                return self::FAILURE;
            }

            @unlink($this->pidFile);

            Log::info('Route watcher daemon stopped', ['pid' => $pid]);

            $this->info("✅ Route watcher daemon stopped successfully (PID was: {$pid})");
            return self::SUCCESS;
        } catch (\Exception $e) {
            Log::error('Failed to stop route watcher daemon', ['error' => $e->getMessage()]);
            $this->error("❌ Error: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    /**
     * Show daemon status
     */
    protected function showStatus(): int
    {
        $this->newLine();
        $this->line('╔════════════════════════════════════════════╗');
        $this->line('║    🔍 ROUTE WATCHER DAEMON STATUS          ║');
        $this->line('╚════════════════════════════════════════════╝');
        $this->newLine();

        if ($this->isDaemonRunning()) {
            $pid = trim(file_get_contents($this->pidFile));
            $this->info("✅ Status: <fg=green>RUNNING</> (PID: {$pid})");
        } else {
            $this->warn("❌ Status: <fg=red>STOPPED</>");
        }

        $this->newLine();
        $this->line('Log file: ' . $this->logFile);

        if (file_exists($this->logFile)) {
            $size = filesize($this->logFile);
            $this->line("Size: " . $this->formatBytes($size));

            $lines = file($this->logFile);
            $recentLines = array_slice($lines, -5);

            $this->newLine();
            $this->line('<fg=yellow>Recent logs (last 5 lines):</>');
            foreach ($recentLines as $line) {
                $this->line('  ' . trim($line));
            }
        } else {
            $this->line('<fg=gray>No logs yet</>');
        }

        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Check if daemon is running
     */
    protected function isDaemonRunning(): bool
    {
        if (!file_exists($this->pidFile)) {
            return false;
        }

        $pid = trim(file_get_contents($this->pidFile));

        if (!is_numeric($pid)) {
            return false;
        }

        // Check if process exists
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows
            $output = shell_exec("tasklist /FI \"PID eq {$pid}\"");
            return str_contains($output, (string) $pid);
        } else {
            // Unix/Linux/macOS
            return @file_exists("/proc/{$pid}");
        }
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
