# Sync Agents Architecture

## System Overview

The sync agent system provides a modular, extensible framework for synchronizing supplier data with the ERP system. Four concrete agents handle different entity types, all coordinated by a base class and service layer.

```
┌─────────────────────────────────────────────────────────────┐
│                    SyncCoordinatorAgent                      │
│            (Orchestrates multiple sync operations)           │
└────────┬────────┬────────┬────────┬────────────────────────┘
         │        │        │        │
    ┌────┴─┐  ┌──┴──┐  ┌──┴──┐  ┌──┴──┐
    │      │  │     │  │     │  │     │
    ▼      ▼  ▼     ▼  ▼     ▼  ▼     ▼
  Product Category Price  Provider
   Agent   Agent   Agent   Agent
    │      │        │       │
    └──────┴────────┴───────┘
           │
           ▼
    ┌──────────────────┐
    │ BaseSyncAgent    │
    │ (Abstract Base)  │
    └──────────────────┘
           │
    ┌──────┴──────────┬──────────┬──────────┐
    │                 │          │          │
    ▼                 ▼          ▼          ▼
SyncStatusService  ErpSyncService  Model  Database
                                  Layer   (Logging)
```

## Class Hierarchy

```
BaseSyncAgent (Abstract)
    ├── ProductSyncAgent
    │   └── Handles: SupplierProduct → ERP
    │       Models: SupplierProduct, SupplierProviderProduct
    │
    ├── CategorySyncAgent
    │   └── Handles: SupplierCategory → ERP
    │       Models: SupplierCategory, Catalog\Category
    │
    ├── PriceSyncAgent
    │   └── Handles: SupplierProductPrice → ERP
    │       Models: SupplierProductPrice, SupplierProviderProduct
    │
    └── ProviderSyncAgent
        └── Handles: SupplierErpProvider ↔ ERP (Bidirectional)
            Models: SupplierErpProvider, SupplierProviderProduct
```

## Data Flow

### Unidirectional Sync (Product, Category, Price)

```
┌─────────────────────┐
│  fetchItems()       │
│  Get unsynced items │
│  from Supplier DB   │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  processItem()      │
│  Per-item logic:    │
│  - Validate         │
│  - Detect changes   │
│  - Check conflicts  │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  ErpSyncService     │
│  Send to ERP via    │
│  API/Driver         │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Update Timestamps  │
│  - last_synced_at   │
│  - Mark success     │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  logAction()        │
│  Audit trail        │
│  Success/Failure    │
└─────────────────────┘
```

### Bidirectional Sync (Provider)

```
┌─────────────────────────────────────────┐
│  fetchItems()                           │
│  Get providers changed in Supplier DB   │
│  (or ERP → Supplier if configured)      │
└──────────┬──────────────────────────────┘
           │
           ▼
┌─────────────────────────────────────────┐
│  Conflict Detection                     │
│  If ERP modified after last_synced_at:  │
│    ERP WINS: Re-sync FROM ERP            │
│  Else:                                  │
│    Sync TO ERP                          │
└──────────┬──────────────────────────────┘
           │
         ┌─┴─┐
         │   │
    ┌────▼┐ ┌┴────┐
    │ ERP │ │ TO  │
    │WINS │ │ ERP │
    └────┘ └─────┘
```

## Batch Processing

All agents process items in configurable batches:

```
Total Items: 10,500 items
Batch Size: 100 items
Total Batches: 106

┌─────────┐  ┌─────────┐  ┌─────────┐         ┌─────────┐
│ Batch 1 │─→│ Batch 2 │─→│ Batch 3 │─→...─→ │Batch 106│
│ 100 it  │  │ 100 it  │  │ 100 it  │        │  100 it │
└────┬────┘  └────┬────┘  └────┬────┘        └────┬────┘
     │            │            │                   │
     ▼            ▼            ▼                   ▼
┌──────────────────────────────────────────────────────┐
│         Report Progress After Each Batch             │
│  - Update Redis cache                               │
│  - Update database status                           │
│  - Check for cancellation request                   │
└──────────────────────────────────────────────────────┘
```

## Error Handling Flow

