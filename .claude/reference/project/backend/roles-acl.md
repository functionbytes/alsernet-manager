# 🔐 Implementación de Sistema de Roles y Privilegios ACL

## Resumen Ejecutivo

Se ha implementado un sistema robusto de **Control de Acceso Basado en Roles (RBAC - Role-Based Access Control)** siguiendo las mejores prácticas del proyecto **Mercosan**. El sistema permite una gestión centralizada y granular de permisos a través de roles.

---

## 📋 Cambios Implementados

### 1. **Modelo Role Mejorado** (`app/Models/Role.php`)

#### Nuevos Atributos:
- `description`: Descripción del rol (255 caracteres)
- `slug`: Identificador único del rol (generado automáticamente)
- `is_default`: Indica si es el rol por defecto para nuevos usuarios
- `created_by`: ID del usuario que creó el rol (auditoría)
- `updated_by`: ID del usuario que actualizó el rol (auditoría)

#### Nuevas Relaciones:
```php
public function creator(): BelongsTo      // Usuario que creó el rol
public function updater(): BelongsTo      // Usuario que actualizó el rol
public function users(): BelongsToMany    // Usuarios asignados al rol
```

#### Nuevos Métodos y Scopes:
```php
scopeDefault()                   // Obtener solo roles por defecto
scopeLatest()                    // Ordenar por fecha de creación
getUsersCount(): int             // Contar usuarios del rol
```

---

### 2. **BaseManagerController Mejorado** (`app/Http/Controllers/Managers/BaseManagerController.php`)

#### Métodos de Respuesta Centralizada:
```php
success(string $message, array $data = []): JsonResponse
error(string $message, array $data = [], int $status = 400): JsonResponse
getPaginationPerPage(): int
getAvailablePermissions(): array
```

#### Permisos Disponibles (Agrupados):
- **Roles**: view, create, edit, delete, assign.permissions, assign.users
- **Users**: view, create, edit, delete, export
- **Tickets**: view, create, edit, delete, assign, close
- **Reports**: view, export, create
- **Settings**: view, edit, system
- **Warehouse**: view, create, edit, delete
- **Returns**: view, create, edit, delete, approve

---

### 3. **Abilities Expandido** (`app/Permissions/V1/Abilities.php`)

#### Nuevas Constantes de Permisos:
- **Tickets**: CreateTicket, UpdateTicket, DeleteTicket, AssignTicket, CloseTicket, etc.
- **Users**: ViewUsers, CreateUser, UpdateUser, DeleteUser, ExportUsers
- **Roles**: ViewRoles, CreateRoles, UpdateRoles, DeleteRoles, AssignPermissions, AssignUsers
- **Warehouse**: ViewWarehouse, CreateWarehouse, UpdateWarehouse, DeleteWarehouse
- **Returns**: ViewReturns, CreateReturns, UpdateReturns, DeleteReturns, ApproveReturns
- **Reports**: ViewReports, ExportReports, CreateReports
- **Settings**: ViewSettings, EditSettings, SystemSettings

#### Nuevos Métodos:
```php
getAllAbilitiesGrouped(): array        // Obtener todos los permisos agrupados
getAbilities(User $user): array        // Permisos según rol del usuario
getAllAdminAbilities(): array          // Todos los permisos de administrador
getManagerAbilities(): array           // Permisos de manager
getBasicUserAbilities(): array         // Permisos de usuario básico
```

---

### 4. **RoleRequest Validaciones** (`app/Http/Requests/RoleRequest.php`)

Validaciones centralizadas para crear/actualizar roles:

```php
name:           required|string|min:3|max:50|unique
description:    nullable|string|max:255
slug:           nullable|string|max:50|unique
guard_name:     required|in:web,api
is_default:     nullable|boolean
permissions:    nullable|array
permissions.*:  exists:permissions,id
```

---

### 5. **RoleController Mejorado** (`app/Http/Controllers/Managers/Roles/RoleController.php`)

#### CRUD Completo:
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `index()` | GET `/roles` | Listar roles con búsqueda |
| `create()` | GET `/roles/create` | Formulario crear rol |
| `store()` | POST `/roles` | Guardar nuevo rol |
| `show()` | GET `/roles/{role}` | Ver detalles del rol |
| `edit()` | GET `/roles/{role}/edit` | Formulario editar rol |
| `update()` | POST `/roles/{role}` | Actualizar rol |
| `destroy()` | DELETE `/roles/{role}` | Eliminar rol |

#### Métodos Avanzados:

##### Gestión de Permisos
```php
showPermissions($role)           // Mostrar permisos del rol
updatePermissions($request, $role) // Actualizar permisos
```

