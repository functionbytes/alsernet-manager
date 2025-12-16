<?php

namespace App\Console\Commands;

use App\Services\RouteSyncService;
use Illuminate\Console\Command;

class SyncRoutesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'routes:sync {--force : Force synchronization even if no changes detected}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Synchronize application routes with database, automatically detecting added and deleted routes';

    /**
     * Execute the console command.
     */
    public function handle(RouteSyncService $syncService)
    {
        $this->info('🔄 Starting route synchronization...');
        $this->newLine();

        try {
            $result = $syncService->syncAllRoutes();

            // Display results
            $this->displayResults($result);

            // Show statistics
            $this->newLine();
            $this->displayStatistics($syncService);

            $this->info('✅ Route synchronization completed successfully!');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error during route synchronization:');
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Display synchronization results
     */
    protected function displayResults($result)
    {
        $this->line('📊 <info>Synchronization Results:</info>');
        $this->line("   Total routes processed: <fg=cyan>{$result['total']}</>");

        if (!empty($result['added'])) {
            $this->line("   <fg=green>✓ Added routes: " . count($result['added']) . "</>");
            foreach ($result['added'] as $route) {
                $this->line("      • <fg=green>{$route}</>");
            }
        }

        if (!empty($result['updated'])) {
            $this->line("   <fg=yellow>⟳ Updated routes: " . count($result['updated']) . "</>");
            foreach ($result['updated'] as $route) {
                $this->line("      • <fg=yellow>{$route}</>");
            }
        }

        if (!empty($result['deleted'])) {
            $this->line("   <fg=red>✗ Deleted routes: " . count($result['deleted']) . "</>");
            foreach ($result['deleted'] as $route) {
                $this->line("      • <fg=red>{$route}</>");
            }
        }

        if (empty($result['added']) && empty($result['updated']) && empty($result['deleted'])) {
            $this->line('   <fg=cyan>No changes detected</>');
        }
    }

    /**
     * Display route statistics
     */
    protected function displayStatistics(RouteSyncService $syncService)
    {
        $stats = $syncService->getStatistics();

        $this->line('📈 <info>Database Statistics:</info>');
        $this->line("   Total routes in database: <fg=cyan>{$stats['total_routes']}</>");
        $this->line("   Active routes: <fg=green>{$stats['active_routes']}</>");

        if (!empty($stats['by_profile'])) {
            $this->line('   <info>Routes by Profile:</info>');
            foreach ($stats['by_profile'] as $profile => $count) {
                $profileName = $profile ?? 'unassigned';
                $this->line("      • $profileName: <fg=cyan>$count</>");
            }
        }

        if (!empty($stats['by_method'])) {
            $this->line('   <info>Routes by Method:</info>');
            foreach ($stats['by_method'] as $method => $count) {
                $this->line("      • $method: <fg=cyan>$count</>");
            }
        }
    }
}
