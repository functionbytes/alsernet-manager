<?php

namespace Modules\Document\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Document\Entities\Document;
use Modules\Document\Entities\DocumentStatus;
use Modules\Document\Entities\DocumentType;

class ValidateAndCreateDocumentsFromPaidOrders extends Command
{
    protected $signature = 'app:validate-and-create-documents-from-paid-orders {--force : Skip confirmation} {--dry-run : Show changes without applying them}';

    protected $description = 'Validate and create documents for paid orders with blocked products, delete documents for orders without estado 27 or blocked products';

    private const PAID_STATE = 27;
    private const ANALYSIS_DATE = '2026-01-01';

    public function handle(): int
    {
        try {
            $this->info('🔍 Validating and creating documents for paid orders with blocked products...');
            $this->line('');

            // Get confirmation
            if (! $this->option('force')) {
                if ($this->option('dry-run')) {
                    $this->comment('DRY RUN MODE - No changes will be made');
                } else {
                    if (! $this->confirm('This will create documents for paid orders with blocked products and delete invalid documents. Continue?')) {
                        return 0;
                    }
                }
            }

            $this->line('');

            // Step 1: Load data for validation
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('📍 STEP 1: Loading data for validation');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->line('');

            // Get orders with estado 27 from analysis date onwards
            $ordersWithStatus27 = DB::connection('prestashop')
                ->table('aalv_orders as o')
                ->join('aalv_order_history as oh', 'o.id_order', '=', 'oh.id_order')
                ->where('oh.id_order_state', self::PAID_STATE)
                ->where('o.date_add', '>=', self::ANALYSIS_DATE)
                ->where('o.id_order', '>', 0)
                ->distinct('o.id_order')
                ->pluck('o.id_order')
                ->toArray();

            $ordersWithStatus27Set = array_flip($ordersWithStatus27);
            $this->info("✓ Orders with estado 27 (from ".self::ANALYSIS_DATE."): ".count($ordersWithStatus27));

            // Get all blockades
            $blockades = DB::connection('mysql')
                ->table('document_product_blockades')
                ->select('product_id', 'product_attribute_id', 'document_type_id')
                ->get();

            // Build blockade map with attribute-aware keys
            $blockadeMap = [];
            foreach ($blockades as $b) {
                $prod = $b->product_id;
                $attr = $b->product_attribute_id;
                $type = $b->document_type_id;

                if ($attr == 0 || $attr === null) {
                    $key = "prod:{$prod}|{$type}";
                    $blockadeMap[$key] = true;
                } else {
                    $key = "attr:{$attr}|{$type}";
                    $blockadeMap[$key] = true;
                }
            }
            $this->info("✓ Blockade records: ".count($blockades));

            // Get order products with attributes
            $orderProducts = DB::connection('prestashop')
                ->table('aalv_order_detail')
                ->select('id_order', 'product_id', 'product_attribute_id')
                ->get();

            $orderProductMap = [];
            foreach ($orderProducts as $item) {
                if (! isset($orderProductMap[$item->id_order])) {
                    $orderProductMap[$item->id_order] = [];
                }
                $orderProductMap[$item->id_order][] = [
                    'product_id' => $item->product_id,
                    'attribute_id' => $item->product_attribute_id,
                ];
            }
            $this->info("✓ Orders mapped: ".count($orderProductMap));
            $this->line('');

            // Step 2: Get all existing documents
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('📍 STEP 2: Validating existing documents & syncing missing data');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->line('');

            $allDocuments = Document::whereNotNull('order_id')
                ->select('id', 'order_id', 'uid', 'type_id')
                ->get();

            $this->info("✓ Total documents: ".count($allDocuments));

            // Sync missing customer data and lang_id in existing documents
            $this->line('');
            $this->comment('🔄 Syncing missing customer data and lang_id...');
            $this->syncMissingDocumentData();
            $this->line('');

            // Validate each document using the same logic as the script
            $validDocuments = [];
            $toDelete = [];

            foreach ($allDocuments as $doc) {
                // Check 1: Does order have estado 27?
                if (! isset($ordersWithStatus27Set[$doc->order_id])) {
                    $toDelete[] = $doc;
                    continue;
                }

                // Check 2: Does order have products?
                if (! isset($orderProductMap[$doc->order_id])) {
                    $toDelete[] = $doc;
                    continue;
                }

                // Check 3: Does order have blocked products (with attribute matching)?
                $hasBlockedProduct = false;
                foreach ($orderProductMap[$doc->order_id] as $product) {
                    $prodId = $product['product_id'];
                    $attrId = $product['attribute_id'];
                    $typeId = $doc->type_id;

                    if ($attrId == 0) {
                        // Simple product: check blockade by product_id
                        $key = "prod:{$prodId}|{$typeId}";
                        if (isset($blockadeMap[$key])) {
                            $hasBlockedProduct = true;
                            break;
                        }
                    } else {
                        // Variant: check blockade by attribute_id
                        $key = "attr:{$attrId}|{$typeId}";
                        if (isset($blockadeMap[$key])) {
                            $hasBlockedProduct = true;
                            break;
                        }
                    }
                }

                if ($hasBlockedProduct) {
                    $validDocuments[] = $doc;
                } else {
                    $toDelete[] = $doc;
                }
            }

            // Get the default document type ID that will be used for new documents
            $defaultTypeId = DocumentType::where('slug', 'general')->first()?->id ?? 1;

            // Extract valid order IDs from valid documents
            $validOrderIds = [];
            foreach ($validDocuments as $doc) {
                if (! in_array($doc->order_id, $validOrderIds)) {
                    $validOrderIds[] = $doc->order_id;
                }
            }
            $validOrdersSet = array_flip($validOrderIds);

            // Find orders that SHOULD have documents (have estado 27 + products + blocked products)
            // but currently DON'T have valid documents
            // IMPORTANT: Use STRICT validation with the DEFAULT type_id to ensure created documents will be valid
            $ordersWithValidationCapacity = [];
            foreach ($ordersWithStatus27 as $orderId) {
                if (! isset($orderProductMap[$orderId])) {
                    continue; // No products in this order
                }

                // Check if any product in this order has a blocked product FOR THE DEFAULT TYPE
                // This ensures documents created with $defaultTypeId will pass validation
                $hasBlockedProduct = false;
                foreach ($orderProductMap[$orderId] as $product) {
                    $prodId = $product['product_id'];
                    $attrId = $product['attribute_id'];
                    $typeId = $defaultTypeId; // Use the SPECIFIC type that will be assigned to created documents

                    if ($attrId == 0) {
                        // Simple product: check blockade by product_id
                        $key = "prod:{$prodId}|{$typeId}";
                        if (isset($blockadeMap[$key])) {
                            $hasBlockedProduct = true;
                            break;
                        }
                    } else {
                        // Variant: check blockade by attribute_id
                        $key = "attr:{$attrId}|{$typeId}";
                        if (isset($blockadeMap[$key])) {
                            $hasBlockedProduct = true;
                            break;
                        }
                    }
                }

                if ($hasBlockedProduct) {
                    $ordersWithValidationCapacity[] = $orderId;
                }
            }

            // Find orders that should have documents but don't
            $toCreate = [];
            foreach ($ordersWithValidationCapacity as $orderId) {
                if (! isset($validOrdersSet[$orderId])) {
                    $toCreate[] = $orderId;
                }
            }

            // Step 3: Display summary
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('📊 VALIDATION SUMMARY');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

            $this->table(
                ['Metric', 'Count'],
                [
                    ['Orders that should have docs (Estado 27 + blocked)', count($ordersWithValidationCapacity)],
                    ['Valid documents currently', count($validDocuments)],
                    ['Documents to create', count($toCreate)],
                    ['Documents to delete', count($toDelete)],
                ]
            );
            $this->line('');

            if ($this->option('dry-run')) {
                $this->comment('DRY RUN: No changes will be applied. Use --force to run without confirmation.');
                return 0;
            }

            // Step 5: Create missing documents
            if (count($toCreate) > 0) {
                $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                $this->info('📍 STEP 3: Creating documents for valid orders');
                $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                $this->line('');

                $bar = $this->output->createProgressBar(count($toCreate));
                $bar->start();

                $created = 0;
                $failed = 0;
                $errors = [];

                foreach ($toCreate as $orderId) {
                    try {

                        $orderData = $this->fetchPrestashopOrderData($orderId);

                        if (! $orderData) {
                            $errors[] = "Order {$orderId}: Could not fetch order data from Prestashop";
                            $failed++;
                            $bar->advance();
                            continue;
                        }

                        $defaultStatusId = DocumentStatus::where('key', 'awaiting_documents')->first()?->id ?? 2;

                        $document = Document::create([
                            'uid' => 'doc-'.$orderId.'-'.time().'-'.rand(1000, 9999),
                            'type_id' => $defaultTypeId,
                            'source_id' => 3,
                            'sync_id' => 2,
                            'load_id' => 2,
                            'upload_id' => 1,
                            'status_id' => $defaultStatusId,
                            'validation_status' => 'pending',
                            'lang_id' => $orderData['id_lang'] ?? 1,
                            'customer_id' => $orderData['id_customer'] ?? null,
                            'customer_firstname' => $orderData['firstname'] ?? null,
                            'customer_lastname' => $orderData['lastname'] ?? null,
                            'customer_email' => $orderData['email'] ?? null,
                            'order_id' => $orderId,
                            'order_reference' => $orderData['reference'] ?? null,
                            'order_date' => $orderData['date_add'] ?? null,
                            'confirmed_at' => null,
                            'current_stage' => 1,
                            'total_stages' => 1,
                        ]);

                        $created++;
                    } catch (\Exception $e) {
                        $errors[] = "Order {$orderId}: ".$e->getMessage();
                        $failed++;
                    }

                    $bar->advance();
                }

                $bar->finish();
                $this->newLine();
                $this->newLine();

                $this->info("✓ Created: {$created}");
                if ($failed > 0) {
                    $this->error("✗ Failed: {$failed}");
                }

                if (! empty($errors)) {
                    $this->line('');
                    $this->warn('⚠️  Errors:');
                    foreach (array_slice($errors, 0, 10) as $error) {
                        $this->error("  • {$error}");
                    }
                    if (count($errors) > 10) {
                        $this->error('  ... and '.(count($errors) - 10).' more');
                    }
                }

                $this->line('');
            }

            // Step 6: Delete documents for invalid orders
            if (count($toDelete) > 0) {
                $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                $this->info('📍 STEP 4: Deleting documents for invalid orders');
                $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                $this->line('');

                $bar = $this->output->createProgressBar(count($toDelete));
                $bar->start();

                $deleted = 0;
                $failed = 0;

                foreach ($toDelete as $doc) {
                    try {
                        $doc->delete();
                        $deleted++;
                    } catch (\Exception $e) {
                        $failed++;
                    }

                    $bar->advance();
                }

                $bar->finish();
                $this->newLine();
                $this->newLine();

                $this->info("✓ Deleted: {$deleted}");
                if ($failed > 0) {
                    $this->error("✗ Failed: {$failed}");
                }

                $this->line('');
            }

            // Final summary
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('✅ VALIDATION AND CREATION COMPLETE');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->line('');

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Operation failed: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return 1;
        }
    }