##### Gestión de Usuarios
```php
showUsers($role, $request)       // Listar usuarios del rol
assignUsers($role, $request)     // Asignar usuarios al rol
removeUser($role, $user)         // Remover usuario del rol
```

##### Utilidades
```php
duplicate($role)                 // Duplicar rol con sus permisos
```

---

### 6. **Rutas Actualizadas** (`routes/managers.php`)

```php
Route::prefix('roles')->group(function () {
    // CRUD básico
    Route::get('/', [RoleController::class, 'index'])->name('manager.roles');
    Route::get('/create', [RoleController::class, 'create'])->name('manager.roles.create');
    Route::post('/store', [RoleController::class, 'store'])->name('manager.roles.store');
    Route::get('/{role}/show', [RoleController::class, 'show'])->name('manager.roles.show');
    Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('manager.roles.edit');
    Route::post('/{role}/update', [RoleController::class, 'update'])->name('manager.roles.update');
    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('manager.roles.destroy');

    // Gestión de permisos
    Route::get('/{role}/permissions', [RoleController::class, 'showPermissions'])->name('manager.roles.show.permissions');
    Route::post('/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('manager.roles.update.permissions');

    // Gestión de usuarios
    Route::get('/{role}/users', [RoleController::class, 'showUsers'])->name('manager.roles.show.users');
    Route::post('/{role}/users/assign', [RoleController::class, 'assignUsers'])->name('manager.roles.assign.users');
    Route::delete('/{role}/users/{user}', [RoleController::class, 'removeUser'])->name('manager.roles.remove.user');

    // Métodos avanzados
    Route::post('/{role}/duplicate', [RoleController::class, 'duplicate'])->name('manager.roles.duplicate');
});
```

---

### 7. **Vistas Mejoradas**

#### `resources/views/managers/views/roles/roles/create.blade.php`
- ✅ Formulario mejorado con campos adicionales
- ✅ Generación automática de slug desde nombre
- ✅ Campo descripción
- ✅ Opción para marcar como rol por defecto
- ✅ Validación en tiempo real con jQuery Validate
- ✅ Notificaciones con Toastr

#### `resources/views/managers/views/roles/roles/edit.blade.php`
- ✅ Interfaz mejorada con tabs para permisos y usuarios
- ✅ Modal de confirmación para eliminación
- ✅ Botones de acción rápida (Permisos, Usuarios, Eliminar)
- ✅ Auto-generación de slug
- ✅ Validación avanzada
- ✅ Auditoría visual (creado/actualizado por)

#### `resources/views/managers/views/roles/roles/index.blade.php`
- ✅ Tabla con búsqueda avanzada
- ✅ Contador de usuarios por rol
- ✅ Acciones contextuales (editar, permisos, eliminar)
- ✅ Protección de roles del sistema
- ✅ Paginación configurable

---

### 8. **Migración de Base de Datos**

`database/migrations/2025_11_29_124305_add_columns_to_roles_table.php`

Agregar columnas a tabla `roles`:
```sql
ALTER TABLE roles ADD COLUMN description TEXT NULL;
ALTER TABLE roles ADD COLUMN slug VARCHAR(50) UNIQUE NULL;
ALTER TABLE roles ADD COLUMN is_default BOOLEAN DEFAULT false;
ALTER TABLE roles ADD COLUMN created_by UNSIGNED BIGINT NULL;
ALTER TABLE roles ADD COLUMN updated_by UNSIGNED BIGINT NULL;

ALTER TABLE roles ADD FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE roles ADD FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL;
```

---

## 🔧 Cómo Usar

### Crear un Rol

```php
$role = Role::create([
    'name' => 'supervisor-inventario',
    'description' => 'Supervisor de inventario con permisos de lectura y escritura',
    'slug' => 'supervisor-inventario',
    'guard_name' => 'web',
    'is_default' => false,
    'created_by' => auth()->id(),
    'updated_by' => auth()->id(),
]);

// Asignar permisos
$permissions = Permission::whereIn('name', [
    'warehouse:view',
    'warehouse:create',
    'warehouse:edit'
])->get();

$role->syncPermissions($permissions);
```

### Asignar Rol a Usuario

```php
$user = User::find(1);
$user->assignRole('supervisor-inventario');

// O múltiples roles
$user->syncRoles(['supervisor-inventario', 'manager']);
```

### Verificar Permisos

```php
// Usando Gates (si está configurado)
if (Gate::allows('warehouse:view')) {
    // Usuario tiene permiso
}

// Usando middleware en rutas
Route::get('/warehouse', function () {
    // Requiere permiso warehouse:view
})->middleware('permission:warehouse:view');

// Usando métodos en controlador
if (auth()->user()->can('warehouse:create')) {
    // Usuario puede crear en warehouse
}
```

