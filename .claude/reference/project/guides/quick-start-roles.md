# ⚡ Quick Start - Gestión de Roles

## 🎯 Tareas Comunes

### 1. Asignar Rol a Usuario (Vía CLI)

```bash
# Ver roles disponibles
php artisan tinker
> Role::all()->pluck('name')

# Asignar rol
php artisan roles:assign email@usuario.com managers

# Cambiar rol
php artisan roles:assign email@usuario.com callcenters
```

**Resultado:**
```
✅ Rol asignado exitosamente
📊 Resumen:
  Usuario: email@usuario.com
  Rol anterior: managers
  Rol nuevo: callcenters

🔐 Permisos y acceso para este rol:
  Permisos: 63 permisos asignados
  Perfiles accesibles:
  • callcenter → callcenter.dashboard
```

---

### 2. Gestionar Roles Vía Panel Admin

**URL:** `http://localhost/admin/roles`

**Pasos:**
1. Login con super-admin
2. Click en usuario
3. Seleccionar roles
4. Guardar

---

### 3. Ver Usuarios y sus Roles (SQL)

```sql
-- Listar todos los usuarios con sus roles
SELECT 
    u.id,
    u.email,
    u.firstname,
    u.lastname,
    GROUP_CONCAT(r.name) as roles
FROM users u
LEFT JOIN model_has_roles mhr ON u.id = mhr.model_id
LEFT JOIN roles r ON mhr.role_id = r.id
GROUP BY u.id;
```

---

### 4. Modificar Role Mappings (Perfiles)

**SQL - Ver configuración actual:**
```sql
SELECT profile, roles, description FROM role_mappings;
```

**SQL - Cambiar roles permitidos para un perfil:**
```sql
UPDATE role_mappings 
SET roles = JSON_ARRAY('super-admins', 'admins', 'managers', 'supports')
WHERE profile = 'manager';
```

**UI - Panel Admin:**
1. Ir a "Configuración" → "Role Mappings"
2. Seleccionar roles para cada perfil
3. Guardar

---

### 5. Modificar Dashboard Routes (Redirecciones)

**SQL - Ver rutas actuales:**
```sql
SELECT profile, dashboard_route FROM profile_routes;
```

**SQL - Cambiar dashboard route:**
```sql
UPDATE profile_routes 
SET dashboard_route = 'custom.dashboard'
WHERE profile = 'shop';
```

**UI - Panel Admin:**
1. Ir a "Configuración" → "Profile Routes"
2. Cambiar ruta para cada perfil
3. Guardar

---

## 🔍 Debugging

### Verificar rol de usuario
```bash
php artisan tinker
> $user = User::find(1)
> $user->roles->pluck('name')  # ['super-admins']
```

### Verificar permisos de rol
```bash
php artisan tinker
> $role = Role::where('name', 'managers')->first()
> $role->permissions->count()  # 728
```

### Ver a qué perfiles puede acceder un rol
```bash
php artisan tinker
> RoleMapping::getActive()
# Muestra role → profile → allowed_roles mapping
```

### Ver dónde se redirige un perfil
```bash
php artisan tinker
> ProfileRoute::getRoute('shop')  # 'shop.dashboard'
```

---

## 🚨 Solucionar Problemas

### Problema: "No tienes permisos para acceder a esta sección"

**Causas posibles:**
1. Usuario no tiene el rol correcto
2. Rol no está en el role_mapping del perfil
3. Rol no tiene los permisos

**Soluciones:**
```bash
# 1. Asignar rol correcto
php artisan roles:assign usuario@email.com managers

# 2. Verificar en BD
php artisan tinker
> RoleMapping::where('profile', 'manager')->first()->roles  # Ver roles permitidos

# 3. Re-crear permisos
php artisan permissions:create --assign

# 4. Limpiar cache
php artisan cache:clear
```

### Problema: Usuario redirigido a login después de autenticarse

**Causas:**
1. Usuario no tiene ningún rol asignado
2. ProfileRoute no existe para su perfil
3. Role no está en role_mappings

**Soluciones:**
```bash
# 1. Asignar rol
php artisan roles:assign usuario@email.com managers

# 2. Verificar profile_routes
php artisan tinker
> ProfileRoute::all()

# 3. Limpiar cache
php artisan cache:clear

# 4. Probar manualmente
> User::find(1)->redirectRouteName()  # Debe retornar una ruta
```

### Problema: Cambios en BD no se reflejan

**Solución:**
```bash
# Limpiar cache (role_mappings se cachea por 1 hora)
php artisan cache:clear

# Verificar cache está limpio
php artisan cache:clear
```

---

## 📊 Estados Usuales

### Usuarios y sus Roles

```
ID | Email                      | Rol              | Acceso a
1  | managers@Alsernet.es       | super-admins     | Todos
2  | callcenters@Alsernet.es    | callcenters      | callcenter
4  | callcentersmadrid1@...es   | callcenters      | callcenter
5  | callcentersmadrid2@...es   | callcenters      | callcenter
6  | administratives@Alsernet.es| administratives  | administrative
7  | warehouses@Alsernet.es     | warehouses       | inventory, warehouse
```

---

## 💾 Operaciones en Batch

### Asignar mismo rol a múltiples usuarios

```bash
# Script que asigna managers a varios usuarios
for email in user1@email.com user2@email.com user3@email.com; do
    php artisan roles:assign $email managers
done
```

### SQL - Cambiar todos los users de un rol a otro

```sql
-- 1. Obtener IDs de usuarios con callcenters
SELECT DISTINCT model_id FROM model_has_roles mhr
JOIN roles r ON mhr.role_id = r.id
WHERE r.name = 'callcenters';

-- 2. Cambiar a managers
DELETE FROM model_has_roles WHERE model_id IN (2, 4, 5) AND role_id = (SELECT id FROM roles WHERE name = 'callcenters');
INSERT INTO model_has_roles (role_id, model_id, model_type)
SELECT (SELECT id FROM roles WHERE name = 'managers'), model_id, 'App\\Models\\User'
FROM (SELECT DISTINCT model_id FROM model_has_roles WHERE role_id = ...) temp;
```

---

## 🔗 Enlaces Útiles

- **Panel Admin:** `/admin/roles`
- **Documentación Completa:** `DYNAMIC_ROLES_SYSTEM.md`
- **Logs:** `storage/logs/laravel.log`
- **Spatie Permission Docs:** https://spatie.be/docs/laravel-permission

---

**¿Necesitas ayuda?** Revisa `DYNAMIC_ROLES_SYSTEM.md` para documentación completa.

