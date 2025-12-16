# Documents Import Implementation Analysis

## Overview
The documents import system is located at `/administrative/documents/import` and handles the import of order documents from Prestashop. It's a comprehensive system for managing document workflows, including creation, synchronization, upload, and email notifications.

---

## 1. Route Definition

**File:** `/Users/functionbytes/Function/Coding/Alsernet/routes/administratives.php`

### Import Route
```php
Route::get('/import', [DocumentsController::class, 'import'])->name('administrative.documents.import');
```

### Related Sync Routes (Used by Import)
```php
Route::get('/sync/all', [DocumentsController::class, 'syncAllDocuments'])->name('administrative.documents.sync.all');
Route::post('/sync/by-order', [DocumentsController::class, 'syncByOrderId'])->name('administrative.documents.sync.by-order');
Route::get('/sync/by-order', [DocumentsController::class, 'syncByOrderId'])->name('administrative.documents.sync.by-order.query');
```

### Middleware
- `auth` - Authentication required
- `roles:administratives` - Restricted to administrative role

---

## 2. Controller: DocumentsController

**File:** `/Users/functionbytes/Function/Coding/Alsernet/app/Http/Controllers/Administratives/Documents/DocumentsController.php`

### Key Methods

#### `import()`
- **Line:** 98-101
- **Purpose:** Display the import form view
- **Returns:** Blade view `administratives.views.documents.import`

```php
public function import()
{
    return view('administratives.views.documents.import');
}
```

#### `syncByOrderId(Request $request)`
- **Line:** 513-627
- **Purpose:** Main import/sync logic for a single order
- **Process:**
  1. Validates order_id parameter
  2. Fetches order from Prestashop
  3. Creates new Document if not exists (prevents duplicates)
  4. Syncs document with order data via `syncDocumentWithOrder()`
  5. Fires `DocumentCreated` event
  6. Returns JSON response with sync results

```php
public function syncByOrderId(Request $request)
{
    // Validates order exists
    $order = PrestashopOrder::find($orderId);
    
    // Checks if document already exists (prevents duplicates)
    if (!$documents->isEmpty()) {
        return response()->json([
            'status' => 'failed',
            'message' => "Orden {$orderId} ya existe.",
            'data' => ['existing_documents' => $documents->count()]
        ], 400);
    }
    
    // Creates new document
    $document = new Document();
    $document->order_id = $orderId;
    $document->type = 'order';
    $document->source = 'api';
    $document->proccess = 0;
    $document->save();
    
    // Syncs with order data
    $this->syncDocumentWithOrder($document, $order);
    
    // Fires event for email notifications
    event(new \App\Events\Documents\DocumentCreated($document));
}
```

#### `syncDocumentWithOrder(Document $document, PrestashopOrder $order): bool`
- **Line:** 1078-1118
- **Purpose:** Synchronize document with Prestashop order data
- **Data Synced:**
  - Order reference, date, cart ID
  - Customer info (ID, name, email, DNI, company, phone)
  - Captures products via `captureProducts()`
  - Auto-detects document type based on products

```php
private function syncDocumentWithOrder(Document $document, PrestashopOrder $order): bool
{
    $customer = $order->customer;
    
    // Sync denormalized customer data
    $document->customer_id = $customer->id_customer;
    $document->customer_firstname = $deliveryAddress?->firstname ?? $customer->firstname;
    $document->customer_lastname = $deliveryAddress?->lastname ?? $customer->lastname;
    $document->customer_email = $customer->email;
    $document->customer_dni = $deliveryAddress?->dni ?? $deliveryAddress?->vat_number ?? null;
    $document->customer_company = $deliveryAddress?->company ?? null;
    $document->customer_cellphone = $deliveryAddress?->phone_mobile ?? null;
    
    $document->save();
    
    // Capture products from order
    $document->captureProducts();
    
    // Detect document type based on products
    $document->type = $document->detectDocumentType();
    $document->save();
}
```

#### `syncAllDocuments()`
- **Line:** 429-503
- **Purpose:** Sync all existing documents (admin operation)

---

## 3. View: Import Form

