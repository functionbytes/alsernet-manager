# Guide: Creating a New Backend Module

**Step-by-step guide to create a complete backend module in Alsernet.**

---

## Overview

A complete module includes:
- ✅ Model with relationships
- ✅ Database migration
- ✅ Controller with CRUD
- ✅ Form Request validation
- ✅ API Resource
- ✅ Service class
- ✅ Repository class
- ✅ Routes definition
- ✅ Tests
- ✅ Documentation

---

## STEP 1: Create the Model

### Command
```bash
php artisan make:model Models/Warehouse -m
```

### Model File
**Location:** `app/Models/Warehouse.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'location',
        'capacity',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'capacity' => 'integer',
    ];

    // Relationships
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function staff()
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivot('role');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', $location);
    }
}
```

---

## STEP 2: Create the Migration

### File Generated
**Location:** `database/migrations/XXXX_XX_XX_XXXXXX_create_warehouses_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('location');
            $table->unsignedInteger('capacity');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('active');
            $table->index('location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
```

### Run Migration
```bash
php artisan migrate
```

---

## STEP 3: Create Form Request Validation

### Command
```bash
php artisan make:request StoreWarehouseRequest
php artisan make:request UpdateWarehouseRequest
```

### Store Request
**Location:** `app/Http/Requests/StoreWarehouseRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Warehouse::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:warehouses,name',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1|max:1000000',
            'active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Warehouse name is required',
            'name.unique' => 'This warehouse name already exists',
            'capacity.min' => 'Capacity must be at least 1',
        ];
    }
}
```

### Update Request
**Location:** `app/Http/Requests/UpdateWarehouseRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->warehouse);
    }

    public function rules(): array
    {
        return [
            'name' => 'string|max:255|unique:warehouses,name,' . $this->warehouse->id,
            'location' => 'string|max:255',
            'capacity' => 'integer|min:1|max:1000000',
            'active' => 'boolean',
        ];
    }
}
```

---

## STEP 4: Create API Resource

### Command
```bash
php artisan make:resource WarehouseResource
```

### Resource File
**Location:** `app/Http/Resources/WarehouseResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location,
            'capacity' => $this->capacity,
            'active' => $this->active,
            'products_count' => $this->products_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

---

## STEP 5: Create Repository

### File
**Location:** `app/Repositories/WarehouseRepository.php`

```php
<?php

namespace App\Repositories;

use App\Models\Warehouse;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class WarehouseRepository
{
    public function __construct(private Warehouse $model)
    {
    }

    public function getAll(int $perPage = 15): Paginator
    {
        return Cache::remember(
            'warehouses_list_page',
            3600,
            fn() => $this->model->paginate($perPage)
        );
    }

    public function getActive(): Collection
    {
        return Cache::remember(
            'active_warehouses',
            3600,
            fn() => $this->model->active()->get()
        );
    }

    public function findById(int $id): ?Warehouse
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Warehouse
    {
        Cache::forget('warehouses_list_page');
        Cache::forget('active_warehouses');

        return $this->model->create($data);
    }

    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        $warehouse->update($data);

        Cache::forget('warehouses_list_page');
        Cache::forget('active_warehouses');

        return $warehouse;
    }

    public function delete(Warehouse $warehouse): bool
    {
        Cache::forget('warehouses_list_page');
        Cache::forget('active_warehouses');

        return $warehouse->delete();
    }
}
```

---

## STEP 6: Create Service Class

### File
**Location:** `app/Services/WarehouseService.php`

```php
<?php

namespace App\Services;

use App\Events\WarehouseCreated;
use App\Events\WarehouseUpdated;
use App\Models\Warehouse;
use App\Repositories\WarehouseRepository;
use Spatie\ActivityLog\Facades\LogActivity;

class WarehouseService
{
    public function __construct(private WarehouseRepository $repository)
    {
    }

    public function createWarehouse(array $data): Warehouse
    {
        $warehouse = $this->repository->create($data);

        // Log activity
        LogActivity::causedBy(auth()->user())
            ->performedOn($warehouse)
            ->log('created');

        // Broadcast event
        broadcast(new WarehouseCreated($warehouse))->toOthers();

        return $warehouse;
    }

