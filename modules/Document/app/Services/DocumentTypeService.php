<?php

namespace Modules\Document\Services;

use Modules\Document\Entities\DocumentConfiguration;

class DocumentTypeService
{
    public static function getRequiredDocuments(string $documentType): array
    {
        $config = DocumentConfiguration::getByType($documentType);

        if ($config && $config->required_documents) {
            return $config->required_documents;
        }

        return self::getDefaultDocuments($documentType);
    }

    public static function getDefaultDocuments(string $documentType): array
    {
        $defaults = [
            'corta' => [
                'doc_1' => 'DNI - Cara delantera',
                'doc_2' => 'DNI - Cara trasera',
                'doc_3' => 'Licencia de armas cortas (tipo B) o licencia de tiro olímpico (tipo F)',
            ],
            'rifle' => [
                'doc_1' => 'DNI - Cara delantera',
                'doc_2' => 'DNI - Cara trasera',
                'doc_3' => 'Licencia de armas largas rayadas (tipo D)',
            ],
            'escopeta' => [
                'doc_1' => 'DNI - Cara delantera',
                'doc_2' => 'DNI - Cara trasera',
                'doc_3' => 'Licencia de escopeta (tipo E)',
            ],
            'dni' => [
                'doc_1' => 'DNI - Cara delantera',
                'doc_2' => 'DNI - Cara trasera',
            ],
            'general' => [
                'doc_1' => 'Pasaporte o carnet de conducir (ambas caras si es tarjeta)',
            ],
        ];

        return $defaults[$documentType] ?? $defaults['general'];
    }

    public static function getMissingDocuments(string $documentType, array $uploadedDocs): array
    {
        $requiredDocs = self::getRequiredDocuments($documentType);
        $missingDocs = [];

        foreach ($requiredDocs as $docKey => $docLabel) {
            if (! isset($uploadedDocs[$docKey])) {
                $missingDocs[$docKey] = $docLabel;
            }
        }

        return $missingDocs;
    }

    public static function allDocumentsUploaded(string $documentType, array $uploadedDocs): bool
    {
        $requiredDocs = self::getRequiredDocuments($documentType);

        foreach ($requiredDocs as $docKey => $docLabel) {
            if (! isset($uploadedDocs[$docKey])) {
                return false;
            }
        }

        return true;
    }
}
