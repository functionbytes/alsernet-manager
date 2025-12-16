<?php

namespace App\Models\Document;

use App\Library\Traits\HasUid;
use App\Services\Documents\DocumentMailService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property string $uid
 * @property string|null $type
 * @property string|null $proccess
 * @property string|null $source
 * @property int|null $lang_id
 * @property \Illuminate\Support\Carbon|null $confirmed_at When the document upload was confirmed
 * @property \Illuminate\Support\Carbon|null $reminder_at
 * @property int|null $order_id
 * @property int|null $customer_id
 * @property int|null $cart_id
 * @property string|null $order_reference
 * @property \Illuminate\Support\Carbon|null $order_date
 * @property string|null $customer_firstname
 * @property string|null $customer_lastname
 * @property string|null $customer_email
 * @property string|null $customer_dni
 * @property string|null $customer_company
 * @property string|null $customer_cellphone
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Prestashop\Cart\Cart|null $cart
 * @property-read \App\Models\Prestashop\Customer|null $customer
 * @property-read \App\Models\Lang|null $lang
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Prestashop\Order\Order|null $order
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document\DocumentProduct> $products
 * @property-read int|null $products_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document ascending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document descending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document filterByDateRange($dateFrom = null, $dateTo = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document filterByUploadStatus($hasMedia = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document filterListing($search = '', $uploadStatus = null, $dateFrom = null, $dateTo = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document id($id)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document order($order)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document orderByUploadPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document searchByCustomerOrOrder($search = '')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document uid($uid)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCartId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCustomerCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCustomerDni($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCustomerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCustomerFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCustomerLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereOrderDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereOrderReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereProccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereReminderAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Document extends Model implements HasMedia
{
    use HasFactory ,HasUid ,  InteractsWithMedia;

    protected $table = 'documents';

    protected $casts = [
        'confirmed_at' => 'datetime',
        'reminder_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'order_date' => 'datetime',
        'required_documents' => 'array',
        'uploaded_documents' => 'array',
    ];

    protected $fillable = [
        'uid',
        'type',
        'proccess',
        'source',
        'lang_id',
        'confirmed_at',
        'reminder_at',
        'reminder_sent_at',
        'order_id',
        'customer_id',
        'cart_id',
        'order_reference',
        'order_date',
        'customer_firstname',
        'customer_lastname',
        'customer_email',
        'customer_cellphone',
        'customer_dni',
        'customer_company',
        'required_documents',
        'uploaded_documents',
        'status_id',
        'sla_policy_id',
        'created_at',
        'updated_at',
    ];

    public function scopeDescending($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeAscending($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    public function scopeOrder($query, $order)
    {
        return $query->where('order_id', $order);
    }

    public function scopeId($query, $id)
    {
        return $query->where('id', $id);
    }

    public function scopeUid($query, $uid)
    {
        return $query->where('uid', $uid);
    }

    /**
     * Filtra documentos por estado de carga (con o sin media)
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int|null  $hasMedia  1 = con media, 0 = sin media, null = todos
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByUploadStatus($query, $hasMedia = null)
    {
        if ($hasMedia === null) {
            return $query;
        }

        return $query->whereRaw('EXISTS (
            SELECT 1 FROM media
            WHERE media.model_id = documents.id
              AND media.model_type = ?
        ) = ?', [self::class, $hasMedia === 1 ? 1 : 0]);
    }

    /**
     * Busca documentos por nombre de cliente, ID de orden u orden reference
     * Busca tanto en datos denormalizados como en relaciones
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search  Término de búsqueda
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchByCustomerOrOrder($query, $search = '')
    {
        if (empty($search)) {
            return $query;
        }

        $search = strtolower($search);

        return $query
            ->where(function ($q) use ($search) {
                // Buscar en datos denormalizados del cliente
                $q->whereRaw('LOWER(customer_firstname) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(customer_lastname) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(customer_email) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(customer_dni) LIKE ?', ["%{$search}%"])
                    // Buscar por ID de orden
                    ->orWhereRaw('CAST(order_id AS CHAR) LIKE ?', ["%{$search}%"])
                    // Buscar por referencia de orden denormalizada
                    ->orWhereRaw('LOWER(order_reference) LIKE ?', ["%{$search}%"]);
            });
    }

    /**
     * Ordena documentos por prioridad (sin carga primero), fecha de creación y agrupa por día
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByUploadPriority($query)
    {
        return $query
            ->orderByRaw('CASE WHEN EXISTS (
                SELECT 1 FROM media
                WHERE media.model_id = documents.id
                AND media.model_type = ?
            ) THEN 1 ELSE 0 END ASC', [self::class])
            ->orderBy('documents.created_at', 'desc');
    }

    /**
     * Filtra documentos por rango de fechas
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $dateFrom  Fecha inicial en formato Y-m-d
     * @param  string|null  $dateTo  Fecha final en formato Y-m-d
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByDateRange($query, $dateFrom = null, $dateTo = null)
    {
        if ($dateFrom) {
            try {
                $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', $dateFrom)->startOfDay();
                $query->whereDate('created_at', '>=', $startDate);
            } catch (\Exception $e) {
                // Si la fecha es inválida, ignorar el filtro
            }
        }

        if ($dateTo) {
            try {
                $endDate = \Carbon\Carbon::createFromFormat('Y-m-d', $dateTo)->endOfDay();
                $query->whereDate('created_at', '<=', $endDate);
            } catch (\Exception $e) {
                // Si la fecha es inválida, ignorar el filtro
            }
        }

        return $query;
    }

    /**
     * Consulta optimizada para listar documentos en admin
     * Combina filtrado, búsqueda, filtrado por fechas y ordenamiento
     * Nota: Usa datos denormalizados sin cargar relaciones para mejor performance
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search  Término de búsqueda
     * @param  int|null  $uploadStatus  1 = con media, 0 = sin media, null = todos
     * @param  string|null  $dateFrom  Fecha inicial en formato Y-m-d
     * @param  string|null  $dateTo  Fecha final en formato Y-m-d
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterListing($query, $search = '', $uploadStatus = null, $dateFrom = null, $dateTo = null)
    {
        return $query
            ->filterByUploadStatus($uploadStatus)
            ->searchByCustomerOrOrder($search)
            ->filterByDateRange($dateFrom, $dateTo)
            ->orderByUploadPriority();
    }

    public function getAllDocumentsUrls(): array
    {
        return $this->getMedia('documents')->map(function ($media) {
            return $media->getUrl();
        })->toArray();
    }

    public function getDocumentUrl(): ?string
    {
        $media = $this->getFirstMedia('documents');

        return $media ? $media->getUrl() : null;
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo('App\Models\Prestashop\Order\Order', 'order_id', 'id_order');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo('App\Models\Prestashop\Customer', 'customer_id', 'id_customer');
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo('App\Models\Prestashop\Cart\Cart', 'cart_id', 'id_cart');
    }

    public function lang(): BelongsTo
    {
        return $this->belongsTo('App\Models\Lang', 'lang_id', 'id');
    }

    /**
     * Relación con los productos del documento
     */
    public function products()
    {
        return $this->hasMany(DocumentProduct::class, 'document_id');
    }

    /**
     * Relación con el historial de acciones
     */
    public function actions()
    {
        return $this->hasMany(DocumentAction::class, 'document_id');
    }

    /**
     * Relación con las notas del documento
     */
    public function notes()
    {
        return $this->hasMany(DocumentNote::class, 'document_id');
    }

    /**
     * Relación con el estado del documento
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(DocumentStatus::class, 'status_id');
    }

    /**
     * Relación con el historial de cambios de estado
     */
    public function statusHistories()
    {
        return $this->hasMany(DocumentStatusHistory::class, 'document_id');
    }

    /**
     * Relación con la política SLA del documento
     */
    public function slaPolicy(): BelongsTo
    {
        return $this->belongsTo(DocumentSlaPolicy::class, 'sla_policy_id');
    }

    /**
     * Relación con los incumplimientos SLA del documento
     */
    public function slaBreaches()
    {
        return $this->hasMany(DocumentSlaBreach::class, 'document_id');
    }

    /**
     * Detecta el tipo de documento basándose en los productos capturados
     * Valida qué etiquetas tienen los productos (DNI, ESCOPETA, RIFLE, CORTA)
     * Busca las features en los productos ya importados
     */
    public function detectDocumentType()
    {
        try {
            // Obtener los productos ya capturados del documento
            $products = $this->products()->get();

            if ($products->isEmpty()) {
                return 'general';
            }

            $documentTypes = [];

            foreach ($products as $docProduct) {

                // Obtener las features/etiquetas del producto desde Prestashop
                $features = DB::connection('prestashop')
                    ->table('aalv_feature_product')
                    ->where('id_product', $docProduct->product_id)
                    ->where('id_feature', 23) // Feature ID para tipo de venta
                    ->get();

                foreach ($features as $feature) {
                    if ($feature->id_feature_value == 263658) { // DNI
                        $documentTypes['dni'] = true;
                    } elseif ($feature->id_feature_value == 263659) { // ESCOPETA
                        $documentTypes['escopeta'] = true;
                    } elseif ($feature->id_feature_value == 263660) { // RIFLE
                        $documentTypes['rifle'] = true;
                    } elseif ($feature->id_feature_value == 263661) { // CORTA
                        $documentTypes['corta'] = true;
                    }
                }
            }

            if (! empty($documentTypes)) {
                if (isset($documentTypes['dni'])) {
                    return 'dni';
                }
                if (isset($documentTypes['escopeta'])) {
                    return 'escopeta';
                }
                if (isset($documentTypes['rifle'])) {
                    return 'rifle';
                }
                if (isset($documentTypes['corta'])) {
                    return 'corta';
                }
            }

            return 'general';

        } catch (\Exception $e) {
            Log::error('Error detectando tipo de documento: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return 'general';
        }
    }

    /**
     * Envía email de notificación inicial pidiendo carga de documentación
     * Envío síncrono (directo, sin pasar por la cola)
     */
    public function sendUploadNotification(): bool
    {
        return DocumentMailService::sendUploadNotification($this);
    }

    /**
     * Envía email de recordatorio para cargar documentación
     * Envío síncrono (directo, sin pasar por la cola)
     */
    public function sendReminder(): bool
    {
        return DocumentMailService::sendReminder($this);
    }

    /**
     * Envía email de confirmación cuando la documentación es cargada
     * Envío síncrono (directo, sin pasar por la cola)
     */
    public function sendUploadedConfirmation(): bool
    {
        return DocumentMailService::sendUploadedConfirmation($this);
    }

    /**
     * Captura y sincroniza los productos de la orden Prestashop
     * Importa los productos de la tabla order_detail de Prestashop
     */
    public function captureProducts()
    {
        try {
            // Limpiar productos previos
            $this->products()->delete();

            // Obtener productos directamente de la BD de Prestashop
            $orderProducts = DB::connection('prestashop')
                ->table('aalv_order_detail')
                ->where('id_order', $this->order_id)
                ->get();

            if ($orderProducts->isEmpty()) {
                return false;
            }

            // Insertar productos en el documento
            foreach ($orderProducts as $orderProduct) {
                DocumentProduct::create([
                    'document_id' => $this->id,
                    'product_id' => $orderProduct->product_id ?? null,
                    'product_name' => $orderProduct->product_name ?? null,
                    'product_reference' => $orderProduct->product_reference ?? null,
                    'quantity' => $orderProduct->product_quantity ?? 0,
                    'price' => $orderProduct->unit_price_tax_incl ?? 0,
                ]);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Error capturando productos del documento: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Obtiene solo las keys de documentos requeridos (estructura simplificada)
     * Retorna array simple: ["dni_frontal", "dni_trasera", "licencia"]
     *
     * @return array Array de keys de documentos requeridos
     */
    public function getRequiredDocuments(): array
    {
        $documentsWithLabels = $this->getRequiredDocumentsWithLabels();

        return array_keys($documentsWithLabels);
    }

    /**
     * Obtiene documentos requeridos con sus labels descriptivos
     * Retorna array asociativo: {"dni_frontal": "DNI - Cara delantera", ...}
     *
     * @return array Array con keys y labels
     */
    public function getRequiredDocumentsWithLabels(): array
    {
        // Obtener configuración del tipo de documento desde la tabla document_types
        $documentType = DocumentType::where('slug', $this->type)->first();

        if ($documentType) {
            return $documentType->getRequiredDocuments();
        }

        return $this->getDefaultDocuments();
    }

    /**
     * Obtiene documentos por defecto si no hay configuración
     */
    private function getDefaultDocuments(): array
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

        return $defaults[$this->type] ?? $defaults['general'];
    }

    /**
     * Obtiene el estado de los documentos (cargados vs faltantes)
     * Retorna un array con información de documentos cargados y pendientes
     */
    public function getDocumentStatus(): array
    {
        $requiredDocuments = $this->getRequiredDocuments();
        $uploadedDocuments = $this->getUploadedDocumentTypes();
        $missingDocuments = $this->getMissingDocuments();

        return [
            'total_required' => count($requiredDocuments),
            'total_uploaded' => count($uploadedDocuments),
            'required_documents' => $requiredDocuments,
            'uploaded_documents' => $uploadedDocuments,
            'missing_documents' => $missingDocuments,
            'is_complete' => empty($missingDocuments),
        ];
    }

    /**
     * Actualiza el estado JSON de documentos requeridos
     * Se llama cuando se detecta el tipo de documento
     */
    public function updateRequiredDocumentsJson(): void
    {
        $this->required_documents = $this->getRequiredDocuments();
        $this->save();
    }

    /**
     * Actualiza el estado JSON de documentos cargados (DEPRECATED)
     * Usar syncUploadedDocumentsJson() en su lugar
     */
    public function updateUploadedDocumentsJson(): void
    {
        $this->syncUploadedDocumentsJson();
    }

    /**
     * Obtiene documentos faltantes comparando requeridos vs subidos
     * Retorna array asociativo con estructura: {"dni_trasera": "DNI - Cara trasera"}
     *
     * @return array Array de documentos faltantes con labels
     */
    public function getMissingDocuments(): array
    {
        $requiredWithLabels = $this->getRequiredDocumentsWithLabels();
        $uploadedKeys = $this->getUploadedDocumentTypes();

        // Usar array_diff_key para obtener solo los documentos requeridos que NO están subidos
        return array_diff_key($requiredWithLabels, array_flip($uploadedKeys));
    }

    /**
     * Obtiene los tipos de documentos ya cargados (solo keys)
     * La información completa de archivos está en media
     *
     * @return array Array de doc_types que han sido cargados: ["doc_1", "doc_2"]
     */
    public function getUploadedDocumentTypes(): array
    {
        $uploadedTypes = [];

        foreach ($this->getMedia('documents') as $media) {
            $docType = $media->getCustomProperty('document_type');
            if ($docType && ! in_array($docType, $uploadedTypes)) {
                $uploadedTypes[] = $docType;
            }
        }

        return $uploadedTypes;
    }

    /**
     * Obtiene los documentos cargados con detalles completos de archivos
     * Combina keys de uploaded_documents con información de media
     *
     * @return array Documentos con detalles: ["doc_1" => ["id", "url", "size", ...]]
     */
    public function getUploadedDocumentsWithDetails(): array
    {
        $uploadedWithDetails = [];

        foreach ($this->getMedia('documents') as $media) {
            $docType = $media->getCustomProperty('document_type');
            if ($docType) {
                $uploadedWithDetails[$docType] = [
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'size' => $media->size,
                    'url' => $media->getUrl(),
                    'created_at' => $media->created_at->format('Y-m-d H:i:s'),
                ];
            }
        }

        return $uploadedWithDetails;
    }

    /**
     * Verifica si todos los documentos requeridos están subidos
     *
     * @return bool True si todos los documentos requeridos están presentes
     */
    public function hasAllRequiredDocuments(): bool
    {
        return empty($this->getMissingDocuments());
    }

    /**
     * Sincroniza el campo JSON uploaded_documents con los archivos media actuales
     * Debe llamarse después de cada operación de carga/eliminación de archivos
     */
    public function syncUploadedDocumentsJson(): void
    {
        $this->uploaded_documents = $this->getUploadedDocumentTypes();
        $this->save();
    }

    /**
     * Static helper to safely retrieve a document by UID
     * Encapsulates the uid() scope to ensure explicit retrieval of a single model instance
     */
    public static function findByUid(?string $uid): ?self
    {
        if (! $uid) {
            return null;
        }

        return self::uid($uid)->first();
    }

    /**
     * Boot method to initialize required_documents when creating a document
     * Uses getRequiredDocuments() to fetch keys based on document type
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Document $document) {
            // If no type is set, default to 'general'
            if (! $document->type) {
                $document->type = 'general';
            }

            // Initialize required_documents with keys only if not already set
            if (empty($document->required_documents)) {
                $document->required_documents = $document->getRequiredDocuments();
            }
        });

        static::updating(function (Document $document) {
            // If type changed and required_documents is not explicitly set, update it
            if ($document->isDirty('type') && empty($document->required_documents)) {
                $document->required_documents = $document->getRequiredDocuments();
            }
        });
    }
}
