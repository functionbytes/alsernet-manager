# Supplier Module Synchronization System Documentation

## Overview

The Supplier module's synchronization system tracks the full lifecycle of data synchronization between the Supplier module and the ERP system. This comprehensive system is built on four core database tables and their corresponding Eloquent models.

## Database Tables & Migrations

### 1. `supplier_sync_statuses` Table
**Migration:** `2025_01_16_create_supplier_sync_statuses_table.php`

Tracks overall sync progress and high-level status information.

**Key Columns:**
- `uid` - ULID unique identifier (26 chars)
- `supplier_id` - Optional supplier FK (for supplier-specific syncs)
- `batch_id` - Associated batch FK
- `sync_type` - Type: 'product', 'category', 'price', 'provider'
- `sync_scope` - Scope: 'all', 'per_supplier', 'per_category'
- `status` - 'pending', 'running', 'completed', 'failed', 'paused'
- `total_items`, `synced_items`, `failed_items`, `skipped_items` - Progress metrics
- `started_at`, `completed_at` - Timestamps
- `elapsed_seconds`, `memory_used_mb` - Performance metrics
- `triggered_by` - Source: 'manual', 'scheduled', 'webhook', 'api'
- `metadata` - JSON additional tracking data

**Indexes:**
- `[status, sync_type]` - Quick status lookups
- `sync_scope` - Filter by scope
- `started_at`, `completed_at` - Time-based queries

---

### 2. `supplier_sync_batches` Table
**Migration:** `2025_01_16_create_supplier_sync_batches_table.php`

Groups related sync operations together and manages batch-level retry logic.

**Key Columns:**
- `uid` - ULID unique identifier
- `supplier_id` - Optional supplier FK
- `batch_name` - Human-readable name
- `sync_type` - Type of sync operation
- `status` - 'pending', 'running', 'completed', 'failed', 'cancelled'
- `priority` - 'low', 'normal', 'high', 'urgent'
- `batch_size` - Items per batch iteration (default: 100)
- `total_batches`, `processed_batches` - Sub-batch tracking
- `total_items`, `processed_items`, `failed_items` - Item counters
- `retry_attempt`, `max_retries` - Retry logic
- `duration_seconds` - Total execution time
- `filter_criteria` - JSON filters applied (e.g., category_id, date_range)
- `metadata` - Additional batch context

**Indexes:**
- `[status, sync_type]` - Status filtering
- `supplier_id` - Supplier-specific lookups
- `[priority, status]` - Priority queue processing
- `created_at` - Timeline queries

---

### 3. `supplier_sync_logs` Table
**Migration:** `2025_01_16_create_supplier_sync_logs_table.php`

Detailed event logs for individual sync actions with comprehensive audit trail.

**Key Columns:**
- `uid` - ULID unique identifier
- `batch_id` - FK to sync batch
- `status_id` - FK to sync status
- `entity_type` - Type of entity synced
- `entity_id`, `erp_id` - Both local and ERP identifiers
- `action` - 'create', 'update', 'delete', 'skip'
- `result` - 'success', 'failed', 'skipped', 'warning'
- `message` - Detailed log message
- `data_before`, `data_after` - JSON snapshots
- `changes` - JSON field-level changes
- `error_code`, `error_message` - Failure details
- `duration_ms` - Execution time in milliseconds
- `triggered_by` - Action source
- `synced_at` - When action occurred

**Indexes:**
- `[batch_id, result]` - Batch result filtering
- `[entity_type, entity_id]` - Entity lookups
- `action`, `result`, `error_code` - Quick filtering
- `synced_at`, `created_at` - Timeline queries

---

### 4. `supplier_sync_failures` Table (Enhanced)
**Migration:** `2025_01_16_update_supplier_sync_failures_table.php`

Dead Letter Queue for failed sync attempts with enhanced retry and resolution logic.

