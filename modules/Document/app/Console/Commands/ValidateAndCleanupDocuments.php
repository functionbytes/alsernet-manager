<?php

namespace Modules\Document\Console\Commands;

use Illuminate\Console\Command;
use Modules\Document\Entities\Document;

class ValidateAndCleanupDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:validate-and-cleanup-documents {--force : Skip confirmation} {--dry-run : Show what would be deleted without deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate existing documents against Prestashop payment status and cleanup unpaid documents (except status_id = 5)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Confirmation
        if (! $this->option('force')) {
            if (! $this->confirm('This will validate and cleanup documents based on payment status. Continue?')) {
                return 0;
            }
        }

        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('🔍 DRY-RUN MODE: No documents will be deleted');
            $this->line('');
        }

        try {
            $this->info('🔍 Starting document validation and cleanup...');
            $this->line('');

            // Step 1: Get validation summary
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('📊 Validating existing documents');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->line('');

            $stats = $this->validateAndAnalyzeDocuments();

            // Display results
            $this->line('');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('📋 Validation Results');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->line('');

            $this->info('📊 Summary:');
            $this->info("  ✓ Total Documents Analyzed: {$stats['total']}");
            $this->info("  ✓ Paid Documents: {$stats['paid']}");
            $this->info("  ⚠️  Exception Status (5): {$stats['exception_status_5']}");
            $this->warn("  ❌ Candidates for Deletion: {$stats['candidates_delete']}");

            $this->line('');

            // If no candidates, finish early
            if ($stats['candidates_delete'] === 0) {
                $this->line('');
                $this->info('✅ All documents are valid. No cleanup needed.');
                $this->line('');

                return 0;
            }

            // Show candidates for deletion
            $this->showCandidatesForDeletion();

            // If dry-run, skip deletion
            if ($isDryRun) {
                $this->line('');
                $this->warn('🔍 Dry-run complete. No documents were deleted.');
                $this->line('');

                return 0;
            }

            // Confirm deletion
            $this->line('');
            if (! $this->option('force')) {
                if (! $this->confirm('Delete these documents?')) {
                    $this->warn('⚠️  Deletion cancelled.');

                    return 0;
                }
            }

            // Step 2: Perform cleanup
            $this->line('');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('🗑️  Performing cleanup');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->line('');

            $deletedCount = $this->deleteUnpaidDocuments();

            // Final results
            $this->line('');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('✅ CLEANUP COMPLETE');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->line('');

            $this->info("🗑️  Documents Deleted: {$deletedCount}");
            $this->line('');

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Validation failed: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return 1;
        }
    }

    /**
     * Validate and analyze documents
     */
    private function validateAndAnalyzeDocuments(): array
    {
        try {
            $config = [
                'host' => env('DB_HOST_PRESTASHOP', '192.168.1.120'),
                'port' => env('DB_PORT_PRESTASHOP', 3306),
                'database' => env('DB_DATABASE_PRESTASHOP', 'alvarez_ana'),
                'username' => env('DB_USERNAME_PRESTASHOP', 'alvarez_ana'),
                'password' => env('DB_PASSWORD_PRESTASHOP', ''),
            ];

            // Get all documents with order_id
            $documents = Document::whereNotNull('order_id')
                ->where('order_id', '>', 0)
                ->with('order')
                ->get();

            $total = $documents->count();
            $paid = 0;
            $exceptionStatus5 = 0;
            $candidatesDelete = 0;

            foreach ($documents as $document) {
                // Check if order is paid in Prestashop
                $isPaid = $this->isOrderPaidInPrestashop($document->order_id, $config);

                if ($isPaid) {
                    $paid++;
                } elseif ($document->status_id === 5) {
                    $exceptionStatus5++;
                } else {
                    $candidatesDelete++;
                }
            }

            $this->info("Found {$total} documents with order_id");

            return [
                'total' => $total,
                'paid' => $paid,
                'exception_status_5' => $exceptionStatus5,
                'candidates_delete' => $candidatesDelete,
            ];
        } catch (\Exception $e) {
            $this->error('Error during validation: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if order is paid in Prestashop
     * METODOLOGÍA: Busca CUALQUIER estado pagado en historial (no solo el último)
     * Alineado con: OrderHistory.php línea 294: if ($new_os->id == 2 || $new_os->paid == 1)
     */
    private function isOrderPaidInPrestashop(int $orderId, array $config): bool
    {
        try {
            // Query simple: Busca si la orden tuvo estado de pago (ID=2)
            $query = "SELECT 1 FROM aalv_order_history oh
                      WHERE oh.id_order = {$orderId}
                        AND oh.id_order_state = 2
                      LIMIT 1";

            $output = shell_exec("mysql -h {$config['host']} -u {$config['username']} -p'{$config['password']}' {$config['database']} -sN -e \"".addslashes($query).'" 2>/dev/null');

            return ! empty($output);
        } catch (\Exception $e) {
            \Log::error("Failed to check order {$orderId} payment status: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Show candidates for deletion
     */
    private function showCandidatesForDeletion(): void
    {
        try {
            $documents = Document::whereNotNull('order_id')
                ->where('order_id', '>', 0)
                ->where('status_id', '<>', 5)
                ->get();

            $config = [
                'host' => env('DB_HOST_PRESTASHOP', '192.168.1.120'),
                'port' => env('DB_PORT_PRESTASHOP', 3306),
                'database' => env('DB_DATABASE_PRESTASHOP', 'alvarez_ana'),
                'username' => env('DB_USERNAME_PRESTASHOP', 'alvarez_ana'),
                'password' => env('DB_PASSWORD_PRESTASHOP', ''),
            ];
            $candidates = [];

            foreach ($documents as $document) {
                $isPaid = $this->isOrderPaidInPrestashop($document->order_id, $config);

                if (! $isPaid) {
                    $candidates[] = $document;
                }
            }

            if (! empty($candidates)) {
                $this->line('');
                $this->warn('⚠️  Documents to be deleted:');
                $this->line('');

                foreach (array_slice($candidates, 0, 20) as $doc) {
                    $this->line("  • ID: {$doc->id} | UID: {$doc->uid} | Order: {$doc->order_id} | Status: {$doc->status_id}");
                }

                if (count($candidates) > 20) {
                    $this->warn('  ... and '.(count($candidates) - 20).' more');
                }
            }
        } catch (\Exception $e) {
            $this->error('Error showing candidates: '.$e->getMessage());
        }
    }

    /**
     * Delete unpaid documents (except status_id = 5)
     */
    private function deleteUnpaidDocuments(): int
    {
        try {
            $config = [
                'host' => env('DB_HOST_PRESTASHOP', '192.168.1.120'),
                'port' => env('DB_PORT_PRESTASHOP', 3306),
                'database' => env('DB_DATABASE_PRESTASHOP', 'alvarez_ana'),
                'username' => env('DB_USERNAME_PRESTASHOP', 'alvarez_ana'),
                'password' => env('DB_PASSWORD_PRESTASHOP', ''),
            ];
            $deletedCount = 0;

            $documents = Document::whereNotNull('order_id')
                ->where('order_id', '>', 0)
                ->where('status_id', '<>', 5)
                ->get();

            $bar = $this->output->createProgressBar(count($documents));
            $bar->start();

            foreach ($documents as $document) {
                try {
                    $isPaid = $this->isOrderPaidInPrestashop($document->order_id, $config);

                    if (! $isPaid) {
                        // Delete document and its products
                        $document->products()->delete();
                        $document->delete();
                        $deletedCount++;
                    }
                } catch (\Exception $e) {
                    \Log::error("Failed to delete document {$document->id}: ".$e->getMessage());
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            return $deletedCount;
        } catch (\Exception $e) {
            $this->error('Error during deletion: '.$e->getMessage());
            throw $e;
        }
    }
}
