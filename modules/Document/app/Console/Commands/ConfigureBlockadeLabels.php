<?php

namespace Modules\Document\Console\Commands;

use Illuminate\Console\Command;
use Modules\Document\Entities\DocumentType;

class ConfigureBlockadeLabels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:configure-blockade-labels {--reset : Reset all labels to empty}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure which PrestaShop labels are associated with each document type';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== Blockade Labels Configuration ===');
        $this->newLine();

        if ($this->option('reset')) {
            $this->warn('Resetting all associated labels...');
            DocumentType::query()->update(['associated_labels' => null]);
            $this->info('All labels have been reset.');

            return Command::SUCCESS;
        }

        $documentTypes = DocumentType::where('is_active', true)->orderBy('label')->get();

        if ($documentTypes->isEmpty()) {
            $this->error('No active document types found.');

            return Command::FAILURE;
        }

        foreach ($documentTypes as $docType) {
            $this->line("\n<fg=cyan>Document Type: {$docType->label}</>");
            $currentLabels = $docType->associated_labels ?? [];

            if (! empty($currentLabels)) {
                $this->line('Current labels: '.implode(', ', $currentLabels));
            } else {
                $this->line('No labels configured');
            }

            $labelsInput = $this->ask('Enter PrestaShop labels (comma-separated, or press Enter to skip)', null);

            if ($labelsInput !== null) {
                $labels = array_map('strtoupper', array_filter(array_map('trim', explode(',', $labelsInput))));
                $docType->update(['associated_labels' => ! empty($labels) ? $labels : null]);
                $this->info('✓ Updated: '.implode(', ', $labels ?: ['(empty)']));
            }
        }

        $this->newLine();
        $this->info('Configuration saved successfully!');

        // Show summary
        $this->showSummary();

        return Command::SUCCESS;
    }

    /**
     * Show a summary of current configuration
     */
    private function showSummary(): void
    {
        $this->line("\n<fg=cyan>=== Current Configuration ===</>");

        $documentTypes = DocumentType::where('is_active', true)->get();

        foreach ($documentTypes as $docType) {
            $labels = $docType->associated_labels ?? [];

            if (! empty($labels)) {
                $this->line("{$docType->label}: ".implode(', ', $labels));
            }
        }
    }
}