```
                    ┌─────────────────┐
                    │ processItem()   │
                    └────────┬────────┘
                             │
                    ┌────────▼────────┐
                    │ Validation      │
                    │ Checks          │
                    └────────┬────────┘
                             │
              ┌──────────────┼──────────────┐
              │              │              │
         FAIL │         SKIP │          OK  │
              │              │              │
              ▼              ▼              ▼
        ┌─────────┐    ┌──────────┐   ┌─────────┐
        │recordFail    │Skip Item │   │  Sync   │
        │ure()   │    │(no error)│   │ to ERP  │
        │        │    │          │   │         │
        └─────────┘    └──────────┘   └────┬────┘
              │              │             │
              │              │             ▼
              │              │        ┌─────────┐
              │              │        │ Success │
              │              │        │  +1     │
              │              │        └─────────┘
              │              │
              ▼              ▼
        ┌──────────────────────────┐
        │ Count in Results:        │
        │ - items_failed: +1       │
        │ - items_skipped: +1      │
        │ - items_processed: +1    │
        └──────────────────────────┘
```

## Database Persistence

### Sync Batch Record

```sql
supplier_sync_batches
├── id: bigint
├── supplier_id: bigint (nullable)
├── batch_name: varchar
├── sync_type: enum('product','category','price','provider')
├── status: enum('pending','running','completed','failed','cancelled')
├── priority: enum('low','normal','high','urgent')
├── batch_size: int (items per batch)
├── total_items: int
├── processed_items: int
├── failed_items: int
├── triggered_by: varchar
├── created_at: timestamp
└── updated_at: timestamp
```

### Sync Status Record

```sql
supplier_sync_statuses
├── id: bigint
├── batch_id: bigint (FK)
├── status: varchar
├── total_items: int
├── synced_count: int
├── failed_count: int
├── skipped_count: int
├── completed_at: timestamp
├── failure_reason: text
├── metadata: json
└── ...
```

### Sync Action Log (Audit Trail)

```sql
supplier_sync_logs
├── id: bigint
├── batch_id: bigint (FK)
├── entity_type: varchar ('product','category','price','provider')
├── entity_id: int
├── action: varchar ('sync','verify','rollback')
├── result: enum('success','failed','skipped')
├── message: text
├── data_before: json (optional)
├── data_after: json (optional)
├── changes: json (changed fields)
├── error_code: varchar (optional)
├── error_message: text (optional)
├── duration_ms: int
├── created_at: timestamp
└── ...
```

### Sync Failure Record (For Retry)

```sql
supplier_sync_failures
├── id: bigint
├── batch_id: bigint (FK)
├── sync_type: varchar
├── supplier_id: int
├── entity_id: int
├── erp_id: int (optional)
├── changed_data: json
├── error_message: text
├── error_code: varchar
├── context: json
├── retry_count: int
├── max_retries: int
├── next_retry_at: timestamp
├── resolved_at: timestamp (optional)
├── created_at: timestamp
└── ...
```

## Service Layer Integration

```
┌────────────────────────────────────────────────┐
│  SyncStatusService                              │
│  ├── startSync()                                │
│  ├── updateProgress()                           │
│  ├── completeSync()                             │
│  ├── failSync()                                 │
│  ├── recordFailure()                            │
│  ├── logAction()                                │
│  ├── isCancellationRequested()                  │
│  └── requestCancellation()                      │
└────────────────┬───────────────────────────────┘
                 │
    ┌────────────┴────────────┐
    │                         │
    ▼                         ▼
 Redis              Database
 (Cache)            (Logs)
 ├─ cancel:{batchId} ├─ sync_logs
 ├─ progress:{batchId} ├─ sync_statuses
 └─ status:{batchId}   └─ sync_failures

┌────────────────────────────────────────────────┐
│  ErpSyncService                                 │
│  ├── syncProductToOracle()                      │
│  ├── syncCategoryToOracle()                     │
│  ├── syncPriceToOracle()                        │
│  ├── syncProviderToOracle()                     │
│  ├── hasConflict()                              │
│  └── registerConflict()                         │
└────────────────┬───────────────────────────────┘
                 │
    ┌────────────┴────────────┐
    │                         │
    ▼                         ▼
 Oracle Driver       Conflict Detection
 (ERP API)           & Resolution
```

## Lifecycle States

### Agent Execution

