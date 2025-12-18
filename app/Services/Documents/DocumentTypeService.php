<?php

namespace App\Services\Documents;

use App\Models\Document\DocumentConfiguration;

class DocumentTypeService
{
    /**
     * Obtener los documentos requeridos para un tipo de documento
     */
    public static function getRequiredDocuments(string $documentType): array
    {
        $config = DocumentConfiguration::getByType($documentType);

        if ($config && $config->required_documents) {
            return $config->required_documents;
        }

        // Fallback a configuración por defecto si no existe en BD
        return self::getDefaultDocuments($documentType);
    }

    /**
     * Obtener documentos por defecto según el tipo
     */
    public static function getDefaultDocuments(string $documentType): array
    {
        $defaults = [
            'corta' => [
                'doc_1' => 'DNI - Cara delantera',
                'doc_2' => 'DNI - Cara trasera',
                'doc_3' => 'Licencia de armas cortas (tipo B) o licencia de tiro olímpico (tipo F)'
            ],
            'rifle' => [
                'doc_1' => 'DNI - Cara delantera',
                'doc_2' => 'DNI - Cara trasera',
                'doc_3' => 'Licencia de armas largas rayadas (tipo D)'
            ],
            'escopeta' => [
                'doc_1' => 'DNI - Cara delantera',
                'doc_2' => 'DNI - Cara trasera',
                'doc_3' => 'Licencia de escopeta (tipo E)'
            ],
            'dni' => [
                'doc_1' => 'DNI - Cara delantera',
                'doc_2' => 'DNI - Cara trasera'
            ],
            'general' => [
                'doc_1' => 'Pasaporte o carnet de conducir (ambas caras si es tarjeta)'
            ],
        ];

        return $defaults[$documentType] ?? $defaults['general'];
    }

    /**
     * Obtener documentos faltantes comparando requeridos vs cargados
     */
    public static function getMissingDocuments(string $documentType, array $uploadedDocs): array
    {
        $requiredDocs = self::getRequiredDocuments($documentType);
        $missingDocs = [];

        foreach ($requiredDocs as $docKey => $docLabel) {
            if (!isset($uploadedDocs[$docKey])) {
                $missingDocs[$docKey] = $docLabel;
            }
        }

        return $missingDocs;
    }

    /**
     * Validar que todos los documentos requeridos estén cargados
     */
    public static function allDocumentsUploaded(string $documentType, array $uploadedDocs): bool
    {
        $requiredDocs = self::getRequiredDocuments($documentType);

        foreach ($requiredDocs as $docKey => $docLabel) {
            if (!isset($uploadedDocs[$docKey])) {
                return false;
            }
        }

        return true;
    }
}
