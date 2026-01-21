# Guía de Configuración de Roles

**Status: ✅ Lista para usar**

---

## 🚀 Inicio Rápido (3 pasos)

### Paso 1: Crear todos los roles

```bash
php artisan roles:create
```

**Salida esperada:**
```
🔐 Creating application roles...

  ✓ Created: super-admin - Super Administrator - Full access
  ✓ Created: admin - Administrator - Full access
  ✓ Created: manager - Manager - Manage users and operations
  ✓ Created: callcenter-manager - Call Center Manager - Manage call center operations
  ✓ Created: callcenter-agent - Call Center Agent - Handle customer calls
  ✓ Created: inventory-manager - Inventory Manager - Manage inventory
  ✓ Created: inventory-staff - Inventory Staff - Update inventory
  ✓ Created: shop-manager - Shop Manager - Manage shop operations
  ✓ Created: shop-staff - Shop Staff - Assist in shop operations
  ✓ Created: administrative - Administrative - Administrative tasks

📊 Summary:
  ✓ Created: 10 new role(s)
  ℹ Already existed: 0 role(s)
  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  Total roles: 10

✅ Role creation completed!
```

### Paso 2: Listar roles disponibles

```bash
php artisan roles:list
```

**Salida:**
```
📋 All Roles:

┌────┬──────────────────┬───────┐
│ ID │ Role Name        │ Users │
├────┼──────────────────┼───────┤
│ 1  │ super-admin      │ 0     │
│ 2  │ admin            │ 0     │
│ 3  │ manager          │ 0     │
│ 4  │ callcenter-...   │ 0     │
│ 5  │ inventory-...    │ 0     │
│ 6  │ shop-manager     │ 0     │
│ 7  │ shop-staff       │ 0     │
│ 8  │ administrative   │ 0     │
└────┴──────────────────┴───────┘
```

### Paso 3: Asignar roles a usuarios

```bash
# Asignar rol 'manager' al usuario con ID 1
php artisan roles:assign 1 manager

# Asignar rol 'super-admin' al usuario con ID 2
php artisan roles:assign 2 super-admin

# Asignar rol 'callcenter-agent' al usuario con ID 3
php artisan roles:assign 3 callcenter-agent
```

---

## 📋 Roles Disponibles

### Categoría: Super-Admin & Admin
| Rol | Descripción | Acceso |
|-----|-------------|--------|
| `super-admin` | Administrador supremo | Todos los perfiles |
| `admin` | Administrador | Todos los perfiles |

### Categoría: Manager
| Rol | Descripción | Acceso |
|-----|-------------|--------|
| `manager` | Gerente general | Manager profile |

### Categoría: Call Center
| Rol | Descripción | Acceso |
|-----|-------------|--------|
| `callcenter-manager` | Gerente de call center | Call Center profile |
| `callcenter-agent` | Agente de call center | Call Center profile |

### Categoría: Inventory (Inventario)
| Rol | Descripción | Acceso |
|-----|-------------|--------|
| `inventory-manager` | Gerente de inventario | Inventory & Warehouse profiles |
| `inventory-staff` | Personal de inventario | Inventory & Warehouse profiles |

### Categoría: Shop (Tienda)
| Rol | Descripción | Acceso |
|-----|-------------|--------|
| `shop-manager` | Gerente de tienda | Shop profile |
| `shop-staff` | Personal de tienda | Shop profile |

### Categoría: Administrative (Administrativo)
| Rol | Descripción | Acceso |
|-----|-------------|--------|
| `administrative` | Administrativo | Administrative profile |

---

## 🎯 Matriz de Acceso

Los roles están mapeados a perfiles según `roleMapping`:

```
┌────────────────────────┬──────────────────────────────────────┐
│ Perfil (Profile)       │ Roles Permitidos                     │
├────────────────────────┼──────────────────────────────────────┤
│ manager                │ super-admin, admin, manager          │
│ callcenter             │ super-admin, admin, callcenter-*     │
│ inventory              │ super-admin, admin, inventory-*      │
│ warehouse              │ super-admin, admin, inventory-*      │
│ shop                   │ super-admin, admin, shop-*           │
│ administrative         │ super-admin, admin, administrative   │
└────────────────────────┴──────────────────────────────────────┘
```

---

## 📊 Comandos Disponibles

### 1. Crear roles
```bash
php artisan roles:create
```
Crea todos los 10 roles basados en la configuración.

**Opciones:** Ninguna
**Idempotente:** ✅ Sí (no crea duplicados)

---

### 2. Listar roles
```bash
# Solo mostrar roles
php artisan roles:list

# Mostrar roles y usuarios
php artisan roles:list --users
```

**Opciones:**
- `--users` : Mostrar también qué usuarios tienen qué roles

**Ejemplo con usuarios:**
```
📋 All Roles:
┌────┬──────────────┬───────┐
│ ID │ Role Name    │ Users │
├────┼──────────────┼───────┤
│ 1  │ super-admin  │ 1     │
│ 2  │ admin        │ 2     │
│ 3  │ manager      │ 5     │
└────┴──────────────┴───────┘

👥 Users with Roles:
┌────┬─────────────────────┬────────────┬──────────────┐
│ ID │ Email               │ Name       │ Roles        │
├────┼─────────────────────┼────────────┼──────────────┤
│ 1  │ admin@example.com   │ Admin User │ super-admin  │
│ 2  │ user@example.com    │ John Doe   │ manager      │
│ 3  │ staff@example.com   │ Jane Smith │ shop-staff   │
└────┴─────────────────────┴────────────┴──────────────┘
```

