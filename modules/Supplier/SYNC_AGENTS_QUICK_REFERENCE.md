# Sync Agents Quick Reference

## Overview

Four concrete sync agent classes have been created to handle entity synchronization with the ERP system. Each extends `BaseSyncAgent` and implements specific sync logic for different entity types.

## Files Created

```
modules/Supplier/app/Services/
├── ProductSyncAgent.php        (431 lines)
├── CategorySyncAgent.php       (376 lines)
├── PriceSyncAgent.php          (505 lines)
└── ProviderSyncAgent.php       (522 lines)
```

Total: 1,834 lines of production code with comprehensive error handling and logging.

---

## 1. ProductSyncAgent

**Purpose:** Synchronizes `SupplierProduct` entities to ERP

**Location:** `modules/Supplier/app/Services/ProductSyncAgent.php`

### Key Features

- Syncs unsynced or outdated products from Supplier DB to ERP
- Maps product code/reference to ERP identifiers
- Validates product data (required fields, pricing constraints)
- Detects changes (code, name, pricing, dimensions, status flags)
- Supports filtering by supplier and date range
- Prevents N+1 queries with eager loading
- Records audit trail of all sync actions

### Usage

```php
$agent = new ProductSyncAgent($batch, $syncStatusService, $erpSyncService);
$result = $agent
    ->forSupplier(123)
    ->withinDateRange('2025-01-01', '2025-01-31')
    ->execute();
```

### Fetchable Items

- Products with `is_active = true`
- Never synced: `last_synced_at IS NULL`
- Modified: `erp_updated_at > last_synced_at`
- Ordered by: `erp_updated_at DESC, id ASC`

### Validation Rules

- Product has name and code
- Product has supplier_id
- Product has erp_product_id (skip if missing)
- Cost values are non-negative
- All pricing fields present

### Changed Fields Detection

Tracked fields:
- `name`, `code`, `barcode`, `reference`
- `average_cost`, `last_purchase_cost`, `recommended_price`
- `dimensions` (length, width, height, weight)
- `is_active`, `brand_name`, `model_name`

---

## 2. CategorySyncAgent

**Purpose:** Synchronizes `SupplierCategory` pivot table entries to ERP

**Location:** `modules/Supplier/app/Services/CategorySyncAgent.php`

### Key Features

- Syncs supplier-category assignments to ERP
- Maintains hierarchical category relationships
- Validates parent categories before syncing children
- Respects priority ordering (highest first)
- Supports hierarchical and flat sync modes
- Records category-supplier linkages

### Usage

```php
$agent = new CategorySyncAgent($batch, $syncStatusService, $erpSyncService);
$result = $agent
    ->forSupplier(123)
    ->hierarchicalOnly(true)
    ->execute();
```

### Fetchable Items

- SupplierCategory with `is_active = true`
- Loads: supplier, category relationships
- Ordered by: `priority DESC, id ASC`

### Validation Rules

- Category has supplier_id
- Category has category_id
- Category relationship exists
- Supplier relationship exists
- Priority value is non-negative

### Parent Validation

- Root categories (no parent): always valid
- Child categories: parent must exist in ERP before sync

### Changed Fields Detection

Tracked fields:
- `supplier_id`, `category_id`, `priority`, `is_active`
- Metadata structure

---

## 3. PriceSyncAgent

**Purpose:** Synchronizes `SupplierProductPrice` entities (price history and current prices)

**Location:** `modules/Supplier/app/Services/PriceSyncAgent.php`

### Key Features

- Syncs product pricing with history tracking
- Validates discount calculations (0-100%)
- Detects and resolves conflicts (ERP wins)
- Supports date range filtering by effective date
- Automatically recalculates final_cost
- Maintains price version control
- Current vs historical price distinction

### Usage

```php
$agent = new PriceSyncAgent($batch, $syncStatusService, $erpSyncService);
$result = $agent
    ->forSupplier(123)
    ->withinDateRange('2025-01-01', '2025-01-31')
    ->currentPricesOnly(true)
    ->execute();
```

### Fetchable Items

- Prices with `is_active = true`
- Optional: `is_current = true` only
- Via supplier: loaded through providerProduct → provider relationships
- Ordered by: `erp_updated_at DESC, id ASC`

### Validation Rules

- Cost is present and >= 0
- Discount1 in range [0, 100]
- Discount2 in range [0, 100]
- provider_product_id exists
- effective_date is present

### Price Conflict Detection

- Compares: `erp_updated_at > last_synced_at`
- If conflict: re-sync FROM ERP (ERP takes precedence)
- Records conflict in audit log

### Changed Fields Detection

Tracked fields:
- `cost`, `discount1`, `discount2`
- `effective_date`, `is_active`, `is_current`
- Automatic final_cost recalculation if mismatch > 0.01

---

## 4. ProviderSyncAgent

**Purpose:** Synchronizes `SupplierErpProvider` entities (bidirectional)

**Location:** `modules/Supplier/app/Services/ProviderSyncAgent.php`

### Key Features

- Bidirectional sync: Supplier ↔ ERP
- Provider contact and address info
- Shipping costs and discount management
- Connection verification before sync
- Status flag management (active, visible, creditor)
- Provider relationship tracking
- Credential validation

### Usage

```php
$agent = new ProviderSyncAgent($batch, $syncStatusService, $erpSyncService);
$result = $agent
    ->forSupplier(123)
    ->bidirectional(true)
    ->execute();
```

