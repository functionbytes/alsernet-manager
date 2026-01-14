<?php

namespace Modules\Supplier\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\Erp\Models\Oracle\Proveedor\Proveedor;
use Modules\Erp\Models\Oracle\Articulo\Articulo;
use Modules\Erp\Models\Oracle\Proveedor\Artiprov;
use Modules\Erp\Models\Oracle\Proveedor\ArtiprovTarifapro;
use Modules\Supplier\Entities\SupplierProductPrice;
use Modules\Supplier\Entities\SupplierErpProvider;
use Modules\Supplier\Entities\SupplierProduct;

/**
 * Servicio centralizado para sincronización inversa (Supplier → ERP)
 *
 * Maneja:
 * - Sincronización de precios de productos (SupplierProductPrice ↔ ArtiprovTarifapro)
 * - Sincronización de proveedores (SupplierErpProvider ↔ Proveedor)
 * - Sincronización de productos (SupplierProduct ↔ Articulo)
 * - Detección y resolución de conflictos (ERP siempre gana)
 * - Prevención de loops infinitos mediante cache flags
 *
 * @package Modules\Supplier\Services
 */
class ErpSyncService
{
    public function __construct()
    {
    }

    /**
     * Sincronizar precio de Supplier → Oracle
     *
     * Estrategia:
     * 1. Buscar precio en Oracle por ID
     * 2. Detectar conflicto si ERP fue modificado después del último sync
     * 3. Si hay conflicto: ERP gana (re-sincronizar desde Oracle)
     * 4. Si no hay conflicto: actualizar Oracle con cambios de Supplier
     * 5. Actualizar last_synced_at en Supplier
     *
     * @param SupplierProductPrice $price Precio a sincronizar
     * @param array $changedFields Campos que cambiaron (ej: ['cost', 'discount1'])
     * @return void
     * @throws Exception Si el precio no existe en Oracle
     */
    public function syncPriceToOracle(SupplierProductPrice $price, array $changedFields): void
    {
        $oraclePrice = ArtiprovTarifapro::on('oracle')
            ->find($price->erp_price_id);

        if (!$oraclePrice) {
            throw new Exception("Oracle price not found: {$price->erp_price_id}");
        }

        // CONFLICT DETECTION: Si ERP cambió después del último sync
        if ($this->hasConflict($oraclePrice, $price)) {
            Log::warning('Conflict detected in price sync - ERP takes precedence', [
                'supplier_price_id' => $price->id,
                'erp_price_id' => $oraclePrice->idartiprov_tarifapro,
                'supplier_updated_at' => $price->updated_at,
                'erp_updated_at' => $oraclePrice->fmodificacion,
                'changed_fields' => $changedFields,
            ]);

            // Re-sincronizar DESDE ERP (ERP gana)
            $this->syncPriceFromOracle($oraclePrice, $price);
            return;
        }

        // No hay conflicto: actualizar Oracle con cambios de Supplier
        $updateData = [];

        if (in_array('cost', $changedFields)) {
            $updateData['pcosto'] = $price->cost;
        }

        if (in_array('discount1', $changedFields)) {
            $updateData['dto1'] = $price->discount1 ?? 0;
        }

        if (in_array('discount2', $changedFields)) {
            $updateData['dto2'] = $price->discount2 ?? 0;
        }

        if (in_array('effective_date', $changedFields)) {
            $updateData['fecha'] = $price->effective_date;
        }

        if (in_array('is_active', $changedFields)) {
            $updateData['estado'] = $price->is_active ? 1 : 0;
        }

        // Actualizar Oracle
        $oraclePrice->update($updateData);

        // Actualizar timestamp de último sync en Supplier
        $price->update(['last_synced_at' => now()]);

        Log::info('Price synced to ERP successfully', [
            'supplier_price_id' => $price->id,
            'erp_price_id' => $oraclePrice->idartiprov_tarifapro,
            'changed_fields' => $changedFields,
        ]);
    }

