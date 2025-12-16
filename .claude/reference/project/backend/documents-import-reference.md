# Documents Import - Quick Reference Guide

## File Locations

| Component | File Path |
|-----------|-----------|
| **Controller** | `/app/Http/Controllers/Administratives/Documents/DocumentsController.php` |
| **Route** | `/routes/administratives.php` (line 44) |
| **View** | `/resources/views/administratives/views/documents/import.blade.php` |
| **Model: Document** | `/app/Models/Order/Document.php` |
| **Model: DocumentProduct** | `/app/Models/Order/DocumentProduct.php` |
| **Model: DocumentAction** | `/app/Models/Order/DocumentAction.php` |
| **Event: Created** | `/app/Events/Documents/DocumentCreated.php` |
| **Event: Uploaded** | `/app/Events/Documents/DocumentUploaded.php` |
| **Listener: Notification** | `/app/Listeners/Documents/SendDocumentUploadNotification.php` |
| **Service: Type** | `/app/Services/DocumentTypeService.php` |
| **Service: Action** | `/app/Services/Documents/DocumentActionService.php` |
| **Service: Mail** | `/app/Services/Documents/DocumentMailService.php` |

---

## Key Controller Methods

```
DocumentsController
├── import()                          Line 98    → Display form
├── syncByOrderId()                   Line 513   → Main import logic
├── syncAllDocuments()                Line 429   → Sync all documents
└── syncDocumentWithOrder()           Line 1078  → Denormalize & capture data
```

---

## Database Tables

```
request_documents
├── id (PK)
├── uid (unique)
├── type (dni/escopeta/rifle/corta/order/general)
├── proccess (pending/incomplete/awaiting_documents/completed/approved/rejected/cancelled)
├── source (email/api/whatsapp/manual/wp)
├── order_id (FK to Prestashop)
├── customer_id (FK to Prestashop)
├── Denormalized: customer_firstname, customer_lastname, customer_email, customer_dni, customer_company, customer_cellphone
├── Denormalized: order_reference, order_date, cart_id
├── Media (via Spatie): documents (files)
└── Timestamps: created_at, updated_at, confirmed_at, reminder_at

request_document_products (One-to-Many)
├── id (PK)
├── document_id (FK)
├── product_id
├── product_name (denormalized)
├── product_reference
├── quantity
└── price

request_document_actions (One-to-Many - Audit Trail)
├── id (PK)
├── document_id (FK)
├── action_type
├── action_name
├── description
├── metadata (JSON)
├── performed_by
└── performed_by_type (admin/system/customer)
```

---

## Import Flow (Simplified)

```
User → /administrative/documents/import
  ↓
Display form with order ID input
  ↓
User enters: "123,456,789"
  ↓
JavaScript validates & displays as badges
  ↓
User clicks "Importar"
  ↓
For each order ID:
  GET /administrative/orders/sync/by-order?order_id=123
    ↓
  DocumentsController::syncByOrderId()
    ↓
    1. Check if document exists → if yes, return error
    2. Create new Document (uid auto-generated)
    3. Fetch Prestashop order
    4. syncDocumentWithOrder():
       a. Denormalize customer data
       b. captureProducts() - Get products from order_detail
       c. detectDocumentType() - Check product features
    5. Fire DocumentCreated event
       → SendDocumentUploadNotification listener
          → Send email (sync)
          → Schedule reminder (+1 day, async)
    6. Return JSON response
  ↓
JavaScript displays result (success/error)
  ↓
Show summary: Total/Imported/Failed
```

---

## Document Type Detection

Based on Prestashop product Feature ID 23:

| Feature Value | Type |
|---|---|
| 263658 | DNI |
| 263659 | ESCOPETA (Shotgun) |
| 263660 | RIFLE |
| 263661 | CORTA (Handgun) |
| (none) | GENERAL (default) |

---

## Required Documents by Type

| Type | Documents |
|------|-----------|
| **corta** | DNI front + DNI back + Pistol license (Type B/F) |
| **rifle** | DNI front + DNI back + Rifle license (Type D) |
| **escopeta** | DNI front + DNI back + Shotgun license (Type E) |
| **dni** | DNI front + DNI back |
| **general** | Passport or Driver's license |

---

## Email Workflow

**On DocumentCreated event:**

1. **Immediate (Sync)**
   - Send initial request email to customer
   - Log action: `email_initial_request`

2. **Delayed (Async, Queued)**
   - Schedule reminder for +1 day
   - Uses job: `SendDocumentReminderJob`
   - Queue: `emails`

**On DocumentUploaded event:**

1. **Immediate (Sync)**
   - Send confirmation email to customer
   - Log action: `documents_uploaded`

---

## Scopes (Query Helpers)

**On Document Model:**

```php
Document::filterListing($search, $uploadStatus, $dateFrom, $dateTo)
Document::searchByCustomerOrOrder($search)
Document::filterByUploadStatus($hasMedia)
Document::filterByDateRange($dateFrom, $dateTo)
Document::orderByUploadPriority()
Document::uid($uid)                    // Find by uid
Document::id($id)                      // Find by id
```

---

## Service Methods

### DocumentTypeService
```php
getRequiredDocuments(string $type): array
getDefaultDocuments(string $type): array
getMissingDocuments(string $type, array $uploaded): array
allDocumentsUploaded(string $type, array $uploaded): bool
```

