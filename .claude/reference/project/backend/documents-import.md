# Documents Import Implementation - Complete Documentation

This directory contains comprehensive documentation of the documents import system for the administrative panel.

## Documentation Files

### 1. **DOCUMENTS_IMPORT_ANALYSIS.md** (21 KB, 656 lines)
**Complete technical analysis and implementation guide**

Contains:
- Route definition and authentication
- Controller architecture (DocumentsController)
- View implementation (import.blade.php)
- Models (Document, DocumentProduct, DocumentAction)
- Events and listeners architecture
- Services (DocumentTypeService, DocumentActionService, DocumentMailService)
- Database schema (request_documents, request_document_products, request_document_actions)
- Complete import process flow with diagrams
- Integration with Prestashop
- Feature overview and key functionality
- Related endpoints and error handling

**Use this when:** You need to understand the complete system architecture and how all components work together.

---

### 2. **DOCUMENTS_IMPORT_QUICK_REFERENCE.md** (11 KB, 370 lines)
**Quick lookup guide for developers**

Contains:
- File locations and component mappings
- Key controller methods with line numbers
- Database table structures and relationships
- Simplified import flow diagram
- Document type detection mapping
- Required documents by type
- Email workflow overview
- Query scopes and service methods
- API endpoints
- Event architecture
- Error scenarios and prevention strategies

**Use this when:** You need to quickly find a component, understand the flow, or reference specific features.

---

### 3. **DOCUMENTS_IMPORT_CODE_SNIPPETS.md** (18 KB, 821 lines)
**Ready-to-use code examples and implementations**

Contains:
- Manual import usage examples
- Query examples (by status, by order, with filtering)
- Document management (upload, retrieve, delete)
- Type detection examples
- Email operations and sending
- Actions and audit trail logging
- Data denormalization examples
- Product capture examples
- JavaScript/frontend implementation
- Route usage and API examples
- Error handling patterns
- Testing examples (unit and feature tests)
- Troubleshooting and debugging
- Performance optimization tips
- Database migration information

**Use this when:** You need to implement a feature, add functionality, or understand how to use the API.

---

## System Overview

The documents import system manages document workflows in the administrative panel:

```
/administrative/documents/import
    ↓
User enters order IDs
    ↓
JavaScript processes & displays them
    ↓
For each order:
    GET /administrative/documents/sync/by-order?order_id=X
    ↓
    Creates Document with denormalized data
    Captures products from Prestashop
    Auto-detects document type
    Sends email notification
    Schedules reminder
    ↓
    Returns result (success/error)
```

---

## Key Components

| Component | Purpose | Location |
|-----------|---------|----------|
| **Controller** | Handles import logic | `/app/Http/Controllers/Administratives/Documents/DocumentsController.php` |
| **Route** | URL endpoint | `/routes/administratives.php` |
| **View** | UI form | `/resources/views/administratives/views/documents/import.blade.php` |
| **Models** | Data layer | `/app/Models/Order/` |
| **Events** | System notifications | `/app/Events/Documents/` |
| **Listeners** | Event handlers | `/app/Listeners/Documents/` |
| **Services** | Business logic | `/app/Services/` |
| **Database** | Data storage | `request_documents`, `request_document_products`, etc. |

---

## Key Features

1. **Order Import** - Import Prestashop orders as documents
2. **Type Detection** - Auto-detect document type from product features
3. **Data Denormalization** - Copy customer/order data for performance
4. **Product Capture** - Automatically capture products from orders
5. **Email Notifications** - Send initial request + scheduled reminders
6. **Audit Trail** - Log all actions and changes
7. **Duplicate Prevention** - Prevent reimporting same order
8. **File Management** - Support for file uploads via Spatie MediaLibrary
9. **Status Tracking** - Track document completion status
10. **Admin Operations** - Manual admin actions (upload, send emails, notes)

---

## Database Schema

### request_documents
Main document table with denormalized customer/order data
- 50+ fields including status, customer info, order reference
- Multiple indexes for performance
- JSON fields for required/uploaded documents tracking

### request_document_products
Product line items for each document
- Links to Prestashop product data
- Denormalized product name, reference, quantity, price

### request_document_actions
Audit trail for all document actions
- Email sends, uploads, status changes
- Admin actions with user ID
- Metadata for detailed tracking

---

## Quick Start

### Access Import Page
```
GET /administrative/documents/import
```

### Import Single Order
```
GET /administrative/documents/sync/by-order?order_id=123
```

### Import Multiple Orders (JavaScript)
```javascript
const orderIds = [123, 456, 789];
orderIds.forEach(id => {
    fetch(`/administrative/documents/sync/by-order?order_id=${id}`)
        .then(r => r.json())
        .then(data => console.log(data));
});
```