    /**
     * Detectar conflicto: ¿ERP fue modificado después del último sync de Supplier?
     *
     * @param ArtiprovTarifapro $oraclePrice
     * @param SupplierProductPrice $supplierPrice
     * @return bool True si hay conflicto
     */
    protected function hasConflict(ArtiprovTarifapro $oraclePrice, SupplierProductPrice $supplierPrice): bool
    {
        // Si ERP no tiene last_synced_at, es primera vez → sin conflicto
        if (!$supplierPrice->last_synced_at) {
            return false;
        }

        // Si ERP fue modificado después del último sync de Supplier → conflicto
        return $oraclePrice->fmodificacion > $supplierPrice->last_synced_at;
    }

    /**
     * Re-sincronizar DESDE ERP cuando hay conflicto (ERP gana)
     *
     * Copia valores de Oracle a Supplier
     *
     * @param ArtiprovTarifapro $oraclePrice
     * @param SupplierProductPrice $supplierPrice
     * @return void
     */
    protected function syncPriceFromOracle(ArtiprovTarifapro $oraclePrice, SupplierProductPrice $supplierPrice): void
    {
        $supplierPrice->update([
            'cost' => $oraclePrice->pcosto,
            'discount1' => $oraclePrice->dto1 ?? 0,
            'discount2' => $oraclePrice->dto2 ?? 0,
            'effective_date' => $oraclePrice->fecha,
            'is_active' => (bool) $oraclePrice->estado,
            'erp_updated_at' => $oraclePrice->fmodificacion,
            'last_synced_at' => now(),
        ]);

        Log::warning('Price conflict resolved - synced from ERP (ERP value kept)', [
            'supplier_price_id' => $supplierPrice->id,
            'erp_price_id' => $oraclePrice->idartiprov_tarifapro,
        ]);
    }

    /**
     * Sincronizar proveedor de Supplier → Oracle
     *
     * @param SupplierErpProvider $provider
     * @param array $changedFields
     * @return void
     * @throws Exception
     */
    public function syncProviderToOracle(SupplierErpProvider $provider, array $changedFields): void
    {
        $oracleProvider = Proveedor::on('oracle')
            ->find($provider->erp_provider_id);

        if (!$oracleProvider) {
            throw new Exception("Oracle provider not found: {$provider->erp_provider_id}");
        }

        // Conflict detection
        if ($this->hasProviderConflict($oracleProvider, $provider)) {
            Log::warning('Conflict detected in provider sync - ERP takes precedence', [
                'supplier_provider_id' => $provider->id,
                'erp_provider_id' => $oracleProvider->idproveedor,
                'changed_fields' => $changedFields,
            ]);

            $this->syncProviderFromOracle($oracleProvider, $provider);
            return;
        }

        // Map fields: Supplier → Oracle
        $updateData = [];

        if (in_array('name', $changedFields)) {
            $updateData['nombre'] = $provider->name;
        }

        if (in_array('email', $changedFields)) {
            $updateData['email'] = $provider->email;
        }

        if (in_array('phone', $changedFields)) {
            $updateData['telefono1'] = $provider->phone;
        }

        if (in_array('website', $changedFields)) {
            $updateData['web'] = $provider->website;
        }

        if (in_array('shipping_cost', $changedFields)) {
            $updateData['portes'] = $provider->shipping_cost;
        }

        if (in_array('discount_percent', $changedFields)) {
            $updateData['dto'] = $provider->discount_percent;
        }

        if (in_array('partial_delivery_days', $changedFields)) {
            $updateData['dias_servir_parcial'] = $provider->partial_delivery_days;
        }

        $oracleProvider->update($updateData);
        $provider->update(['last_synced_at' => now()]);

        Log::info('Provider synced to ERP successfully', [
            'supplier_provider_id' => $provider->id,
            'erp_provider_id' => $oracleProvider->idproveedor,
            'changed_fields' => $changedFields,
        ]);
    }

    /**
     * Detectar conflicto en proveedor
     *
     * @param Proveedor $oracleProvider
     * @param SupplierErpProvider $supplierProvider
     * @return bool
     */
    protected function hasProviderConflict(Proveedor $oracleProvider, SupplierErpProvider $supplierProvider): bool
    {
        if (!$supplierProvider->last_synced_at) {
            return false;
        }

        return $oracleProvider->fmodificacion > $supplierProvider->last_synced_at;
    }

