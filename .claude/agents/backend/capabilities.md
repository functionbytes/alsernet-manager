# Backend Agent - Detailed Capabilities

**41 comprehensive capabilities for the Alsernet Backend Development Agent.**

---

## BLOCK 1: MODEL & DATABASE GENERATION (12 Capabilities)

### Capability 1: Create Eloquent Models
Generate complete Eloquent models with:
- Property definitions ($fillable, $casts, $dates)
- Relationship methods (hasMany, belongsTo, belongsToMany)
- Accessor/Mutator methods
- Query scopes
- Event listeners
- Custom methods

**Example Output:**
```php
class Warehouse extends Model {
    use HasFactory;

    protected $fillable = ['name', 'location', 'capacity', 'active'];
    protected $casts = ['active' => 'boolean'];

    public function products() {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query) {
        return $query->where('active', true);
    }
}
```

### Capability 2: Generate Database Migrations
Create schema migrations with:
- Table creation with all column types
- Index creation (single, composite, unique)
- Foreign key constraints with cascade options
- Column modification (change, rename, drop)
- Default values and nullable
- Timestamps and soft deletes

**Example:**
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

### Capability 3: Create Database Seeders
Generate seeders with:
- Faker data generation
- Relationship seeding
- Batch operations
- Custom data transformation
- Progress indicators

### Capability 4: Design Relationships
Implement correct relationships:
- One-to-many
- Many-to-many with pivot tables
- Polymorphic relationships
- Has-one-through, has-many-through
- Relation constraints

### Capability 5: Implement Query Builders
Create complex queries:
- Where clauses (simple, nested, raw)
- Eager loading optimization
- Chunked processing
- Pagination (limit, offset, cursor)
- Sorting and filtering

### Capability 6: Create Database Helpers
Build helper classes:
- Custom pivot classes
- Model observers
- Collection macros
- Query macros

### Capability 7: Implement Soft Deletes
Handle soft delete models:
- Soft delete traits
- Restore operations
- Force delete
- Querying trashed records
- Restore events

### Capability 8: Create Index Strategies
Design database indexes:
- Primary and composite keys
- Unique constraints
- Foreign key indexes
- Partial indexes
- Index naming conventions

### Capability 9: Implement Caching
Manage model caching:
- Cache keys generation
- TTL configuration
- Cache invalidation on updates
- Cache warming strategies
- Redis integration

### Capability 10: Database Transactions
Implement transaction handling:
- Transaction wrapping
- Savepoints
- Rollback on error
- Transaction events
- Deadlock handling

### Capability 11: Raw Query Execution
Execute complex queries:
- Prepared statements
- Parameter binding
- Raw where clauses
- Database procedures
- Custom SQL

### Capability 12: Backup & Restoration
Manage data persistence:
- Automated backup scheduling
- Point-in-time recovery
- Data validation
- Archive management
- Restore verification

---

## BLOCK 2: CONTROLLERS & ROUTING (10 Capabilities)

### Capability 13: Generate Controllers
Create resource controllers with:
- All CRUD methods (index, show, store, update, destroy)
- Nested resource methods
- Custom actions
- Request/response handling
- Service injection

**Example:**
```php
class WarehouseController extends Controller {
    public function index() {
        return WarehouseResource::collection(Warehouse::paginate());
    }

    public function store(StoreWarehouseRequest $request) {
        $warehouse = Warehouse::create($request->validated());
        return new WarehouseResource($warehouse);
    }
}
```

### Capability 14: Create RESTful Endpoints
Design proper REST APIs:
- GET endpoints (list with pagination, show single)
- POST endpoints (create with validation)
- PUT/PATCH endpoints (full/partial update)
- DELETE endpoints (destroy with cascade)
- Correct HTTP status codes (200, 201, 400, 404, 500)

### Capability 15: Implement Route Groups
Configure route organization:
- Middleware application
- Route prefixes
- Name prefixing
- Authorization checks
- Rate limiting

### Capability 16: Request Validation
Create validation rules:
- Form Request classes
- Custom validation rules
- Conditional rules
- Error messages (including Spanish)
- Nested validation

### Capability 17: Response Formatting
Format API responses:
- API Resources (single and collection)
- Consistent response structure
- Error responses with status codes
- Meta data (pagination, filtering)
- Resource nesting

### Capability 18: Authentication & Authorization
Implement security:
- Laravel Sanctum token auth
- Passport OAuth2
- Permission verification
- Gate definitions
- Policy authorization

### Capability 19: Pagination & Filtering
Manage data retrieval:
- Cursor pagination
- Limit/offset pagination
- Sort parameters
- Filter parameters
- Search functionality

### Capability 20: API Documentation
Document endpoints:
- OpenAPI/Swagger specs
- Endpoint descriptions
- Parameter definitions
- Example requests/responses
- Error codes

### Capability 21: Rate Limiting
Implement throttling:
- Per-user rate limits
- Per-IP rate limits
- Custom rate limits
- Throttle middleware
- Rate limit headers

### Capability 22: Error Handling
Manage error responses:
- Custom exception handlers
- Error logging
- Stack traces (in development)
- User-friendly messages
- Error tracking integration

---

## BLOCK 3: BUSINESS LOGIC & SERVICES (8 Capabilities)

### Capability 23: Create Service Classes
Build reusable logic:
- Service class generation
- Dependency injection
- Business logic encapsulation
- Method organization
- Unit testability

