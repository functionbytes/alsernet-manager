# 🔐 Sistema Dinámico de Roles y Perfiles

Este documento describe el sistema completamente dinámico de gestión de roles, perfiles y permisos implementado en Alsernet.

## 📋 Descripción General

El sistema permite:
- **Gestión dinámica de roles** sin modificar código
- **Mapeo dinámico de roles a perfiles** (profile → allowed_roles)
- **Redirección dinámica a dashboards** basada en perfiles
- **Control centralizado** desde base de datos o panel admin

## 🏗️ Arquitectura del Sistema

### Tablas Principales

#### 1. `roles` (Spatie Permission)
```sql
- id: INTEGER PRIMARY KEY
- name: VARCHAR(255) UNIQUE - Nombre del rol (e.g., managers, callcenters, shops)
- guard_name: VARCHAR(255) - Guardia (default: 'web')
- label: VARCHAR(255) - Etiqueta descriptiva
- description: TEXT - Descripción del rol
- color: VARCHAR(50) - Color para UI
- created_at, updated_at: TIMESTAMP
```

**Roles Disponibles:**
- `super-admins` - Acceso total a todos los perfiles
- `admins` - Acceso administrativo
- `managers` - Gestión general
- `callcenters` - Call center
- `shops` - Tiendas
- `warehouses` - Almacenes/inventario
- `supports` - Soporte
- `administratives` - Administrativos

#### 2. `role_mappings` - Mapeo dinámico Perfil → Roles
```sql
- id: INTEGER PRIMARY KEY
- profile: VARCHAR(255) UNIQUE - Nombre del perfil (manager, shop, callcenter, etc.)
- roles: JSON - Array de nombres de roles permitidos
- description: TEXT - Descripción
- is_active: BOOLEAN - Si el mapeo está activo
- created_at, updated_at: TIMESTAMP
```

**Perfiles y sus Roles Permitidos:**
```
manager       → super-admins, admins, managers
callcenter    → super-admins, admins, callcenters
inventory     → super-admins, admins, warehouses
warehouse     → super-admins, admins, warehouses
shop          → super-admins, admins, shops
administrative → super-admins, admins, administratives
```

#### 3. `profile_routes` - Redirección dinámica Perfil → Dashboard
```sql
- id: INTEGER PRIMARY KEY
- profile: VARCHAR(255) UNIQUE - Nombre del perfil
- dashboard_route: VARCHAR(255) - Nombre de ruta del dashboard
- description: TEXT - Descripción
- created_at, updated_at: TIMESTAMP
```

**Rutas de Redirección:**
```
manager       → manager.dashboard
callcenter    → callcenter.dashboard
inventory     → warehouse.dashboard
warehouse     → warehouse.dashboard
shop          → shop.dashboard
administrative → administrative.dashboard
```

#### 4. `model_has_roles` - Asignación usuario-rol
```sql
- role_id: INTEGER FK → roles.id
- model_id: INTEGER FK → users.id
- model_type: VARCHAR(255) - (default: 'App\Models\User')
PRIMARY KEY (role_id, model_id, model_type)
```

#### 5. `role_has_permissions` - Asignación rol-permiso
```sql
- permission_id: INTEGER FK → permissions.id
- role_id: INTEGER FK → roles.id
PRIMARY KEY (permission_id, role_id)
```

#### 6. `permissions` (Spatie Permission)
```sql
- id: INTEGER PRIMARY KEY
- name: VARCHAR(255) UNIQUE - Nombre del permiso
- guard_name: VARCHAR(255) - Guardia
- created_at, updated_at: TIMESTAMP
```

## 🔄 Flujo de Funcionamiento

### 1. Login de Usuario
```
Usuario ingresa credenciales
    ↓
Usuario autenticado → Session creada
    ↓
Middleware CheckRolesAndPermissions verifica rol
    ↓
User::redirectRouteName() determina dashboard
    ↓
Usuario redirigido al dashboard de su perfil
```

### 2. Validación de Acceso a Perfil
```
Usuario accede a /callcenter/products/...
    ↓
CheckRolesAndPermissions::handle() intercepta
    ↓
Busca RoleMapping para 'callcenter'
    ↓
¿Usuario tiene alguno de los roles permitidos?
    - SÍ → Continúa, valida permisos específicos
    - NO → 403 Forbidden
```