**File:** `/Users/functionbytes/Function/Coding/Alsernet/resources/views/administratives/views/documents/import.blade.php`

### UI Components
1. **Order Input Form**
   - Text input for comma-separated order IDs
   - "Add" button to populate list
   - Enter key support

2. **Selected Orders Display**
   - Badge-style list of added order IDs
   - Remove button for each order
   - Only shows when orders are added

3. **Import Button**
   - Disabled until orders are selected
   - Shows spinner during import

4. **Results Display**
   - Success/failure alerts per order
   - Shows synced products count
   - Shows customer name and reference

### JavaScript Implementation
- **Lines:** 59-282
- **Process:**
  1. Parses comma-separated order IDs
  2. Validates numeric format
  3. Prevents duplicates
  4. Fetches `/administrative/orders/sync/by-order?order_id={id}` per order
  5. Displays results with UI feedback
  6. Handles errors gracefully

```javascript
function importOrders(orderIds) {
    const totalOrders = orderIds.length;
    let importedCount = 0;
    let resultsHtml = '<div class="row">';
    
    const importNext = (index) => {
        if (index >= orderIds.length) {
            showResults(resultsHtml, importedCount, totalOrders);
            return;
        }
        
        const orderId = orderIds[index];
        
        fetch(`/administrative/orders/sync/by-order?order_id=${orderId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                importedCount++;
                // Display success alert with order details
            } else {
                // Display error alert
            }
            importNext(index + 1);
        });
    };
    
    importNext(0);
}
```

---

## 4. Models

### Document Model
**File:** `/Users/functionbytes/Function/Coding/Alsernet/app/Models/Order/Document.php`

**Table:** `request_documents`

#### Key Fields
```php
protected $fillable = [
    'uid',              // Unique identifier (generated via HasUid trait)
    'type',             // Document type: 'dni', 'escopeta', 'rifle', 'corta', 'order', 'general'
    'proccess',         // Status: pending, incomplete, awaiting_documents, completed, approved, rejected, cancelled
    'source',           // Origin: email, api, whatsapp, wp, manual
    'confirmed_at',     // When upload was confirmed
    'reminder_at',      // When reminder was sent
    'order_id',         // Prestashop order ID
    'customer_id',      // Prestashop customer ID
    'cart_id',          // Prestashop cart ID
    'order_reference',  // Order reference (denormalized)
    'order_date',       // Order date (denormalized)
    'customer_firstname',   // Customer first name (denormalized)
    'customer_lastname',    // Customer last name (denormalized)
    'customer_email',       // Customer email (denormalized)
    'customer_dni',         // Customer DNI/VAT (denormalized)
    'customer_company',     // Customer company (denormalized)
    'customer_cellphone',   // Customer phone (denormalized)
    'required_documents',   // JSON: required docs for type
    'uploaded_documents',   // JSON: uploaded docs info
];
```

#### Key Methods

**`captureProducts()`** - Lines 404-438
- Retrieves products from Prestashop order_detail table
- Creates DocumentProduct records
- Denormalizes product data (name, reference, quantity, price)

**`detectDocumentType()`** - Lines 315-371
- Queries product features from Prestashop (Feature ID 23)
- Maps feature values to document types:
  - 263658 → DNI
  - 263659 → ESCOPETA (shotgun)
  - 263660 → RIFLE
  - 263661 → CORTA (handgun)
- Returns 'general' as fallback

**`getDocumentStatus()`** - Lines 499-530
- Returns uploaded vs required documents
- Identifies missing documents
- Marks as complete when all required docs uploaded

**`getRequiredDocuments()`** - Lines 446-456
- Fetches from DocumentConfiguration by type
- Falls back to defaults if not configured

#### Relationships
```php
public function order(): BelongsTo              // PrestashopOrder
public function customer(): BelongsTo           // PrestashopCustomer
public function cart(): BelongsTo               // PrestashopCart
public function products()                      // DocumentProduct (hasMany)
public function actions()                       // DocumentAction (hasMany)
public function notes()                         // DocumentNote (hasMany)
public function media                           // Spatie MediaLibrary
```

#### Scopes (Filtering)
- `filterListing()` - Combined search, filter, date range
- `searchByCustomerOrOrder()` - Full-text search
- `filterByUploadStatus()` - Media presence filter
- `filterByDateRange()` - Date range filtering
- `orderByUploadPriority()` - Priority sorting (no upload first)

---

### DocumentProduct Model
**File:** `/Users/functionbytes/Function/Coding/Alsernet/app/Models/Order/DocumentProduct.php`

**Table:** `request_document_products`

#### Fields
```php
protected $fillable = [
    'document_id',      // Foreign key to Document
    'product_id',       // Prestashop product ID
    'product_name',     // Product name (denormalized)
    'product_reference',// Product reference code
    'quantity',         // Ordered quantity
    'price',           // Unit price (denormalized)
];
```

---

### DocumentAction Model
**File:** `/Users/functionbytes/Function/Coding/Alsernet/app/Models/Order/DocumentAction.php`

**Table:** `request_document_actions`

#### Purpose
Audit trail for all document actions (emails, uploads, status changes, etc.)

#### Fields
```php
protected $fillable = [
    'document_id',      // Foreign key to Document
    'action_type',      // Type: email_initial_request, email_reminder, documents_uploaded, etc.
    'action_name',      // Display name
    'description',      // Details
    'metadata',        // JSON: additional data
    'performed_by',    // Admin user ID
    'performed_by_type',// 'admin', 'system', 'customer'
];
```

#### Static Methods
```php
public static function logAction(          // Create action entry
    int $documentId,
    string $actionType,
    string $actionName,
    ?string $description = null,
    ?array $metadata = null,
    ?int $performedBy = null,
    string $performedByType = 'system'
)