**Enhanced Columns:**
- `uid` - ULID unique identifier
- `batch_id` - FK to sync batch (new)
- `sync_type` - Failure type
- `supplier_id`, `entity_id`, `erp_id` - Entity identifiers
- `changed_data` - JSON data that failed to sync
- `context` - JSON additional context (new)
- `error_code`, `error_message` - Error details
- `retry_count`, `max_retries` - Retry tracking (new: max_retries)
- `failure_status` - 'pending', 'resolved', 'acknowledged', 'archived' (new)
- `last_retry_at` - Last retry timestamp
- `resolved_at`, `resolved_by_user_id`, `resolution_notes` - Resolution tracking (new)

**Indexes:**
- `batch_id` - Batch lookup
- `[sync_type, entity_id]` - Entity failures
- `failure_status` - Status filtering
- `resolved_at` - Resolution timeline

---

## Eloquent Models

### Model: `SupplierSyncStatus`
**Location:** `/modules/Supplier/app/Models/SupplierSyncStatus.php`

**Traits:** `HasFactory`, `HasUid`

**Key Methods:**
- `supplier()` - BelongsTo relationship
- `batch()` - BelongsTo relationship
- `logs()` - HasMany SupplierSyncLog
- `failures()` - HasMany SupplierSyncFailure
- `getProgressPercentageAttribute()` - Calculate progress %
- `getSuccessRateAttribute()` - Calculate success %
- `isRunning()`, `isCompleted()`, `isFailed()` - Status checks
- `canRetry()` - Check if retryable
- `markAsStarted()`, `markAsCompleted()`, `markAsFailed()` - Status transitions

**Scopes:**
- `running()`, `completed()`, `failed()` - Status filtering
- `byType(string)` - Filter by sync type
- `byScope(string)` - Filter by scope
- `triggeredBy(string)` - Filter by trigger source
- `recent(int)` - Recent syncs (hours)
- `slow(float)` - Slow syncs (threshold seconds)

---

### Model: `SupplierSyncBatch`
**Location:** `/modules/Supplier/app/Models/SupplierSyncBatch.php`

**Traits:** `HasFactory`, `HasUid`

**Key Methods:**
- `supplier()` - BelongsTo relationship
- `logs()` - HasMany SupplierSyncLog
- `failures()` - HasMany SupplierSyncFailure
- `statuses()` - HasMany SupplierSyncStatus
- `getProgressPercentageAttribute()` - Overall progress %
- `getBatchProgressPercentageAttribute()` - Batch progress %
- `getSuccessRateAttribute()` - Success rate %
- `getRemainingItemsAttribute()` - Calculate remaining items
- `isRunning()`, `isCompleted()`, `isFailed()`, `isCancelled()` - Status checks
- `canRetry()` - Check retry eligibility
- `markAsStarted()`, `markAsCompleted()`, `markAsFailed()`, `markAsCancelled()` - Status transitions
- `incrementProcessedItems(int)` - Update progress
- `incrementFailedItems(int)` - Update failures
- `incrementProcessedBatches(int)` - Sub-batch tracking
- `incrementRetryAttempt()` - Increment retries

**Scopes:**
- `pending()`, `running()`, `completed()`, `failed()` - Status filtering
- `byType(string)` - Filter by type
- `byPriority(string)` - Filter by priority
- `highPriority()` - High/urgent priority batches
- `retryable()` - Eligible for retry
- `recent(int)` - Recent batches (days)

---

### Model: `SupplierSyncLog`
**Location:** `/modules/Supplier/app/Models/SupplierSyncLog.php`

**Traits:** `HasFactory`, `HasUid`

**Key Methods:**
- `batch()` - BelongsTo SupplierSyncBatch
- `status()` - BelongsTo SupplierSyncStatus
- `isSuccess()`, `isFailed()`, `isWarning()`, `isSkipped()` - Result checks
- `isCreate()`, `isUpdate()`, `isDelete()` - Action checks
- `getChangesSummaryAttribute()` - Formatted changes array
- `getFormattedDurationAttribute()` - Duration formatted as "Xms" or "Xs"