### Query Documents

```php
use App\Models\Document\Document;

// Get all pending
Document::where('proccess', 'pending')->get();

// Search by customer
Document::searchByCustomerOrOrder('John Doe')->get();

// With filters
Document::filterListing('search', null, '2024-11-01', '2024-11-30')->paginate(15);
```

---

## Document Types

| Type | Trigger | Required Documents |
|------|---------|-------------------|
| **corta** | Product has feature 263661 | DNI front/back + Pistol license |
| **rifle** | Product has feature 263660 | DNI front/back + Rifle license |
| **escopeta** | Product has feature 263659 | DNI front/back + Shotgun license |
| **dni** | Product has feature 263658 | DNI front/back |
| **general** | No matching features | Passport or driver's license |

---

## Status Values

| Status | Meaning |
|--------|---------|
| pending | Document created, awaiting upload |
| incomplete | Partial documents uploaded |
| awaiting_documents | Explicitly awaiting documents |
| completed | All required documents received |
| approved | Documents approved by admin |
| rejected | Documents rejected |
| cancelled | Document cancelled |

---

## Email Workflow

```
DocumentCreated Event
    ↓
SendDocumentUploadNotification
    ├─ Send initial email (sync)
    └─ Schedule reminder (async, +1 day)

DocumentUploaded Event
    ↓
SendDocumentUploadConfirmation
    └─ Send confirmation email (sync)

DocumentReminderRequested Event
    ↓
SendDocumentUploadReminder
    └─ Send reminder email (sync)
```

---

## Performance Notes

- All query scopes use proper indexes
- Denormalized fields avoid expensive joins
- Spatie MediaLibrary handles file storage
- Events dispatched asynchronously where possible
- Database indexes on commonly searched fields

---

## Integration with Prestashop

The system reads from Prestashop tables:
- `aalv_orders` - Order data
- `aalv_order_detail` - Products in order
- `aalv_customer` - Customer info
- `aalv_address` - Delivery addresses
- `aalv_feature_product` - Product features (for type detection)

---

## File Upload

Uses Spatie MediaLibrary:
- Single collection: `documents`
- Custom properties: `document_type` (dni_frontal, dni_trasera, licencia, etc.)
- Supported: PDF, images, etc.
- File size limits: 10MB max
- URL retrieval: `$media->getUrl()`

---

## API Response Examples

### Success Response
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

### Error Response
```json
{
  "status": "failed",
  "message": "Orden 456 ya existe.",
  "data": {
    "order_id": 456,
    "existing_documents": 1
  }
}
```

---

## Troubleshooting

### Document Not Created
- Check if order exists in Prestashop (order ID correct)
- Check if document already exists (duplicate prevention)
- Check logs for sync errors

### Type Not Detected
- Verify products have feature ID 23
- Check feature values in Prestashop
- Review product feature assignments

### Email Not Sent
- Check customer email is populated
- Verify mail configuration
- Check DocumentMailService implementation

### Products Not Captured
- Verify order has products in Prestashop
- Check aalv_order_detail table
- Review captureProducts() method

---

## Development Tips

1. **Always check for duplicates** before creating documents
2. **Use denormalized fields** in queries (customer_firstname, etc.) for performance
3. **Fire events** after creating/uploading documents for email notifications
4. **Log actions** using DocumentActionService for audit trail
5. **Test with actual Prestashop data** to verify integration
6. **Monitor email delivery** using DocumentAction audit trail

---

## Related Documentation

- Laravel: https://laravel.com/docs
- Spatie MediaLibrary: https://spatie.be/docs/laravel-medialibrary
- Prestashop API: https://devdocs.prestashop-project.org/

---

## Support Resources

- Controller: `/app/Http/Controllers/Administratives/Documents/DocumentsController.php`
- Models: `/app/Models/Order/` directory
- Events: `/app/Events/Documents/` directory
- Services: `/app/Services/` directory
- Tests: `/tests/` directory
- Logs: `/storage/logs/`

---

## Summary

The documents import system is a complete solution for managing document requests from orders. It:

1. Imports orders from Prestashop
2. Creates documents with intelligent type detection
3. Sends automated email notifications
4. Tracks all actions in an audit trail
5. Supports file uploads and management
6. Provides admin controls for manual operations

Use the three documentation files for different needs:
- **ANALYSIS** for understanding architecture
- **QUICK_REFERENCE** for finding components quickly
- **CODE_SNIPPETS** for implementation examples

---

Generated: November 28, 2024
Project: Alsernet
Module: Administrative Documents Import