### Duplicar Rol

```bash
POST /manager/roles/{role}/duplicate
```

### Asignar Usuarios a Rol

```bash
POST /manager/roles/{role}/users/assign
Content-Type: application/json

{
    "user_ids": [1, 2, 3]
}
```

---

## 📊 Características Principales

### ✅ Gestión de Roles
- Crear, leer, actualizar, eliminar roles
- Marcar rol como "por defecto"
- Generar slug automáticamente
- Auditoría (quién creó/editó)

### ✅ Gestión de Permisos
- Asignar múltiples permisos a rol
- Permisos agrupados por módulo
- Verificación de permisos en tiempo de ejecución

### ✅ Gestión de Usuarios
- Ver usuarios asignados a rol
- Asignar múltiples usuarios a rol
- Remover usuario de rol
- Sincronización bidireccional

### ✅ Seguridad
- Protección de roles del sistema (super-admin, customer)
- Prevención de eliminación de roles con usuarios asignados
- Validación de integridad de datos
- Respuestas JSON seguras

### ✅ Auditoría
- Rastreo de creación y última actualización
- Identificación de usuario responsable
- Registros completos en activity_log

### ✅ API
- Endpoints JSON para integración externa
- Respuestas consistentes (success/error)
- Manejo de errores mejorado

---

## 🚀 Próximos Pasos Recomendados

### 1. Ejecutar Migración
```bash
php artisan migrate
```

### 2. Crear Permisos en Base de Datos
```bash
php artisan tinker
> \Spatie\Permission\Models\Permission::create(['name' => 'warehouse:view', 'guard_name' => 'web']);
> \Spatie\Permission\Models\Permission::create(['name' => 'warehouse:create', 'guard_name' => 'web']);
// ... etc
```

O crear un Seeder:
```bash
php artisan make:seeder PermissionSeeder
```

### 3. Crear Roles Iniciales
```bash
php artisan tinker
> $role = \App\Models\Role::create(['name' => 'supervisor', 'guard_name' => 'web']);
> $role->syncPermissions(\Spatie\Permission\Models\Permission::where('name', 'like', 'warehouse:%')->get());
```

### 4. Asignar Roles a Usuarios
```bash
> $user = \App\Models\User::find(1);
> $user->assignRole('supervisor');
```

### 5. Crear Vistas Faltantes
Las siguientes vistas necesitan ser creadas para funcionalidad completa:
- `managers/views/roles/roles/show.blade.php` - Detalle del rol
- `managers/views/roles/roles/users.blade.php` - Usuarios asignados
- `managers/views/roles/permissions/*.blade.php` - Gestión de permisos

### 6. Implementar Middleware de Autorización
```php
// En routes
Route::middleware('permission:warehouse:view')->group(function () {
    Route::get('/warehouse', [WarehouseController::class, 'index']);
});
```

---

## 📝 Comparación: Mercosan vs Alsernet

| Característica | Mercosan | Alsernet (Nuevo) |
|----------------|----------|-----------------|
| Almacenamiento permisos | JSON | Tabla relacional (Spatie) |
| Campos de rol | 8+ | 7 (name, guard_name, description, slug, is_default, created_by, updated_by) |
| Auditoría | ✅ Sí | ✅ Sí |
| Métodos avanzados | ✅ Sí | ✅ Sí |
| Búsqueda | Por name + description | Por name + description |
| Validaciones | RoleRequest | RoleRequest |
| Respuestas | JSON y Blade | JSON y Blade |
| Protección de roles | ✅ Sí | ✅ Sí |

---

## 🔒 Seguridad

### Roles Protegidos del Sistema
```php
if (in_array($role->name, ['super-admin', 'customer'])) {
    // No se pueden editar o eliminar
}
```

### Validación de Integridad
```php
// No se puede eliminar rol con usuarios asignados
if ($role->users()->count() > 0) {
    return $this->error('Cannot delete role with assigned users');
}
```

### Auditoría de Cambios
```php
// Todos los cambios registran quién los hizo
$data['updated_by'] = auth()->id();
```

---

## 📞 Contacto y Soporte

Para preguntas sobre la implementación del sistema de roles y privilegios, consultar:
- Documentación de Spatie Permission: https://spatie.be/docs/laravel-permission
- Archivo: `app/Http/Controllers/Managers/Roles/RoleController.php`
- Documentación interna: `ROLES_ACL_IMPLEMENTATION.md`

---

**Última actualización:** 29 de Noviembre de 2025
**Versión:** 1.0
**Estado:** ✅ Implementado
