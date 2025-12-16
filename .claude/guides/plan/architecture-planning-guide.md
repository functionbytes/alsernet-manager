# Plan Agent - Architecture Planning Guide

**Design database schemas, APIs, and system architecture for Alsernet features.**

---

## Architecture Planning Process

```
Requirements
    ↓
Data Model Design
    ↓
API Design
    ↓
Service Architecture
    ↓
Integration Points
    ↓
Ready for Implementation
```

---

## SECTION 1: Database Schema Design

### Step 1: Identify Entities

From requirements, extract the NOUNS:
```
Feature: "Managers can track warehouse inventory and get notifications when stock is low"

Entities:
├── Warehouse (where items are stored)
├── Product (what is stored)
├── Inventory (stock level linking warehouse + product)
└── InventoryAlert (low stock notification)
```

### Step 2: Define Relationships

```
Warehouse
├── has many Products (through Inventory)
└── has many InventoryAlerts

Product
├── has many Warehouses (through Inventory)
└── has many InventoryAlerts

Inventory
├── belongs to Warehouse
└── belongs to Product

InventoryAlert
├── belongs to Inventory
└── belongs to Manager (User)
```

### Step 3: Design Table Structure

```sql
CREATE TABLE warehouses (
    id UUID PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    capacity INTEGER,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE products (
    id UUID PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) UNIQUE,
    min_stock_level INTEGER DEFAULT 10,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE inventory (
    id UUID PRIMARY KEY,
    warehouse_id UUID NOT NULL (FK → warehouses),
    product_id UUID NOT NULL (FK → products),
    current_quantity INTEGER,
    reserved_quantity INTEGER,
    available_quantity INTEGER AS (current_quantity - reserved_quantity),
    last_updated_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(warehouse_id, product_id)
);

CREATE TABLE inventory_alerts (
    id UUID PRIMARY KEY,
    inventory_id UUID NOT NULL (FK → inventory),
    alert_type VARCHAR(50), -- low_stock, overstock, critical
    threshold_value INTEGER,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- INDEXES for performance
CREATE INDEX idx_inventory_warehouse ON inventory(warehouse_id);
CREATE INDEX idx_inventory_product ON inventory(product_id);
CREATE INDEX idx_alerts_inventory ON inventory_alerts(inventory_id);
CREATE INDEX idx_alerts_active ON inventory_alerts(is_active);
```

### Design Rules for Alsernet

✅ **Always include:**
- `id` (UUID primary key)
- `created_at` (timestamp)
- `updated_at` (timestamp)
- Soft deletes if data should be preserved

✅ **Use JSON columns for:**
- Flexible metadata
- ERP data
- Historical snapshots

❌ **DON'T use JSON for:**
- Queryable data
- Related entities (use separate table)
- Searchable content

✅ **Index these fields:**
- Foreign keys
- Status fields
- Created_at for filtering
- Fields used in WHERE clauses

---

## SECTION 2: API Endpoint Design

### REST Endpoint Structure

```
/api/v1/warehouses
├── GET /api/v1/warehouses                    (list all)
├── POST /api/v1/warehouses                   (create)
├── GET /api/v1/warehouses/{id}              (show)
├── PUT /api/v1/warehouses/{id}              (update)
├── DELETE /api/v1/warehouses/{id}           (delete)
│
└── /api/v1/warehouses/{id}/inventory
    ├── GET /inventory                        (list inventory)
    ├── POST /inventory                       (add product)
    ├── PUT /inventory/{invId}               (update quantity)
    └── DELETE /inventory/{invId}            (remove product)
```

### Request/Response Examples

```
POST /api/v1/warehouses
{
  "name": "Central Warehouse",
  "location": "Madrid",
  "capacity": 5000
}

Response:
{
  "success": true,
  "data": {
    "id": "uuid-...",
    "name": "Central Warehouse",
    "location": "Madrid",
    "capacity": 5000,
    "created_at": "2025-01-15T10:30:00Z"
  }
}
```

### Error Responses

```
400 Bad Request:
{
  "error": "validation_error",
  "messages": {
    "name": "Name is required",
    "capacity": "Capacity must be positive"
  }
}

401 Unauthorized:
{
  "error": "unauthorized",
  "message": "Authentication required"
}

404 Not Found:
{
  "error": "not_found",
  "message": "Warehouse not found"
}

422 Unprocessable Entity:
{
  "error": "business_logic_error",
  "message": "Warehouse capacity exceeded"
}
```

---

## SECTION 3: Service Architecture

### Service Layer Design

```
Controller (HTTP handling)
    ↓
Service (Business logic)
    ↓
Repository (Data access)
    ↓
Model (Database)
```

### Example Service

