<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Document\Entities\Document;
use Modules\Prestashop\Entities\Customer;

class UpdateDocumentCustomerEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-document-customer-emails {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update customer_email in all documents from Prestashop customer data using customer_id';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Confirmation
        if (! $this->option('force')) {
            if (! $this->confirm('This will update customer_email for all documents from LEGACY database (alvarez_db). Continue?')) {
                return;
            }
        }

        try {
            $this->info('🔄 Starting customer email update from LEGACY database...');
            $this->line('');

            // Get all documents with customer_id
            $documents = Document::whereNotNull('customer_id')
                ->orderBy('id')
                ->get();

            $total = $documents->count();
            $this->info("Found {$total} documents to update");

            if ($total === 0) {
                $this->warn('No documents with customer_id found');
                return;
            }

            // Create progress bar
            $bar = $this->output->createProgressBar($total);
            $bar->start();

            $updated = 0;
            $skipped = 0;
            $errors = [];

            foreach ($documents as $document) {
                try {
                    // Get the customer from LEGACY database (alvarez_db)
                    $customerEmail = \DB::connection('prestashop')
                        ->table('aalv_customer')
                        ->where('id_customer', $document->customer_id)
                        ->value('email');

                    if (! $customerEmail) {
                        $errors[] = "Document {$document->id}: Customer {$document->customer_id} not found or has no email in legacy DB";
                        $skipped++;
                        $bar->advance();
                        continue;
                    }

                    // Update customer email
                    $document->customer_email = $customerEmail;
                    $document->save();
                    $updated++;
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
            $this->info('✅ Update complete!');
            $this->info("  ✓ Updated: {$updated}");
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
            $this->comment('  • Check that customer emails are correctly linked from legacy DB');

        } catch (\Exception $e) {
            $this->error('❌ Update failed: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return 1;
        }

        return 0;
    }
}
