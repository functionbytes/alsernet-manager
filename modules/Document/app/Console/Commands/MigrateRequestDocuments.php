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
            $statusStats = [
                'awaiting_documents' => 0,
                'received' => 0,
                'approved' => 0,
            ];

            foreach ($sourceRecords as $source) {
                try {
                    // Map document type using legacy type field
                    // Note: Legacy database (webadmin) does not have request_document_products table
                    // Product associations will need to be handled separately if needed
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

                    // Map source to source_id with fallback mappings
                    // Note: Legacy database doesn't have source field, so we default to 'prestashop'
                    $sourceKey = strtolower($source->source ?? 'prestashop');

                    // Handle legacy source mappings
                    $sourceMappings = [
                        'api' => 'prestashop', // Map API to PrestaShop
                    ];
                    if (isset($sourceMappings[$sourceKey])) {
                        $sourceKey = $sourceMappings[$sourceKey];
                    }

                    // Ensure source exists, default to prestashop if not
                    if (! isset($sourceMap[$sourceKey])) {
                        $sourceKey = 'prestashop'; // Default fallback
                    }

                    if (! isset($sourceMap[$sourceKey])) {
                        $errors[] = "Record {$source->id}: Unknown source (defaulted to prestashop)";
                        $skipped++;
                        $bar->advance();

                        continue;
                    }

                    // Determine status based on upload_at and proccess (legacy field)
                    // Legacy database uses 'upload_at' instead of 'confirmed_at'
                    // Logic: upload_at + proccess=1 → approved
                    //        upload_at + proccess=0 → received
                    //        no upload_at → awaiting_documents
                    $statusId = $defaultStatusId; // awaiting_documents (2)
                    $validationStatus = 'pending'; // Default validation status

                    if (isset($source->upload_at) && $source->upload_at) {
                        if ($source->proccess === 1 || $source->proccess === '1') {
                            // Documento subido y procesado → aprobado
                            $statusId = $statusMap['approved'] ?? 5;
                            $validationStatus = 'approved';
                        } else {
                            // Documento subido pero no procesado → recibido
                            $statusId = $statusMap['received'] ?? 3;
                            $validationStatus = 'pending';
                        }
                    }

                    // Prepare data for insertion (using null coalescing for potentially missing fields)
                    $documentData = [
                        'id' => $source->id,
                        'uid' => $source->uid,
                        'type_id' => $documentTypeId,
                        'source_id' => $sourceMap[$sourceKey],
                        'sync_id' => 2, // 'none' - no synchronization
                        'load_id' => 2, // 'system' - loaded by migration system
                        'status_id' => $statusId,
                        'validation_status' => $validationStatus,
                        'customer_id' => $source->customer_id ?? null,
                        'customer_firstname' => $source->customer_firstname ?? null,
                        'customer_lastname' => $source->customer_lastname ?? null,
                        'customer_email' => $source->customer_email ?? null,
                        'customer_dni' => $source->customer_dni ?? null,
                        'customer_company' => $source->customer_company ?? null,
                        'order_id' => $source->order_id ?? null,
                        'order_reference' => $source->order_reference ?? null,
                        'order_date' => $source->order_date ?? null,
                        'cart_id' => $source->cart_id ?? null,
                        'confirmed_at' => $source->upload_at ?? null, // Legacy uses 'upload_at'
                        'reminder_at' => $source->reminder_at ?? null,
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

                    // Track status statistics
                    $statusKey = array_search($statusId, $statusMap);
                    if ($statusKey && isset($statusStats[$statusKey])) {
                        $statusStats[$statusKey]++;
                    }
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

            // Show status distribution
            $this->line('');
            $this->comment('📊 Status distribution:');
            $this->info("  • Awaiting documents: {$statusStats['awaiting_documents']}");
            $this->info("  • Received: {$statusStats['received']}");
            $this->info("  • Approved: {$statusStats['approved']}");

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
            $this->comment('  • Run: php artisan app:migrate-request-document-products --force');
            $this->comment('  • Run: php artisan app:migrate-request-document-media --force');

        } catch (\Exception $e) {
            $this->error('❌ Migration failed: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return 1;
        }

        return 0;
    }
}