```php
// InventoryService.php
class InventoryService {

    // Check if low stock
    public function checkLowStock(Inventory $inventory): bool {
        $minLevel = $inventory->product->min_stock_level;
        return $inventory->available_quantity <= $minLevel;
    }

    // Update inventory after order
    public function reserveInventory(
        Inventory $inventory,
        int $quantity
    ): void {
        if ($inventory->available_quantity < $quantity) {
            throw new InsufficientStockException();
        }

        $inventory->reserved_quantity += $quantity;
        $inventory->save();

        // Check if alert needed
        if ($this->checkLowStock($inventory)) {
            event(new InventoryLowEvent($inventory));
        }
    }

    // Release reserved inventory
    public function releaseInventory(
        Inventory $inventory,
        int $quantity
    ): void {
        $inventory->reserved_quantity -= $quantity;
        $inventory->save();
    }
}
```

---

## SECTION 4: Event-Driven Architecture

### Event Design

```
When: Inventory becomes low
Event: InventoryLowEvent
Listeners:
├── NotifyManagerListener (send email)
├── CreateAlertListener (create record)
└── LogActivityListener (audit trail)
```

### Implementation

```php
// Event
class InventoryLowEvent implements ShouldBroadcast {
    public function __construct(
        public Inventory $inventory,
        public int $alertThreshold
    ) {}

    public function broadcastOn() {
        return new PrivateChannel('warehouse.' . $this->inventory->warehouse_id);
    }
}

// Listeners
class NotifyManagerListener {
    public function handle(InventoryLowEvent $event) {
        Mail::to($event->inventory->warehouse->manager)
            ->send(new LowStockAlert($event->inventory));
    }
}

class CreateAlertListener {
    public function handle(InventoryLowEvent $event) {
        InventoryAlert::create([
            'inventory_id' => $event->inventory->id,
            'alert_type' => 'low_stock',
            'threshold_value' => $event->alertThreshold,
        ]);
    }
}
```

---

## SECTION 5: Real-time Features

### WebSocket Broadcasting

```
Event: InventoryUpdated
Channel: warehouse.{warehouse_id}
Broadcast: Frontend updates in real-time
```

### Frontend Integration

```javascript
// Subscribe to warehouse inventory updates
window.Echo.private('warehouse.' + warehouseId)
    .listen('InventoryUpdated', (e) => {
        // Update UI with new inventory
        updateInventoryRow(e.inventory);

        // Show alert if low stock
        if (e.isLowStock) {
            showLowStockAlert(e.inventory);
        }
    });
```

---

## SECTION 6: Caching Strategy

### What to Cache

```
✅ Cache these:
├── Warehouse list (expires: 1 hour)
├── Product list (expires: 30 minutes)
├── Inventory summary (expires: 5 minutes)
└── Low stock alerts (expires: 1 minute)

❌ Don't cache:
├── Current user data
├── Real-time inventory
└── Active orders
```

### Implementation

```php
// Cache warehouse data
$warehouses = Cache::remember(
    'warehouses_list',
    60 * 60, // 1 hour
    fn() => Warehouse::all()
);

// Invalidate cache on update
public function update(Warehouse $warehouse) {
    $warehouse->update($data);
    Cache::forget('warehouses_list');
    return $warehouse;
}
```

---

## SECTION 7: Security Considerations

### Authentication & Authorization

```
Endpoints need:
├── Authentication (who is making request?)
├── Authorization (do they have permission?)
└── Validation (is the data correct?)

Example:
- Manager can view warehouse inventory
- Only manager can update warehouse
- Admin can delete warehouse
```

### Implementation

```php
// Check permission in controller
public function update(UpdateWarehouseRequest $request, Warehouse $warehouse) {
    // Authorization check
    if (!$request->user()->can('update', $warehouse)) {
        abort(403);
    }

    // Update
    $warehouse->update($request->validated());

    return response()->json($warehouse);
}
```

---

## Architecture Checklist

```
BEFORE YOU IMPLEMENT:

Database:
□ All entities identified
□ Relationships defined
□ Foreign keys planned
□ Indexes planned
□ Soft deletes considered

API:
□ Endpoints listed
□ HTTP methods correct
□ Request/response format clear
□ Error handling defined
□ Pagination planned (if list endpoint)

Services:
□ Business logic identified
□ Transaction boundaries clear
□ Error handling defined
□ Validation points identified

Events:
□ Business events identified
□ Listeners needed
□ Broadcast channels needed

Security:
□ Authentication required
□ Authorization checks planned
□ Validation rules clear
□ Sensitive data identified
```

---

## Common Architecture Patterns

### Pattern 1: Simple CRUD
```
Table → Model → Service → Controller → API
No events, no real-time
Caching: minimal
Time: 4-6 hours
```

### Pattern 2: With Events
```
Table → Model → Service ← Event → Listener
Broadcasting to frontend
Caching: moderate
Time: 8-12 hours
```

### Pattern 3: Real-time Feature
```
Table → Model → Service ← Event → Listener → WebSocket
Broadcasting to all connected clients
Caching: active invalidation
Time: 12-16 hours
```

### Pattern 4: Complex Domain
```
Multiple Tables → Models → Services (domain-specific) → Controllers → API
Events throughout
Complex transactions
Heavy caching strategy
Time: 20-30+ hours
```

---

**Version:** 1.0
**Updated:** November 30, 2024