    /**
     * Re-sincronizar proveedor desde Oracle
     *
     * @param Proveedor $oracleProvider
     * @param SupplierErpProvider $supplierProvider
     * @return void
     */
    protected function syncProviderFromOracle(Proveedor $oracleProvider, SupplierErpProvider $supplierProvider): void
    {
        $supplierProvider->update([
            'name' => $oracleProvider->nombre,
            'email' => $oracleProvider->email,
            'phone' => $oracleProvider->telefono1,
            'website' => $oracleProvider->web,
            'shipping_cost' => $oracleProvider->portes,
            'discount_percent' => $oracleProvider->dto,
            'partial_delivery_days' => $oracleProvider->dias_servir_parcial,
            'erp_updated_at' => $oracleProvider->fmodificacion,
            'last_synced_at' => now(),
        ]);

        Log::warning('Provider conflict resolved - synced from ERP (ERP value kept)', [
            'supplier_provider_id' => $supplierProvider->id,
            'erp_provider_id' => $oracleProvider->idproveedor,
        ]);
    }

    /**
     * Sincronizar producto de Supplier → Oracle
     *
     * @param SupplierProduct $product
     * @param array $changedFields
     * @return void
     * @throws Exception
     */
    public function syncProductToOracle(SupplierProduct $product, array $changedFields): void
    {
        $oracleProduct = Articulo::on('oracle')
            ->find($product->erp_product_id);

        if (!$oracleProduct) {
            throw new Exception("Oracle product not found: {$product->erp_product_id}");
        }

        // Conflict detection
        if ($this->hasProductConflict($oracleProduct, $product)) {
            Log::warning('Conflict detected in product sync - ERP takes precedence', [
                'supplier_product_id' => $product->id,
                'erp_product_id' => $oracleProduct->idarticulo,
                'changed_fields' => $changedFields,
            ]);

            $this->syncProductFromOracle($oracleProduct, $product);
            return;
        }

        // Map fields: Supplier → Oracle
        $updateData = [];

        if (in_array('name', $changedFields)) {
            $updateData['descripcion'] = $product->name;
        }

        if (in_array('barcode', $changedFields)) {
            $updateData['codbar'] = $product->barcode;
        }

        if (in_array('recommended_price', $changedFields)) {
            $updateData['preciorecomendadoprov'] = $product->recommended_price;
        }

        if (in_array('is_active', $changedFields)) {
            $updateData['estado'] = $product->is_active ? 1 : 0;
        }

        $oracleProduct->update($updateData);
        $product->update(['last_synced_at' => now()]);

        Log::info('Product synced to ERP successfully', [
            'supplier_product_id' => $product->id,
            'erp_product_id' => $oracleProduct->idarticulo,
            'changed_fields' => $changedFields,
        ]);
    }

    /**
     * Detectar conflicto en producto
     *
     * @param Articulo $oracleProduct
     * @param SupplierProduct $supplierProduct
     * @return bool
     */
    protected function hasProductConflict(Articulo $oracleProduct, SupplierProduct $supplierProduct): bool
    {
        if (!$supplierProduct->last_synced_at) {
            return false;
        }

        return $oracleProduct->fmodificacion > $supplierProduct->last_synced_at;
    }

    /**
     * Re-sincronizar producto desde Oracle
     *
     * @param Articulo $oracleProduct
     * @param SupplierProduct $supplierProduct
     * @return void
     */
    protected function syncProductFromOracle(Articulo $oracleProduct, SupplierProduct $supplierProduct): void
    {
        $supplierProduct->update([
            'name' => $oracleProduct->descripcion,
            'barcode' => $oracleProduct->codbar,
            'recommended_price' => $oracleProduct->preciorecomendadoprov,
            'is_active' => (bool) $oracleProduct->estado,
            'erp_updated_at' => $oracleProduct->fmodificacion,
            'last_synced_at' => now(),
        ]);

        Log::warning('Product conflict resolved - synced from ERP (ERP value kept)', [
            'supplier_product_id' => $supplierProduct->id,
            'erp_product_id' => $oracleProduct->idarticulo,
        ]);
    }
}
