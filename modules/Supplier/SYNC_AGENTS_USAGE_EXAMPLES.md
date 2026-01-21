# Sync Agents - Usage Examples

Complete examples of how to use each sync agent in your application.

## Table of Contents

1. [Basic Usage](#basic-usage)
2. [ProductSyncAgent Examples](#productsyncagent-examples)
3. [CategorySyncAgent Examples](#categorysyncagent-examples)
4. [PriceSyncAgent Examples](#pricesyncagent-examples)
5. [ProviderSyncAgent Examples](#providersyncagent-examples)
6. [Integration with SyncCoordinatorAgent](#integration-with-synccoordinatoragent)
7. [Error Handling](#error-handling)
8. [Monitoring Progress](#monitoring-progress)

---

## Basic Usage

All agents follow the same pattern:

```php
use Modules\Supplier\Services\{
    ProductSyncAgent,
    SyncStatusService,
    ErpSyncService,
};
use Modules\Supplier\Models\SupplierSyncBatch;

// 1. Create a sync batch
$batch = SupplierSyncBatch::create([
    'supplier_id' => 123,
    'batch_name' => 'Product Sync - Supplier 123',
    'sync_type' => 'product',
    'status' => 'pending',
    'priority' => 'normal',
    'batch_size' => 100,
    'triggered_by' => 'manual',
]);

// 2. Instantiate services
$syncStatusService = app(SyncStatusService::class);
$erpSyncService = app(ErpSyncService::class);

// 3. Create agent with batch and services
$agent = new ProductSyncAgent(
    $batch,
    $syncStatusService,
    $erpSyncService
);

// 4. Configure filters (optional)
$agent->forSupplier(123)
      ->withinDateRange('2025-01-01', '2025-01-31');

// 5. Execute sync
$result = $agent->execute();

// 6. Check result
if ($result['success']) {
    echo "Synced {$result['items_processed']} products";
} else {
    echo "Sync failed: {$result['message']}";
}
```

---

## ProductSyncAgent Examples

### Example 1: Sync All Products for a Supplier

```php
$batch = SupplierSyncBatch::create([
    'supplier_id' => 123,
    'batch_name' => 'Full Product Sync - Supplier 123',
    'sync_type' => 'product',
    'status' => 'pending',
    'priority' => 'high',
    'batch_size' => 100,
    'triggered_by' => 'manual',
]);

$agent = new ProductSyncAgent(
    $batch,
    app(SyncStatusService::class),
    app(ErpSyncService::class)
);

$result = $agent->forSupplier(123)->execute();

echo sprintf(
    "Product Sync Complete: %d processed, %d failed, %d skipped",
    $result['items_processed'],
    $result['items_failed'],
    $result['items_skipped']
);
```

### Example 2: Sync Recent Product Changes

```php
$batch = SupplierSyncBatch::create([
    'supplier_id' => 123,
    'batch_name' => 'Recent Product Changes - Jan 16, 2025',
    'sync_type' => 'product',
    'status' => 'pending',
    'batch_size' => 50,
    'triggered_by' => 'scheduled',
]);

$agent = new ProductSyncAgent(
    $batch,
    app(SyncStatusService::class),
    app(ErpSyncService::class)
);

// Only sync products modified in last 7 days
$startDate = now()->subDays(7)->toDateString();
$endDate = now()->toDateString();

$result = $agent
    ->forSupplier(123)
    ->withinDateRange($startDate, $endDate)
    ->execute();

// Log the result
Log::info('Recent products synced', [
    'batch_id' => $batch->id,
    'date_range' => "{$startDate} to {$endDate}",
    'processed' => $result['items_processed'],
    'success' => $result['success'],
]);
```

### Example 3: Sync Without Supplier Filter (All Products)

```php
$batch = SupplierSyncBatch::create([
    'batch_name' => 'Global Product Sync',
    'sync_type' => 'product',
    'status' => 'pending',
    'batch_size' => 200,  // Larger batches for global sync
    'triggered_by' => 'scheduled',
]);

$agent = new ProductSyncAgent(
    $batch,
    app(SyncStatusService::class),
    app(ErpSyncService::class)
);

// Note: forSupplier() is optional - omitting it syncs all suppliers
$result = $agent->execute();

// Could also be explicit:
// $result = $agent->forSupplier(0)->execute();  // 0 = all suppliers

if ($result['success']) {
    $totalSynced = $result['items_processed'] +
                   $result['items_failed'] +
                   $result['items_skipped'];

    echo "Global sync completed: {$totalSynced} items processed";
}
```

### Example 4: Handle Product Sync with Custom Error Handling

```php
try {
    $batch = SupplierSyncBatch::create([
        'supplier_id' => 123,
        'batch_name' => 'Product Sync with Error Handling',
        'sync_type' => 'product',
        'status' => 'pending',
        'batch_size' => 100,
        'triggered_by' => 'api',
    ]);

    $agent = new ProductSyncAgent(
        $batch,
        app(SyncStatusService::class),
        app(ErpSyncService::class)
    );

    $result = $agent->forSupplier(123)->execute();

    // Check detailed results
    if (!$result['success']) {
        // Log failure details
        Log::error('Product sync failed', [
            'batch_id' => $batch->id,
            'reason' => $result['message'],
        ]);

        // Notify admin
        Notification::send(
            Admin::whereRole('sync_admin')->get(),
            new SyncFailedNotification($batch, $result['message'])
        );

        return response()->json(['error' => $result['message']], 422);
    }

    // Check for high failure rate
    $failureRate = $result['items_failed'] /
                   ($result['items_processed'] + $result['items_failed']);

    if ($failureRate > 0.1) {  // 10% failure threshold
        Log::warning('High product sync failure rate', [
            'batch_id' => $batch->id,
            'failure_rate' => $failureRate,
            'failed_items' => $result['items_failed'],
        ]);
    }

    return response()->json([
        'message' => 'Products synced successfully',
        'stats' => [
            'processed' => $result['items_processed'],
            'failed' => $result['items_failed'],
            'skipped' => $result['items_skipped'],
        ]
    ]);

} catch (Exception $e) {
    Log::error('Product sync exception', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);

    return response()->json(
        ['error' => 'Sync failed: '.$e->getMessage()],
        500
    );
}
```

---

## CategorySyncAgent Examples

### Example 1: Sync Supplier Categories

```php
$batch = SupplierSyncBatch::create([
    'supplier_id' => 123,
    'batch_name' => 'Supplier Category Sync',
    'sync_type' => 'category',
    'status' => 'pending',
    'batch_size' => 50,
    'triggered_by' => 'manual',
]);

$agent = new CategorySyncAgent(
    $batch,
    app(SyncStatusService::class),
    app(ErpSyncService::class)
);

$result = $agent
    ->forSupplier(123)
    ->hierarchicalOnly(true)  // Only hierarchical categories
    ->execute();

if ($result['success']) {
    echo "Categories synced: {$result['items_processed']}";
}
```

### Example 2: Sync All Categories (Non-Hierarchical)

```php
$batch = SupplierSyncBatch::create([
    'batch_name' => 'Global Category Sync',
    'sync_type' => 'category',
    'status' => 'pending',
    'batch_size' => 100,
    'triggered_by' => 'scheduled',
]);

$agent = new CategorySyncAgent(
    $batch,
    app(SyncStatusService::class),
    app(ErpSyncService::class)
);

$result = $agent->hierarchicalOnly(false)->execute();

// Log results for audit
Log::info('Category sync completed', [
    'batch_id' => $batch->id,
    'processed' => $result['items_processed'],
    'failed' => $result['items_failed'],
    'skipped' => $result['items_skipped'],
]);
```

---

## PriceSyncAgent Examples

### Example 1: Sync All Current Prices for a Supplier

```php
$batch = SupplierSyncBatch::create([
    'supplier_id' => 123,
    'batch_name' => 'Current Price Sync - Supplier 123',
    'sync_type' => 'price',
    'status' => 'pending',
    'batch_size' => 100,
    'triggered_by' => 'scheduled',
]);

$agent = new PriceSyncAgent(
    $batch,
    app(SyncStatusService::class),
    app(ErpSyncService::class)
);

$result = $agent
    ->forSupplier(123)
    ->currentPricesOnly(true)  // Only current prices
    ->execute();

echo "Prices synced: {$result['items_processed']}";
```

### Example 2: Sync Recent Price Changes

```php
$batch = SupplierSyncBatch::create([
    'supplier_id' => 456,
    'batch_name' => 'Recent Price Changes - Jan 2025',
    'sync_type' => 'price',
    'status' => 'pending',
    'batch_size' => 200,
    'triggered_by' => 'manual',
]);

$agent = new PriceSyncAgent(
    $batch,
    app(SyncStatusService::class),
    app(ErpSyncService::class)
);

// Sync prices effective in January 2025
$result = $agent
    ->forSupplier(456)
    ->withinDateRange('2025-01-01', '2025-01-31')
    ->currentPricesOnly(false)  // Include history
    ->execute();

if ($result['success']) {
    $totalSynced = $result['items_processed'] + $result['items_skipped'];
    echo "Synced {$totalSynced} price records";
}
```

### Example 3: Sync All Price History

```php
$batch = SupplierSyncBatch::create([
    'batch_name' => 'Global Price History Sync',
    'sync_type' => 'price',
    'status' => 'pending',
    'batch_size' => 500,  // Larger for history
    'triggered_by' => 'scheduled',
]);

$agent = new PriceSyncAgent(
    $batch,
    app(SyncStatusService::class),
    app(ErpSyncService::class)
);

// Sync all active prices (current and history)
$result = $agent->currentPricesOnly(false)->execute();

Log::info('Price history sync completed', [
    'batch_id' => $batch->id,
    'total_synced' => $result['items_processed'],
    'conflicts_detected' => $result['items_failed'],
]);
```

---

## ProviderSyncAgent Examples

### Example 1: Sync Providers for a Supplier

```php
$batch = SupplierSyncBatch::create([
    'supplier_id' => 123,
    'batch_name' => 'ERP Provider Sync - Supplier 123',
    'sync_type' => 'provider',
    'status' => 'pending',
    'batch_size' => 50,
    'triggered_by' => 'manual',
]);

$agent = new ProviderSyncAgent(
    $batch,
    app(SyncStatusService::class),
    app(ErpSyncService::class)
);

$result = $agent
    ->forSupplier(123)
    ->bidirectional(true)  // Enable bidirectional sync
    ->execute();

echo "Providers synced: {$result['items_processed']}";
```

### Example 2: Sync All Providers (Bidirectional)

```php
$batch = SupplierSyncBatch::create([
    'batch_name' => 'Global Provider Sync',
    'sync_type' => 'provider',
    'status' => 'pending',
    'batch_size' => 100,
    'triggered_by' => 'scheduled',
]);

$agent = new ProviderSyncAgent(
    $batch,
    app(SyncStatusService::class),
    app(ErpSyncService::class)
);

// Full bidirectional sync
$result = $agent->bidirectional(true)->execute();

if ($result['success']) {
    echo "All providers synced successfully";
} else {
    Log::error('Provider sync failed', $result);
}
```

### Example 3: Unidirectional Provider Sync (Supplier → ERP only)

```php
$batch = SupplierSyncBatch::create([
    'supplier_id' => 789,
    'batch_name' => 'One-way Provider Sync',
    'sync_type' => 'provider',
    'status' => 'pending',
    'batch_size' => 50,
    'triggered_by' => 'api',
]);

$agent = new ProviderSyncAgent(
    $batch,
    app(SyncStatusService::class),
    app(ErpSyncService::class)
);

// Unidirectional: Supplier → ERP only
$result = $agent
    ->forSupplier(789)
    ->bidirectional(false)
    ->execute();

// Process result
if ($result['success']) {
    event(new ProvidersSynced($batch));
}
```

---

## Integration with SyncCoordinatorAgent

The coordinator manages multiple agents in parallel:

```php
use Modules\Supplier\Services\SyncCoordinatorAgent;

$coordinator = app(SyncCoordinatorAgent::class);

// Sync ALL types for a specific supplier
$result = $coordinator->coordinateSync(
    syncType: 'all',
    supplierId: 123,
    triggeredBy: 'scheduled'
);

// Result:
// [
//   'success' => true,
//   'batch_id' => 45,
//   'sync_types' => ['product', 'category', 'price', 'provider'],
//   'agents_started' => 4,
//   'total_items' => 5432,
// ]

if ($result['success']) {
    echo "Coordinator batch {$result['batch_id']} started";
    echo "Syncing: " . implode(', ', $result['sync_types']);
}
```

---

## Error Handling

### Handling Sync Failures

```php
try {
    $agent = new ProductSyncAgent(
        $batch,
        app(SyncStatusService::class),
        app(ErpSyncService::class)
    );

    $result = $agent->forSupplier(123)->execute();

    // Check if sync succeeded
    if (!$result['success']) {
        // Determine failure type
        if ($result['items_failed'] > $result['items_processed']) {
            // Most items failed - critical issue
            Log::critical('Product sync mostly failed', $result);

            // Alert admins
            Notification::send(
                Admin::role('admin')->get(),
                new CriticalSyncFailureNotification($batch, $result)
            );
        } else {
            // Some items failed - informational
            Log::warning('Product sync had failures', $result);
        }

        // Return error to caller
        return [
            'success' => false,
            'message' => $result['message'],
            'failure_count' => $result['items_failed'],
        ];
    }

    return [
        'success' => true,
        'processed' => $result['items_processed'],
        'failed' => $result['items_failed'],
        'skipped' => $result['items_skipped'],
    ];

} catch (Exception $e) {
    Log::error('Sync exception', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);

    throw $e;
}
```

### Accessing Detailed Failure Information

```php
$batch = SupplierSyncBatch::find(45);

// Get all recorded failures for this batch
$failures = $batch->failures()->get();

foreach ($failures as $failure) {
    Log::info('Failure detail', [
        'entity_id' => $failure->entity_id,
        'error_code' => $failure->error_code,
        'error_message' => $failure->error_message,
        'retry_count' => $failure->retry_count,
    ]);
}
```

---

## Monitoring Progress

### Real-time Progress Tracking

```php
$agent = new ProductSyncAgent(
    $batch,
    app(SyncStatusService::class),
    app(ErpSyncService::class)
);

// Could run in background job
$result = $agent->forSupplier(123)->execute();

// Get progress at any time
$progress = $agent->getProgress();

echo sprintf(
    "Progress: %d/%d items (%0.1f%%)",
    $progress['items_processed'] + $progress['items_failed'] + $progress['items_skipped'],
    $progress['total_items'],
    $progress['progress_percentage']
);

echo sprintf(
    "Processed: %d, Failed: %d, Skipped: %d",
    $progress['items_processed'],
    $progress['items_failed'],
    $progress['items_skipped']
);

echo sprintf(
    "Batch %d of %d",
    $progress['current_batch'],
    $progress['total_batches']
);
```

### Cancellation Support

```php
// Start sync in background
$job = ProcessSupplierSyncJob::dispatch($batch);

// Later: request cancellation
$syncStatusService = app(SyncStatusService::class);
$syncStatusService->requestCancellation($batch->id);

// Agent checks for cancellation flag and stops gracefully
// All progress up to that point is preserved
```

---

## Batch Job Usage

### In a Queued Job

```php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Supplier\Services\ProductSyncAgent;
use Modules\Supplier\Services\SyncStatusService;
use Modules\Supplier\Services\ErpSyncService;
use Modules\Supplier\Models\SupplierSyncBatch;

class ProcessProductSyncJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public SupplierSyncBatch $batch,
        public ?int $supplierId = null,
    ) {}

    public function handle(): void
    {
        $agent = new ProductSyncAgent(
            $this->batch,
            app(SyncStatusService::class),
            app(ErpSyncService::class)
        );

        if ($this->supplierId) {
            $agent->forSupplier($this->supplierId);
        }

        $result = $agent->execute();

        if (!$result['success']) {
            $this->fail(new Exception($result['message']));
        }

        $this->batch->markAsCompleted();
    }
}
```

### Dispatching from Controller

```php
public function syncProducts(Request $request): JsonResponse
{
    $batch = SupplierSyncBatch::create([
        'supplier_id' => $request->integer('supplier_id'),
        'batch_name' => 'Manual Product Sync',
        'sync_type' => 'product',
        'status' => 'pending',
        'triggered_by' => 'manual',
    ]);

    ProcessProductSyncJob::dispatch($batch);

    return response()->json([
        'message' => 'Sync started in background',
        'batch_id' => $batch->id,
    ]);
}
```

---

## Summary of Key Methods

### Available on All Agents

| Method | Purpose | Returns |
|--------|---------|---------|
| `execute()` | Run the sync | `array` |
| `getProgress()` | Get current progress | `array` |
| `getBatch()` | Get sync batch | `SupplierSyncBatch` |
| `getStatus()` | Get sync status | `?SupplierSyncStatus` |
| `getSyncStatusService()` | Get service | `SyncStatusService` |

### Agent-Specific Filters

| Agent | Methods |
|-------|---------|
| ProductSyncAgent | `forSupplier()`, `withinDateRange()` |
| CategorySyncAgent | `forSupplier()`, `hierarchicalOnly()` |
| PriceSyncAgent | `forSupplier()`, `withinDateRange()`, `currentPricesOnly()` |
| ProviderSyncAgent | `forSupplier()`, `bidirectional()` |
