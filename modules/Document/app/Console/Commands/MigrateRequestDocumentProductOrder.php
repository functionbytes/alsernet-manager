<?php

namespace Modules\Document\Console\Commands;

use Illuminate\Console\Command;
use Modules\Document\Entities\Document;
use Modules\Prestashop\Entities\Order as PrestashopOrder;

class MigrateRequestDocumentProductOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-request-document-products-orders {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync document data and products from Prestashop orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Confirmation
        if (! $this->option('force')) {
            if (! $this->confirm('This will sync document data with Prestashop orders. Continue?')) {
                return;
            }
        }

        try {
            $this->info('🔄 Starting document-order synchronization...');
            $this->line('');

            // Get all documents with order_id
            $documents = Document::whereNotNull('order_id')
                ->orderBy('id')
                ->get();

            $total = $documents->count();
            $this->info("Found {$total} documents to sync");

            if ($total === 0) {
                $this->warn('No documents with order_id found');

                return;
            }

            // Create progress bar
            $bar = $this->output->createProgressBar($total);
            $bar->start();

            $synced = 0;
            $skipped = 0;
            $errors = [];

            foreach ($documents as $document) {
                try {
                    // Get the Prestashop order
                    $order = PrestashopOrder::find($document->order_id);

                    if (! $order) {
                        $errors[] = "Document {$document->id}: Prestashop order {$document->order_id} not found";
                        $skipped++;
                        $bar->advance();

                        continue;
                    }

                    // Sync document with order data
                    if ($this->syncDocumentWithOrder($document, $order)) {
                        $synced++;
                    } else {
                        $errors[] = "Document {$document->id}: Failed to sync with order {$order->id_order}";
                        $skipped++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Document {$document->id}: {$e->getMessage()}";
                    $skipped++;
                }

                $bar->advance();
            }

            $bar->finish();
            $this->line('');

            // Summary
            $this->line('');
            $this->info('✅ Synchronization complete!');
            $this->info("  ✓ Synced: {$synced}");
            $this->warn("  ⊘ Skipped: {$skipped}");
            $this->info("  Total: {$total}");

            if (! empty($errors)) {
                $this->line('');
                $this->warn('⚠️  Errors encountered (first 20):');
                foreach (array_slice($errors, 0, 20) as $error) {
                    $this->error("  • {$error}");
                }
                if (count($errors) > 20) {
                    $this->error('  ... and '.(count($errors) - 20).' more');
                }
            }

            $this->line('');
            $this->comment('💡 Next steps:');
            $this->comment('  • Verify document data in /settings/documents/');
            $this->comment('  • Check that products are properly linked');

        } catch (\Exception $e) {
            $this->error('❌ Synchronization failed: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return 1;
        }

        return 0;
    }

    /**
     * Synchronize document with Prestashop order data
     */
    private function syncDocumentWithOrder(Document $document, PrestashopOrder $order): bool
    {
        try {
            // Get the customer
            $customer = $order->customer;

            if (! $customer) {
                return false;
            }

            // Get delivery address
            $deliveryAddress = $order->addressDelivery;

            // Update denormalized order and customer data
            $document->order_reference = $order->reference ?? $document->order_reference;
            $document->order_date = $order->date_add ?? $document->order_date;

            // Fill customer data - name and lastname come from delivery address
            $document->customer_id = $customer->id_customer;
            $document->customer_firstname = $deliveryAddress?->firstname ?? $customer->firstname;
            $document->customer_lastname = $deliveryAddress?->lastname ?? $customer->lastname;
            $document->customer_email = $customer->email;

            // DNI/SIRET come from delivery address
            $document->customer_dni = $deliveryAddress?->dni ?? $deliveryAddress?->vat_number ?? null;

            // Company comes from delivery address
            $document->customer_company = $deliveryAddress?->company ?? null;

            // Cellphone comes from delivery address
            $document->customer_cellphone = $deliveryAddress?->phone_mobile ?? null;

            // Language ID from order
            $document->lang_id = $order->id_lang;

            $document->save();

            // Capture products from the order
            $document->captureProducts();

            return true;
        } catch (\Exception $e) {
            \Log::error('Error syncing document with order: '.$e->getMessage(), [
                'document_id' => $document->id,
                'order_id' => $order->id_order,
            ]);

            return false;
        }
    }
}
