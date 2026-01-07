# Warehouse Module - Permisos

## Descripción

El módulo Warehouse utiliza **Spatie Permission** para controlar el acceso. Es un sistema **simple y limpio**:

- **Un solo permiso principal**: `warehouse.access`
- Si un usuario tiene este permiso, puede acceder a **todo** el módulo
- Los super-admin siempre tienen acceso automáticamente

## Instalación

### 1. Ejecutar el Seeder

```bash
php artisan db:seed --class="Modules\\Warehouse\\Database\\Seeders\\WarehousePermissionsSeeder"
```

Esto crea el permiso `warehouse.access` en la base de datos.

## Uso

### Crear un Rol con Permiso de Warehouse

```php
use Spatie\Permission\Models\Role;

// Crear rol
$warehouseOperator = Role::firstOrCreate([
    'name' => 'warehouse-operator',
    'guard_name' => 'web'
]);

// Asignar permiso
$warehouseOperator->givePermissionTo('warehouse.access');
```

### Asignar Rol a Usuario

```php
use App\Models\User;

$user = User::find(1);
$user->assignRole('warehouse-operator');
```

### Verificar Permisos en Código

```php
// En un controlador
if (auth()->user()->hasPermissionTo('warehouse.access')) {
    // Usuario tiene acceso al módulo
}

// O usar el helper
if (auth()->user()->can('warehouse.access')) {
    // Usuario tiene acceso
}

// Super-admin siempre tiene acceso
if (auth()->user()->hasRole('super-admin')) {
    // Acceso automático
}
```

### En Vistas Blade

```blade
@can('warehouse.access')
    <!-- Mostrar módulo de warehouse -->
@endcan
```

### En Rutas (middleware)

El middleware `CheckWarehouseAccess` ya está registrado en las rutas:

```php
// En routes/web.php (ya configurado en WarehouseServiceProvider)
Route::middleware(['web', 'auth', 'warehouse.access'])
    ->prefix('warehouse')
    ->group(function () {
        // Rutas del módulo
    });
```

## Estructura de Permisos

| Permiso | Descripción | Guard |
|---------|-------------|-------|
| `warehouse.access` | Acceso completo al módulo de almacenes | web |

## Cómo Funciona

1. **Usuario accede al módulo** → El middleware `CheckWarehouseAccess` verifica si tiene `warehouse.access`
2. **Si tiene el permiso** → Acceso completo a todas las rutas
3. **Si NO tiene el permiso** → Error 403 (Forbidden)
4. **Si es super-admin** → Acceso automático

## Ejemplo Completo

### Crear un grupo de operadores y asignarlo a usuarios

```php
use Spatie\Permission\Models\Role, Permission;
use App\Models\User;

// 1. Crear permiso (si no existe)
$permission = Permission::firstOrCreate([
    'name' => 'warehouse.access',
    'guard_name' => 'web'
]);

// 2. Crear rol
$role = Role::firstOrCreate([
    'name' => 'warehouse-operator',
    'guard_name' => 'web'
]);

// 3. Asignar permiso al rol
$role->givePermissionTo($permission);

// 4. Asignar rol a usuario
$user = User::find(1);
$user->assignRole($role);

// 5. Verificar
echo $user->hasPermissionTo('warehouse.access'); // true
```

## Limpiar Permisos en Cache

Si cambias permisos en la BD, necesitas limpiar el cache:

```bash
php artisan cache:clear
php artisan permission:cache-reset
```

## Notas

- El sistema es **flexible**: puedes agregar más permisos en el futuro si necesitas granularidad
- No modifica el funcionamiento de Spatie Permission
- Compatible con la estructura actual de usuarios y roles
- El super-admin siempre tiene acceso a todo
