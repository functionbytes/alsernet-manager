<?php

namespace Modules\Supplier\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Dead Letter Queue para sincronizaciones fallidas (Supplier → ERP)
 *
 * Almacena registros que fallaron en la sincronización bidireccional
 * para poder reintentar manualmente o diagnosticar problemas.
 *
 * @property int $id
 * @property string $uid ULID único (26 caracteres)
 * @property string $sync_type Tipo de sincronización: 'price', 'provider', 'product'
 * @property int $supplier_id ID del registro en tabla Supplier (SupplierProductPrice, SupplierErpProvider, SupplierProduct)
 * @property int|null $erp_id ID del registro en Oracle (ArtiprovTarifapro, Proveedor, Articulo)
 * @property array $changed_data Campos que se intentaron sincronizar
 * @property string $error_message Mensaje de error del fallo
 * @property int $retry_count Número de reintentos realizados
 * @property \Illuminate\Support\Carbon|null $last_retry_at Última vez que se intentó reintentar
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class SupplierSyncFailure extends Model
{
    use HasFactory;

    protected $table = 'supplier_sync_failures';

    protected $fillable = [
        'uid',
        'sync_type',
        'supplier_id',
        'erp_id',
        'changed_data',
        'error_message',
        'retry_count',
        'last_retry_at',
    ];

    protected $casts = [
        'changed_data' => 'array',
        'last_retry_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Verificar si el registro puede ser reintentado
     *
     * Condiciones para retry:
     * - retry_count < 5
     * - last_retry_at hace más de 30 minutos
     *
     * @return bool
     */
    public function canRetry(): bool
    {
        return $this->retry_count < 5 &&
               (!$this->last_retry_at || $this->last_retry_at->lessThan(now()->subMinutes(30)));
    }

    /**
     * Obtener descripción legible del tipo de sincronización
     *
     * @return string
     */
    public function getTypeNameAttribute(): string
    {
        return match ($this->sync_type) {
            'price' => 'Precio de Producto',
            'provider' => 'Proveedor',
            'product' => 'Producto',
            default => $this->sync_type,
        };
    }

    /**
     * Registros ordenados por intento fallido más reciente
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatestFailures($query)
    {
        return $query->orderByDesc('last_retry_at')->orderByDesc('created_at');
    }

    /**
     * Filtrar por tipo de sincronización
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, $type)
    {
        return $query->where('sync_type', $type);
    }

    /**
     * Filtrar por número de reintentos
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $maxRetries
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRetryable($query, $maxRetries = 5)
    {
        return $query->where('retry_count', '<', $maxRetries);
    }
}
