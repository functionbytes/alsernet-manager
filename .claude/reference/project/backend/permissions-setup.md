# Guía Completa de Permisos (Permissions)

**Status: ✅ Lista para usar**

---

## 🎯 Conceptos Clave

### Roles vs Permisos

| Aspecto | Rol | Permiso |
|--------|-----|---------|
| **Qué es** | Grupo de permisos | Acción específica |
| **Ejemplo** | `manager` | `users.index`, `users.create` |
| **Usuario tiene** | 1+ roles | Muchos permisos (via roles) |
| **Asignación** | A usuario | A rol o usuario directo |

### Cómo se relacionan

```
Usuario (john@example.com)
  ↓ assignRole()
  ├─ Rol: manager
  │   ├─ Permission: users.index
  │   ├─ Permission: users.create
  │   ├─ Permission: users.edit
  │   └─ Permission: users.delete
  │
  └─ Rol: admin
      ├─ Permission: users.index
      ├─ Permission: users.create
      ├─ Permission: settings.manage
      └─ Permission: logs.view
```

---

## 🚀 Inicio Rápido (3 pasos)

### Paso 1: Crear Roles (si no lo hiciste)

```bash
php artisan roles:create
```

### Paso 2: Sincronizar Rutas

```bash
php artisan routes:sync
```

Esto asegura que todas las rutas estén en la base de datos.

### Paso 3: Crear Permisos desde las Rutas

```bash
# Solo crear permisos
php artisan permissions:create

# Crear permisos Y asignarlos a roles automáticamente
php artisan permissions:create --assign
```

**¡Eso es todo!** Los permisos se crean automáticamente desde las rutas sincronizadas.

---

## 📋 Comandos Disponibles

### 1. Crear Permisos

```bash
# Crear permisos basados en rutas sincronizadas
php artisan permissions:create

# Crear permisos Y asignarlos a roles automáticamente
php artisan permissions:create --assign
```

**Salida:**
```
🔐 Creating permissions from synced routes...

  ✓ Created: users.index (GET /manager/users)
  ✓ Created: users.create (GET /manager/users/create)
  ✓ Created: users.store (POST /manager/users)
  ✓ Created: users.edit (GET /manager/users/{id}/edit)
  ✓ Created: users.update (PUT /manager/users/{id})
  ✓ Created: users.destroy (DELETE /manager/users/{id})
  ... y más

📊 Summary:
  ✓ Created: 45 new permission(s)
  ℹ Already existed: 0 permission(s)
  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  Total permissions: 45

💡 To assign permissions to roles, run: php artisan permissions:create --assign
```

Con `--assign`:
```
🔗 Assigning permissions to roles...

  ✓ Role 'super-admin' → 45 permissions assigned
  ✓ Role 'admin' → 45 permissions assigned
  ✓ Role 'manager' → 12 permissions assigned
  ✓ Role 'callcenter-manager' → 8 permissions assigned
  ... más roles
```

---

### 2. Listar Permisos

```bash
# Ver todos los permisos
php artisan permissions:list

# Ver permisos de un rol específico
php artisan permissions:list --role=manager

# Ver permisos de un usuario específico
php artisan permissions:list --user=1
```

**Ejemplo: Ver todos los permisos**
```
📋 All Permissions (45):

┌────┬──────────────────┬────────────┐
│ ID │ Permission Name  │ Roles      │
├────┼──────────────────┼────────────┤
│ 1  │ users.index      │ 3          │
│ 2  │ users.create     │ 2          │
│ 3  │ users.edit       │ 2          │
│ 4  │ products.index   │ 2          │
│ ... │ ...              │ ...        │
└────┴──────────────────┴────────────┘
```

**Ejemplo: Ver permisos de un rol**
```
📋 Permissions for Role: manager
   Total: 12 permissions

┌────┬─────────────────────┐
│ ID │ Permission          │
├────┼─────────────────────┤
│ 1  │ users.index         │
│ 2  │ users.create        │
│ 3  │ users.edit          │
│ 4  │ users.destroy       │
│ ... │ ...                 │
└────┴─────────────────────┘
```

**Ejemplo: Ver permisos de un usuario**
```
👤 Permissions for User: john@example.com

Roles:
  • manager
  • admin

Direct Permissions:
  (none)

Permissions from Roles (Total: 45):
┌─────────────────────────┐
│ Permission              │
├─────────────────────────┤
│ users.index             │
│ users.create            │
│ users.edit              │
│ ... (45 total)          │
└─────────────────────────┘
```

---

### 3. Asignar Permiso a Usuario

