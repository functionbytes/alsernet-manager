# Database Patterns - PostgreSQL Alsernet

**Patrones y mejores prácticas para diseño de base de datos en Alsernet.**

---

## 📋 Tabla de Contenidos

- [Principios de Diseño](#principios-de-diseño)
- [Patrones de Tablas](#patrones-de-tablas)
- [Índices y Performance](#índices-y-performance)
- [Relaciones y Constraints](#relaciones-y-constraints)
- [Campos Especiales](#campos-especiales)
- [Ejemplos Reales](#ejemplos-reales)

---

## Principios de Diseño

### 1. **UUIDs como Primary Key**
```php
Schema::create('inventaries', function (Blueprint $table) {
    $table->uuid('id')->primary();
    // En lugar de $table->id();
});
```

**Beneficios:**
- Importar/exportar datos fácilmente
- Distribuido en múltiples bases de datos
- No secuencial (seguridad)
- Compatible con Laravel Sanctum

---

### 2. **Soft Deletes para Auditoría**
```php
$table->softDeletes();
```

**Por qué:**
- Nunca pierdes datos
- Puedes "recuperar" registros
- Auditoría completa del ciclo de vida
- Los modelos respetan soft deletes automáticamente

---

### 3. **Timestamps Automáticos**
```php
$table->timestamps(); // created_at, updated_at
```

---

### 4. **Campos Requeridos vs Opcionales**
```php
$table->string('name');                    // Requerido
$table->string('phone')->nullable();       // Opcional
$table->string('email')->unique();         // Único
$table->decimal('price', 10, 2)->default(0); // Con default
```

---

## Patrones de Tablas

### Patrón 1: Modelo Principal (Products)

```php
Schema::create('inventaries', function (Blueprint $table) {
    $table->uuid('id')->primary();

    // Datos principales
    $table->string('name');
    $table->text('description')->nullable();
    $table->decimal('price', 10, 2);
    $table->decimal('cost', 10, 2)->default(0);

    // Estado
    $table->enum('status', ['active', 'inactive', 'draft'])->default('draft');
    $table->integer('stock')->default(0);

    // Relación con categoría
    $table->foreignUuid('category_id')
        ->constrained('categories')
        ->onDelete('restrict');

    // Auditoría
    $table->timestamps();
    $table->softDeletes();

    // Índices
    $table->index('category_id');
    $table->index('status');
    $table->fulltext('name', 'description'); // Búsqueda full-text
});
```

---

### Patrón 2: Tabla de Auditoría (LogActivity)

```php
Schema::create('activity_logs', function (Blueprint $table) {
    $table->uuid('id')->primary();

    // Usuario que realizó la acción
    $table->foreignUuid('user_id')
        ->constrained('users')
        ->onDelete('cascade');

    // Modelo afectado
    $table->string('loggable_type');        // Ej: "App\Models\Product"
    $table->uuid('loggable_id');             // ID del modelo

    // Acción
    $table->string('event');                 // created, updated, deleted
    $table->json('properties')->nullable();  // Datos del cambio
    $table->json('old_values')->nullable();  // Valores anteriores

    // Metadata
    $table->string('ip_address')->nullable();
    $table->string('user_agent')->nullable();
    $table->timestamps();

    // Índices para búsquedas rápidas
    $table->index(['loggable_type', 'loggable_id']);
    $table->index('user_id');
    $table->index('event');
    $table->index('created_at');
});
```

---

### Patrón 3: Tabla Pivote (has_many_through)

```php
Schema::create('order_items', function (Blueprint $table) {
    $table->uuid('id')->primary();

    // Relaciones
    $table->foreignUuid('order_id')
        ->constrained('orders')
        ->onDelete('cascade');

    $table->foreignUuid('product_id')
        ->constrained('inventaries')
        ->onDelete('restrict');

    // Datos de la compra (snapshot)
    $table->integer('quantity');
    $table->decimal('price_at_purchase', 10, 2); // Precio en el momento
    $table->decimal('discount', 10, 2)->default(0);

    // Estado del item
    $table->enum('status', ['pending', 'shipped', 'delivered'])->default('pending');

    $table->timestamps();
    $table->softDeletes();

    // Índices
    $table->unique(['order_id', 'product_id']);
    $table->index('status');
});
```

---

## Índices y Performance

### Tipos de Índices

```php
// Índice simple
$table->index('status');

// Índice único
$table->unique('email');

// Índice compuesto (para búsquedas comunes)
$table->index(['user_id', 'status', 'created_at']);

// Índice de texto completo (PostgreSQL)
$table->fulltext(['name', 'description']);

// Índice BRIN para series de tiempo
$table->rawIndex('created_at BRIN');
```

---

### Regla de Oro: Índices

**CREAR ÍNDICE cuando:**
- ✅ Columna está en `WHERE`
- ✅ Columna está en `JOIN`
- ✅ Columna está en `ORDER BY` frecuentemente
- ✅ Columna está en combinaciones comunes

**NO CREAR ÍNDICE para:**
- ❌ Columnas con pocos valores únicos (status, boolean)
- ❌ Columnas raramente consultadas
- ❌ Demasiados índices en tabla (máximo 5-7)

---

### Ejemplo: Búsqueda de Órdenes

```php
// MAL - Sin índices, buscar es lento
$orders = Order::where('user_id', $userId)
    ->where('status', 'completed')
    ->orderBy('created_at', 'desc')
    ->get();

// BIEN - Con índice compuesto
Schema::table('orders', function (Blueprint $table) {
    $table->index(['user_id', 'status', 'created_at']);
});
```

---

## Relaciones y Constraints

### Foreign Keys Correctas

```php
// ✅ CORRECTO: Restricción clara
$table->foreignUuid('category_id')
    ->constrained('categories')
    ->onDelete('restrict');      // No borrar si tiene items

// ✅ CORRECTO: Cascada controlada
$table->foreignUuid('order_id')
    ->constrained('orders')
    ->onDelete('cascade');       // Borrar items si se borra orden

// ❌ EVITAR: Sin constraints
$table->uuid('user_id');  // ¿Qué pasa si el usuario se borra?
```

---

### Restricciones de Negocio

```php
// Precio no puede ser negativo
$table->decimal('price', 10, 2)
    ->check('price > 0');

// Stock mínimo
$table->integer('stock')
    ->check('stock >= 0');

// Email válido
$table->string('email')
    ->unique()
    ->check("email ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Z|a-z]{2,}$'");

// Fecha válida
$table->date('birth_date')
    ->check('birth_date <= CURRENT_DATE');
```

---

## Campos Especiales

### JSON Storage para Datos Flexibles

```php
Schema::create('orders', function (Blueprint $table) {
    // Guardar dirección completa como JSON
    $table->json('shipping_address')->nullable();

    // Guardar preferencias del usuario
    $table->json('preferences')->default('{}');

    // Guardar historial de cambios
    $table->json('status_history')->default('[]');
});

// En el modelo:
protected $casts = [
    'shipping_address' => 'array',
    'preferences' => 'array',
    'status_history' => 'array',
];

// Acceso en código:
$order->shipping_address['street']; // Acceso directo
```

---

### Enums para Estados

```php
// Crear enum en PostgreSQL
Schema::create('orders', function (Blueprint $table) {
    $table->enum('status', [
        'pending',
        'paid',
        'processing',
        'shipped',
        'delivered',
        'cancelled'
    ])->default('pending');
});

// En el modelo PHP:
enum OrderStatus: string {
    case PENDING = 'pending';
    case PAID = 'paid';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
}

protected $casts = [
    'status' => OrderStatus::class,
];
```

---

### Campos Computed (Generados)

```php
// Calcular total automáticamente
$table->decimal('price', 10, 2);
$table->integer('quantity');
$table->computed('total')->storedAs('price * quantity');

// O mejor, calcular en el modelo:
class OrderItem extends Model {
    public function getTotalAttribute() {
        return $this->price * $this->quantity;
    }
}
```

---

## Ejemplos Reales

### Ejemplo 1: Sistema de Productos y Categorías

```php
// Tabla de categorías
Schema::create('categories', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('name')->unique();
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->foreignUuid('parent_category_id')
        ->nullable()
        ->constrained('categories')
        ->onDelete('restrict');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();

    $table->index('parent_category_id');
    $table->index('is_active');
});

// Tabla de productos
Schema::create('inventaries', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('sku')->unique();
    $table->string('name');
    $table->text('description')->nullable();

    $table->decimal('price', 10, 2);
    $table->decimal('cost', 10, 2)->default(0);
    $table->integer('stock')->default(0);
    $table->integer('reorder_level')->default(10);

    $table->foreignUuid('category_id')
        ->constrained('categories')
        ->onDelete('restrict');

    $table->enum('status', ['active', 'inactive', 'discontinued'])
        ->default('active');

    $table->json('attributes')->nullable(); // Color, tamaño, etc
    $table->timestamps();
    $table->softDeletes();

    // Índices
    $table->unique('sku');
    $table->index('category_id');
    $table->index('status');
    $table->index('stock');
    $table->fulltext(['name', 'description']);
});
```

---

### Ejemplo 2: Sistema de Órdenes con Auditoría

```php
Schema::create('orders', function (Blueprint $table) {
    $table->uuid('id')->primary();

    // Relaciones
    $table->foreignUuid('user_id')
        ->constrained('users')
        ->onDelete('cascade');

    // Datos de la orden
    $table->decimal('subtotal', 10, 2);
    $table->decimal('tax', 10, 2)->default(0);
    $table->decimal('shipping', 10, 2)->default(0);
    $table->decimal('total', 10, 2)
        ->storedAs('subtotal + tax + shipping');

    // Direcciones guardadas como JSON
    $table->json('shipping_address');
    $table->json('billing_address')->nullable();

    // Estado y historial
    $table->enum('status', [
        'pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled'
    ])->default('pending');

    $table->json('status_history')->default('[]');
    $table->dateTime('shipped_at')->nullable();
    $table->dateTime('delivered_at')->nullable();

    // Notas internas
    $table->text('notes')->nullable();

    $table->timestamps();
    $table->softDeletes();

    // Índices para búsquedas comunes
    $table->index('user_id');
    $table->index('status');
    $table->index(['user_id', 'status', 'created_at']);
    $table->index('created_at');
});
```

---

## Checklist de Diseño

```
ANTES DE CREAR UNA TABLA:

□ ¿Necesitas UUID o ID secuencial?
□ ¿Necesitas soft deletes (auditoría)?
□ ¿Qué campos son requeridos vs opcionales?
□ ¿Hay restricciones de negocio (checks)?
□ ¿Necesitas tracking de cambios?
□ ¿Qué búsquedas son comunes? (índices)
□ ¿Qué relaciones con otras tablas?
□ ¿Foreign keys con cascada o restrict?
□ ¿JSON para datos flexibles?
□ ¿Full-text search? (índice fulltext)
```

---

**Última actualización:** Noviembre 30, 2025