    public function updateWarehouse(Warehouse $warehouse, array $data): Warehouse
    {
        $warehouse = $this->repository->update($warehouse, $data);

        // Log activity
        LogActivity::causedBy(auth()->user())
            ->performedOn($warehouse)
            ->log('updated');

        // Broadcast event
        broadcast(new WarehouseUpdated($warehouse))->toOthers();

        return $warehouse;
    }

    public function deleteWarehouse(Warehouse $warehouse): bool
    {
        LogActivity::causedBy(auth()->user())
            ->performedOn($warehouse)
            ->log('deleted');

        return $this->repository->delete($warehouse);
    }
}
```

---

## STEP 7: Create Controller

### Command
```bash
php artisan make:controller Api/WarehouseController --api
```

### Controller File
**Location:** `app/Http/Controllers/Api/WarehouseController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class WarehouseController extends Controller
{
    public function __construct(private WarehouseService $service)
    {
        $this->authorizeResource(Warehouse::class);
    }

    public function index(): AnonymousResourceCollection
    {
        $warehouses = Warehouse::paginate(15);
        return WarehouseResource::collection($warehouses);
    }

    public function store(StoreWarehouseRequest $request): WarehouseResource
    {
        $warehouse = $this->service->createWarehouse($request->validated());
        return new WarehouseResource($warehouse);
    }

    public function show(Warehouse $warehouse): WarehouseResource
    {
        return new WarehouseResource($warehouse);
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): WarehouseResource
    {
        $warehouse = $this->service->updateWarehouse($warehouse, $request->validated());
        return new WarehouseResource($warehouse);
    }

    public function destroy(Warehouse $warehouse): Response
    {
        $this->service->deleteWarehouse($warehouse);
        return response()->noContent();
    }
}
```

---

## STEP 8: Create Routes

### File
**Location:** `routes/api.php`

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('warehouses', WarehouseController::class);

    // Nested resources
    Route::get('warehouses/{warehouse}/products', [WarehouseController::class, 'products']);
});
```

---

## STEP 9: Create Tests

### Unit Test
**Location:** `tests/Unit/WarehouseServiceTest.php`

```php
<?php

namespace Tests\Unit;

use App\Models\Warehouse;
use App\Services\WarehouseService;
use Tests\TestCase;

class WarehouseServiceTest extends TestCase
{
    public function test_can_create_warehouse()
    {
        $service = $this->app->make(WarehouseService::class);

        $warehouse = $service->createWarehouse([
            'name' => 'Test Warehouse',
            'location' => 'Madrid',
            'capacity' => 5000,
        ]);

        $this->assertDatabaseHas('warehouses', ['name' => 'Test Warehouse']);
        $this->assertEquals(5000, $warehouse->capacity);
    }
}
```

### Feature Test
**Location:** `tests/Feature/WarehouseApiTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use Tests\TestCase;

class WarehouseApiTest extends TestCase
{
    public function test_can_list_warehouses()
    {
        Warehouse::factory()->count(3)->create();

        $response = $this->actingAs(User::factory()->create())
            ->getJson('/api/warehouses');

        $response->assertOk()
                 ->assertJsonCount(3, 'data');
    }

    public function test_can_create_warehouse()
    {
        $response = $this->actingAs(User::factory()->create())
            ->postJson('/api/warehouses', [
                'name' => 'New Warehouse',
                'location' => 'Barcelona',
                'capacity' => 3000,
            ]);

        $response->assertCreated()
                 ->assertJsonPath('data.name', 'New Warehouse');
    }
}
```

---

## STEP 10: Create Events (Optional - for Real-time)

### Command
```bash
php artisan make:event WarehouseCreated
```

### Event File
**Location:** `app/Events/WarehouseCreated.php`

```php
<?php

namespace App\Events;

use App\Models\Warehouse;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WarehouseCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Warehouse $warehouse)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('warehouses');
    }

    public function broadcastAs(): string
    {
        return 'warehouse.created';
    }
}
```

---

## STEP 11: Run All Tests

```bash
php artisan test
```

---

## STEP 12: Create Documentation

Create API documentation in Swagger/OpenAPI format

---

## Checklist

- [ ] Model created with relationships
- [ ] Migration created and ran
- [ ] Validation Requests created
- [ ] API Resource created
- [ ] Repository created
- [ ] Service class created
- [ ] Controller created
- [ ] Routes defined
- [ ] Unit tests pass
- [ ] Feature tests pass
- [ ] Documentation created
- [ ] Events created (if needed)

---

**That's it! Your module is complete and ready to use.**