**Scopes:**
- `successful()`, `failed()`, `warnings()`, `skipped()` - Result filtering
- `byAction(string)` - Filter by action type
- `byEntityType(string)` - Filter by entity type
- `byEntityId(int)` - Filter by entity ID
- `byErpId(int)` - Filter by ERP ID
- `withErrors()` - Logs with errors/warnings
- `slow(int)` - Slow operations (threshold ms)
- `withRetries()` - Operations with retry attempts
- `recent(int)` - Recent logs (hours)

---

### Model: `SupplierSyncFailure` (Enhanced)
**Location:** `/modules/Supplier/app/Models/SupplierSyncFailure.php`

**Traits:** `HasFactory`, `HasUid`

**Key Methods:**
- `batch()` - BelongsTo SupplierSyncBatch
- `supplier()` - BelongsTo Supplier
- `canRetry()` - Check retry eligibility
- `isResolved()`, `isPending()`, `isAcknowledged()` - Status checks
- `markAsResolved(?userId, ?notes)` - Mark as resolved
- `markAsAcknowledged()` - Mark as acknowledged
- `recordRetryAttempt()` - Increment retry counter

**Scopes:**
- `pending()`, `resolved()`, `acknowledged()`, `archived()` - Status filtering
- `unresolved()` - Pending/acknowledged failures
- `byType(string)` - Filter by sync type
- `retryable(?maxRetries)` - Eligible for retry
- `byBatch(int)` - Filter by batch ID
- `byEntity(int)` - Filter by entity ID
- `byErrorCode(string)` - Filter by error code
- `latestFailures()` - Ordered by most recent
- `latestFailures()` - Ordered by retry attempt

---

## Usage Examples

### Creating a Sync Status
```php
$status = SupplierSyncStatus::create([
    'sync_type' => 'product',
    'sync_scope' => 'all',
    'status' => 'pending',
    'total_items' => 1000,
    'triggered_by' => 'scheduled',
]);

$status->markAsStarted();
// ... process items ...
$status->markAsCompleted();
```

### Creating a Sync Batch
```php
$batch = SupplierSyncBatch::create([
    'batch_name' => 'Daily Product Sync',
    'sync_type' => 'product',
    'priority' => 'normal',
    'batch_size' => 100,
    'total_items' => 5000,
    'max_retries' => 3,
    'triggered_by' => 'scheduled',
]);

$batch->markAsStarted();
// Process batches of items...
$batch->incrementProcessedItems(100);
$batch->incrementProcessedBatches(1);

if ($itemFailed) {
    $batch->incrementFailedItems(1);
}

$batch->markAsCompleted();
```

### Logging Sync Operations
```php
$log = SupplierSyncLog::create([
    'batch_id' => $batch->id,
    'status_id' => $status->id,
    'entity_type' => 'product',
    'entity_id' => $product->id,
    'erp_id' => $erpProduct->id,
    'action' => 'update',
    'result' => 'success',
    'data_before' => ['name' => 'Old Name'],
    'data_after' => ['name' => 'New Name'],
    'changes' => ['name' => ['Old Name', 'New Name']],
    'duration_ms' => 150,
    'synced_at' => now(),
]);
```

### Handling Sync Failures
```php
try {
    // Sync operation
} catch (Exception $e) {
    $failure = SupplierSyncFailure::create([
        'batch_id' => $batch->id,
        'sync_type' => 'product',
        'supplier_id' => $supplierId,
        'entity_id' => $entityId,
        'erp_id' => $erpId,
        'changed_data' => $data,
        'error_code' => 'VALIDATION_ERROR',
        'error_message' => $e->getMessage(),
        'failure_status' => 'pending',
        'max_retries' => 5,
    ]);
}
```

### Querying Sync Data
```php
// Get all failed syncs from today
$failed = SupplierSyncStatus::failed()
    ->recent(24)
    ->get();

// Get retryable failures
$retryable = SupplierSyncFailure::retryable()
    ->pending()
    ->get();

// Get slow operations
$slowOps = SupplierSyncLog::slow(5000)->get();

// Get high-priority batches
$urgent = SupplierSyncBatch::highPriority()
    ->whereIn('status', ['pending', 'running'])
    ->get();
```