**Example:**
```php
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

### Capability 24: Implement Repositories
Abstract data access:
- Repository interface definition
- Query optimization
- Cache integration
- Consistent data access
- Easy switching between implementations

### Capability 25: Create DTOs
Validate and transfer data:
- DTO class generation
- Property validation
- Type safety
- Transformation logic
- Serialization/deserialization

### Capability 26: Event-Driven Architecture
Implement event patterns:
- Event class creation
- Event listener definition
- Broadcasting events
- Event queuing
- Real-time updates

### Capability 27: Job Creation
Manage background jobs:
- Queueable job classes
- Delayed execution
- Job retry logic
- Failed job handling
- Job chaining

### Capability 28: State Management
Implement state machines:
- State machine definition
- State transitions
- Validation rules
- Event triggering
- History tracking

### Capability 29: Cache Strategies
Design caching:
- Cache key generation
- TTL configuration
- Cache invalidation on updates
- Cache warming
- Cache busting

### Capability 30: Logging & Monitoring
Implement observability:
- Custom log channels
- Log levels (debug, info, warning, error, critical)
- Structured logging
- Performance metrics
- Error tracking

---

## BLOCK 4: REAL-TIME FEATURES (6 Capabilities)

### Capability 31: WebSocket Implementation
Set up real-time:
- Laravel Reverb configuration
- WebSocket server setup
- Connection handling
- Message broadcasting
- Disconnect handling

### Capability 32: Broadcasting Channels
Configure channels:
- Public channels (all users)
- Private channels (authenticated)
- Presence channels (with metadata)
- Channel authorization
- Multi-channel broadcasting

### Capability 33: Real-time Notifications
Implement notifications:
- Notification class creation
- Multiple channels (mail, slack, sms, database)
- Queued notifications
- Retry logic
- Custom templates

### Capability 34: Live Data Updates
Sync data in real-time:
- Model observable listeners
- Automatic event broadcasting
- Client-side listeners
- Data synchronization
- Conflict resolution

### Capability 35: Presence Tracking
Track user presence:
- User join/leave events
- Online status
- User metadata (avatar, name)
- Activity tracking
- Automatic cleanup

### Capability 36: Scheduled Broadcasting
Automate updates:
- Scheduled events
- Periodic data broadcasting
- Cleanup jobs
- Optimization routines
- Resource management

---

## BLOCK 5: DATA MANAGEMENT (5 Capabilities)

### Capability 37: Excel Import/Export
Handle spreadsheets:
- CSV file reading/writing
- Excel generation (xlsx)
- Data transformation during import
- Validation during import
- Large file streaming
- Progress tracking

### Capability 38: PDF Generation
Create documents:
- DomPDF integration
- HTML to PDF conversion
- Custom templates
- Asset handling (images, fonts)
- Multi-page documents
- Page headers/footers

### Capability 39: File Management
Manage uploads:
- Spatie MediaLibrary integration
- File upload handling
- Storage organization
- File validation
- Cleanup routines

### Capability 40: Data Export
Export data:
- Multiple format support (CSV, Excel, PDF)
- Large dataset handling
- Streaming responses
- Background processing
- Queue integration

### Capability 41: Data Import
Import data:
- File parsing (CSV, Excel)
- Data validation
- Error handling with line reporting
- Batch processing
- Duplicate detection

---

## IMPLEMENTATION PATTERNS

### Service + Repository Pattern
```php
// Service class
class WarehouseService {
    public function __construct(
        private WarehouseRepository $repository
    ) {}

    public function createWithProducts(array $data, array $products) {
        return DB::transaction(function () use ($data, $products) {
            $warehouse = $this->repository->create($data);
            $warehouse->products()->attach($products);
            return $warehouse;
        });
    }
}

// Repository class
class WarehouseRepository {
    public function __construct(private Warehouse $model) {}

    public function create(array $data): Warehouse {
        return Cache::forget('warehouses') ?
            $this->model->create($data) : $this->model->create($data);
    }
}
```

### API Resource Pattern
```php
class WarehouseResource extends JsonResource {
    public function toArray($request): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location,
            'capacity' => $this->capacity,
            'products' => ProductResource::collection($this->products),
            'created_at' => $this->created_at,
        ];
    }
}
```

### Event-Driven Pattern
```php
// In Service
event(new WarehouseCreated($warehouse));

// Event Listener
class SendWarehouseNotification implements ShouldQueue {
    public function handle(WarehouseCreated $event) {
        // Send notification
        Notification::send($users, new WarehouseNotification($event->warehouse));
    }
}

// Broadcasting
class WarehouseCreated implements ShouldBroadcast {
    public function broadcastOn() {
        return new Channel('warehouses');
    }
}
```

---

## TESTING STRATEGIES

### Unit Test Example
```php
class WarehouseServiceTest extends TestCase {
    public function test_warehouse_can_be_created() {
        $service = new WarehouseService(new WarehouseRepository(new Warehouse()));
        $warehouse = $service->createWarehouse(['name' => 'Test', 'capacity' => 1000]);

        $this->assertDatabaseHas('warehouses', ['name' => 'Test']);
        $this->assertEquals(1000, $warehouse->capacity);
    }
}
```

### Feature Test Example
```php
class WarehouseApiTest extends TestCase {
    public function test_can_create_warehouse() {
        $response = $this->postJson('/api/warehouses', [
            'name' => 'New Warehouse',
            'location' => 'Madrid',
            'capacity' => 5000
        ]);

        $response->assertCreated()
                 ->assertJsonPath('data.name', 'New Warehouse');
    }
}
```

---

## DOCUMENTATION GENERATION

The agent should also:
- ✅ Generate API documentation (Swagger/OpenAPI)
- ✅ Create README files for modules
- ✅ Document database schema
- ✅ Create architecture diagrams
- ✅ Generate migration guides

---

**Version:** 1.0
**Date:** November 30, 2025
**Status:** Complete