```bash
# Asignar permiso directo a usuario
php artisan permissions:assign <user_id> <permission_name>

# Asignar permiso a rol
php artisan permissions:assign <role_id> <permission_name> --role
```

**Ejemplos:**
```bash
# Asignar permiso 'users.create' al usuario 5
php artisan permissions:assign 5 users.create

# Asignar permiso 'settings.manage' al rol admin
php artisan permissions:assign 2 settings.manage --role
```

---

## 🔄 Flujo Completo

### Escenario: Crear todo desde cero

```bash
# 1. Crear roles
php artisan roles:create
# → Crea: super-admin, admin, manager, etc.

# 2. Sincronizar rutas
php artisan routes:sync
# → Sincroniza todas las rutas a base de datos

# 3. Crear permisos desde rutas
php artisan permissions:create --assign
# → Crea permisos para cada ruta
# → Asigna permisos a roles automáticamente

# 4. Asignar roles a usuarios
php artisan roles:assign 1 manager
php artisan roles:assign 2 super-admin
# → Los usuarios heredan todos los permisos del rol

# 5. Verificar
php artisan permissions:list --user=1
# → Muestra todos los permisos del usuario
```

---

## 📊 Tabla de Base de Datos

### permissions
```sql
┌────┬──────────────────┬───────────┬────────────────┐
│ id │ name             │ guard_name│ created_at     │
├────┼──────────────────┼───────────┼────────────────┤
│ 1  │ users.index      │ web       │ 2024-11-29     │
│ 2  │ users.create     │ web       │ 2024-11-29     │
│ 3  │ users.edit       │ web       │ 2024-11-29     │
│ ... │ ...              │ ...       │ ...            │
└────┴──────────────────┴───────────┴────────────────┘
```

### role_has_permissions (Pivot)
```sql
┌─────────┬────────────┐
│ role_id │ permission_id
├─────────┼────────────┤
│ 1       │ 1          │ ← super-admin tiene users.index
│ 1       │ 2          │ ← super-admin tiene users.create
│ 2       │ 1          │ ← admin tiene users.index
│ 2       │ 2          │ ← admin tiene users.create
│ 3       │ 1          │ ← manager tiene users.index
│ ...     │ ...        │
└─────────┴────────────┘
```

### model_has_permissions (Pivot para usuarios)
```sql
┌──────────────────┬──────────────┬────────────┐
│ model_type       │ model_id     │ permission_id
├──────────────────┼──────────────┼────────────┤
│ App\Models\User  │ 5            │ 1          │ ← Usuario 5 tiene permiso 1
└──────────────────┴──────────────┴────────────┘
```

---

## 💻 Uso en Código

### En Controladores

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Verificar permiso
        if (!auth()->user()->hasPermissionTo('users.index')) {
            abort(403, 'Unauthorized');
        }

        return User::all();
    }

    public function create()
    {
        // Verificar permiso
        if (!auth()->user()->hasPermissionTo('users.create')) {
            abort(403, 'Unauthorized');
        }

        return view('users.create');
    }

    public function store(Request $request)
    {
        auth()->user()->hasPermissionTo('users.create')
            ? /* crear usuario */
            : abort(403);
    }
}
```

### Con Authorize (más limpio)

```php
class UserController extends Controller
{
    public function index()
    {
        // Laravel automáticamente verifica el permiso
        // Basado en el nombre de la ruta (users.index)
        // El middleware "can:users.index" verifica esto
        return User::all();
    }
}

// En routes/managers.php
Route::resource('users', UserController::class)
    ->middleware('can:users.index|users.create|users.edit|users.delete');
```

### En Vistas (Blade)

```blade
<!-- Si usuario tiene permiso -->
@if(auth()->user()->hasPermissionTo('users.create'))
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        Crear Usuario
    </a>
@endif

<!-- Si usuario tiene alguno de estos permisos -->
@if(auth()->user()->hasAnyPermission(['users.edit', 'users.delete']))
    <div class="admin-panel">...</div>
@endif

<!-- Si usuario NO tiene permiso -->
@unless(auth()->user()->hasPermissionTo('settings.manage'))
    <p>No tienes acceso a configuración</p>
@endunless
```

### En Artisan Tinker

```php
php artisan tinker

# Obtener usuario
>>> $user = User::find(1);

# Ver roles
>>> $user->getRoleNames();
=> Illuminate\Support\Collection {#4941
     #items => ['manager', 'admin'],
   }

# Ver permisos (heredados de roles + directos)
>>> $user->getPermissionsViaRoles();
=> Illuminate\Database\Eloquent\Collection {
     ... (45 permisos)
   }

# Verificar permiso
>>> $user->hasPermissionTo('users.create')
=> true

