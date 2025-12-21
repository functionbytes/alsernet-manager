<?php

use App\Models\Document\Document;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Actualizar todos los documentos existentes con la nueva estructura simplificada
        Document::all()->each(function (Document $document) {
            // Actualizar required_documents para que contenga solo las keys
            if ($document->type) {
                $document->required_documents = $document->getRequiredDocuments();
            }

            // Actualizar uploaded_documents para que contenga solo las keys
            $uploadedKeys = [];
            foreach ($document->getMedia('documents') as $media) {
                $docType = $media->getCustomProperty('document_type');
                if ($docType && ! in_array($docType, $uploadedKeys)) {
                    $uploadedKeys[] = $docType;
                }
            }
            $document->uploaded_documents = $uploadedKeys;

            $document->save();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se puede revertir fácilmente sin los datos originales
        // Esta es una migración unidireccional
    }
};
