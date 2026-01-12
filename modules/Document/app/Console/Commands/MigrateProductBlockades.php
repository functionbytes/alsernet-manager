<?php

namespace Modules\Document\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Document\Entities\DocumentProductBlockade;
use Modules\Document\Entities\DocumentType;

class MigrateProductBlockades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:product-blockades {--fresh : Delete existing blockades before migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate product blockades from PrestaShop MySQL tables to document_product_blockades';

    /**
     * Map blockade types to document type IDs (loaded dynamically)
     */
    private array $blockadeTypeMapping = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting product blockades migration...');

        // Load document type mapping dynamically
        $this->loadDocumentTypeMapping();

        // Configure external MySQL connection
        $this->configureExternalConnection();

        // Fresh start if requested
        if ($this->option('fresh')) {
            $this->warn('Deleting existing blockades...');
            DocumentProductBlockade::truncate();
            $this->info('Existing blockades deleted.');
        }

        $blockadeLabels = implode(',', array_keys($this->blockadeTypeMapping));
        $this->info("Using blockade labels: {$blockadeLabels}");

        // Migrate from both tables
        $totalMigrated = 0;
        // Combinations: read id_origen from PrestaShop → save as source_id locally
        $totalMigrated += $this->migrateFromTable('aalv_combinaciones_import', 'id_origen', 'id_product_attribute', $blockadeLabels, 'combination');
        // Simple inventaries: read id_origen from PrestaShop → save as source_id locally
        $totalMigrated += $this->migrateFromTable('aalv_combinacionunica_import', 'id_origen', 'id_product', $blockadeLabels, 'product');

        $this->info("Migration completed! Total records migrated: {$totalMigrated}");

        return Command::SUCCESS;
    }

    /**
     * Load document type mapping from database
     * Maps blockade type keywords to document_type_id
     * Uses label slug matching for dynamic mapping
     */
    private function loadDocumentTypeMapping(): void
    {
        $documentTypes = DocumentType::all();

        foreach ($documentTypes as $docType) {
            // Convert label to lowercase slug for matching
            $slug = strtolower($docType->label);
            // Remove special characters and normalize spaces
            $slug = preg_replace('/[^a-z0-9]/', '', $slug);

            // Map by the processed slug
            $this->blockadeTypeMapping[$slug] = $docType->id;

            // Also add the original lowercase label for fuzzy matching
            $lowerLabel = strtolower($docType->label);
            if ($lowerLabel !== $slug) {
                $this->blockadeTypeMapping[$lowerLabel] = $docType->id;
            }
        }

        // Map common blockade type aliases to document types
        $aliases = [
            'corta' => $this->findDocumentTypeByKeyword('corta'),
            'rifle' => $this->findDocumentTypeByKeyword('rifle'),
            'escopeta' => $this->findDocumentTypeByKeyword('escopeta'),
            'balines' => $this->findDocumentTypeByKeyword('balines'),
            'dni' => $this->findDocumentTypeByKeyword('identificacion'),
        ];

        foreach ($aliases as $alias => $docTypeId) {
            if ($docTypeId) {
                $this->blockadeTypeMapping[$alias] = $docTypeId;
            }
        }

        $this->info('Loaded document type mapping: '.json_encode($this->blockadeTypeMapping));
    }

    /**
     * Find document type ID by keyword matching in label
     */
    private function findDocumentTypeByKeyword(string $keyword): ?int
    {
        $keyword = strtolower($keyword);
        $docType = DocumentType::whereRaw('LOWER(label) LIKE ?', ["%{$keyword}%"])->first();

        return $docType?->id;
    }

    /**
     * Configure external MySQL connection
     * Uses the existing 'prestashop' connection instead of creating a new one
     */
    private function configureExternalConnection(): void
    {
        // No need to configure - use existing 'prestashop' connection
        // Connection is already configured in config/database.php
    }

    /**
     * Migrate data from a specific table
     *
     * @param  string  $tableName  Name of the source table
     * @param  string  $origenColumn  Column name in external table (id_origen) → maps to source_id locally
     * @param  string  $productColumn  Column name in external table (id_product or id_product_attribute) → maps to product_id or product_attribute_id locally
     * @param  string  $blockadeLabels  Comma-separated blockade labels
     * @param  string  $type  Type of product: 'product' for simple inventaries, 'combination' for combinations
     */
    private function migrateFromTable(string $tableName, string $origenColumn, string $productColumn, string $blockadeLabels, string $type): int
    {
        $this->info("Migrating from {$tableName} ({$type})...");

        $migratedCount = 0;
        $skippedCount = 0;

        // Parse blockade labels (e.g., "DNI,ESCOPETA,RIFLE,CORTA")
        $labels = array_map('trim', explode(',', $blockadeLabels));

        $bar = $this->output->createProgressBar();
        $bar->start();

        // Process each label
        foreach ($labels as $label) {
            if (empty($label)) {
                continue;
            }

            $this->newLine();
            $this->line("Processing label: {$label}");

            // Convert label to lowercase for blockade_type
            $blockadeType = strtolower($label);

            // Get all inventaries with this label in the etiqueta column
            $products = DB::connection('prestashop')
                ->table($tableName)
                ->select($origenColumn, $productColumn, 'etiqueta')
                ->whereRaw("FIND_IN_SET(?, REPLACE(etiqueta, ', ', ','))", [$label])
                ->get();

            $this->info("Found {$products->count()} inventaries with label {$label}");

            foreach ($products as $product) {
                $idOrigen = $product->{$origenColumn};
                $productId = $product->{$productColumn};

                // Prepare data based on product type
                $data = [
                    'source_id' => $idOrigen,
                    'blockade_type' => $blockadeType,
                    'document_type_id' => $this->blockadeTypeMapping[$blockadeType] ?? null,
                ];

                if ($type === 'product') {
                    // Simple inventaries: product_id = product_id, product_attribute_id = null
                    $data['product_id'] = $productId;
                    $data['product_attribute_id'] = null;
                    // Check if already exists
                    if (DocumentProductBlockade::hasBlockade($productId, null, $blockadeType)) {
                        $skippedCount++;

                        continue;
                    }
                } else { // combination
                    // Combinations: product_id = null, product_attribute_id = product_attribute_id
                    $data['product_id'] = null;
                    $data['product_attribute_id'] = $productId;
                    // Check if already exists
                    if (DocumentProductBlockade::hasBlockade(null, $productId, $blockadeType)) {
                        $skippedCount++;

                        continue;
                    }
                }

                // Insert blockade
                DocumentProductBlockade::create($data);

                $migratedCount++;
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("Migrated: {$migratedCount}, Skipped (duplicates): {$skippedCount}");

        return $migratedCount;
    }
}
