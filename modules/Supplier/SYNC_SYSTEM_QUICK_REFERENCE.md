# Sync System - Quick Reference Guide

## File Locations

```
Migrations:
  └── modules/Supplier/database/migrations/
      ├── 2025_01_16_create_supplier_sync_statuses_table.php
      ├── 2025_01_16_create_supplier_sync_batches_table.php
      ├── 2025_01_16_create_supplier_sync_logs_table.php
      └── 2025_01_16_update_supplier_sync_failures_table.php

Models:
  └── modules/Supplier/app/Models/
      ├── SupplierSyncStatus.php
      ├── SupplierSyncBatch.php
      ├── SupplierSyncLog.php
      └── SupplierSyncFailure.php (Enhanced)
```

## Model Quick Reference

### SupplierSyncStatus - Overall Sync Tracking
```php
// Create
$status = SupplierSyncStatus::create([
    'sync_type' => 'product',
    'status' => 'pending',
    'total_items' => 1000,
    'triggered_by' => 'scheduled',
]);

// Status transitions
$status->markAsStarted();
$status->markAsCompleted();
$status->markAsFailed('Error message');

// Check status
$status->isRunning();
$status->isCompleted();
$status->isFailed();
$status->canRetry();

// Get progress
$status->progress_percentage;     // 0.0 - 100.0
$status->success_rate;             // 0.0 - 100.0

// Query
SupplierSyncStatus::running()->get();
SupplierSyncStatus::failed()->recent(24)->get();
SupplierSyncStatus::byType('product')->get();
```

### SupplierSyncBatch - Grouped Operations
```php
// Create
$batch = SupplierSyncBatch::create([
    'batch_name' => 'Daily Sync',
    'sync_type' => 'product',
    'status' => 'pending',
    'priority' => 'normal',
    'total_items' => 5000,
    'max_retries' => 3,
]);

// Status transitions
$batch->markAsStarted();
$batch->markAsCompleted();
$batch->markAsFailed();
$batch->markAsCancelled();

// Update progress
$batch->incrementProcessedItems(100);
$batch->incrementFailedItems(5);
$batch->incrementProcessedBatches(1);
$batch->incrementRetryAttempt();

// Check status
$batch->isRunning();
$batch->isCompleted();
$batch->canRetry();

// Get metrics
$batch->progress_percentage;        // Overall %
$batch->batch_progress_percentage;  // Sub-batch %
$batch->success_rate;
$batch->remaining_items;

// Query
SupplierSyncBatch::highPriority()->running()->get();
SupplierSyncBatch::byType('product')->retryable()->get();
SupplierSyncBatch::recent(7)->get();
```

### SupplierSyncLog - Detailed Events
```php
// Create
$log = SupplierSyncLog::create([
    'batch_id' => $batch->id,
    'entity_type' => 'product',
    'entity_id' => 123,
    'erp_id' => 456,
    'action' => 'update',
    'result' => 'success',
    'message' => 'Product updated successfully',
    'data_before' => ['name' => 'Old'],
    'data_after' => ['name' => 'New'],
    'changes' => ['name' => ['Old', 'New']],
    'duration_ms' => 150,
    'synced_at' => now(),
]);

// Check results
$log->isSuccess();
$log->isFailed();
$log->isWarning();
$log->isSkipped();

// Check actions
$log->isCreate();
$log->isUpdate();
$log->isDelete();

// Format data
$log->changes_summary;       // Formatted changes
$log->formatted_duration;    // "150ms" or "2.5s"

// Query
SupplierSyncLog::successful()->get();
SupplierSyncLog::failed()->withErrors()->get();
SupplierSyncLog::byEntityType('product')->byEntityId(123)->get();
SupplierSyncLog::slow(5000)->get();
SupplierSyncLog::recent(24)->get();
```

### SupplierSyncFailure - Failed Operations
```php
// Create
$failure = SupplierSyncFailure::create([
    'batch_id' => $batch->id,
    'sync_type' => 'product',
    'supplier_id' => 1,
    'entity_id' => 123,
    'erp_id' => 456,
    'changed_data' => ['name' => 'New Name'],
    'error_code' => 'VALIDATION_ERROR',
    'error_message' => 'Invalid product data',
    'failure_status' => 'pending',
    'max_retries' => 5,
]);

// Check retry eligibility
$failure->canRetry();
$failure->recordRetryAttempt();

// Resolution
$failure->markAsResolved($userId, 'Fixed the issue');
$failure->markAsAcknowledged();

// Check status
$failure->isResolved();
$failure->isPending();
$failure->isAcknowledged();

// Query
SupplierSyncFailure::pending()->get();
SupplierSyncFailure::unresolved()->get();
SupplierSyncFailure::retryable()->get();
SupplierSyncFailure::byBatch($batchId)->get();
SupplierSyncFailure::byErrorCode('VALIDATION_ERROR')->get();
```

## Common Workflows