# Asignar rol
>>> $user->assignRole('manager')
=> true

# Asignar permiso directo
>>> $user->givePermissionTo('users.delete')
=> true

# Remover permiso
>>> $user->revokePermissionTo('users.delete')
=> true

# Remover rol
>>> $user->removeRole('manager')
=> true
```

---

## 🔐 Seguridad

### Niveles de Control

```
1. Autenticación (auth middleware)
   ↓ Usuario está logueado?

2. Autorización (roles y permisos)
   ↓ Tiene el rol/permiso?

3. Lógica de negocio
   ↓ Puede hacer la acción?

4. Auditoría (logging)
   ↓ Registrar qué hizo
```

### Buenas Prácticas

✅ **Usa permisos en middleware**
```php
// ✅ Correcto
Route::resource('users', UserController::class)
    ->middleware('can:users.index');

// ❌ Incorrecto
Route::resource('users', UserController::class); // Sin verificación
```

✅ **Verifica en el controlador como respaldo**
```php
public function edit($id)
{
    // El middleware verificó, pero verificamos de nuevo
    // para estar seguros
    if (!auth()->user()->hasPermissionTo('users.edit')) {
        abort(403);
    }

    return view('users.edit');
}
```

✅ **Registra acciones importantes**
```php
Log::info('User created', [
    'user_id' => $user->id,
    'created_by' => auth()->id(),
    'permission' => 'users.create',
]);
```

---

## 📚 Referencia Rápida

```bash
# Crear roles
php artisan roles:create

# Crear permisos desde rutas
php artisan permissions:create --assign

# Listar permisos
php artisan permissions:list
php artisan permissions:list --role=manager
php artisan permissions:list --user=1

# Asignar permiso
php artisan permissions:assign <user_id> <permission_name>
php artisan permissions:assign <role_id> <permission_name> --role

# Asignar rol
php artisan roles:assign <user_id> <role_name>

# Ver roles y usuarios
php artisan roles:list --users
```

---

## 🧪 Escenarios de Uso

### Escenario 1: Manager necesita crear usuarios

```bash
# 1. Manager ya tiene rol 'manager'
# 2. Rol 'manager' tiene permiso 'users.create'
# 3. Usuario accede: POST /manager/users
# 4. Middleware verifica: ¿Tiene 'users.create'?
# 5. ✅ Acceso permitido
```

### Escenario 2: Shop Staff NO puede eliminar

```bash
# 1. User tiene rol 'shop-staff'
# 2. Rol 'shop-staff' NO tiene 'users.delete'
# 3. Usuario intenta: DELETE /shop/users/5
# 4. Middleware verifica: ¿Tiene 'users.delete'?
# 5. ❌ Acceso denegado
```

### Escenario 3: Super-admin tiene TODO

```bash
# 1. User tiene rol 'super-admin'
# 2. Rol 'super-admin' tiene TODOS los permisos (45)
# 3. Usuario accede a cualquier ruta
# 4. ✅ Acceso siempre permitido
```

---

## ⚠️ Troubleshooting

### Error: "Permission not found"
```bash
# Crear permisos desde rutas
php artisan permissions:create

# Verificar que la ruta está sincronizada
php artisan routes:sync
```

### Usuario no puede acceder
```bash
# 1. Verificar que tiene el rol
php artisan roles:list --users

# 2. Verificar que el rol tiene el permiso
php artisan permissions:list --role=manager

# 3. Verificar que el permiso existe
php artisan permissions:list
```

### El comando `permissions:create --assign` no funciona
```bash
# 1. Primero crear roles
php artisan roles:create

# 2. Luego crear permisos sin --assign
php artisan permissions:create

# 3. Luego crear de nuevo con --assign
php artisan permissions:create --assign
```

---

## 🎯 Checklist de Configuración

- [ ] `php artisan roles:create` ✅
- [ ] `php artisan routes:sync` ✅
- [ ] `php artisan permissions:create --assign` ✅
- [ ] `php artisan permissions:list` (verificar que se crearon)
- [ ] `php artisan roles:list --users` (verificar usuarios)
- [ ] Asignar roles a usuarios: `php artisan roles:assign <id> <role>`
- [ ] Probar acceso a ruta protegida
- [ ] Verificar logs si hay errores

---

## 📖 Más Información

**Documentación oficial:**
- https://spatie.be/docs/laravel-permission/v6/introduction

**Tu documentación:**
- `ROLES_SETUP_GUIDE.md` - Guía de roles
- `SYSTEM_ARCHITECTURE.md` - Arquitectura general
- `README_ROUTE_SYSTEM.md` - Sistema de rutas
