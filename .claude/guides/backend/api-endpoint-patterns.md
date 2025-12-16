# Guide: API Endpoint Patterns

**Standard patterns for all Alsernet API endpoints.**

---

## REST Endpoints Structure

### Resource Endpoints
```
GET    /api/warehouses              # List all
POST   /api/warehouses              # Create one
GET    /api/warehouses/{id}         # Show one
PUT    /api/warehouses/{id}         # Update one
DELETE /api/warehouses/{id}         # Delete one
```

### Nested Resources
```
GET    /api/warehouses/{id}/products           # List warehouse products
POST   /api/warehouses/{id}/products           # Add product to warehouse
GET    /api/warehouses/{id}/products/{pid}     # Get specific product
PUT    /api/warehouses/{id}/products/{pid}     # Update product in warehouse
DELETE /api/warehouses/{id}/products/{pid}     # Remove product from warehouse
```

### Custom Actions
```
POST   /api/warehouses/{id}/activate           # Custom action
POST   /api/warehouses/bulk-import              # Bulk operation
GET    /api/warehouses/search                   # Search endpoint
```

---

## Request Format

### JSON POST/PUT
```json
{
  "name": "Main Warehouse",
  "location": "Madrid",
  "capacity": 5000,
  "active": true
}
```

### Query Parameters
```
GET /api/warehouses?page=1&per_page=15&sort=name&filter[active]=true&search=Madrid
```

Parameters:
- `page` - Page number for pagination
- `per_page` - Items per page (max 100)
- `sort` - Sort field (prefix with - for desc: -created_at)
- `filter[field]` - Filter by field value
- `search` - Search query across searchable fields
- `include` - Include relationships

---

## Response Format

### Success Response (200 OK)
```json
{
  "data": {
    "id": 1,
    "name": "Main Warehouse",
    "location": "Madrid",
    "capacity": 5000,
    "active": true,
    "created_at": "2025-11-30T10:00:00Z",
    "updated_at": "2025-11-30T10:00:00Z"
  }
}
```

### Collection Response (200 OK)
```json
{
  "data": [
    {
      "id": 1,
      "name": "Main Warehouse",
      "location": "Madrid",
      "capacity": 5000
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7,
    "from": 1,
    "to": 15
  },
  "links": {
    "first": "http://api.localhost/api/warehouses?page=1",
    "last": "http://api.localhost/api/warehouses?page=7",
    "prev": null,
    "next": "http://api.localhost/api/warehouses?page=2"
  }
}
```

### Create Response (201 Created)
```json
{
  "data": {
    "id": 5,
    "name": "New Warehouse",
    "location": "Barcelona",
    "capacity": 3000,
    "active": true,
    "created_at": "2025-11-30T11:00:00Z"
  }
}
```

### Error Response (400/422/500)
```json
{
  "message": "The given data was invalid",
  "errors": {
    "name": ["The name field is required"],
    "capacity": ["The capacity must be at least 1"]
  }
}
```

### Validation Error (422)
```json
{
  "message": "The given data was invalid",
  "errors": {
    "email": ["The email has already been taken"],
    "password": ["The password confirmation does not match"]
  }
}
```

### Not Found (404)
```json
{
  "message": "No query results for model [App\\Models\\Warehouse] 5"
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated"
}
```

### Forbidden (403)
```json
{
  "message": "This action is unauthorized"
}
```

---

## HTTP Status Codes

| Code | Meaning | Usage |
|------|---------|-------|
| **200** | OK | GET, PUT successful |
| **201** | Created | POST successful |
| **204** | No Content | DELETE successful |
| **400** | Bad Request | Invalid request format |
| **401** | Unauthorized | Not authenticated |
| **403** | Forbidden | Not authorized for action |
| **404** | Not Found | Resource doesn't exist |
| **422** | Unprocessable Entity | Validation failed |
| **429** | Too Many Requests | Rate limited |
| **500** | Server Error | Unexpected server error |

---

## Authentication

### Bearer Token
```bash
curl -H "Authorization: Bearer {token}" \
     http://api.localhost/api/warehouses
```

### Sanctum Token Example
```php
// Generate token
$token = $user->createToken('api-token')->plainTextToken;

// Response
{
  "token": "3|AbCdEfGhIjKlMnOpQrStUvWxYz"
}
```

---

## Rate Limiting