---

### 3. Asignar rol a usuario
```bash
php artisan roles:assign <user_id> <role_name>
```

**Argumentos:**
- `user_id` : ID del usuario (requerido)
- `role_name` : Nombre del rol (requerido)

**Ejemplos:**
```bash
# Asignar manager al usuario 5
php artisan roles:assign 5 manager

# Asignar super-admin al usuario 1
php artisan roles:assign 1 super-admin

# Asignar callcenter-agent al usuario 10
php artisan roles:assign 10 callcenter-agent
```

**Salida:**
```
✅ Role 'manager' assigned to john@example.com
   User roles: manager
```

---

## 💻 Uso Programático (en Código)

### En un Controller o Service

```php
use Spatie\Permission\Models\Role;
use App\Models\User;

// Crear rol (si no existe)
$role = Role::firstOrCreate(['name' => 'manager']);

// Asignar rol a usuario
$user = User::find(1);
$user->assignRole('manager');

// Verificar si usuario tiene rol
if ($user->hasRole('manager')) {
    // Hacer algo
}

// Asignar múltiples roles
$user->syncRoles(['manager', 'admin']);

// Obtener roles del usuario
$roles = $user->getRoleNames(); // Collection
```

### En Blade (vistas)

```blade
<!-- Si usuario tiene rol manager -->
@if(auth()->user()->hasRole('manager'))
    <div>Este contenido solo lo ven los managers</div>
@endif

<!-- Si usuario tiene alguno de estos roles -->
@if(auth()->user()->hasAnyRole(['manager', 'admin']))
    <div>Contenido para managers o admins</div>
@endif

<!-- Mostrar roles del usuario -->
@foreach(auth()->user()->getRoleNames() as $role)
    <span class="badge">{{ $role }}</span>
@endforeach
```

---

## 🔐 Integración con Middleware

El middleware `CheckRolesAndPermissions` automáticamente valida:

```php
// En routes/theme.php
Route::middleware(['auth', 'check.roles.permissions:manager'])
    ->group(function () {
        // Solo usuarios con roles: super-admin, admin, o manager
        Route::resource('users', UserController::class);
    });
```

El middleware verifica:
1. ✅ Usuario está autenticado (`auth`)
2. ✅ Usuario tiene uno de los roles permitidos para "manager"
3. ✅ Usuario tiene los permisos requeridos para la acción

---

## 🧪 Escenarios Comunes

### Escenario 1: Crear super-admin

```bash
# 1. Crear roles
php artisan roles:create

# 2. Asignar super-admin a usuario
php artisan roles:assign 1 super-admin

# 3. Verificar
php artisan roles:list --users
```

### Escenario 2: Crear manager

```bash
# 1. Roles ya creados
# 2. Asignar manager
php artisan roles:assign 5 manager

# 3. Verificar acceso
php artisan roles:list --users
```

### Escenario 3: Múltiples roles

```bash
# En código (Controller o Seeder)
$user = User::find(5);
$user->syncRoles(['manager', 'shop-manager']);

// Ahora tiene acceso a manager Y shop profiles
```

### Escenario 4: Cambiar rol

```bash
# Asignar nuevo rol (reemplaza el anterior)
php artisan roles:assign 5 admin

# O en código
$user->syncRoles(['admin']); // Reemplaza todos los roles
$user->assignRole('editor');  // Añade sin reemplazar
```

---

## ✅ Checklist de Configuración

- [ ] Ejecutar: `php artisan roles:create`
- [ ] Verificar: `php artisan roles:list`
- [ ] Asignar super-admin: `php artisan roles:assign 1 super-admin`
- [ ] Asignar otros usuarios: `php artisan roles:assign <user_id> <role>`
- [ ] Verificar: `php artisan roles:list --users`
- [ ] Probar acceso a ruta protegida
- [ ] Verificar logs si hay errores

---

## 🐛 Troubleshooting

### Error: "Role not found"
```bash
# El rol no existe. Crear primero:
php artisan roles:create
```

### Error: "User not found"
```bash
# El usuario no existe. Verificar ID:
php artisan tinker
>>> User::pluck('id', 'email')
```

### Usuario no puede acceder a ruta
```bash
# 1. Verificar que tiene el rol correcto
php artisan roles:list --users

# 2. Verificar que el rol está en roleMapping
cat app/Http/Middleware/CheckRolesAndPermissions.php

# 3. Verificar la ruta tiene el middleware correcto
php artisan route:list | grep tu-ruta
```

---

## 📚 Referencia Rápida

```bash
# Crear roles
php artisan roles:create

# Listar roles
php artisan roles:list

# Listar roles y usuarios
php artisan roles:list --users

# Asignar rol
php artisan roles:assign <user_id> <role_name>

# Ver ayuda
php artisan roles:assign --help
```

---

## 🎯 Próximos Pasos

1. ✅ Crear roles: `php artisan roles:create`
2. ✅ Asignar a usuarios: `php artisan roles:assign <id> <role>`
3. ✅ Crear permisos (opcional): `php artisan permissions:create`
4. ✅ Probar acceso a rutas

---

## 📖 Más Información

**Documentación Spatie:**
- https://spatie.be/docs/laravel-permission/v6/introduction

**Tu documentación:**
- `SYSTEM_ARCHITECTURE.md` - Cómo funciona el sistema
- `README_ROUTE_SYSTEM.md` - Documentación general