```
┌──────────────┐
│   Created    │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│   execute()  │
│  called      │
└──────┬───────┘
       │
       ▼
┌──────────────────┐
│ initializeSync() │
│ Create status    │
│ record           │
└──────┬───────────┘
       │
       ▼
┌──────────────────┐    ┌─────────────┐
│  fetchItems()    │───→│ No items?   │
│  Get items from  │    │ Skip to     │
│  database        │    │ complete    │
└──────┬───────────┘    └─────────────┘
       │
       ▼
┌──────────────────┐
│ processBatch()   │
│ Loop through     │
│ items in batches │
└──────┬───────────┘
       │
       ├─→ Success: completeSync()  ──→ ┌──────────────┐
       │                                │   COMPLETE   │
       ├─→ Cancelled: failSync()  ──────→ (with stats) │
       │                                └──────────────┘
       └─→ Error: failSync()  ──────────→ ┌──────────────┐
                                         │   FAILED     │
                                         │(with reason) │
                                         └──────────────┘
```

## Queryable and Indexable Models

### Models Used

```
SupplierProduct
├── Columns: id, supplier_id, erp_product_id, code, name, ...
├── Relationships: group, supplier, providerProducts
└── Scopes: active(), byErpId(), byCode(), needsSync()

SupplierCategory (Pivot)
├── Columns: id, supplier_id, category_id, priority, is_active
├── Relationships: supplier, category
└── Scopes: active(), forSupplier(), byPriority()

SupplierProductPrice
├── Columns: id, provider_product_id, erp_price_id, cost, ...
├── Relationships: providerProduct
└── Scopes: active(), current(), byErpId(), needsSync()

SupplierErpProvider
├── Columns: id, supplier_id, erp_provider_id, code, name, ...
├── Relationships: supplier, providerProducts
└── Scopes: active(), byErpId(), byCode(), needsSync()

SupplierProviderProduct
├── Columns: id, provider_id, product_id, erp_artiprov_id, ...
├── Relationships: provider, product, prices
└── Scopes: active(), byErpId(), needsSync()
```

## Performance Characteristics

### Query Optimization

- **Eager Loading**: All relationships preloaded to prevent N+1
- **Cursor Pagination**: Large result sets streamed via cursor()
- **Batch Processing**: 100-500 items per batch (configurable)
- **Database Indexes**: On last_synced_at, erp_updated_at, supplier_id

### Typical Performance

```
Entity Type    Count    Time    Rate
────────────────────────────────────
Product        5,000    45s    111/sec
Category       500      8s     62/sec
Price          10,000   120s   83/sec
Provider       1,000    15s    67/sec
────────────────────────────────────
Total Sync     16,500   ~3m    91/sec (avg)
```

## Failure Recovery

### Retry Flow

```
Initial Sync Fails
    │
    ▼
recordFailure()
├── record_failure entry created
├── retry_count = 0
├── max_retries = 3
└── next_retry_at = now + 5 minutes
    │
    ▼
Queue Check (Every 5 min)
├── Find failures with next_retry_at <= now
├── retry_count < max_retries
└── Spawn RetryFailedExecutionJob
    │
    ▼
Retry Attempt
├── Load original failed item
├── Re-validate
├── Re-sync to ERP
├── If success: Mark resolved_at
├── If fail: Increment retry_count
│           Update next_retry_at
└── At max_retries: Alert admin
```

## Concurrency & Locking

### No Locking Strategy

Agents are designed to work independently without locking:

- **Supplier filtering**: Different suppliers synced independently
- **Entity type separation**: Each agent handles one type
- **Timestamp tracking**: Prevents duplicate syncs via last_synced_at
- **Batch isolation**: Batches don't interfere with each other

Multiple agents can run concurrently on different batches without issues.

## Testing Strategy

```
Unit Tests
├── ProductSyncAgent
├── CategorySyncAgent
├── PriceSyncAgent
└── ProviderSyncAgent

Integration Tests
├── Agent + SyncStatusService
├── Agent + ErpSyncService
├── Agent + Database
└── Full sync lifecycle

End-to-End Tests
├── Controller → Agent → ERP
├── Progress monitoring
├── Cancellation handling
└── Error recovery
```

---

See `SYNC_AGENTS_QUICK_REFERENCE.md` for detailed feature reference.
See `SYNC_AGENTS_USAGE_EXAMPLES.md` for code examples.