## Relationships Summary

```
SupplierSyncStatus
  ├── belongsTo(Supplier)
  ├── belongsTo(SupplierSyncBatch)
  ├── hasMany(SupplierSyncLog)
  └── hasMany(SupplierSyncFailure) [via batch]

SupplierSyncBatch
  ├── belongsTo(Supplier)
  ├── hasMany(SupplierSyncLog)
  ├── hasMany(SupplierSyncFailure)
  └── hasMany(SupplierSyncStatus)

SupplierSyncLog
  ├── belongsTo(SupplierSyncBatch)
  └── belongsTo(SupplierSyncStatus)

SupplierSyncFailure
  ├── belongsTo(SupplierSyncBatch)
  └── belongsTo(Supplier)
```

## Status Enums

### Sync Types
- `product` - Product synchronization
- `category` - Category synchronization
- `price` - Price synchronization
- `provider` - Provider synchronization

### Sync Scope
- `all` - Full synchronization
- `per_supplier` - Supplier-specific sync
- `per_category` - Category-specific sync

### SupplierSyncStatus & SupplierSyncBatch Status
- `pending` - Awaiting execution
- `running` - Currently executing
- `completed` - Successfully completed
- `failed` - Failed during execution
- `paused` - (Status only) Temporarily paused
- `cancelled` - (Batch only) Cancelled before completion

### SupplierSyncBatch Priority
- `low` - Low priority
- `normal` - Normal priority
- `high` - High priority
- `urgent` - Urgent priority

### SupplierSyncLog Action & Result
**Actions:**
- `create` - New record created
- `update` - Existing record updated
- `delete` - Record deleted
- `skip` - Record skipped

**Results:**
- `success` - Operation successful
- `failed` - Operation failed
- `skipped` - Operation skipped
- `warning` - Operation succeeded with warnings

### SupplierSyncFailure Status
- `pending` - Unresolved failure
- `resolved` - Failure resolved
- `acknowledged` - Failure acknowledged
- `archived` - Failure archived

### Trigger Sources
- `manual` - Manual trigger
- `scheduled` - Scheduled job
- `webhook` - Webhook trigger
- `api` - API trigger
- `sync_job` - Background job

## Performance Considerations

1. **Indexing:** All frequently queried columns are indexed
2. **Batch Processing:** Use `batch_size` to control memory usage
3. **Retry Logic:** Exponential backoff recommended for retries
4. **Cleanup:** Archive old logs periodically to maintain performance
5. **Progress Tracking:** Use calculated attributes for real-time progress

## Migration Execution Order

When running migrations, execute in this order:
1. `2025_01_16_create_supplier_sync_batches_table.php`
2. `2025_01_16_create_supplier_sync_statuses_table.php`
3. `2025_01_16_create_supplier_sync_logs_table.php`
4. `2025_01_16_update_supplier_sync_failures_table.php`

Note: The update migration for `supplier_sync_failures` checks for existing columns before adding them, making it safe to run multiple times.

## Files Created

**Migrations:**
- `/modules/Supplier/database/migrations/2025_01_16_create_supplier_sync_statuses_table.php`
- `/modules/Supplier/database/migrations/2025_01_16_create_supplier_sync_batches_table.php`
- `/modules/Supplier/database/migrations/2025_01_16_create_supplier_sync_logs_table.php`
- `/modules/Supplier/database/migrations/2025_01_16_update_supplier_sync_failures_table.php`

**Models:**
- `/modules/Supplier/app/Models/SupplierSyncStatus.php`
- `/modules/Supplier/app/Models/SupplierSyncBatch.php`
- `/modules/Supplier/app/Models/SupplierSyncLog.php`
- `/modules/Supplier/app/Models/SupplierSyncFailure.php` (Enhanced)