### Headers in Response
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1638451200
```

### Rate Limited Response (429)
```json
{
  "message": "Too Many Requests"
}
```

---

## Filtering Examples

### By Field Value
```
GET /api/warehouses?filter[active]=true
GET /api/warehouses?filter[location]=Madrid
```

### By Range
```
GET /api/warehouses?filter[capacity][min]=1000&filter[capacity][max]=5000
```

### By Date
```
GET /api/warehouses?filter[created_at][from]=2025-01-01&filter[created_at][to]=2025-12-31
```

---

## Sorting Examples

### Ascending
```
GET /api/warehouses?sort=name
GET /api/warehouses?sort=created_at
```

### Descending
```
GET /api/warehouses?sort=-name
GET /api/warehouses?sort=-created_at
```

### Multiple Fields
```
GET /api/warehouses?sort=-active,name
```

---

## Pagination Examples

### Limit/Offset
```
GET /api/warehouses?page=1&per_page=15
```

### Cursor Pagination
```
GET /api/warehouses?cursor=eyJpZCI6IDE1LCAiX3BvaW50c1RvIjogIm5leHQifQ==
```

---

## Include Relations

### Single Relation
```
GET /api/warehouses?include=products
```

### Multiple Relations
```
GET /api/warehouses?include=products,staff
```

### Nested Relations
```
GET /api/warehouses?include=products.reviews
```

---

## Search Examples

### Text Search
```
GET /api/warehouses/search?q=Madrid
```

### Advanced Search
```
GET /api/warehouses/search?q=Madrid&fields=name,location
```

---

## Batch Operations

### Bulk Create
```bash
POST /api/warehouses/bulk
{
  "data": [
    {"name": "Warehouse 1", "location": "Madrid"},
    {"name": "Warehouse 2", "location": "Barcelona"}
  ]
}
```

### Bulk Update
```bash
PATCH /api/warehouses/bulk
{
  "data": [
    {"id": 1, "active": false},
    {"id": 2, "active": true}
  ]
}
```

### Bulk Delete
```bash
DELETE /api/warehouses/bulk
{
  "ids": [1, 2, 3, 4, 5]
}
```

---

## Implementation Example

### Controller
```php
class WarehouseController extends Controller {
    public function index(Request $request) {
        $query = Warehouse::query();

        // Filter
        if ($request->filled('filter')) {
            foreach ($request->input('filter') as $field => $value) {
                $query->where($field, $value);
            }
        }

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
        }

        // Sort
        if ($request->filled('sort')) {
            $sort = $request->input('sort');
            $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
            $field = ltrim($sort, '-');
            $query->orderBy($field, $direction);
        }

        // Include relations
        if ($request->filled('include')) {
            $query->with(explode(',', $request->input('include')));
        }

        // Paginate
        $warehouses = $query->paginate($request->input('per_page', 15));

        return WarehouseResource::collection($warehouses);
    }
}
```

---

## API Version Management

### Versioned Routes
```php
Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('warehouses', WarehouseController::class);
});
```

### Version Header
```bash
curl -H "X-API-Version: v1" \
     -H "Authorization: Bearer {token}" \
     http://api.localhost/api/warehouses
```

---

## Error Handling Best Practices

1. **Always include message and errors in responses**
2. **Use correct HTTP status codes**
3. **Log errors for debugging**
4. **Never expose sensitive information**
5. **Provide actionable error messages**

---

## Testing Endpoints

### Using Artisan Tinker
```bash
php artisan tinker

# Get warehouses
App\Models\Warehouse::all();

# Create warehouse
App\Models\Warehouse::create(['name' => 'Test', 'location' => 'Madrid', 'capacity' => 1000]);
```

### Using cURL
```bash
# List
curl -H "Authorization: Bearer {token}" \
     http://api.localhost/api/warehouses

# Show
curl -H "Authorization: Bearer {token}" \
     http://api.localhost/api/warehouses/1

# Create
curl -X POST -H "Authorization: Bearer {token}" \
     -H "Content-Type: application/json" \
     -d '{"name":"New","location":"Madrid","capacity":5000}' \
     http://api.localhost/api/warehouses

# Update
curl -X PUT -H "Authorization: Bearer {token}" \
     -H "Content-Type: application/json" \
     -d '{"name":"Updated"}' \
     http://api.localhost/api/warehouses/1

# Delete
curl -X DELETE -H "Authorization: Bearer {token}" \
     http://api.localhost/api/warehouses/1
```

---

**All API endpoints must follow these patterns for consistency.**
