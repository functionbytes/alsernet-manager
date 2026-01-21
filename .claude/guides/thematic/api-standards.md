# API Standards - REST API Alsernet

**Estándares y patrones para endpoints REST en Alsernet.**

---

## 📋 Tabla de Contenidos

- [Estructura de Endpoints](#estructura-de-endpoints)
- [Requests y Responses](#requests-y-responses)
- [Errores y Status Codes](#errores-y-status-codes)
- [Autenticación](#autenticación)
- [Paginación](#paginación)
- [Validación](#validación)
- [Ejemplos Reales](#ejemplos-reales)

---

## Estructura de Endpoints

### Convención de Rutas

```
/api/v1/resource              # Recurso principal
/api/v1/resource/{id}         # Recurso específico
/api/v1/resource/{id}/nested  # Recurso anidado
/api/v1/resource/search       # Búsqueda
/api/v1/resource/{id}/action  # Acción específica
```

### Métodos HTTP Estándar

```
GET    /api/v1/products          → Listar
GET    /api/v1/products/123      → Obtener uno
POST   /api/v1/products          → Crear
PUT    /api/v1/products/123      → Actualizar completo
PATCH  /api/v1/products/123      → Actualizar parcial
DELETE /api/v1/products/123      → Eliminar
```

---

## Requests y Responses

### Response Success (200)

```json
{
  "success": true,
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "Product Name",
    "price": 99.99,
    "created_at": "2025-11-30T10:00:00Z"
  }
}
```

### Response List (200)

```json
{
  "success": true,
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "name": "Product 1",
      "price": 99.99
    }
  ],
  "meta": {
    "total": 100,
    "per_page": 15,
    "current_page": 1,
    "last_page": 7,
    "from": 1,
    "to": 15
  }
}
```

### Response Create (201)

```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "New Product",
    "created_at": "2025-11-30T10:00:00Z"
  }
}
```

---

## Errores y Status Codes

### Status Codes Standard

| Code | Significado | Ejemplo |
|------|-------------|---------|
| **200** | OK | GET exitoso, datos retornados |
| **201** | Created | POST exitoso, recurso creado |
| **204** | No Content | DELETE exitoso |
| **400** | Bad Request | Validación fallida |
| **401** | Unauthorized | Token no válido/expirado |
| **403** | Forbidden | No tienes permiso |
| **404** | Not Found | Recurso no existe |
| **422** | Unprocessable Entity | Validación de datos |
| **429** | Too Many Requests | Rate limit excedido |
| **500** | Internal Server Error | Error del servidor |

---

### Response Error (400/422)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required"],
    "email": ["The email must be a valid email address"]
  },
  "status_code": 422
}
```

### Response Error (401/403)

```json
{
  "success": false,
  "message": "Unauthorized",
  "status_code": 401
}
```

### Response Error (404)

```json
{
  "success": false,
  "message": "Product not found",
  "status_code": 404
}
```

---

## Autenticación

### Headers Requeridos

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Obtener Token

```
POST /api/v1/auth/login
{
  "email": "user@example.com",
  "password": "password"
}

Response:
{
  "success": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "John Doe",
    "email": "user@example.com"
  }
}
```

---

### Proteger Endpoints

```php
// Route
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/api/v1/inventaries', [ProductController::class, 'index']);
    Route::post('/api/v1/inventaries', [ProductController::class, 'store']);
});

// En el controlador
class ProductController extends Controller {
    public function index(Request $request) {
        $user = $request->user(); // Usuario autenticado
        return Product::where('user_id', $user->id)->get();
    }
}
```

---

## Paginación

### Parámetros

```
GET /api/v1/products?page=2&per_page=20&sort=-created_at&filter[status]=active
```

### En el Controlador

```php
public function index(Request $request) {
    $page = $request->get('page', 1);
    $per_page = $request->get('per_page', 15);
    $sort = $request->get('sort', '-created_at');

    $query = Product::query();

    // Filtrar
    if ($request->has('filter.status')) {
        $query->where('status', $request->input('filter.status'));
    }

    // Ordenar
    if ($sort[0] === '-') {
        $query->orderByDesc(substr($sort, 1));
    } else {
        $query->orderBy($sort);
    }

    $products = $query->paginate($per_page, ['*'], 'page', $page);

    return response()->json([
        'success' => true,
        'data' => $products->items(),
        'meta' => [
            'total' => $products->total(),
            'per_page' => $products->perPage(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
        ]
    ]);
}
```

---

## Validación

### FormRequest

```php
// app/Http/Requests/StoreProductRequest.php
class StoreProductRequest extends FormRequest {
    public function authorize() {
        return $this->user()->can('create', Product::class);
    }

    public function rules() {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'cost' => 'required|numeric|min:0',
            'category_id' => 'required|uuid|exists:categories,id',
            'stock' => 'required|integer|min:0',
        ];
    }

    public function messages() {
        return [
            'name.required' => 'El nombre del producto es requerido',
            'price.min' => 'El precio debe ser mayor a 0',
            'category_id.exists' => 'La categoría seleccionada no existe',
        ];
    }
}

// En el Controlador
class ProductController extends Controller {
    public function store(StoreProductRequest $request) {
        $validated = $request->validated(); // Ya validado

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Producto creado exitosamente',
            'data' => $product
        ], 201);
    }
}
```

---

## Ejemplos Reales

### Ejemplo 1: CRUD de Productos

```php
// app/Http/Controllers/Api/ProductController.php
class ProductController extends Controller {
    // Listar
    public function index(Request $request) {
        $query = Product::query();

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        // Paginación
        $products = $query->paginate(
            $request->get('per_page', 15)
        );

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
            ]
        ]);
    }

    // Obtener uno
    public function show(Product $product) {
        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    // Crear
    public function store(StoreProductRequest $request) {
        $product = Product::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Producto creado',
            'data' => $product
        ], 201);
    }

    // Actualizar
    public function update(UpdateProductRequest $request, Product $product) {
        $product->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado',
            'data' => $product
        ]);
    }

    // Eliminar
    public function destroy(Product $product) {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado'
        ]);
    }
}

// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('inventaries', ProductController::class);
});
```

---

### Ejemplo 2: Acción Específica (Actualizar Stock)

```php
// Ruta
Route::post('/api/v1/inventaries/{product}/adjust-stock',
    [ProductController::class, 'adjustStock']
)->middleware('auth:sanctum');

// Controlador
class ProductController extends Controller {
    public function adjustStock(Request $request, Product $product) {
        $request->validate([
            'quantity' => 'required|integer',
            'reason' => 'required|string|in:sale,return,adjustment,inventory',
        ]);

        $oldStock = $product->stock;
        $product->increment('stock', $request->quantity);

        // Log de cambio
        activity()
            ->performedOn($product)
            ->withProperties([
                'old_stock' => $oldStock,
                'new_stock' => $product->stock,
                'reason' => $request->reason,
            ])
            ->log('stock_adjusted');

        return response()->json([
            'success' => true,
            'message' => 'Stock actualizado',
            'data' => [
                'old_stock' => $oldStock,
                'new_stock' => $product->stock,
                'change' => $request->quantity,
            ]
        ]);
    }
}
```

---

### Ejemplo 3: Búsqueda Avanzada

```php
// GET /api/v1/inventaries/search?q=laptop&category=electronics&sort=-price&page=1

class ProductController extends Controller {
    public function search(Request $request) {
        $query = Product::query();

        // Búsqueda full-text
        if ($request->has('q')) {
            $search = $request->q;
            $query->whereRaw("
                to_tsvector('spanish', name || ' ' || description)
                @@ plainto_tsquery('spanish', ?)
            ", [$search]);
        }

        // Filtros
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Ordenar
        $sort = $request->get('sort', '-created_at');
        if (substr($sort, 0, 1) === '-') {
            $query->orderByDesc(substr($sort, 1));
        } else {
            $query->orderBy($sort);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(15)->items(),
        ]);
    }
}
```

---

## Checklist de API

```
ANTES DE PUBLICAR UN ENDPOINT:

□ ¿Está autenticado (auth:sanctum)?
□ ¿Tiene autorización (can/policy)?
□ ¿Valida todas las entradas?
□ ¿Retorna status codes correctos?
□ ¿Respuesta JSON estructurada?
□ ¿Manejo de errores completo?
□ ¿Logging de acciones importantes?
□ ¿Rate limiting si es necesario?
□ ¿Documentado con ejemplos?
□ ¿Testeado (unit + integration)?
```

---

**Última actualización:** Noviembre 30, 2025
