<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Model;

class DocumentConfiguration extends Model
{
    protected $table = 'document_configurations';

    protected $fillable = [
        'document_type',
        'document_type_label',
        'required_documents',
    ];

    protected $casts = [
        'required_documents' => 'array',
    ];

    /**
     * Obtener configuración por tipo de documento
     */
    public static function getByType(string $documentType)
    {
        return self::where('document_type', $documentType)->first();
    }

    /**
     * Crear o actualizar configuración para un tipo
     */
    public static function createOrUpdate(string $documentType, array $requiredDocuments, ?string $label = null)
    {
        return self::updateOrCreate(
            ['document_type' => $documentType],
            [
                'required_documents' => $requiredDocuments,
                'document_type_label' => $label,
            ]
        );
    }
}
