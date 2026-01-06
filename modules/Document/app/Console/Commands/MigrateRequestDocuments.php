<?php

namespace Modules\Document\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Document\Entities\Document;
use Modules\Document\Entities\DocumentProductBlockade;
use Modules\Document\Entities\DocumentSource;
use Modules\Document\Entities\DocumentStatus;
use Modules\Document\Entities\DocumentType;

class MigrateRequestDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-request-documents {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate documents from legacy webadminprueba.request_documents to new documents module (sync_id=none, load_id=system)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Confirmation
        if (! $this->option('force')) {
            if (! $this->confirm('This will migrate data from webadminprueba.request_documents. Continue?')) {
                return;
            }
        }

        try {
            $this->info('🔄 Starting document migration...');

            // Get source database connection
            $sourceDb = DB::connection('legacy');
            $sourceDb->getPdo(); // Test connection

            // Get type mappings
            $typeMap = DocumentType::pluck('id', 'slug')->toArray();
            $sourceMap = DocumentSource::pluck('id', 'key')->toArray();
            $statusMap = DocumentStatus::pluck('id', 'key')->toArray();

            // Get default status (awaiting_documents - documents awaiting customer to upload files)
            $defaultStatusId = $statusMap['awaiting_documents'] ?? 2;

            $this->line('');
            $this->info('📊 Querying source database...');

            // Get all source records
            $sourceRecords = $sourceDb->table('request_documents')
                ->orderBy('id')
                ->get();

            $total = $sourceRecords->count();
            $this->info("Found {$total} records to migrate");

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
                    // First, check if this document has products in blockades
                    $documentTypeId = null;

                    // Get products associated with this document from legacy database
                    $documentProducts = $sourceDb->table('request_document_products')
                        ->where('document_id', $source->id)
                        ->pluck('product_id')
                        ->toArray();

                    if (! empty($documentProducts)) {
                        // Check if any of these products are in document_product_blockades
                        $blockade = DocumentProductBlockade::whereIn('product_id', $documentProducts)
                            ->whereNotNull('document_type_id')
                            ->first();

                        if ($blockade) {
                            // Use the document_type_id from the blockade
                            $documentTypeId = $blockade->document_type_id;
                            $this->line("  ℹ Document {$source->id}: Using type from blockade (product_id: {$blockade->product_id})");
                        }
                    }

                    // If no blockade found, use legacy type mapping
                    if (! $documentTypeId) {
                        $typeSlug = strtolower($source->type ?? '');

                        // Handle legacy type mappings
                        $typeMappings = [
                            'gun' => 'corta', // Default mapping for legacy 'gun' type
                        ];
                        if (isset($typeMappings[$typeSlug])) {
                            $typeSlug = $typeMappings[$typeSlug];
                        }

                        if (! isset($typeMap[$typeSlug])) {
                            $errors[] = "Record {$source->id}: Unknown type '{$source->type}'";
                            $skipped++;
                            $bar->advance();

                            continue;
                        }

                        $documentTypeId = $typeMap[$typeSlug];
                    }

                    // Map source to source_id with fallback mappings
                    $sourceKey = strtolower($source->source ?? '');

                    // Handle legacy source mappings
                    $sourceMappings = [
                        'api' => 'prestashop', // Map API to PrestaShop
                    ];
                    if (isset($sourceMappings[$sourceKey])) {
                        $sourceKey = $sourceMappings[$sourceKey];
                    }

                    if (! isset($sourceMap[$sourceKey])) {
                        $errors[] = "Record {$source->id}: Unknown source '{$source->source}'";
                        $skipped++;
                        $bar->advance();

                        continue;
                    }

                    // Prepare data for insertion
                    $documentData = [
                        'uid' => $source->uid,
                        'type_id' => $documentTypeId,
                        'source_id' => $sourceMap[$sourceKey],
                        'sync_id' => 2, // 'none' - no synchronization
                        'load_id' => 2, // 'system' - loaded by migration system
                        'status_id' => $defaultStatusId,
                        'validation_status' => 'pending',
                        'customer_id' => $source->customer_id,
                        'customer_firstname' => $source->customer_firstname,
                        'customer_lastname' => $source->customer_lastname,
                        'customer_email' => $source->customer_email,
                        'customer_dni' => $source->customer_dni,
                        'customer_company' => $source->customer_company,
                        'order_id' => $source->order_id,
                        'order_reference' => $source->order_reference,
                        'order_date' => $source->order_date,
                        'cart_id' => $source->cart_id,
                        'confirmed_at' => $source->confirmed_at,
                        'reminder_at' => $source->reminder_at,
                        'current_stage' => 1,
                        'total_stages' => 1,
                        'created_at' => $source->created_at,
                        'updated_at' => $source->updated_at,
                    ];

                    // Use updateOrCreate to avoid duplicates
                    Document::updateOrCreate(
                        ['uid' => $source->uid],
                        $documentData
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
            $this->info('✅ Migration complete!');
            $this->info("  ✓ Migrated: {$migrated}");
            $this->warn("  ⊘ Skipped: {$skipped}");
            $this->info("  Total: {$total}");

            if (! empty($errors)) {
                $this->line('');
                $this->warn('⚠️  Errors encountered:');
                foreach ($errors as $error) {
                    $this->error("  • {$error}");
                }
            }

            $this->line('');
            $this->comment('💡 Next steps:');
            $this->comment('  • Review migrated documents in /settings/documents/');
            $this->comment('  • Update validation stages and status as needed');
            $this->comment('  • Run: php artisan app:migrate-request-document-products');

        } catch (\Exception $e) {
            $this->error('❌ Migration failed: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return 1;
        }

        return 0;
    }
}
