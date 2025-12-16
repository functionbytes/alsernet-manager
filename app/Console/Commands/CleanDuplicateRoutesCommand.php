<?php

namespace App\Console\Commands;

use App\Models\AppRoute;
use Illuminate\Console\Command;

class CleanDuplicateRoutesCommand extends Command
{
    protected $signature = 'routes:clean-duplicates';

    protected $description = 'Remove duplicate routes from the database (by name)';

    public function handle()
    {
        $this->info('🧹 Cleaning duplicate routes...');

        // Find all routes grouped by name
        $routesByName = AppRoute::all()->groupBy('name');

        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($routesByName as $name => $routes) {
            if ($routes->count() > 1) {
                // Keep the most recently updated one
                $keepRoute = $routes->sortByDesc('updated_at')->first();

                // Delete all others
                foreach ($routes as $route) {
                    if ($route->id !== $keepRoute->id) {
                        $this->line("  🗑️  Deleting duplicate: {$name} (id: {$route->id})");
                        $route->delete();
                        $deletedCount++;
                    }
                }
            } else {
                $skippedCount++;
            }
        }

        $this->info("✅ Cleanup complete!");
        $this->info("  ✓ Deleted: {$deletedCount} duplicate routes");
        $this->info("  ✓ Kept: {$skippedCount} unique routes");
    }
}