### 3. Validación de Permisos
```
Usuario tiene rol callcenters
    ↓
Rol callcenters tiene 63 permisos asignados
    ↓
¿Usuario tiene el permiso requerido para la acción?
    - SÍ → Acción ejecutada
    - NO → 403 Forbidden
```

## 🛠️ Gestión de Roles

### Opción 1: Comando Artisan (Línea de Comandos)

**Asignar un rol a usuario:**
```bash
php artisan roles:assign usuario@email.com managers
```

**Cambiar rol de usuario:**
```bash
php artisan roles:assign usuario@email.com callcenters
```

El comando mostrará:
- Rol anterior/nuevo
- Cantidad de permisos
- Perfiles a los que tiene acceso

### Opción 2: Panel Admin (UI)

**Acceder al panel:**
```
http://localhost/admin/roles
```

Requiere ser super-admin.

**Gestionar usuarios:**
1. Ir a "Usuarios"
2. Click en "Editar" para cada usuario
3. Seleccionar nuevos roles
4. Guardar

**Gestionar configuraciones:**
1. Ir a "Configuración"
2. Editar Role Mappings (qué roles acceden a qué perfiles)
3. Editar Profile Routes (a dónde redirigir cada perfil)

### Opción 3: Base de Datos Directa (SQL)

**Asignar rol a usuario:**
```sql
-- Obtener IDs
SELECT id FROM users WHERE email = 'usuario@email.com';
SELECT id FROM roles WHERE name = 'managers';

-- Asignar
INSERT INTO model_has_roles (role_id, model_id, model_type)
VALUES (1, 7, 'App\\Models\\User');

-- O actualizar (primero eliminar, luego insertar)
DELETE FROM model_has_roles WHERE model_id = 7;
INSERT INTO model_has_roles VALUES (1, 7, 'App\\Models\\User');
```

**Modificar Role Mapping:**
```sql
-- Ver mapeos actuales
SELECT * FROM role_mappings;

-- Cambiar roles para un perfil
UPDATE role_mappings 
SET roles = JSON_ARRAY('super-admins', 'admins', 'managers', 'supports')
WHERE profile = 'manager';
```

**Cambiar redirección de perfil:**
```sql
UPDATE profile_routes 
SET dashboard_route = 'custom.dashboard'
WHERE profile = 'shop';
```

## 📁 Archivos del Sistema

### Modelos
- `app/Models/User.php` - Usuario con `redirectRouteName()` dinámico
- `app/Models/RoleMapping.php` - Mapeo dinámico perfil → roles
- `app/Models/ProfileRoute.php` - Redirección dinámica perfil → dashboard

### Middleware
- `app/Http/Middleware/CheckRolesAndPermissions.php` - Validación dinámica de roles

### Controlador
- `app/Http/Controllers/RoleManagementController.php` - Admin panel

### Vistas
- `resources/views/admin/roles/index.blade.php` - Listar usuarios
- `resources/views/admin/roles/edit.blade.php` - Editar roles de usuario
- `resources/views/admin/roles/mappings.blade.php` - Configurar mappings

### Migraciones
- `database/migrations/2024_11_29_create_role_mappings_table.php`
- `database/migrations/2024_11_29_create_profile_routes_table.php`
- `database/migrations/2024_11_29_add_description_to_roles_table.php`

### Comandos
- `app/Console/Commands/AssignRoleCommand.php` - Comando `roles:assign`

### Rutas
- `routes/web.php` - Rutas del admin panel bajo `/admin/roles`

## 💾 Caching para Performance

### Middleware Caching
```php
// Cache de 1 hora para role_mappings
Cache::remember('role_mappings_active', 3600, fn() => RoleMapping::getActive())
```

Invalidar cache:
```bash
php artisan cache:clear
```

## 🔐 Seguridad

### Protecciones Implementadas
1. **Middleware de Autenticación** - Requiere login
2. **Validación de Roles** - Comprueba roles antes de acceso
3. **Validación de Permisos** - Comprueba permisos específicos
4. **Logging de Accesos Denegados** - Auditoría en `storage/logs`

### Roles Especiales
- **super-admins** - Acceso total, ve todos los 6 perfiles
- **admins** - Acceso administrativo a todos los perfiles

## 📊 Estado Actual de Usuarios

