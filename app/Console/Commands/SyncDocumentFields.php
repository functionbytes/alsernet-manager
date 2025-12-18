<?php

namespace App\Console\Commands;

use App\Models\Document\Document;
use App\Services\Documents\DocumentTypeService;
use Illuminate\Console\Command;

class SyncDocumentFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:sync-fields {--uid= : Sync specific document by UID} {--type= : Sync documents of specific type} {--force : Force resync even if fields already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Valida y genera required_documents y uploaded_documents para todos los documentos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $uid = $this->option('uid');
        $type = $this->option('type');
        $force = $this->option('force');

        if ($uid) {
            return $this->syncSingleDocument($uid, $force);
        }

        if ($type) {
            return $this->syncDocumentsByType($type, $force);
        }

        return $this->syncAllDocuments($force);
    }

    /**
     * Sincroniza un documento específico por UID
     */
    private function syncSingleDocument(string $uid, bool $force = false): int
    {
        $document = Document::uid($uid);

        if (! $document) {
            $this->error("Documento no encontrado: {$uid}");

            return 1;
        }

        $this->info("Sincronizando documento: {$uid}");

        return $this->syncDocument($document, $force) ? 0 : 1;
    }

    /**
     * Sincroniza documentos de un tipo específico
     */
    private function syncDocumentsByType(string $type, bool $force = false): int
    {
        $documents = Document::where('type', $type)->get();

        if ($documents->isEmpty()) {
            $this->error("No se encontraron documentos del tipo: {$type}");

            return 1;
        }

        $this->info("Sincronizando {$documents->count()} documento(s) del tipo: {$type}");

        $synced = 0;
        $skipped = 0;

        foreach ($documents as $document) {
            if ($this->syncDocument($document, $force)) {
                $synced++;
            } else {
                $skipped++;
            }
        }

        $this->info("✓ Sincronizados: {$synced} | Omitidos: {$skipped}");

        return 0;
    }

    /**
     * Sincroniza TODOS los documentos
     */
    private function syncAllDocuments(bool $force = false): int
    {
        $documents = Document::all();

        if ($documents->isEmpty()) {
            $this->error('No hay documentos para sincronizar');

            return 1;
        }

        $this->info("Sincronizando {$documents->count()} documento(s)...");

        $bar = $this->output->createProgressBar($documents->count());
        $bar->start();

        $synced = 0;
        $skipped = 0;

        foreach ($documents as $document) {
            if ($this->syncDocument($document, $force)) {
                $synced++;
            } else {
                $skipped++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("✓ Sincronizados: {$synced}");
        $this->info("⊘ Omitidos: {$skipped}");

        return 0;
    }

    /**
     * Sincroniza un documento individual
     * Valida y genera required_documents y uploaded_documents
     */
    private function syncDocument(Document $document, bool $force = false): bool
    {
        // Si ya está sincronizado y no es force, omitir
        if (! $force && ! empty($document->required_documents) && ! empty($document->uploaded_documents)) {
            return false;
        }

        try {
            // 1. Establecer tipo por defecto si no existe
            if (! $document->type) {
                $document->type = 'general';
            }

            // 2. Generar required_documents desde DocumentTypeService
            $requiredDocs = DocumentTypeService::getRequiredDocuments($document->type);
            $document->required_documents = $requiredDocs;

            // 3. Generar uploaded_documents desde media actual
            $uploadedDocs = [];
            foreach ($document->getMedia('documents') as $media) {
                $docType = $media->getCustomProperty('document_type', 'documento');
                $uploadedDocs[$docType] = [
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'size' => $media->size,
                    'url' => $media->getUrl(),
                    'created_at' => $media->created_at->format('Y-m-d H:i:s'),
                ];
            }
            $document->uploaded_documents = $uploadedDocs;

            // 4. Guardar el documento
            $document->save();

            return true;
        } catch (\Exception $e) {
            $this->error("Error sincronizando {$document->uid}: {$e->getMessage()}");

            return false;
        }
    }
}
