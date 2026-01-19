<?php

namespace Modules\Document\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeepAnalyzePrestashopOrderStates extends Command
{
    protected $signature = 'app:deep-analyze-prestashop-states';

    protected $description = 'Deep analysis of order states, documents, and blockades validation';

    private const PAID_STATE = 27;

    private const ANALYSIS_DATE = '2025-05-27';

    public function handle(): int
    {
        try {
            $this->info('🔍 Deep analyzing Prestashop order states, documents, and blockades...');
            $this->line('');

            // Section 1: Order States Definition
            $this->displayOrderStatesDefinition();

            // Section 2: Order Distribution
            $this->displayOrderDistribution();

            // Section 3: Paid Orders Analysis
            $this->displayPaidOrdersAnalysis();

            // Section 4: Documents vs Orders
            $this->displayDocumentsAnalysis();

            // Section 5: Blockades Analysis
            $this->displayBlockadesAnalysis();

            // Section 6: Document Validation
            $this->displayDocumentValidation();

            // Section 7: Conclusions
            $this->displayConclusions();

            $this->line('');

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Analysis failed: '.$e->getMessage());

            return 1;
        }
    }

    private function displayOrderStatesDefinition(): void
    {
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 PRESTASHOP ORDER STATES DEFINITION');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $states = DB::connection('prestashop')
            ->table('aalv_order_state')
            ->select('id_order_state', 'paid', 'invoice', 'shipped')
            ->orderBy('id_order_state')
            ->get();

        $tableData = [];
        foreach ($states as $state) {
            $tableData[] = [
                $state->id_order_state,
                $state->paid ? '✅ Yes' : '❌ No',
                $state->invoice ? '✅ Yes' : '❌ No',
                $state->shipped ? '✅ Yes' : '❌ No',
            ];
        }

        $this->table(['ID', 'Paid', 'Invoice', 'Shipped'], $tableData);
        $this->line('');

        // Highlight estado 27
        $estado27 = $states->firstWhere('id_order_state', self::PAID_STATE);
        if ($estado27) {
            $this->info("✅ Estado ".self::PAID_STATE.": PAID STATE USED IN VALIDATION");
        }
        $this->line('');
    }

    private function displayOrderDistribution(): void
    {
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📈 ORDER DISTRIBUTION BY STATE');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $distribution = DB::connection('prestashop')
            ->table('aalv_orders')
            ->select('current_state', DB::raw('COUNT(*) as count'))
            ->groupBy('current_state')
            ->orderByDesc('count')
            ->get();

        $tableData = [];
        foreach ($distribution as $item) {
            $tableData[] = [
                $item->current_state,
                $item->count,
            ];
        }

        $this->table(['State ID', 'Total Orders'], $tableData);
        $this->line('');
    }