```
ID | Email                      | Rol           | Acceso a
---|----------------------------|---------------|------------------
1  | managers@Alsernet.es       | super-admins  | Todos los perfiles
2  | callcenters@Alsernet.es    | callcenters   | callcenter
4  | callcentersmadrid1@...es   | callcenters   | callcenter
5  | callcentersmadrid2@...es   | callcenters   | callcenter
6  | administratives@Alsernet.es| administratives| administrative
7  | warehouses@Alsernet.es     | warehouses    | inventory, warehouse
```

## 🚀 Casos de Uso

### Caso 1: Agregar nuevo usuario con rol
```bash
# Via Artisan
php artisan roles:assign nuevo@email.com shops

# Via Panel Admin
1. Ir a /admin/roles
2. Hacer clic en usuario nuevo
3. Asignar rol "shops"
4. Guardar
```

### Caso 2: Crear nuevo perfil
```sql
-- 1. Crear role mapping
INSERT INTO role_mappings (profile, roles, description, is_active)
VALUES ('reports', JSON_ARRAY('super-admins', 'admins', 'managers'), 
        'Acceso a módulo de reportes', 1);

-- 2. Crear redirección
INSERT INTO profile_routes (profile, dashboard_route, description)
VALUES ('reports', 'reports.dashboard', 'Dashboard de reportes');

-- 3. Crear rutas sincronizadas (si existen)
-- Ejecutar: php artisan routes:sync
```

### Caso 3: Cambiar permisos de rol sin tocar código
```sql
-- Los permisos se asignan automáticamente vía:
php artisan permissions:create --assign

-- Y se pueden modificar en la base de datos:
SELECT * FROM role_has_permissions WHERE role_id = 1; -- Ver permisos de super-admins
```

## 📚 Comandos Útiles

### Routers
```bash
# Sincronizar rutas a base de datos
php artisan routes:sync

# Limpiar duplicados de rutas
php artisan routes:clean-duplicates

# Ver rutas sincronizadas
php artisan routes:list --name=manager
```

### Permisos
```bash
# Crear permisos desde rutas
php artisan permissions:create

# Asignar permisos a roles
php artisan permissions:create --assign
```

### Roles
```bash
# Asignar rol a usuario
php artisan roles:assign usuario@email.com managers
```

### Cache
```bash
# Limpiar cache (incluyendo role_mappings)
php artisan cache:clear

# Limpiar solo cache
php artisan cache:clear

# Reconstruir cache
php artisan config:cache
```

## 🎯 Ventajas del Sistema Dinámico

✅ **Sin Deploy Requerido** - Cambios instantáneos sin reiniciar
✅ **Escalable** - Agregar perfiles/roles fácilmente
✅ **Auditable** - Todos los cambios logged
✅ **Flexible** - Control total desde DB o UI
✅ **Performance** - Caching de 1 hora
✅ **Seguro** - Validación en múltiples capas

## 🐛 Troubleshooting

### Problema: Usuario sin acceso a su dashboard
**Solución:**
1. Verificar que usuario tiene rol asignado: `SELECT * FROM model_has_roles WHERE model_id = X;`
2. Verificar que rol existe en rol_mappings: `SELECT * FROM role_mappings;`
3. Verificar que profile_route existe: `SELECT * FROM profile_routes;`
4. Limpiar cache: `php artisan cache:clear`

### Problema: Permiso denegado aunque tiene rol
**Solución:**
1. Verificar que rol tiene permiso: `SELECT * FROM role_has_permissions WHERE role_id = X;`
2. Re-ejecutar: `php artisan permissions:create --assign`
3. Verificar que ruta existe en app_routes: `SELECT * FROM app_routes;`

### Problema: Route mapping no se actualiza
**Solución:**
1. Limpiar cache: `php artisan cache:clear`
2. Verificar role_mappings: `SELECT * FROM role_mappings;`
3. Verificar middleware está usando getRoleMapping(): grep "getRoleMapping" app/Http/Middleware/CheckRolesAndPermissions.php

## 📞 Soporte

Para problemas o preguntas sobre el sistema dinámico de roles:
1. Revisar logs en `storage/logs/laravel.log`
2. Ejecutar: `php artisan tinker` para debugging interactivo
3. Consultar base de datos directamente para verificar estados

---

**Última actualización:** 29 de noviembre de 2024
**Versión:** 1.0 - Sistema dinámico completo