public static function getDocumentHistory(int $documentId)  // Get all actions
```

---

## 5. Events & Listeners

### Events

#### DocumentCreated
**File:** `/Users/functionbytes/Function/Coding/Alsernet/app/Events/Documents/DocumentCreated.php`

Fired when a new document is created during sync.

**Listeners:**
- `SendDocumentUploadNotification` - Sends initial email request + schedules reminder

#### DocumentUploaded
**File:** `/Users/functionbytes/Function/Coding/Alsernet/app/Events/Documents/DocumentUploaded.php`

Fired when documents are uploaded by customer or admin.

**Listeners:**
- `SendDocumentUploadConfirmation` - Sends confirmation email to customer

#### DocumentReminderRequested
**File:** `/Users/functionbytes/Function/Coding/Alsernet/app/Events/Documents/DocumentReminderRequested.php`

Fired when reminder is manually requested.

### Event Listeners

#### SendDocumentUploadNotification
**File:** `/Users/functionbytes/Function/Coding/Alsernet/app/Listeners/Documents/SendDocumentUploadNotification.php`

```php
public function handle(DocumentCreated $event): void
{
    $document = $event->document->fresh();
    $recipient = $document->customer_email ?? $document->customer?->email;
    
    // Send initial email SYNCHRONOUSLY
    DocumentMailService::sendUploadNotification($document);
    
    // Schedule reminder for +1 day ASYNCHRONOUSLY
    dispatch(new SendDocumentReminderJob($document))
        ->delay(now()->addDay())
        ->onQueue('emails');
}
```

---

## 6. Services

### DocumentTypeService
**File:** `/Users/functionbytes/Function/Coding/Alsernet/app/Services/DocumentTypeService.php`

#### Static Methods

**`getRequiredDocuments(string $documentType): array`**
- Returns required documents for type
- Fallback to defaults if not in config

**`getDefaultDocuments(string $documentType): array`**
- Predefined defaults:
  - `corta`: DNI front/back + pistol license (type B/F)
  - `rifle`: DNI front/back + rifle license (type D)
  - `escopeta`: DNI front/back + shotgun license (type E)
  - `dni`: DNI front/back only
  - `general`: Passport or driver's license

**`getMissingDocuments(string $documentType, array $uploadedDocs): array`**
- Compares required vs uploaded
- Returns missing document keys/labels

**`allDocumentsUploaded(string $documentType, array $uploadedDocs): bool`**
- Validates all required documents are present

### DocumentActionService
**File:** `/Users/functionbytes/Function/Coding/Alsernet/app/Services/Documents/DocumentActionService.php`

#### Logging Methods
```php
logInitialRequestEmail(Document, string $email)
logReminderEmail(Document, string $email)
logMissingDocumentsEmail(Document, string $email, array $missingDocs)
logUploadConfirmation(Document)
logDocumentUpload(Document, array $uploadedFiles)
logStatusChange(Document, string $oldStatus, string $newStatus)
logAdminDocumentUpload(Document, array $uploadedFiles, int $adminId)
logDocumentDeletion(Document, string $fileName, int $adminId)
logCustomEmail(Document, string $email, string $subject, string $content)
```

#### Data Methods
```php
getDocumentHistory(Document)
addNote(Document, int $adminId, string $content, bool $isInternal)
getDocumentNotes(Document, bool $onlyInternal)
```

### DocumentMailService
**File:** `/Users/functionbytes/Function/Coding/Alsernet/app/Services/Documents/DocumentMailService.php`

Handles email sending:
- `sendUploadNotification()` - Initial request
- `sendReminder()` - Follow-up reminder
- `sendUploadedConfirmation()` - Confirmation to customer

---

## 7. Data Structure: Request Documents Table

```sql
CREATE TABLE request_documents (
    id BIGINT PRIMARY KEY,
    uid VARCHAR(255) UNIQUE,
    type VARCHAR(255) NULLABLE,
    proccess ENUM('pending', 'incomplete', 'awaiting_documents', 'completed', 'approved', 'rejected', 'cancelled'),
    source ENUM('email', 'api', 'whatsapp', 'manual', 'wp'),
    confirmed_at TIMESTAMP NULLABLE,
    reminder_at TIMESTAMP NULLABLE,
    order_id INT NULLABLE,
    customer_id INT NULLABLE,
    cart_id INT NULLABLE,
    order_reference VARCHAR(64) NULLABLE,
    order_date DATETIME NULLABLE,
    customer_firstname VARCHAR(32) NULLABLE,
    customer_lastname VARCHAR(32) NULLABLE,
    customer_email VARCHAR(128) NULLABLE,
    customer_dni VARCHAR(32) NULLABLE,
    customer_company VARCHAR(64) NULLABLE,
    customer_cellphone VARCHAR(20) NULLABLE,
    required_documents JSON NULLABLE,
    uploaded_documents JSON NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_customer_firstname,
    INDEX idx_customer_lastname,
    INDEX idx_customer_email,
    INDEX idx_customer_dni,
    INDEX idx_order_reference,
    INDEX idx_order_id,
    INDEX idx_order_date,
    INDEX idx_customer_names
);