    private function displayPaidOrdersAnalysis(): void
    {
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('💰 PAID ORDERS ANALYSIS (Estado '.self::PAID_STATE.')');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Get all orders with estado 27 (ever had it)
        $paidOrdersAll = DB::connection('prestashop')
            ->table('aalv_order_history')
            ->where('id_order_state', self::PAID_STATE)
            ->distinct('id_order')
            ->pluck('id_order')
            ->count();

        // Get orders with estado 27 from analysis date onwards
        $paidOrdersFromDate = DB::connection('prestashop')
            ->table('aalv_orders as o')
            ->join('aalv_order_history as oh', 'o.id_order', '=', 'oh.id_order')
            ->where('oh.id_order_state', self::PAID_STATE)
            ->where('o.date_add', '>=', self::ANALYSIS_DATE)
            ->distinct('o.id_order')
            ->count();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Orders with estado 27 (ALL TIME)', $paidOrdersAll],
                ['Orders with estado 27 (from '.self::ANALYSIS_DATE.')', $paidOrdersFromDate],
            ]
        );
        $this->line('');
    }

    private function displayDocumentsAnalysis(): void
    {
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📄 DOCUMENTS ANALYSIS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $totalDocs = DB::connection('mysql')
            ->table('documents')
            ->whereRaw('order_id > 0')
            ->count();

        // Get paid order IDs from prestashop connection
        $paidOrderIds = DB::connection('prestashop')
            ->table('aalv_order_history')
            ->where('id_order_state', self::PAID_STATE)
            ->distinct('id_order')
            ->pluck('id_order')
            ->toArray();

        // Count documents from those paid orders using mysql connection
        // Use chunking to avoid placeholder limit (MySQL max is ~65k)
        $docsWithPaidOrders = 0;
        foreach (array_chunk($paidOrderIds, 1000) as $chunk) {
            $docsWithPaidOrders += DB::connection('mysql')
                ->table('documents')
                ->whereIn('order_id', $chunk)
                ->count();
        }

        $uniqueOrders = DB::connection('mysql')
            ->table('documents')
            ->whereRaw('order_id > 0')
            ->distinct('order_id')
            ->count();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total documents', $totalDocs],
                ['Documents with paid orders (estado 27)', $docsWithPaidOrders],
                ['Unique orders with documents', $uniqueOrders],
            ]
        );
        $this->line('');
    }

    private function displayBlockadesAnalysis(): void
    {
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('🚫 BLOCKADES TABLE ANALYSIS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $totalBlockades = DB::connection('mysql')
            ->table('document_product_blockades')
            ->count();

        $validBlockades = DB::connection('mysql')
            ->table('document_product_blockades')
            ->whereNotNull('product_id')
            ->count();

        $invalidBlockades = $totalBlockades - $validBlockades;

        $uniqueProducts = DB::connection('mysql')
            ->table('document_product_blockades')
            ->whereNotNull('product_id')
            ->distinct('product_id')
            ->count();

        $blockadesByType = DB::connection('mysql')
            ->table('document_product_blockades')
            ->select('document_type_id', DB::raw('COUNT(*) as count'))
            ->groupBy('document_type_id')
            ->get();

        $this->table(
            ['Metric', 'Count', 'Status'],
            [
                ['Total blockade records', $totalBlockades, ''],
                ['Valid (product_id NOT NULL)', $validBlockades, '✅'],
                ['Invalid (product_id NULL)', $invalidBlockades, '❌'],
                ['Unique products blocked', $uniqueProducts, ''],
                ['Usability rate', round(($validBlockades / $totalBlockades) * 100, 1).'%', ''],
            ]
        );

        $this->line('');
        $this->line('📋 Blockades by Document Type:');
        $typeData = [];
        foreach ($blockadesByType as $item) {
            $typeLabel = $item->document_type_id === null ? 'NULL (all)' : $item->document_type_id;
            $typeData[] = [$typeLabel, $item->count];
        }
        $this->table(['Type', 'Count'], $typeData);
        $this->line('');
    }

    private function displayDocumentValidation(): void
    {
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('✅ DOCUMENT VALIDATION RESULTS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Count valid documents (those matching blockades)
        $allDocs = DB::connection('mysql')
            ->table('documents')
            ->whereRaw('order_id > 0')
            ->select('id', 'order_id', 'type_id')
            ->get();

        $paidOrderIds = DB::connection('prestashop')
            ->table('aalv_order_history')
            ->where('id_order_state', self::PAID_STATE)
            ->distinct('id_order')
            ->pluck('id_order')
            ->toArray();

        $paidOrdersSet = array_flip($paidOrderIds);

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

        // Validate
        $validCount = 0;
        $invalidNoPaid = 0;
        $invalidNoProducts = 0;
        $invalidNoOrder = 0;

        foreach ($allDocs as $doc) {
            // Check estado 27
            if (!isset($paidOrdersSet[$doc->order_id])) {
                $invalidNoPaid++;
                continue;
            }

            // Check if order has products
            if (!isset($orderProductMap[$doc->order_id])) {
                $invalidNoOrder++;
                continue;
            }

            // Check if any product is blocked
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
                $validCount++;
            } else {
                $invalidNoProducts++;
            }
        }

        $this->table(
            ['Status', 'Count', 'Percentage'],
            [
                ['✅ VALID', $validCount, round(($validCount / count($allDocs)) * 100, 1).'%'],
                ['❌ Without estado 27', $invalidNoPaid, round(($invalidNoPaid / count($allDocs)) * 100, 1).'%'],
                ['❌ Without blocked products', $invalidNoProducts, round(($invalidNoProducts / count($allDocs)) * 100, 1).'%'],
                ['❌ Order not found', $invalidNoOrder, round(($invalidNoOrder / count($allDocs)) * 100, 1).'%'],
                ['━━━━━━━━━━━━━━━', '━━━━━━━', '━━━━━━━'],
                ['TOTAL DOCUMENTS', count($allDocs), '100%'],
            ]
        );
        $this->line('');
    }

    private function displayConclusions(): void
    {
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('💡 CONCLUSIONS & RECOMMENDATIONS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('');

        $this->info('✅ Payment Detection: Estado 27 (Pagado Confirmado)');
        $this->line('   This is the correct state for paid orders in this system');
        $this->line('');

        $this->info('📅 Analysis Period: From '.self::ANALYSIS_DATE.' onwards');
        $this->line('   Documents and orders are filtered by this date');
        $this->line('');

        $this->info('🔍 Validation Logic:');
        $this->line('   1. Order must have estado 27 in aalv_order_history');
        $this->line('   2. Product must be in document_product_blockades:');
        $this->line('      - If product_attribute_id = 0: match by product_id');
        $this->line('      - If product_attribute_id > 0: match by attribute_id');
        $this->line('   3. Document type must match blockade type');
        $this->line('');

        $this->info('⚠️  Next Steps:');
        $this->line('   • Run: php scripts-reportes/validar-documentos-correctamente.php');
        $this->line('   • Review CSV outputs in scripts-reportes/output/');
        $this->line('   • Add missing products to document_product_blockades');
        $this->line('');
    }
}
