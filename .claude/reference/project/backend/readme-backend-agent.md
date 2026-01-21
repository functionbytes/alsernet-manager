# 🚀 Backend Agent Specification - Alsernet

**Complete specification for the Alsernet Backend Development Agent.**

---

## 📋 TABLE OF CONTENTS

1. [Overview](#overview)
2. [Technology Stack](#stack)
3. [Agent Capabilities](#capabilities)
4. [Architecture Patterns](#patterns)
5. [Module Specifications](#modules)
6. [Database Patterns](#database)
7. [API Patterns](#api)
8. [Real-time Patterns](#realtime)
9. [Queue & Background Jobs](#queues)
10. [Testing Strategies](#testing)

---

## OVERVIEW {#overview}

### Agent Purpose
The Backend Agent is responsible for:
- ✅ Generating Laravel controllers, models, migrations
- ✅ Creating RESTful API endpoints
- ✅ Implementing business logic
- ✅ Managing database operations
- ✅ Setting up queue jobs
- ✅ Configuring real-time features
- ✅ Creating test suites
- ✅ Generating documentation

### Technology Stack {#stack}

#### Core Framework
| Component | Version | Purpose |
|-----------|---------|---------|
| Laravel | 12.x | Web framework |
| PHP | 8.2+ | Language |
| PostgreSQL | 14+ | Primary database |
| Redis | 6+ | Caching & queues |

#### Key Libraries
| Library | Purpose | Usage |
|---------|---------|-------|
| **Laravel Sanctum** | API authentication | Token auth |
| **Laravel Reverb** | WebSocket server | Real-time features |
| **Laravel Horizon** | Queue management | Job monitoring |
| **Spatie Permissions** | RBAC system | Role-based access |
| **Laravel Activity Log** | Audit trail | User actions |
| **Maatwebsite Excel** | Excel import/export | Spreadsheet handling |
| **DomPDF** | PDF generation | Document creation |
| **Guzzle HTTP** | HTTP client | External APIs |
| **Laravel Broadcasting** | Event broadcasting | Real-time updates |
| **Laravel Queue** | Job processing | Background jobs |

---

## AGENT CAPABILITIES {#capabilities}

### BLOCK 1: Model & Database (12 capabilities)

1. **Create Eloquent Models** with relationships
   - One-to-many, many-to-many, morphs
   - Accessors, mutators, casts
   - Scopes and query helpers
   - Timestamps, soft deletes

2. **Generate Migrations** with columns
   - Index creation
   - Foreign key constraints
   - Default values, nullable
   - Column modification

3. **Create Database Seeders** with data
   - Faker integration
   - Relationship seeding
   - Batch operations
   - Testing data generation

4. **Design Relationships** correctly
   - BelongsTo, HasMany
   - BelongsToMany with pivots
   - Polymorphic relationships
   - Morph relationships

5. **Implement Query Builders**
   - Complex where clauses
   - Eager loading (with, load)
   - Chunk processing
   - Pagination

6. **Create Database Helpers**
   - Custom pivot classes
   - Observables for models
   - Model events (creating, created, etc.)
   - Custom collections

7. **Implement Soft Deletes**
   - Soft delete models
   - Restore operations
   - Force delete handling
   - Trashed queries

8. **Create Index Strategies**
   - Primary keys
   - Composite indexes
   - Foreign key indexes
   - Performance optimization

9. **Implement Caching**
   - Model caching
   - Query result caching
   - Cache invalidation
   - Redis caching

10. **Database Transactions**
    - Begin/commit/rollback
    - Savepoints
    - Error recovery
    - Deadlock handling

11. **Raw Query Execution**
    - Prepared statements
    - Bind parameters
    - Procedure calling
    - Complex queries

12. **Backup & Restoration**
    - Automated backups
    - Point-in-time recovery
    - Data validation
    - Archive management

---

### BLOCK 2: Controllers & Routing (10 capabilities)

13. **Generate Controllers** with CRUD
    - Resource controllers
    - Nested resources
    - Custom methods
    - Request/response handling

14. **Create RESTful Endpoints**
    - GET (list, show)
    - POST (create)
    - PUT/PATCH (update)
    - DELETE (destroy)
    - Proper HTTP status codes

15. **Implement Route Groups**
    - Middleware assignment
    - Prefix definitions
    - Name prefixing
    - Authorization checks

16. **Request Validation**
    - Form Request classes
    - Rule definitions
    - Custom validation rules
    - Error messages

17. **Response Formatting**
    - API resources
    - Collection responses
    - Error responses
    - Status codes (200, 201, 400, 404, 500)

18. **Authentication & Authorization**
    - Sanctum token auth
    - Permission checking
    - Role verification
    - Gate definitions

19. **Pagination & Filtering**
    - Cursor pagination
    - Limit/offset
    - Sorting
    - Filter parameters

20. **API Documentation**
    - OpenAPI/Swagger specs
    - Endpoint documentation
    - Parameter descriptions
    - Example responses

21. **Rate Limiting**
    - Per-user limits
    - Per-IP limits
    - Throttle middleware
    - Custom limits

22. **Error Handling**
    - Custom exception handlers
    - Error logging
    - Stack traces
    - User-friendly messages

---

### BLOCK 3: Business Logic & Services (8 capabilities)

23. **Create Service Classes**
    - Business logic encapsulation
    - Reusable operations
    - Dependency injection
    - Testability

24. **Implement Repositories**
    - Data access abstraction
    - Query optimization
    - Cache integration
    - Consistent interface

25. **Create DTOs (Data Transfer Objects)**
    - Data validation
    - Type safety
    - Transformation logic
    - Serialization

26. **Event-Driven Architecture**
    - Event creation
    - Listener implementation
    - Broadcasting events
    - Real-time updates

27. **Job Creation**
    - Queueable jobs
    - Delayed execution
    - Failed job handling
    - Job chaining

28. **State Management**
    - State machines
    - Workflow transitions
    - Validation rules
    - Event triggering

29. **Cache Strategies**
    - Cache keys
    - TTL management
    - Cache invalidation
    - Cache warming

30. **Logging & Monitoring**
    - Custom log channels
    - Log levels
    - Structured logging
    - Performance metrics

---

### BLOCK 4: Real-time Features (6 capabilities)

31. **WebSocket Implementation**
    - Laravel Reverb setup
    - Channel configuration
    - Broadcasting events
    - Message handling

32. **Broadcasting Channels**
    - Public channels
    - Private channels
    - Presence channels
    - Authorization

33. **Real-time Notifications**
    - Notification classes
    - Channel handlers
    - Delivery methods
    - Retry logic

34. **Live Data Updates**
    - Model observables
    - Event broadcasting
    - Client listeners
    - Sync patterns

35. **Presence Tracking**
    - User presence
    - Join/leave events
    - Online status
    - User metadata

36. **Scheduled Broadcasting**
    - Timed events
    - Periodic updates
    - Cleanup jobs
    - Optimization

---

### BLOCK 5: Data Management (5 capabilities)

37. **Excel Import/Export**
    - CSV reading
    - Excel generation
    - Data transformation
    - Validation

38. **PDF Generation**
    - DomPDF integration
    - HTML to PDF
    - Custom templates
    - Asset handling

39. **File Management**
    - MediaLibrary integration
    - File uploads
    - Storage optimization
    - Cleanup

40. **Data Export**
    - Multiple formats
    - Large dataset handling
    - Streaming responses
    - Background processing

41. **Data Import**
    - Validation
    - Error handling
    - Batch processing
    - Duplicate detection

---

## ARCHITECTURE PATTERNS {#patterns}

### Service Layer Pattern
```php
namespace App\Services;

class WarehouseService {
    public function __construct(
        private WarehouseRepository $repository,
        private ActivityLogger $logger
    ) {}

    public function createWarehouse(array $data): Warehouse {
        $warehouse = $this->repository->create($data);
        $this->logger->log('warehouse_created', $warehouse);
        broadcast(new WarehouseCreated($warehouse))->toOthers();
        return $warehouse;
    }
}
```

### Repository Pattern
```php
namespace App\Repositories;

class WarehouseRepository {
    public function __construct(private Warehouse $model) {}

    public function getActive(): Collection {
        return Cache::remember('active_warehouses', 3600, fn() =>
            $this->model->where('active', true)->get()
        );
    }
}
```

### API Resource Pattern
```php
namespace App\Http\Resources;

class WarehouseResource extends JsonResource {
    public function toArray($request): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location,
            'capacity' => $this->capacity,
            'created_at' => $this->created_at,
        ];
    }
}
```

---

## MODULE SPECIFICATIONS {#modules}

### Core Modules
1. **Users & Authentication**
2. **Roles & Permissions**
3. **Warehouses**
4. **Products & Inventory**
5. **Returns & RMA**
6. **Tickets & Support**
7. **Notifications**
8. **Audit Logs**

### Each Module Includes
- ✅ Model with relationships
- ✅ Controller with CRUD
- ✅ Form Request validation
- ✅ API Resource formatting
- ✅ Service class
- ✅ Repository class
- ✅ Migration
- ✅ Seeder
- ✅ Tests (unit & feature)
- ✅ Routes definition

---

## DATABASE PATTERNS {#database}

### Migration Structure
```php
Schema::create('warehouses', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('location');
    $table->unsignedInteger('capacity');
    $table->boolean('active')->default(true);
    $table->timestamps();
    $table->softDeletes();

    $table->index('active');
});
```

### Relationship Definition
```php
class Warehouse extends Model {
    public function products() {
        return $this->hasMany(Product::class);
    }

    public function staff() {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivot('role');
    }
}
```

---

## API PATTERNS {#api}

### RESTful Endpoints
```
GET    /api/warehouses              # List with pagination
GET    /api/warehouses/{id}         # Show single
POST   /api/warehouses              # Create
PUT    /api/warehouses/{id}         # Update
DELETE /api/warehouses/{id}         # Delete
GET    /api/warehouses/{id}/products # Nested resource
```

### Response Format
```json
{
  "data": [
    {
      "id": 1,
      "name": "Main Warehouse",
      "location": "Madrid",
      "capacity": 5000,
      "created_at": "2025-11-30T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7
  }
}
```

---

## REAL-TIME PATTERNS {#realtime}

### Broadcasting Events
```php
class WarehouseUpdated implements ShouldBroadcast {
    public function broadcastOn(): array {
        return [new Channel('warehouse-updates')];
    }

    public function broadcastAs(): string {
        return 'warehouse.updated';
    }
}
```

### Listening on Frontend
```javascript
window.Echo.channel('warehouse-updates')
    .listen('warehouse.updated', (event) => {
        console.log('Warehouse updated:', event.warehouse);
    });
```

---

## QUEUES & BACKGROUND JOBS {#queues}

### Job Definition
```php
class ProcessWarehouseImport implements ShouldQueue {
    public function handle(WarehouseImportService $service) {
        $service->import($this->file);
    }
}
```

### Job Dispatch
```php
ProcessWarehouseImport::dispatch($file)
    ->delay(now()->addMinutes(5))
    ->onConnection('redis')
    ->onQueue('default');
```

---

## TESTING STRATEGIES {#testing}

### Unit Tests
```php
public function test_warehouse_can_be_created() {
    $warehouse = Warehouse::factory()->create();
    $this->assertDatabaseHas('warehouses', [
        'id' => $warehouse->id
    ]);
}
```

### Feature Tests
```php
public function test_can_list_warehouses() {
    $response = $this->getJson('/api/warehouses');
    $response->assertOk()
             ->assertJsonStructure(['data' => [['id', 'name']]]);
}
```

---

## NEXT STEPS

1. **Review Module Specifications** in `guides/` folder
2. **Study Architectural Patterns** in `patterns/` documentation
3. **Follow Implementation Guides** for each feature
4. **Run Tests** before deployment
5. **Document APIs** with Swagger/OpenAPI

---

**Version:** 1.0
**Date:** November 30, 2025
**Status:** Production Ready
**For:** Alsernet Backend Development Team
