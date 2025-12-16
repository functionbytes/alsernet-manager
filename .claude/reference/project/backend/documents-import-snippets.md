# Documents Import - Code Snippets & Examples

## 1. Manual Import Usage

### Import a Single Order

```php
// In controller or elsewhere
use App\Models\Document\Document;use App\Models\Prestashop\Order\Order as PrestashopOrder;

$orderId = 123;
$order = PrestashopOrder::find($orderId);

// Check if exists
$existing = Document::where('order_id', $orderId)->get();
if (!$existing->isEmpty()) {
    throw new Exception("Order already has documents");
}

// Create document
$document = new Document();
$document->order_id = $orderId;
$document->type = 'order';
$document->source = 'api';
$document->proccess = 0;
$document->save();

// Sync with order
$this->syncDocumentWithOrder($document, $order);

// Fire event
event(new \App\Events\Documents\DocumentCreated($document));
```

### Get Import Status

```php
$document = Document::uid('doc-uuid-here');

// Check required documents
$requiredDocs = DocumentTypeService::getRequiredDocuments($document->type);
// Returns: ['dni_frontal' => 'DNI - Cara delantera', 'dni_trasera' => '...']

// Check uploaded documents
$uploadedDocs = [];
foreach ($document->media as $media) {
    $docType = $media->getCustomProperty('document_type', 'documento');
    $uploadedDocs[$docType] = $media->file_name;
}

// Get missing documents
$missingDocs = DocumentTypeService::getMissingDocuments($document->type, $uploadedDocs);

// Check if complete
$allUploaded = DocumentTypeService::allDocumentsUploaded($document->type, $uploadedDocs);
```

---

## 2. Query Examples

### Find Documents by Status

```php
use App\Models\Document\Document;

// Pending documents
$pending = Document::where('proccess', 'pending')->get();

// Documents without uploads
$noUploads = Document::filterByUploadStatus(0)->get();

// Documents with uploads
$withUploads = Document::filterByUploadStatus(1)->get();

// Documents created in last 7 days
$recent = Document::where('created_at', '>=', now()->subDays(7))->get();

// Search by customer
$results = Document::searchByCustomerOrOrder('John Doe')->get();

// Advanced listing with filters
$documents = Document::filterListing(
    search: 'customer name',
    uploadStatus: null,
    dateFrom: '2024-11-01',
    dateTo: '2024-11-30'
)->paginate(15);
```

### Find Documents by Order

```php
use App\Models\Document\Document;

// Get document for specific order
$document = Document::where('order_id', 456)->first();

// Get all documents for order
$documents = Document::where('order_id', 456)->get();

// Find by UID
$document = Document::uid('abc123def456');
```

### List with Pagination

```php
use App\Models\Document\Document;

// Default
$documents = Document::paginate(15);

// Descending
$documents = Document::descending()->paginate(15);

// By priority (no uploads first)
$documents = Document::orderByUploadPriority()->paginate(15);
```

---

## 3. Document Management

### Upload Documents

```php
use App\Models\Document\Document;

$document = Document::uid('doc-uuid');

// Upload single file
$media = $document->addMediaFromRequest('file')
    ->withCustomProperties(['document_type' => 'dni_frontal'])
    ->toMediaCollection('documents');

// Multiple files
$files = [
    'dni_frontal' => $request->file('dni_frontal'),
    'dni_trasera' => $request->file('dni_trasera'),
    'licencia' => $request->file('licencia'),
];

foreach ($files as $docType => $file) {
    if ($file) {
        $document->addMedia($file)
            ->withCustomProperties(['document_type' => $docType])
            ->toMediaCollection('documents');
    }
}

// Fire upload event
event(new \App\Events\Documents\DocumentUploaded($document));
```

### Get Uploaded Files

```php
$document = Document::uid('doc-uuid');

// Get all media
$allFiles = $document->media;

// Get specific collection
$documents = $document->getMedia('documents');

// Get first media
$first = $document->getFirstMedia('documents');

// Get URLs
$urls = $document->getAllDocumentsUrls();
// Returns: ['https://...file1.pdf', 'https://...file2.jpg']

$url = $document->getDocumentUrl();
// Returns: 'https://...first_file.pdf'

// Get custom property
foreach ($document->media as $media) {
    $type = $media->getCustomProperty('document_type');
    $name = $media->file_name;
    $size = $media->size;
    $url = $media->getUrl();
}
```

### Delete Documents

