<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $document_type
 * @property array $required_documents
 * @property bool $enable_initial_request
 * @property string|null $initial_request_message
 * @property bool $enable_reminder
 * @property int $reminder_days
 * @property string|null $reminder_message
 * @property bool $enable_missing_docs
 * @property string|null $missing_docs_message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @mixin \Eloquent
 */
class DocumentsConfiguration extends Model
{
    use HasFactory;

    protected $table = 'document_configurations';

    protected $fillable = [
        'document_type',
        'required_documents',
        'enable_initial_request',
        'initial_request_message',
        'enable_reminder',
        'reminder_days',
        'reminder_message',
        'enable_missing_docs',
        'missing_docs_message',
    ];

    protected $casts = [
        'required_documents' => 'array',
        'enable_initial_request' => 'boolean',
        'enable_reminder' => 'boolean',
        'enable_missing_docs' => 'boolean',
        'reminder_days' => 'integer',
    ];

    /**
     * Obtiene la configuración por tipo de documento
     */
    public static function getByType(string $documentType): ?self
    {
        return self::where('document_type', $documentType)->first();
    }

    /**
     * Obtiene todas las configuraciones
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAll()
    {
        return self::all();
    }

    /**
     * Crea o actualiza la configuración para un tipo de documento
     */
    public static function createOrUpdate(string $documentType, array $data): self
    {
        return self::updateOrCreate(
            ['document_type' => $documentType],
            $data
        );
    }

    /**
     * Exporta todas las configuraciones para caché o seeding
     */
    public static function exportAll(): array
    {
        $configs = [];
        foreach (self::all() as $config) {
            $configs[$config->document_type] = [
                'required_documents' => $config->required_documents,
                'enable_initial_request' => $config->enable_initial_request,
                'initial_request_message' => $config->initial_request_message,
                'enable_reminder' => $config->enable_reminder,
                'reminder_days' => $config->reminder_days,
                'reminder_message' => $config->reminder_message,
                'enable_missing_docs' => $config->enable_missing_docs,
                'missing_docs_message' => $config->missing_docs_message,
            ];
        }

        return $configs;
    }
}
