<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MigrateTicketCategoriesToHelpdesk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:ticket-categories-to-helpdesk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate ticket categories from old system to new Helpdesk structure';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting migration of ticket categories...');

        try {
            // Get all old categories
            $oldCategories = DB::table('ticket_categories')->get();

            if ($oldCategories->isEmpty()) {
                $this->warn('No categories found in old system.');
                return 0;
            }

            $this->info("Found {$oldCategories->count()} categories to migrate.");

            foreach ($oldCategories as $oldCategory) {
                // Check if already exists in new system
                $exists = DB::table('helpdesk_ticket_categories')
                    ->where('name', $oldCategory->title)
                    ->exists();

                if (!$exists) {
                    DB::table('helpdesk_ticket_categories')->insert([
                        'name' => $oldCategory->title,
                        'slug' => Str::slug($oldCategory->title),
                        'description' => null,
                        'icon' => 'ti-ticket',
                        'color' => '#5D87FF',
                        'order' => 0,
                        'active' => $oldCategory->available == 1,
                        'is_system' => false,
                        'created_at' => $oldCategory->created_at ?? now(),
                        'updated_at' => $oldCategory->updated_at ?? now(),
                    ]);

                    $this->line("✓ Migrated category: {$oldCategory->title}");
                } else {
                    $this->line("⊘ Category already exists: {$oldCategory->title}");
                }
            }

            $this->info('Migration completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            return 1;
        }
    }
}