```php
$document = Document::uid('doc-uuid');
$media = $document->media->first();

// Delete specific file
$media->delete();

// Clear all media
$document->clearMediaCollection('documents');

// Delete entire document
$document->delete();
```

---

## 4. Type Detection

### Auto-Detect Document Type

```php
use App\Models\Document\Document;

$document = Document::uid('doc-uuid');

// Get detected type
$type = $document->detectDocumentType();
// Returns: 'dni', 'escopeta', 'rifle', 'corta', or 'general'

// Get required documents for type
$required = $document->getRequiredDocuments();
/*
Returns for 'escopeta':
[
    'dni_frontal' => 'DNI - Cara delantera',
    'dni_trasera' => 'DNI - Cara trasera',
    'licencia' => 'Licencia de escopeta (tipo E)'
]
*/

// Get document status
$status = $document->getDocumentStatus();
/*
Returns:
[
    'total_required' => 3,
    'total_uploaded' => 1,
    'required_documents' => [...],
    'uploaded_documents' => [...],
    'missing_documents' => [...],
    'is_complete' => false
]
*/
```

---

## 5. Email Operations

### Send Emails Programmatically

```php
use App\Mail\Documents\DocumentCustomMail;use App\Models\Document\Document;use App\Services\Documents\DocumentActionService;use App\Services\Documents\DocumentMailService;use Illuminate\Support\Facades\Mail;

$document = Document::uid('doc-uuid');

// Send initial request
DocumentMailService::sendUploadNotification($document);
DocumentActionService::logInitialRequestEmail($document, $document->customer_email);

// Send reminder
DocumentMailService::sendReminder($document);
DocumentActionService::logReminderEmail($document, $document->customer_email);

// Send confirmation
DocumentMailService::sendUploadedConfirmation($document);

// Send custom email

Mail::to($document->customer_email)->send(
    new DocumentCustomMail($document, $subject, $content)
);

DocumentActionService::logCustomEmail($document, $document->customer_email, $subject, $content);
```

---

## 6. Actions & Audit Trail

### Log Actions

```php
use App\Models\Document\Document;use App\Services\Documents\DocumentActionService;

$document = Document::uid('doc-uuid');

// Log status change
DocumentActionService::logStatusChange(
    $document,
    'pending',
    'completed',
    auth()->id()
);

// Log document upload
DocumentActionService::logDocumentUpload(
    $document,
    [
        ['file_name' => 'dni_frontal.pdf', 'size' => 2048],
        ['file_name' => 'dni_trasera.pdf', 'size' => 2048],
    ]
);

// Log admin upload
DocumentActionService::logAdminDocumentUpload(
    $document,
    [
        ['file_name' => 'licencia.pdf', 'size' => 3000],
    ],
    auth()->id()
);

// Add internal note
DocumentActionService::addNote(
    $document,
    auth()->id(),
    'Customer called, said documents are coming tomorrow',
    isInternal: true
);

// Add external note
DocumentActionService::addNote(
    $document,
    auth()->id(),
    'We received your documents, thank you!',
    isInternal: false
);
```

### Get Audit Trail

```php
use App\Models\Document\Document;use App\Services\Documents\DocumentActionService;

$document = Document::uid('doc-uuid');

// Get all actions
$history = DocumentActionService::getDocumentHistory($document);

// Iterate through actions
foreach ($history as $action) {
    echo "{$action->action_name}: {$action->description}\n";
    // Output:
    // Correo de Solicitud Inicial Enviado: Se envió correo...
    // Documentos Cargados: Se cargaron 2 documento(s)
    // Estado Modificado: Estado cambió de 'pending' a 'completed'
}

// Get notes only
$notes = DocumentActionService::getDocumentNotes($document, onlyInternal: false);

foreach ($notes as $note) {
    echo "{$note->content} - by {$note->admin->name}\n";
}

// Directly query actions
$actions = $document->actions()->orderBy('created_at', 'desc')->get();
```

---

## 7. Data Denormalization

### Update Denormalized Customer Data

```php
use App\Models\Document\Document;use App\Models\Prestashop\Order\Order as PrestashopOrder;

$document = Document::uid('doc-uuid');
$order = PrestashopOrder::find($document->order_id);

// Sync customer data from order
$customer = $order->customer;
$deliveryAddress = $order->deliveryAddress;

$document->customer_id = $customer->id_customer;
$document->customer_firstname = $deliveryAddress?->firstname ?? $customer->firstname;
$document->customer_lastname = $deliveryAddress?->lastname ?? $customer->lastname;
$document->customer_email = $customer->email;
$document->customer_dni = $deliveryAddress?->dni ?? $deliveryAddress?->vat_number ?? null;
$document->customer_company = $deliveryAddress?->company ?? null;
$document->customer_cellphone = $deliveryAddress?->phone_mobile ?? null;
$document->order_reference = $order->reference;
$document->order_date = $order->date_add;
$document->cart_id = $order->id_cart;

$document->save();
```