    private function fetchPrestashopOrderData($orderId): ?array
    {
        try {
            $order = DB::connection('prestashop')
                ->table('aalv_orders')
                ->where('id_order', $orderId)
                ->select('id_customer', 'id_lang', 'reference', 'date_add')
                ->first();

            if (! $order) {
                return null;
            }

            $customer = null;
            if ($order->id_customer) {
                $customer = DB::connection('prestashop')
                    ->table('aalv_customer')
                    ->where('id_customer', $order->id_customer)
                    ->select('firstname', 'lastname', 'email')
                    ->first();
            }

            return [
                'id_customer' => $order->id_customer,
                'id_lang' => $order->id_lang ?? 1,
                'reference' => $order->reference,
                'date_add' => $order->date_add,
                'firstname' => $customer?->firstname,
                'lastname' => $customer?->lastname,
                'email' => $customer?->email,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    private function syncMissingDocumentData(): void
    {
        try {
            // Find documents with missing customer data or lang_id
            $docsToSync = Document::whereNotNull('order_id')
                ->where(function ($q) {
                    $q->whereNull('customer_firstname')
                      ->orWhereNull('customer_lastname')
                      ->orWhereNull('customer_email')
                      ->orWhereNull('order_reference')
                      ->orWhereNull('lang_id');
                })
                ->select('id', 'order_id')
                ->get();

            if ($docsToSync->isEmpty()) {
                $this->info('  ✓ All documents have complete data');
                return;
            }

            $synced = 0;
            $failed = 0;

            foreach ($docsToSync as $doc) {
                try {
                    $orderData = $this->fetchPrestashopOrderData($doc->order_id);
                    if (! $orderData) {
                        $failed++;
                        continue;
                    }

                    $doc->update([
                        'lang_id' => $orderData['id_lang'],
                        'customer_firstname' => $orderData['firstname'],
                        'customer_lastname' => $orderData['lastname'],
                        'customer_email' => $orderData['email'],
                        'order_reference' => $orderData['reference'],
                    ]);

                    $synced++;
                } catch (\Exception $e) {
                    $failed++;
                }
            }

            if ($synced > 0) {
                $this->info("  ✓ Synced: {$synced} documents");
            }
            if ($failed > 0) {
                $this->warn("  ⚠️  Failed: {$failed} documents");
            }
        } catch (\Exception $e) {
            $this->warn("  ⚠️  Sync error: ".$e->getMessage());
        }
    }
}