### Fetchable Items

- SupplierErpProvider with `is_active = true`
- Loads: supplier relationship
- Ordered by: `erp_updated_at DESC, id ASC`

### Validation Rules

- Provider has name and code
- Provider has erp_provider_id
- Email format is valid (if provided)
- Shipping cost >= 0
- Discount percent >= 0
- Country code length >= 2 (if provided)

### Connection Verification

- Checks provider is_active status
- Validates basic connectivity
- Can be extended for actual API/DB connection tests
- Failure prevents sync from proceeding

### Changed Fields Detection

Tracked fields:
- Basic: `name`, `code`, `tax_id`
- Contact: `contact_person`, `email`, `phone`, `phone_alt`, `fax`, `website`
- Address: `street`, `street_number`, `city`, `postal_code`, `province`, `country`
- Shipping: `shipping_cost`, `discount_percent`, `partial_delivery_*`
- Status: `is_active`, `is_visible`, `is_creditor`
- Notes: `notes`, `shipping_notes`

---

## Shared Base Features (All Agents)

### Initialization

```php
->initializeSync(
    totalItems: int,
    triggeredBy: 'manual|scheduled|webhook|api'
)
```

### Batch Processing

- Automatic batch sizing (default 100 items per batch)
- Progress reporting to Redis/Database
- Cancellation support via cache flag
- Automatic error handling and logging

### Error Recording

```php
->recordFailure(
    syncType: string,
    supplierId: int,
    entityId: int,
    erpId: int|null,
    changedData: array,
    errorMessage: string,
    errorCode: string,
    maxRetries: 3
)
```

### Action Logging

```php
->logAction(
    entityType: string,
    entityId: int,
    action: string,
    result: 'success|failed|skipped',
    message: string,
    changes: array|null,
    errorCode: string|null,
    durationMs: int
)
```

### Results Format

All `execute()` methods return:

```php
[
    'success' => bool,
    'items_processed' => int,
    'items_failed' => int,
    'items_skipped' => int,
    'message' => string,
    // ... additional metadata from completeSync()
]
```

---

## Error Handling

### Skip Patterns

- No ERP ID mapping → Skip with reason
- No changes detected → Skip with reason
- Validation failure → Fail with error record

### Failure Patterns

- Sync to ERP failed → Record failure for retry
- Connection failed → Fail with error code
- Critical exception → Fail whole batch

### Retry Strategy

All failures recorded with:
- `max_retries: 3` (configurable per recordFailure call)
- Sync type and entity identifiers
- Full context for later retry processing

---

## Performance Considerations

### Eager Loading

All agents use eager loading to prevent N+1 queries:

```php
->with(['relationship1', 'relationship2.nested'])
```

### Cursor Pagination

Results processed via `cursor()` for memory efficiency with large datasets:

```php
$query->cursor()  // Generator, not in-memory collection
```

### Batch Processing

Items processed in configurable batches (default 100):
- Progress reported after each batch
- Cancellation checked per item
- Performance metrics tracked

### Database Indexes

Relies on performance indexes on:
- `last_synced_at`, `erp_updated_at` (filtering)
- `supplier_id` (partitioning)
- `is_active` (filtering)

---

## Integration with ErpSyncService

Each agent calls one of:

- `$erpSyncService->syncProductToOracle($product, $changedFields)`
- `$erpSyncService->syncCategoryToOracle($category)`
- `$erpSyncService->syncPriceToOracle($price, $changedFields)`
- `$erpSyncService->syncProviderToOracle($provider, $changedFields)`

These methods handle:
- ERP connection and API calls
- Conflict detection
- Transaction management
- Error handling

---

## Usage in SyncCoordinatorAgent

The coordinator spawns agents based on sync type:

```php
$coordinator->coordinateSync(
    syncType: 'all|product|category|price|provider',
    supplierId: 123,
    triggeredBy: 'manual',
    filterCriteria: [...]
)
```

Each agent runs independently with its own:
- Sync batch record
- Status tracking
- Error handling
- Progress reporting

---

## Testing

Each agent can be unit tested independently:

```php
$batch = SupplierSyncBatch::create([...]);
$agent = new ProductSyncAgent($batch, $syncStatusService, $erpSyncService);
$result = $agent->forSupplier(123)->execute();

assert($result['success'] === true);
assert($result['items_processed'] > 0);
```

---

## Logging

All agents log at multiple levels:

- **DEBUG**: Detailed fetch operations, item processing
- **INFO**: Sync started/completed, summary statistics
- **WARNING**: Item failures, conflicts, skipped items
- **ERROR**: Critical sync failures, exceptions

Searchable by:
- `batch_id`: Batch the sync belongs to
- `agent_type`: Fully qualified class name
- `entity_id`: The item being synced
- `error`: Exception message

Example:

```php
Log::info('Product synced successfully', [
    'product_id' => $product->id,
    'erp_id' => $product->erp_product_id,
    'code' => $product->code,
    'changed_fields' => $changedFields,
    'duration_ms' => $durationMs,
]);
```

---

## Migration from Entities to Models

The agents query the Models folder (not deleted Entities):

```
modules/Supplier/app/Models/
├── SupplierProduct.php
├── SupplierCategory.php
├── SupplierProductPrice.php
├── SupplierErpProvider.php
├── SupplierProviderProduct.php
└── ... (other models)
```

All model relationships are fully loaded before sync to ensure data consistency.
