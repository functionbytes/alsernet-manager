<?php

namespace App\Console\Commands;

use App\Models\Document\Document;
use Illuminate\Console\Command;

class InitializeDocumentWorkflows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:init-workflows
                            {--dry-run : Run without making changes}
                            {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize validation workflows for documents that don\'t have them configured';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Buscando documentos sin workflow inicializado...');

        // Find documents without workflow initialized
        $documents = Document::whereNull('current_validator_group')
            ->whereNotIn('validation_status', ['approved', 'rejected'])
            ->get();

        $count = $documents->count();

        if ($count === 0) {
            $this->info('✅ No hay documentos que necesiten inicialización de workflow.');

            return Command::SUCCESS;
        }

        $this->info("📊 Se encontraron {$count} documentos que necesitan workflow.");

        // Show sample
        $this->table(
            ['UID', 'Type', 'Financing', 'Status'],
            $documents->take(5)->map(fn ($doc) => [
                $doc->uid,
                $doc->type,
                $doc->requires_financing ? 'Sí' : 'No',
                $doc->validation_status,
            ])
        );

        if ($count > 5) {
            $this->line("... y {$count} documentos más");
        }

        // Dry run check
        if ($this->option('dry-run')) {
            $this->warn('🔎 Modo dry-run: no se realizarán cambios.');

            return Command::SUCCESS;
        }

        // Confirmation
        if (! $this->option('force')) {
            if (! $this->confirm("¿Deseas inicializar el workflow en {$count} documentos?", true)) {
                $this->warn('❌ Operación cancelada.');

                return Command::FAILURE;
            }
        }

        // Process documents
        $this->info('⚙️  Inicializando workflows...');
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $successful = 0;
        $failed = 0;

        foreach ($documents as $document) {
            try {
                $document->initializeWorkflow();
                $successful++;
            } catch (\Exception $e) {
                $failed++;
                $this->newLine();
                $this->error("❌ Error en documento {$document->uid}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('📈 Resumen:');
        $this->table(
            ['Estado', 'Cantidad'],
            [
                ['✅ Exitosos', $successful],
                ['❌ Fallidos', $failed],
                ['📊 Total', $count],
            ]
        );

        if ($successful > 0) {
            $this->info('✅ Workflows inicializados correctamente.');
        }

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
