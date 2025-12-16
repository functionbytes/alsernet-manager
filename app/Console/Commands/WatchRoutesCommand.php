<?php

namespace App\Console\Commands;

use App\Services\RouteFileWatcherService;
use Illuminate\Console\Command;

class WatchRoutesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'routes:watch
                            {--interval=5 : Check interval in seconds}
                            {--add= : Add additional file to watch}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Monitor route files for changes and automatically sync with database. Press Ctrl+C to stop.';

    /**
     * Execute the console command.
     */
    public function handle(RouteFileWatcherService $watcher)
    {
        $interval = (int) $this->option('interval');

        // Validate interval
        if ($interval < 1 || $interval > 60) {
            $this->error('❌ Interval must be between 1 and 60 seconds');
            return self::FAILURE;
        }

        // Add additional file if provided
        if ($this->option('add')) {
            $watcher->addFileToWatch($this->option('add'));
            $this->info("✅ Added file to watch: {$this->option('add')}");
        }

        $this->displayHeader();
        $this->displayMonitoredFiles($watcher);

        try {
            $watcher->startWatching($interval);
        } catch (\Throwable $e) {
            $this->error("❌ Watcher error: {$e->getMessage()}");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Display header information
     */
    protected function displayHeader(): void
    {
        $this->newLine();
        $this->line('╔════════════════════════════════════════════════════════╗');
        $this->line('║          🔍 ROUTE FILE WATCHER                         ║');
        $this->line('║     Monitoring route files for automatic sync...       ║');
        $this->line('╚════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    /**
     * Display monitored files
     */
    protected function displayMonitoredFiles(RouteFileWatcherService $watcher): void
    {
        $files = $watcher->getMonitoredFiles();

        $this->line('<info>📁 Monitored Files:</info>');
        foreach ($files as $file) {
            $path = base_path($file);
            $exists = file_exists($path) ? '✅' : '❌';
            $this->line("   {$exists} {$file}");
        }

        $this->newLine();
        $this->line('<fg=yellow>⏱️  Check Interval: ' . $this->option('interval') . ' seconds</>');
        $this->newLine();
        $this->line('<fg=cyan>💡 How it works:</>');
        $this->line('   1. Monitors route file timestamps and content');
        $this->line('   2. Detects when files are added, modified, or deleted');
        $this->line('   3. Automatically runs "php artisan routes:sync"');
        $this->line('   4. Updates database with route changes');
        $this->newLine();
    }
}