CREATE TABLE request_document_products (
    id BIGINT PRIMARY KEY,
    document_id BIGINT,
    product_id INT NULLABLE,
    product_name VARCHAR(255),
    product_reference VARCHAR(64) NULLABLE,
    quantity INT,
    price DECIMAL(12,2) NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (document_id) REFERENCES request_documents(id) ON DELETE CASCADE,
    INDEX idx_document_id,
    INDEX idx_product_id,
    INDEX idx_product_name
);

CREATE TABLE request_document_actions (
    id BIGINT PRIMARY KEY,
    document_id BIGINT,
    action_type VARCHAR(255),
    action_name VARCHAR(255),
    description TEXT NULLABLE,
    metadata JSON NULLABLE,
    performed_by INT NULLABLE,
    performed_by_type VARCHAR(50),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 8. Import Process Flow

### Step-by-Step Import Workflow

```
User Access /administrative/documents/import
    ↓
Display Import Form (import.blade.php)
    ↓
User enters comma-separated order IDs
    ↓
JavaScript validates & displays selected orders
    ↓
User clicks "Importar" button
    ↓
FOR EACH ORDER:
    ├─ GET /administrative/orders/sync/by-order?order_id={id}
    ├─ DocumentsController::syncByOrderId()
    │   ├─ Validate order_id parameter
    │   ├─ Fetch order from Prestashop
    │   ├─ Check if document already exists (prevent duplicates)
    │   ├─ Create new Document
    │   │   ├─ Set order_id, type='order', source='api', proccess='pending'
    │   │   └─ Save document (generates uid via HasUid trait)
    │   ├─ Call syncDocumentWithOrder()
    │   │   ├─ Denormalize customer data
    │   │   ├─ Save document with customer info
    │   │   ├─ Call captureProducts()
    │   │   │   ├─ Query Prestashop aalv_order_detail table
    │   │   │   ├─ Create DocumentProduct records
    │   │   │   └─ Denormalize product data
    │   │   ├─ Call detectDocumentType()
    │   │   │   ├─ Query product features (ID 23)
    │   │   │   ├─ Map feature values to types
    │   │   │   └─ Set document.type
    │   │   └─ Save document
    │   ├─ Fire DocumentCreated event
    │   │   └─ SendDocumentUploadNotification listener
    │   │       ├─ Send initial request email (sync)
    │   │       └─ Schedule reminder job (+1 day, async)
    │   └─ Return JSON response
    │
    └─ JavaScript displays result (success/error alert)
    
Display Results Summary
    ├─ Total orders
    ├─ Imported count
    └─ Failed count
```

---

## 9. Integration Points with Prestashop

### Tables Used
- `aalv_orders` - Order data
- `aalv_order_detail` - Order products
- `aalv_customer` - Customer information
- `aalv_address` - Delivery addresses
- `aalv_feature_product` - Product features
- `aalv_cart` - Shopping cart

### Connection
- Uses Laravel's `prestashop` database connection
- Models in `App\Models\Prestashop` namespace
- Foreign keys referenced but not enforced (nullable)

---

## 10. Key Features

### Document Type Detection
Automatic detection based on product features:
- **DNI**: Any product with feature value 263658
- **ESCOPETA**: Any product with feature value 263659
- **RIFLE**: Any product with feature value 263660
- **CORTA**: Any product with feature value 263661
- **GENERAL**: Default when no matching features

### Email Workflow
1. **DocumentCreated** event fires
2. **SendDocumentUploadNotification** listener handles it:
   - Sends initial request email immediately (sync)
   - Schedules reminder job for +1 day (async, queued)

### Duplicate Prevention
Documents are checked before creation:
```php
if (!$documents->isEmpty()) {
    return response()->json([
        'status' => 'failed',
        'message' => "Orden {$orderId} ya existe."
    ], 400);
}
```

### Data Denormalization
Customer and order data are denormalized in Document for:
- Performance (no joins needed for common queries)
- Consistency (preserves historical data)
- Search efficiency (indexed fields)

### Audit Trail
Every action is logged to `request_document_actions`:
- Email sends (type, recipient, scheduled time)
- Document uploads (file count, file info)
- Status changes (old/new status)
- Manual admin actions (user ID, type)

---

## 11. Related Controllers/Routes

### Document Management Endpoints
```php
Route::get('/', [DocumentsController::class, 'index'])          // List all
Route::get('/pending', [DocumentsController::class, 'pending']) // Pending only
Route::get('/history', [DocumentsController::class, 'history']) // Historical
Route::get('/manage/{uid}', [DocumentsController::class, 'manage']) // Manage view
Route::post('/{uid}/send-notification', ...)  // Send initial email
Route::post('/{uid}/send-reminder', ...)      // Send reminder
Route::post('/{uid}/send-missing', ...)       // Request specific docs
Route::post('/{uid}/admin-upload', ...)       // Admin upload docs
Route::get('/{uid}/missing-documents', ...)   // Get missing docs list
Route::post('/manage/{uid}/add-note', ...)    // Add internal note
```

---

## Summary

The documents import system is a sophisticated order-to-document workflow that:

1. **Imports orders** from Prestashop by order ID
2. **Creates documents** with denormalized customer/order data
3. **Captures products** with type detection based on features
4. **Auto-detects document type** (DNI, ESCOPETA, RIFLE, CORTA, etc.)
5. **Sends email notifications** with automatic reminders
6. **Maintains audit trails** of all actions
7. **Supports manual admin operations** (upload, manage, send emails)
8. **Tracks upload status** with media library integration

The system uses events, services, and models to maintain clean separation of concerns while providing a flexible, extensible architecture for document management.
