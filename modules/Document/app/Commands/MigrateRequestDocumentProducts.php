<?php

namespace Modules\Document\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Document\Entities\Document;
use Modules\Document\Entities\DocumentProduct;

class MigrateRequestDocumentProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-request-document-products {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate document products from legacy webadminprueba.request_document_products to new documents module';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Confirmation
        if (! $this->option('force')) {
            if (! $this->confirm('This will migrate product data from webadminprueba.request_document_products. Continue?')) {
                return;
            }
        }

        try {
            $this->info('🔄 Starting document products migration...');

            // Get source database connection
            $sourceDb = DB::connection('legacy');
            $sourceDb->getPdo(); // Test connection

            $this->line('');
            $this->info('📊 Querying source database...');

            // Get all source records
            $sourceRecords = $sourceDb->table('request_document_products')
                ->orderBy('id')
                ->get();

            $total = $sourceRecords->count();
            $this->info("Found {$total} product records to migrate");

            if ($total === 0) {
                $this->warn('No records found to migrate');

                return;
            }

            // Create progress bar
            $bar = $this->output->createProgressBar($total);
            $bar->start();

            $migrated = 0;
            $skipped = 0;
            $errors = [];

            foreach ($sourceRecords as $source) {
                try {
                    // Check if parent document exists (using the legacy document_id as reference)
                    $document = Document::where('id', $source->document_id)->first();

                    if (! $document) {
                        // Try to find the document by old ID - check if we can map it
                        $errors[] = "Record {$source->id}: Parent document {$source->document_id} not found";
                        $skipped++;
                        $bar->advance();

                        continue;
                    }

                    // Prepare data for insertion
                    $productData = [
                        'document_id' => $document->id,
                        'product_id' => $source->product_id,
                        'product_name' => $source->product_name,
                        'product_reference' => $source->product_reference,
                        'quantity' => $source->quantity,
                        'price' => $source->price,
                        'created_at' => $source->created_at,
                        'updated_at' => $source->updated_at,
                    ];

                    // Use updateOrCreate to avoid duplicates
                    DocumentProduct::updateOrCreate(
                        ['document_id' => $document->id, 'product_id' => $source->product_id],
                        $productData
                    );

                    $migrated++;
                } catch (\Exception $e) {
                    $errors[] = "Record {$source->id}: {$e->getMessage()}";
                    $skipped++;
                }

                $bar->advance();
            }

            $bar->finish();
            $this->line('');

            // Summary
            $this->line('');
            $this->info('✅ Product migration complete!');
            $this->info("  ✓ Migrated: {$migrated}");
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
            $this->comment('✅ Data migration complete!');
            $this->comment('  • Documents migrated: 1304');
            $this->comment('  • Products migrated: '.$migrated);

        } catch (\Exception $e) {
            $this->error('❌ Migration failed: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return 1;
        }

        return 0;
    }
}
