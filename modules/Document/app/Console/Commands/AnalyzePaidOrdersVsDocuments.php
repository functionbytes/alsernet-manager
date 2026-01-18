<?php

namespace Modules\Document\Console\Commands;

use Illuminate\Console\Command;
use Modules\Document\Entities\Document;

class AnalyzePaidOrdersVsDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:analyze-paid-orders-vs-documents {--show-details : Show detailed list of documents to delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compare paid orders in Prestashop with existing documents - full analysis';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->info('🔍 Starting analysis of paid orders vs documents...');
            $this->line('');

            // Get all statistics
            $stats = $this->analyzeOrders();

            // Display results
            $this->displayResults($stats);

            if ($this->option('show-details')) {
                $this->displayDetailedDocumentsToDelete();
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Analysis failed: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return 1;
        }
    }

    /**
     * Analyze orders and documents
     */
    private function analyzeOrders(): array
    {
        try {
            $config = [
                'host' => env('DB_HOST_PRESTASHOP', '192.168.1.120'),
                'port' => env('DB_PORT_PRESTASHOP', 3306),
                'database' => env('DB_DATABASE_PRESTASHOP', 'alvarez_ana'),
                'username' => env('DB_USERNAME_PRESTASHOP', 'alvarez_ana'),
                'password' => env('DB_PASSWORD_PRESTASHOP', ''),
            ];

            // Query 1: Get all paid orders from Prestashop
            $paidOrders = $this->getPaidOrdersFromPrestashop($config);
            $totalPaidOrders = count($paidOrders);

            // Query 2: Get all documents with order_id
            $documents = Document::whereNotNull('order_id')
                ->where('order_id', '>', 0)
                ->get();
            $totalDocuments = $documents->count();

            // Query 3: Count paid orders with documents
            $paidOrdersWithDocuments = 0;
            $paidOrdersWithoutDocuments = 0;

            foreach ($paidOrders as $orderId) {
                $hasDocument = $documents->where('order_id', $orderId)->count() > 0;
                if ($hasDocument) {
                    $paidOrdersWithDocuments++;
                } else {
                    $paidOrdersWithoutDocuments++;
                }
            }

            // Query 4: Count documents without paid order
            $documentsWithoutPaidOrder = 0;
            $exceptionStatus5 = 0;
            $documentsToDelete = 0;

            foreach ($documents as $document) {
                $isPaid = in_array($document->order_id, $paidOrders);

                if (! $isPaid) {
                    $documentsWithoutPaidOrder++;

                    if ($document->status_id === 5) {
                        $exceptionStatus5++;
                    } else {
                        $documentsToDelete++;
                    }
                }
            }

            return [
                'total_paid_orders' => $totalPaidOrders,
                'total_documents' => $totalDocuments,
                'paid_orders_with_documents' => $paidOrdersWithDocuments,
                'paid_orders_without_documents' => $paidOrdersWithoutDocuments,
                'documents_without_paid_order' => $documentsWithoutPaidOrder,
                'exception_status_5' => $exceptionStatus5,
                'documents_to_delete' => $documentsToDelete,
                'paid_order_ids' => $paidOrders,
            ];
        } catch (\Exception $e) {
            $this->error('Error analyzing orders: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all paid orders from Prestashop
     * METODOLOGÍA: Busca CUALQUIER estado pagado en historial (no solo el último)
     * Alineado con: OrderHistory.php línea 294: if ($new_os->id == 2 || $new_os->paid == 1)
     */
    private function getPaidOrdersFromPrestashop(array $config): array
    {
        try {
            // Query simple: Busca órdenes con estado de pago (ID=2)
            $query = 'SELECT DISTINCT o.id_order
                      FROM aalv_orders o
                      WHERE EXISTS (
                          SELECT 1
                          FROM aalv_order_history oh
                          WHERE oh.id_order = o.id_order
                            AND oh.id_order_state = 2
                      )
                      ORDER BY o.id_order ASC';

            $output = shell_exec("mysql -h {$config['host']} -u {$config['username']} -p'{$config['password']}' {$config['database']} -sN -e \"".addslashes($query).'" 2>/dev/null');

            if (! $output) {
                return [];
            }

            $paidOrders = [];
            $lines = explode("\n", trim($output));

            foreach ($lines as $line) {
                if (! empty($line)) {
                    $paidOrders[] = (int) trim($line);
                }
            }

            return $paidOrders;
        } catch (\Exception $e) {
            \Log::error('Failed to fetch paid orders: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Display results in formatted table
     */
    private function displayResults(array $stats): void
    {
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 ANALYSIS RESULTS: PAID ORDERS vs DOCUMENTS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('');

        // Paid Orders Summary
        $this->info('🛒 Prestashop Paid Orders:');
        $this->line("  Total Paid Orders: {$stats['total_paid_orders']}");
        $this->line('');

        // Documents Summary
        $this->info('📄 Our Documents:');
        $this->line("  Total Documents: {$stats['total_documents']}");
        $this->line('');

        // Concordance Analysis
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('✅ MATCHES: Paid Orders WITH Documents');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("  Paid Orders with Documents: {$stats['paid_orders_with_documents']}");

        $percentage = $stats['total_paid_orders'] > 0
            ? round(($stats['paid_orders_with_documents'] / $stats['total_paid_orders']) * 100, 2)
            : 0;

        $this->line("  Coverage: {$percentage}% of paid orders have documents");
        $this->line('');

        // Missing Documents
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->warn('⚠️  MISSING: Paid Orders WITHOUT Documents');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->warn("  Paid Orders without Documents: {$stats['paid_orders_without_documents']}");
        $this->line('  → These orders should have documents but don\'t');
        $this->line('');

        // Orphan Documents
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->error('❌ ORPHAN: Documents WITHOUT Paid Orders');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->error("  Documents without Paid Order: {$stats['documents_without_paid_order']}");
        $this->line('');

        // Breakdown of orphan documents
        $this->info('  Breakdown:');
        $this->line("    • Exception (status_id = 5): {$stats['exception_status_5']} (KEEP)");
        $this->error("    • Candidates for Deletion: {$stats['documents_to_delete']} (DELETE)");
        $this->line('');

        // Summary
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📋 SUMMARY & RECOMMENDATIONS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('');

        $this->info('Document Status:');
        $this->line("  ✅ Well-matched: {$stats['paid_orders_with_documents']} / {$stats['total_paid_orders']}");
        $this->line("  ⚠️  Need attention: {$stats['paid_orders_without_documents']} paid orders without docs");
        $this->error("  ❌ To be cleaned: {$stats['documents_to_delete']} documents");
        $this->line('');

        $this->info('Actions:');
        if ($stats['paid_orders_without_documents'] > 0) {
            $this->line("  1. Create {$stats['paid_orders_without_documents']} missing documents");
            $this->line('     → Run: php artisan app:create-blocked-product-documents --force');
        }

        if ($stats['documents_to_delete'] > 0) {
            $this->line("  2. Delete {$stats['documents_to_delete']} orphan documents");
            $this->line('     → Run: php artisan app:validate-and-cleanup-documents --force --dry-run');
            $this->line('     → Then: php artisan app:validate-and-cleanup-documents --force');
        }

        if ($stats['paid_orders_without_documents'] === 0 && $stats['documents_to_delete'] === 0) {
            $this->info('  ✅ All good! Everything is in sync.');
        }

        $this->line('');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }

    /**
     * Display detailed list of documents to delete
     */
    private function displayDetailedDocumentsToDelete(): void
    {
        try {
            $config = [
                'host' => env('DB_HOST_PRESTASHOP', '192.168.1.120'),
                'port' => env('DB_PORT_PRESTASHOP', 3306),
                'database' => env('DB_DATABASE_PRESTASHOP', 'alvarez_ana'),
                'username' => env('DB_USERNAME_PRESTASHOP', 'alvarez_ana'),
                'password' => env('DB_PASSWORD_PRESTASHOP', ''),
            ];
            $paidOrders = $this->getPaidOrdersFromPrestashop($config);

            $documents = Document::whereNotNull('order_id')
                ->where('order_id', '>', 0)
                ->where('status_id', '<>', 5)
                ->get();

            $candidatesForDeletion = [];

            foreach ($documents as $doc) {
                if (! in_array($doc->order_id, $paidOrders)) {
                    $candidatesForDeletion[] = $doc;
                }
            }

            if (empty($candidatesForDeletion)) {
                $this->info('No documents to delete.');

                return;
            }

            $this->line('');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->error('📄 DETAILED LIST: Documents to Delete');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->line('');

            $this->table(
                ['ID', 'UID', 'Order ID', 'Status', 'Created At'],
                array_map(function ($doc) {
                    return [
                        $doc->id,
                        substr($doc->uid, 0, 12).'...',
                        $doc->order_id,
                        $doc->status_id,
                        $doc->created_at->format('Y-m-d H:i:s'),
                    ];
                }, array_slice($candidatesForDeletion, 0, 50))
            );

            if (count($candidatesForDeletion) > 50) {
                $this->line('  ... and '.(count($candidatesForDeletion) - 50).' more');
            }

            $this->line('');
        } catch (\Exception $e) {
            $this->error('Error displaying details: '.$e->getMessage());
        }
    }
}
