<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MigrateTicketStatusToHelpdesk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:ticket-status-to-helpdesk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate ticket status from old system to new Helpdesk structure';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting migration of ticket statuses...');

        try {
            // Get all old statuses
            $oldStatuses = DB::table('ticket_status')->get();

            if ($oldStatuses->isEmpty()) {
                $this->warn('No statuses found in old system.');
                return 0;
            }

            $this->info("Found {$oldStatuses->count()} statuses to migrate.");

            foreach ($oldStatuses as $oldStatus) {
                // Check if already exists in new system
                $exists = DB::table('helpdesk_ticket_statuses')
                    ->where('name', $oldStatus->title)
                    ->exists();

                if (!$exists) {
                    DB::table('helpdesk_ticket_statuses')->insert([
                        'name' => $oldStatus->title,
                        'slug' => Str::slug($oldStatus->title),
                        'color' => $oldStatus->color ?? '#5D87FF',
                        'description' => null,
                        'order' => 0,
                        'is_default' => false,
                        'is_system' => false,
                        'is_open' => true,
                        'stops_sla_timer' => false,
                        'active' => $oldStatus->available == 1,
                        'created_at' => $oldStatus->created_at ?? now(),
                        'updated_at' => $oldStatus->updated_at ?? now(),
                    ]);

                    $this->line("✓ Migrated status: {$oldStatus->title}");
                } else {
                    $this->line("⊘ Status already exists: {$oldStatus->title}");
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
