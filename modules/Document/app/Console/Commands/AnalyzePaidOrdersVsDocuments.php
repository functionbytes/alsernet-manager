<?php

namespace Modules\Document\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Document\Entities\Document;

class AnalyzePaidOrdersVsDocuments extends Command
{
    protected $signature = 'app:analyze-paid-orders-vs-documents {--show-details : Show detailed list of documents}';

    protected $description = 'Compare paid orders (estado 27) with documents and blockades validation';

    private const PAID_STATE = 27;

    private const ANALYSIS_DATE = '2025-05-27';

    public function handle(): int
    {
        try {
            $this->info('🔍 Analyzing paid orders vs documents...');
            $this->line('');

            // Get statistics
            $stats = $this->analyzeOrders();

            // Display results
            $this->displayResults($stats);

            if ($this->option('show-details')) {
                $this->displayDetailedAnalysis($stats);
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Analysis failed: '.$e->getMessage());

            return 1;
        }
    }

    private function analyzeOrders(): array
    {
        // Get all paid orders (estado 27)
        $paidOrderIds = DB::connection('prestashop')
            ->table('aalv_order_history')
            ->where('id_order_state', self::PAID_STATE)
            ->distinct('id_order')
            ->pluck('id_order')
            ->toArray();

        $paidOrdersSet = array_flip($paidOrderIds);

        // Get paid orders from analysis date
        $paidOrdersFromDate = DB::connection('prestashop')
            ->table('aalv_orders as o')
            ->join('aalv_order_history as oh', 'o.id_order', '=', 'oh.id_order')
            ->where('oh.id_order_state', self::PAID_STATE)
            ->where('o.date_add', '>=', self::ANALYSIS_DATE)
            ->distinct('o.id_order')
            ->pluck('o.id_order')
            ->toArray();

        // Get all documents
        $allDocuments = Document::whereRaw('order_id > 0')
            ->select('id', 'order_id', 'uid', 'type_id', 'status_id', 'created_at')
            ->get();

        // Get blockades
        $blockades = DB::connection('mysql')
            ->table('document_product_blockades')
            ->select('product_id', 'product_attribute_id', 'document_type_id')
            ->get();

        // Build blockade map
        $blockadeMap = [];
        foreach ($blockades as $b) {
            $attr = $b->product_attribute_id;
            $type = $b->document_type_id;

            if ($attr == 0 || $attr === null) {
                $key = "prod:{$b->product_id}|{$type}";
                $blockadeMap[$key] = true;
            } else {
                $key = "attr:{$attr}|{$type}";
                $blockadeMap[$key] = true;
            }
        }

        // Get order products
        $orderProducts = DB::connection('prestashop')
            ->table('aalv_order_detail')
            ->select('id_order', 'product_id', 'product_attribute_id')
            ->get();

        $orderProductMap = [];
        foreach ($orderProducts as $item) {
            if (!isset($orderProductMap[$item->id_order])) {
                $orderProductMap[$item->id_order] = [];
            }
            $orderProductMap[$item->id_order][] = [
                'product_id' => $item->product_id,
                'attribute_id' => $item->product_attribute_id,
            ];
        }

        // Analyze documents
        $validDocs = 0;
        $docsWithoutEstado27 = 0;
        $docsWithoutBlockedProducts = 0;
        $docsOrphaned = 0;
        $docsValid = [];
        $docsInvalid = [];

        foreach ($allDocuments as $doc) {
            // Check estado 27
            if (!isset($paidOrdersSet[$doc->order_id])) {
                $docsWithoutEstado27++;
                $docsInvalid[] = [
                    'id' => $doc->id,
                    'order_id' => $doc->order_id,
                    'reason' => 'Without estado 27',
                    'status' => $doc->status_id,
                    'created_at' => $doc->created_at,
                ];
                continue;
            }

            // Check if order has products
            if (!isset($orderProductMap[$doc->order_id])) {
                $docsOrphaned++;
                $docsInvalid[] = [
                    'id' => $doc->id,
                    'order_id' => $doc->order_id,
                    'reason' => 'Order not found',
                    'status' => $doc->status_id,
                    'created_at' => $doc->created_at,
                ];
                continue;
            }

            // Check if product is blocked
            $hasBlocked = false;
            foreach ($orderProductMap[$doc->order_id] as $product) {
                $attrId = $product['attribute_id'];
                $prodId = $product['product_id'];
                $typeId = $doc->type_id;

                if ($attrId == 0) {
                    $key = "prod:{$prodId}|{$typeId}";
                    if (isset($blockadeMap[$key])) {
                        $hasBlocked = true;
                        break;
                    }
                } else {
                    $key = "attr:{$attrId}|{$typeId}";
                    if (isset($blockadeMap[$key])) {
                        $hasBlocked = true;
                        break;
                    }
                }
            }

            if ($hasBlocked) {
                $validDocs++;
                $docsValid[] = [
                    'id' => $doc->id,
                    'order_id' => $doc->order_id,
                    'status' => $doc->status_id,
                    'created_at' => $doc->created_at,
                ];
            } else {
                $docsWithoutBlockedProducts++;
                $docsInvalid[] = [
                    'id' => $doc->id,
                    'order_id' => $doc->order_id,
                    'reason' => 'Without blocked products',
                    'status' => $doc->status_id,
                    'created_at' => $doc->created_at,
                ];
            }
        }

        return [
            'total_paid_orders_all' => count($paidOrderIds),
            'total_paid_orders_from_date' => count($paidOrdersFromDate),
            'total_documents' => $allDocuments->count(),
            'valid_documents' => $validDocs,
            'invalid_documents' => $allDocuments->count() - $validDocs,
            'docs_without_estado27' => $docsWithoutEstado27,
            'docs_without_blocked_products' => $docsWithoutBlockedProducts,
            'docs_orphaned' => $docsOrphaned,
            'valid_docs' => $docsValid,
            'invalid_docs' => $docsInvalid,
            'total_blockades' => $blockades->count(),
            'valid_blockades' => $blockades->whereNotNull('product_id')->count(),
        ];
    }

    private function displayResults(array $stats): void
    {
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 PAID ORDERS vs DOCUMENTS ANALYSIS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('');

        // Paid Orders
        $this->info('💰 PAID ORDERS (Estado '.self::PAID_STATE.')');
        $this->table(
            ['Metric', 'Count'],
            [
                ['ALL TIME', $stats['total_paid_orders_all']],
                ['From '.self::ANALYSIS_DATE, $stats['total_paid_orders_from_date']],
            ]
        );
        $this->line('');

        // Documents
        $this->info('📄 DOCUMENTS STATUS');
        $validPercentage = $stats['total_documents'] > 0
            ? round(($stats['valid_documents'] / $stats['total_documents']) * 100, 1)
            : 0;

        $this->table(
            ['Status', 'Count', 'Percentage'],
            [
                ['✅ VALID', $stats['valid_documents'], $validPercentage.'%'],
                ['❌ INVALID', $stats['invalid_documents'], (100 - $validPercentage).'%'],
                ['TOTAL', $stats['total_documents'], '100%'],
            ]
        );
        $this->line('');

        // Invalid Documents Breakdown
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->warn('⚠️  INVALID DOCUMENTS BREAKDOWN');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->table(
            ['Reason', 'Count'],
            [
                ['Without estado 27', $stats['docs_without_estado27']],
                ['Without blocked products', $stats['docs_without_blocked_products']],
                ['Order not found', $stats['docs_orphaned']],
            ]
        );
        $this->line('');

        // Blockades Status
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('🚫 BLOCKADES TABLE');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $blockadeUsability = $stats['total_blockades'] > 0
            ? round(($stats['valid_blockades'] / $stats['total_blockades']) * 100, 1)
            : 0;

        $this->table(
            ['Metric', 'Count', 'Status'],
            [
                ['Total blockade records', $stats['total_blockades'], ''],
                ['Valid (product_id NOT NULL)', $stats['valid_blockades'], '✅'],
                ['Invalid (product_id NULL)', $stats['total_blockades'] - $stats['valid_blockades'], '❌'],
                ['Usability', $blockadeUsability.'%', ''],
            ]
        );
        $this->line('');

        // Recommendations
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('💡 RECOMMENDATIONS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        if ($stats['docs_without_blocked_products'] > 0) {
            $this->line("❌ {$stats['docs_without_blocked_products']} documents need blocked products added");
            $this->line('   → Add missing products to document_product_blockades');
        }

        if ($stats['docs_without_estado27'] > 0) {
            $this->line("❌ {$stats['docs_without_estado27']} documents from orders never paid");
            $this->line('   → Review/delete these documents');
        }

        if ($stats['valid_documents'] == $stats['total_documents']) {
            $this->info('✅ All documents are VALID!');
        }

        $this->line('');
    }

    private function displayDetailedAnalysis(array $stats): void
    {
        $this->line('');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📋 DETAILED: Valid Documents (first 50)');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $validTableData = array_map(function ($doc) {
            return [$doc['id'], $doc['order_id'], $doc['status'], $doc['created_at']->format('Y-m-d H:i:s')];
        }, array_slice($stats['valid_docs'], 0, 50));

        $this->table(['Doc ID', 'Order ID', 'Status', 'Created At'], $validTableData);

        if (count($stats['valid_docs']) > 50) {
            $this->line('... and '.(count($stats['valid_docs']) - 50).' more');
        }

        $this->line('');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->error('📋 DETAILED: Invalid Documents (first 50)');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $invalidTableData = array_map(function ($doc) {
            return [$doc['id'], $doc['order_id'], $doc['reason'], $doc['status'], $doc['created_at']->format('Y-m-d H:i:s')];
        }, array_slice($stats['invalid_docs'], 0, 50));

        $this->table(['Doc ID', 'Order ID', 'Reason', 'Status', 'Created At'], $invalidTableData);

        if (count($stats['invalid_docs']) > 50) {
            $this->line('... and '.(count($stats['invalid_docs']) - 50).' more');
        }

        $this->line('');
    }
}