### DocumentActionService
```php
logInitialRequestEmail(Document, string $email)
logReminderEmail(Document, string $email)
logMissingDocumentsEmail(Document, string $email, array $docs)
logUploadConfirmation(Document)
logDocumentUpload(Document, array $files)
logStatusChange(Document, string $old, string $new)
logAdminDocumentUpload(Document, array $files, int $adminId)
logDocumentDeletion(Document, string $fileName, int $adminId)
logCustomEmail(Document, string $email, string $subject, string $content)
addNote(Document, int $adminId, string $content, bool $isInternal)
getDocumentHistory(Document)
getDocumentNotes(Document, bool $onlyInternal)
```

---

## API Endpoints (From Controller)

| Method | Route | Handler |
|--------|-------|---------|
| GET | `/administrative/documents/import` | `import()` → Display form |
| GET/POST | `/administrative/documents/sync/by-order` | `syncByOrderId()` → Import single |
| GET | `/administrative/documents/sync/all` | `syncAllDocuments()` → Import all |

---

## Prevent Duplicates

Before creating a document, the system checks:

```php
$existing = Document::where('order_id', $orderId)->get();

if (!$existing->isEmpty()) {
    return error("Orden {$orderId} ya existe.");
}
```

Returns HTTP 400 with existing document count.

---

## Data Denormalization Strategy

Why denormalize customer/order data in `request_documents`?

1. **Performance** - No joins needed for listing/filtering
2. **Historical accuracy** - Preserves customer/order data at time of import
3. **Search efficiency** - Indexed fields for fast lookups
4. **Data integrity** - Independent of Prestashop changes

Example fields:
- `customer_firstname`, `customer_lastname` (from Order → Customer)
- `customer_dni` (from Order → DeliveryAddress)
- `order_reference`, `order_date` (from Prestashop Order)

---

## Event Architecture

```
DocumentCreated Event
  ↓
SendDocumentUploadNotification Listener
  ├─ DocumentMailService::sendUploadNotification() [SYNC]
  ├─ Log: logInitialRequestEmail()
  ├─ Dispatch SendDocumentReminderJob (+1 day) [ASYNC]
  └─ Log: log action in DocumentAction

DocumentUploaded Event
  ↓
SendDocumentUploadConfirmation Listener
  ├─ DocumentMailService::sendUploadedConfirmation() [SYNC]
  └─ Log: logDocumentUpload()

DocumentReminderRequested Event
  ↓
SendDocumentUploadReminder Listener
  ├─ DocumentMailService::sendReminder() [SYNC]
  └─ Log: logReminderEmail()
```

---

## Example: Import Order #456

**Request:** `GET /administrative/documents/sync/by-order?order_id=456`

**Flow:**
1. Fetch order from Prestashop
2. Check if Document exists for order_id=456 → No
3. Create Document:
   - `uid` = auto-generated UUID
   - `order_id` = 456
   - `source` = 'api'
   - `proccess` = 0 (pending)
   - `type` = 'order'
4. syncDocumentWithOrder():
   - Fetch Customer → `customer_id=789`
   - Copy: `customer_firstname`, `customer_lastname`, `customer_email`
   - Get delivery address → `customer_dni`, `customer_company`, `customer_cellphone`
   - captureProducts(): Query `aalv_order_detail` for products
     - Create DocumentProduct records
   - detectDocumentType(): Query product features
     - Find feature ID 23, value 263659
     - Set `type` = 'escopeta'
5. Save Document
6. Fire DocumentCreated event
   - SendDocumentUploadNotification listener
   - Send email to customer
   - Schedule reminder for +1 day
7. Return JSON:
   ```json
   {
     "status": "success",
     "message": "Successfully synced 1 document(s) for order 456.",
     "data": {
       "order_id": 456,
       "synced": 1,
       "products_count": 3,
       "customer_name": "John Doe",
       "order_reference": "BKFDP0IJ1"
     }
   }
   ```

**Result:** Document created with:
- Type: `escopeta` (auto-detected)
- Status: `pending` (awaiting documents)
- 3 DocumentProduct records
- Email sent to customer
- Reminder scheduled for tomorrow

---

## Pagination & Filtering

**Example:** List pending documents

```php
Document::filterListing('', null, null, null)
         ->whereIn('proccess', ['pending', 'incomplete', 'awaiting_documents'])
         ->paginate(15)
```

**Filters available:**
- `$search` - Customer name, email, DNI, order ID, reference
- `$proccess` - Status filter (pending, completed, etc.)
- `$dateFrom` - Date range start (Y-m-d)
- `$dateTo` - Date range end (Y-m-d)

---

## Related Features (Not Covered in Import)

These are available through DocumentsController but not part of import:

- **Manage view** - `/administrative/documents/manage/{uid}` - Full document management
- **Upload** - Admin can upload documents for customer
- **Send emails** - Send notification, reminder, or custom emails
- **Add notes** - Internal notes for document tracking
- **Confirm upload** - Mark documents as confirmed
- **Delete** - Remove document or individual files
- **History** - Audit trail via DocumentAction records
- **Generate PDF** - Combine all media into single PDF via `/summary/{id}`

---

## Key Traits & Utilities

- **HasUid** - Auto-generates unique `uid` field
- **HasMedia** - Spatie MediaLibrary integration for file uploads
- **InteractsWithMedia** - Methods for managing media

---

## Error Scenarios

| Scenario | Response |
|----------|----------|
| Order not found in Prestashop | 404: "Order not found in Prestashop" |
| Document already exists | 400: "Orden {id} ya existe" + count |
| Customer not found | 500: "Customer not found" |
| Product capture fails | Document still created but without products |
| Email service down | Error logged, document still created |