### 1. Start a Full Product Sync
```php
$batch = SupplierSyncBatch::create([
    'batch_name' => 'Full Product Sync - ' . now()->toDateTimeString(),
    'sync_type' => 'product',
    'status' => 'pending',
    'priority' => 'normal',
    'total_items' => Product::count(),
    'batch_size' => 100,
    'max_retries' => 3,
    'triggered_by' => 'manual',
]);

$status = SupplierSyncStatus::create([
    'batch_id' => $batch->id,
    'sync_type' => 'product',
    'sync_scope' => 'all',
    'status' => 'pending',
    'total_items' => $batch->total_items,
    'triggered_by' => 'manual',
]);

$batch->markAsStarted();
$status->markAsStarted();
```

### 2. Log Individual Item Sync
```php
$log = SupplierSyncLog::create([
    'batch_id' => $batch->id,
    'status_id' => $status->id,
    'entity_type' => 'product',
    'entity_id' => $product->id,
    'erp_id' => $erpProduct->id,
    'action' => 'update',
    'result' => 'success',
    'data_before' => $product->getOriginal(),
    'data_after' => $product->toArray(),
    'changes' => $product->getChanges(),
    'duration_ms' => $duration,
    'synced_at' => now(),
]);

$batch->incrementProcessedItems();
$status->synced_items += 1;
$status->save();
```

### 3. Handle Sync Failure
```php
try {
    // Sync operation
    syncProductToERP($product);
} catch (SyncException $e) {
    $failure = SupplierSyncFailure::create([
        'batch_id' => $batch->id,
        'sync_type' => 'product',
        'supplier_id' => $product->supplier_id,
        'entity_id' => $product->id,
        'erp_id' => $product->erp_product_id,
        'changed_data' => $product->getChanges(),
        'error_code' => $e->getCode(),
        'error_message' => $e->getMessage(),
        'context' => [
            'stack_trace' => $e->getTraceAsString(),
            'attempt' => 1,
        ],
        'failure_status' => 'pending',
        'max_retries' => 5,
    ]);

    $batch->incrementFailedItems();
    $status->failed_items += 1;
    $status->save();
}
```

### 4. Complete Batch Sync
```php
$batch->markAsCompleted();
$status->markAsCompleted();

// Log summary
Log::info('Sync completed', [
    'batch_id' => $batch->id,
    'processed' => $batch->processed_items,
    'failed' => $batch->failed_items,
    'duration' => $batch->duration_seconds,
    'success_rate' => $batch->success_rate,
]);
```

### 5. Retry Failed Items
```php
$retryable = SupplierSyncFailure::retryable()
    ->pending()
    ->where('batch_id', $batch->id)
    ->get();

foreach ($retryable as $failure) {
    try {
        // Retry logic
        retrySync($failure);
        $failure->recordRetryAttempt();
        $failure->markAsResolved(null, 'Retry successful');
    } catch (Exception $e) {
        if (!$failure->canRetry()) {
            $failure->failure_status = 'archived';
            $failure->save();
        }
    }
}
```

## Status Values Reference

| Model | Status | Meaning |
|-------|--------|---------|
| SyncStatus/Batch | pending | Awaiting execution |
| SyncStatus/Batch | running | Currently executing |
| SyncStatus/Batch | completed | Successfully finished |
| SyncStatus/Batch | failed | Failed during execution |
| SyncStatus | paused | Temporarily paused |
| SyncBatch | cancelled | Cancelled before completion |
| SyncLog | success | Operation successful |
| SyncLog | failed | Operation failed |
| SyncLog | skipped | Operation skipped |
| SyncLog | warning | Succeeded with warnings |
| SyncFailure | pending | Unresolved failure |
| SyncFailure | resolved | Failure resolved |
| SyncFailure | acknowledged | Failure acknowledged |
| SyncFailure | archived | Failure archived |

## Attribute Accessors

```php
// SupplierSyncStatus
$status->sync_type_name;        // Translated type name
$status->status_name;            // Translated status name
$status->progress_percentage;    // 0.0 - 100.0
$status->success_rate;           // 0.0 - 100.0

// SupplierSyncBatch
$batch->sync_type_name;
$batch->status_name;
$batch->priority_name;
$batch->progress_percentage;     // Overall progress
$batch->batch_progress_percentage; // Sub-batch progress
$batch->success_rate;
$batch->remaining_items;

// SupplierSyncLog
$log->action_name;               // Translated action
$log->result_name;               // Translated result
$log->entity_type_name;          // Translated entity type
$log->changes_summary;           // Formatted changes
$log->formatted_duration;        // "150ms" or "2.5s"

// SupplierSyncFailure
$failure->type_name;             // Translated type name
$failure->failure_status_name;   // Translated status name
```

## Key Design Principles

1. **Audit Trail:** All operations are logged for compliance and debugging
2. **Retry Logic:** Failed items can be retried up to configured limits
3. **Batch Processing:** Operations grouped for efficient processing
4. **Real-time Tracking:** Progress metrics updated in real-time
5. **Status Transparency:** Clear status at each level (Status, Batch, Log)
6. **Error Context:** Failures include detailed error codes and messages
7. **Resolution Tracking:** Failed items can be marked as resolved by users
8. **Performance Metrics:** Track duration, memory usage, and success rates