---

## 8. Product Capture

### Get Captured Products

```php
use App\Models\Document\Document;

$document = Document::uid('doc-uuid');

// Get products
$products = $document->products()->get();

foreach ($products as $product) {
    echo "{$product->product_name} x {$product->quantity} @ {$product->price}\n";
}

// Get product count
$count = $document->products()->count();

// Manually capture products
$document->captureProducts();
```

---

## 9. JavaScript Import Implementation

### Frontend Fetch Call

```javascript
// Single order import
fetch(`/administrative/documents/sync/by-order?order_id=456`, {
    method: 'GET',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        'Content-Type': 'application/json',
    },
})
.then(response => response.json())
.then(data => {
    if (data.status === 'success') {
        console.log(`Imported: ${data.data.synced} documents`);
        console.log(`Products: ${data.data.products_count}`);
        console.log(`Customer: ${data.data.customer_name}`);
    } else {
        console.error(`Error: ${data.message}`);
        if (data.data?.existing_documents) {
            console.log(`Existing documents: ${data.data.existing_documents}`);
        }
    }
})
.catch(error => console.error('Fetch error:', error));
```

### Batch Import

```javascript
function importMultipleOrders(orderIds) {
    const results = [];
    let completed = 0;
    
    const processNext = (index) => {
        if (index >= orderIds.length) {
            console.log('All done!', results);
            return;
        }
        
        const orderId = orderIds[index];
        
        fetch(`/administrative/documents/sync/by-order?order_id=${orderId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Content-Type': 'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            results.push({
                order_id: orderId,
                status: data.status,
                message: data.message
            });
            completed++;
            console.log(`Progress: ${completed}/${orderIds.length}`);
            processNext(index + 1);
        })
        .catch(err => {
            results.push({
                order_id: orderId,
                status: 'error',
                message: err.message
            });
            processNext(index + 1);
        });
    };
    
    processNext(0);
}

// Usage
importMultipleOrders([123, 456, 789]);
```

---

## 10. Route Usage

### Access Import Form

```
GET /administrative/documents/import
```

Returns HTML form view.

### Trigger Import

```
GET /administrative/documents/sync/by-order?order_id=456
POST /administrative/documents/sync/by-order (with form data)
```

Returns JSON:
```json
{
  "status": "success",
  "message": "Successfully synced 1 document(s) for order 456.",
  "data": {
    "order_id": 456,
    "synced": 1,
    "failed": 0,
    "total": 1,
    "products_count": 3,
    "order_reference": "BKFDP0IJ1",
    "customer_name": "John Doe",
    "errors": []
  }
}
```

### Sync All Documents

```
GET /administrative/documents/sync/all
```

Returns JSON summary of all synced documents.

---

## 11. Error Handling

### Validate Before Import

```php
use Illuminate\Validation\ValidationException;

$request->validate([
    'order_id' => 'required|integer|exists:aalv_orders,id_order',
]);

// Or check manually
$order = PrestashopOrder::find($orderId);
if (!$order) {
    return response()->json([
        'status' => 'failed',
        'message' => 'Order not found'
    ], 404);
}

// Check if document exists
$existing = Document::where('order_id', $orderId)->first();
if ($existing) {
    return response()->json([
        'status' => 'failed',
        'message' => "Orden {$orderId} ya existe."
    ], 400);
}
```

### Handle Missing Data

```php
try {
    $document->captureProducts();
} catch (Exception $e) {
    Log::error("Failed to capture products: {$e->getMessage()}");
    // Document still created, but with no products
}

try {
    $this->syncDocumentWithOrder($document, $order);
} catch (Exception $e) {
    Log::error("Failed to sync: {$e->getMessage()}");
    // Optionally delete the document
    $document->delete();
    throw $e;
}
```

---

## 12. Testing

### Unit Test Example

```php
namespace Tests\Unit;

use App\Models\Document\Document;use App\Models\Prestashop\Order\Order as PrestashopOrder;use Tests\TestCase;

class DocumentImportTest extends TestCase
{
    public function test_create_document_for_order()
    {
        $order = PrestashopOrder::find(456);
        
        // Import
        $response = $this->get('/administrative/documents/sync/by-order?order_id=456');
        
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
        
        // Verify document was created
        $document = Document::where('order_id', 456)->first();
        $this->assertNotNull($document);
        $this->assertEquals('pending', $document->proccess);
        $this->assertNotNull($document->uid);
    }
    
    public function test_prevent_duplicate_import()
    {
        // Create first import
        $response1 = $this->get('/administrative/documents/sync/by-order?order_id=456');
        $response1->assertStatus(200);
        
        // Try second import
        $response2 = $this->get('/administrative/documents/sync/by-order?order_id=456');
        $response2->assertStatus(400);
        $response2->assertJson(['status' => 'failed']);
    }
}
```

### Feature Test Example

```php
namespace Tests\Feature;

use App\Models\Document\Document;use Tests\TestCase;

class DocumentImportFeatureTest extends TestCase
{
    public function test_import_form_displays()
    {
        $this->actingAs($admin)
            ->get('/administrative/documents/import')
            ->assertStatus(200)
            ->assertViewIs('administratives.views.documents.import');
    }
    
    public function test_batch_import_orders()
    {
        $orderIds = [123, 456, 789];
        
        foreach ($orderIds as $id) {
            $response = $this->get("/administrative/documents/sync/by-order?order_id={$id}");
            $response->assertStatus(200);
        }
        
        // Verify all documents created
        $documents = Document::whereIn('order_id', $orderIds)->get();
        $this->assertCount(3, $documents);
    }
}
```

---

## 13. Troubleshooting

### Debug Import Process

```php
// Enable query logging
\DB::enableQueryLog();

// Import document
$document = new Document();
$document->order_id = 456;
$document->save();

// Check queries
dd(\DB::getQueryLog());

// Or use Log
\Log::info('Document created', [
    'document_id' => $document->id,
    'uid' => $document->uid,
    'order_id' => $document->order_id,
]);
```

### Verify Products Captured

```php
$document = Document::uid('doc-uuid');

// Check if products exist
$products = $document->products()->get();
\Log::info('Products captured', ['count' => $products->count()]);

foreach ($products as $p) {
    \Log::info("Product: {$p->product_name} (ID: {$p->product_id})");
}
```

### Check Type Detection

```php
$document = Document::uid('doc-uuid');

// Check product features
$features = \DB::connection('prestashop')
    ->table('aalv_feature_product')
    ->where('id_product', $document->products->first()->product_id)
    ->where('id_feature', 23)
    ->get();

\Log::info('Features detected', $features->toArray());

// Check detected type
$type = $document->detectDocumentType();
\Log::info("Document type: {$type}");
```

---

## 14. Performance Tips

### Optimize Queries

```php
// Bad: N+1 problem
$documents = Document::all();
foreach ($documents as $doc) {
    echo $doc->customer->firstname;  // Query per document
}

// Good: Eager loading
$documents = Document::with('customer')->get();
foreach ($documents as $doc) {
    echo $doc->customer->firstname;  // No extra queries
}

// Even better: Use denormalized fields
$documents = Document::all();
foreach ($documents as $doc) {
    echo $doc->customer_firstname;  // Already in table
}
```

### Batch Operations

```php
// Bad: Individual saves
foreach ($orderIds as $id) {
    $doc = new Document();
    $doc->order_id = $id;
    $doc->save();  // One query per order
}

// Better: Consider batching if possible
// But import needs event firing, so per-order is fine
```

### Use Indexes

The `request_documents` table has indexes on:
- `customer_firstname`, `customer_lastname`
- `customer_email`, `customer_dni`
- `order_reference`, `order_id`, `order_date`
- Composite: `(customer_firstname, customer_lastname)`

Use these in WHERE clauses for performance.

---

## 15. Migration/Database

### Run Migrations

```bash
php artisan migrate
```

Creates:
- `request_documents` table
- `request_document_products` table
- `request_document_actions` table
- Related tables (document notes, configurations)

### Add Indexes Manually

```php
// In migration
Schema::table('request_documents', function (Blueprint $table) {
    $table->index('order_id');
    $table->index('customer_email');
});
```

---

## References

- Full docs: `DOCUMENTS_IMPORT_ANALYSIS.md`
- Quick ref: `DOCUMENTS_IMPORT_QUICK_REFERENCE.md`
- Route: `/routes/administratives.php`
- Controller: `/app/Http/Controllers/Administratives/Documents/DocumentsController.php`
