# Descripción Completa de Roles

**Versión: 1.0**
**Última actualización: 29 de Noviembre de 2024**

---

## 📋 Tabla de Contenidos

1. [Roles de Sistema](#roles-de-sistema)
2. [Roles de Gestión](#roles-de-gestión)
3. [Roles de Operaciones](#roles-de-operaciones)
4. [Matriz de Acceso](#matriz-de-acceso)
5. [Permisos Detallados](#permisos-detallados)

---

## 🔴 Roles de Sistema

### 1. Super Administrador (`super-admin`)

**Label:** Super Administrador
**Color:** <span style="color: #FF0000;">■</span> Rojo
**Prioridad:** Máxima

#### Descripción
Acceso **COMPLETO** a todas las funciones y módulos del sistema. Puede gestionar usuarios, roles, permisos, configuraciones y ver todos los datos.

**⚠️ USO:** Solo para administradores supremos del sistema.

#### Responsabilidades
- ✅ Crear y eliminar usuarios
- ✅ Crear y modificar roles
- ✅ Crear y asignar permisos
- ✅ Configurar el sistema
- ✅ Ver todos los reportes
- ✅ Acceder a todos los módulos
- ✅ Auditoría y logs
- ✅ Backups y recuperación

#### Acceso a Perfiles
- ✅ Manager (Gerencia)
- ✅ Call Center
- ✅ Inventory (Inventario)
- ✅ Warehouse (Almacén)
- ✅ Shop (Tienda)
- ✅ Administrative (Administrativo)

#### Ejemplo de Uso
```bash
php artisan roles:assign 1 super-admin
# Usuario con ID 1 es Super Administrador
```

---

### 2. Administrador (`admin`)

**Label:** Administrador
**Color:** <span style="color: #FF6600;">■</span> Naranja
**Prioridad:** Muy Alta

#### Descripción
Acceso **casi completo** al sistema. Puede gestionar usuarios, roles, y la mayoría de funciones.

**Limitaciones:** No puede modificar configuración del sistema ni gestionar roles de super-admin.

#### Responsabilidades
- ✅ Crear y editar usuarios (excepto super-admin)
- ✅ Crear y editar roles (excepto super-admin)
- ✅ Asignar permisos
- ⚠️ Configuración limitada del sistema
- ✅ Ver todos los reportes
- ✅ Acceder a casi todos los módulos
- ✅ Auditoría básica

#### Acceso a Perfiles
- ✅ Manager
- ✅ Call Center
- ✅ Inventory
- ✅ Warehouse
- ✅ Shop
- ✅ Administrative

---

## 🔵 Roles de Gestión

### 3. Gerente General (`manager`)

**Label:** Gerente General
**Color:** <span style="color: #0066FF;">■</span> Azul
**Prioridad:** Alta
**Perfil:** Manager

#### Descripción
Gestiona usuarios y operaciones generales del perfil Manager. Responsable de la supervisión general y toma de decisiones operativas.

#### Responsabilidades
- ✅ Crear, editar y eliminar usuarios del perfil Manager
- ✅ Asignar roles a usuarios (solo roles Manager)
- ✅ Ver todos los datos del sistema
- ✅ Generar y ver reportes
- ✅ Administrar datos básicos
- ✅ Supervisar operaciones diarias
- ⚠️ No puede crear nuevos roles

#### Acceso a Funcionalidades
- ✅ Gestión de Usuarios
- ✅ Reportes y Analytics
- ✅ Configuración básica
- ✅ Dashboard ejecutivo
- ⚠️ No puede crear roles

#### Ejemplo
```bash
php artisan roles:assign 5 manager
# Usuario gestiona operaciones Manager
```

---

### 4. Gerente de Call Center (`callcenter-manager`)

**Label:** Gerente de Call Center
**Color:** <span style="color: #00AA00;">■</span> Verde
**Prioridad:** Alta
**Perfil:** Call Center

#### Descripción
Gestiona todas las operaciones del call center. Responsable de la calidad del servicio, productividad del equipo y cumplimiento de objetivos.

#### Responsabilidades
- ✅ Supervisar agentes de call center
- ✅ Asignar tareas a agentes
- ✅ Ver reportes de llamadas
- ✅ Gestionar colas de llamadas
- ✅ Monitorear desempeño del equipo
- ✅ Resolver escalamientos
- ✅ Generar reportes de productividad
- ✅ Gestionar horarios

#### Acceso a Funcionalidades
- ✅ Dashboard del Call Center
- ✅ Monitoreo de llamadas en vivo
- ✅ Reportes de agentes
- ✅ Gestión de colas
- ✅ Evaluación de llamadas
- ✅ Reportes de SLA

#### Ejemplo
```bash
php artisan roles:assign 10 callcenter-manager
# Usuario gestiona Call Center
```

---

### 5. Gerente de Inventario (`inventory-manager`)

**Label:** Gerente de Inventario
**Color:** <span style="color: #9900FF;">■</span> Púrpura
**Prioridad:** Alta
**Perfil:** Inventory & Warehouse

#### Descripción
Gestiona inventario y almacén. Responsable del control de stock, optimización de almacenamiento y precisión de datos.

#### Responsabilidades
- ✅ Crear y editar productos
- ✅ Gestionar niveles de stock
- ✅ Controlar entradas y salidas
- ✅ Realizar ajustes de inventario
- ✅ Generar reportes de inventario
- ✅ Identificar productos con bajo stock
- ✅ Supervisar personal de inventario
- ✅ Auditoría de almacén

#### Acceso a Funcionalidades
- ✅ Gestión de Productos
- ✅ Control de Stock
- ✅ Movimientos de Inventario
- ✅ Reportes de Inventario
- ✅ Auditoría de Almacén
- ✅ Dashboard de Inventario

#### Ejemplo
```bash
php artisan roles:assign 15 inventory-manager
# Usuario gestiona Inventario
```

---

### 6. Gerente de Tienda (`shop-manager`)

**Label:** Gerente de Tienda
**Color:** <span style="color: #FF9900;">■</span> Naranja Oscuro
**Prioridad:** Alta
**Perfil:** Shop

#### Descripción
Gestiona operaciones de tienda. Responsable de ventas, satisfacción del cliente y rentabilidad de la tienda.

#### Responsabilidades
- ✅ Vender productos
- ✅ Gestionar clientes
- ✅ Procesar pagos
- ✅ Manejar caja
- ✅ Generar reportes de ventas
- ✅ Supervisar personal de tienda
- ✅ Gestionar devoluciones
- ✅ Control de calidad de servicio

#### Acceso a Funcionalidades
- ✅ Módulo de Ventas
- ✅ Gestión de Clientes
- ✅ Caja y Pagos
- ✅ Reportes de Ventas
- ✅ Dashboard de Tienda
- ✅ Gestión de Devoluciones

#### Ejemplo
```bash
php artisan roles:assign 20 shop-manager
# Usuario gestiona Tienda
```

---

## 🟢 Roles de Operaciones

### 7. Agente de Call Center (`callcenter-agent`)

**Label:** Agente de Call Center
**Color:** <span style="color: #00DD00;">■</span> Verde Claro
**Prioridad:** Media
**Perfil:** Call Center

#### Descripción
Atiende llamadas de clientes. Responsable de la satisfacción del cliente y registro preciso de información.

#### Responsabilidades
- ✅ Atender llamadas de clientes
- ✅ Consultar información de clientes
- ✅ Registrar llamadas en el sistema
- ✅ Crear tickets de soporte
- ✅ Seguimiento de casos
- ✅ Proporcionar información de productos
- ✅ Resolver problemas simples
- ✅ Escalar casos complejos

#### Acceso a Funcionalidades
- ✅ Información de Clientes
- ✅ Registro de Llamadas
- ✅ Creación de Tickets
- ✅ Base de Conocimiento
- ✅ Reportes básicos
- ⚠️ Acceso limitado a datos

#### Ejemplo
```bash
php artisan roles:assign 25 callcenter-agent
# Usuario atiende Call Center
```

---

### 8. Personal de Inventario (`inventory-staff`)

**Label:** Personal de Inventario
**Color:** <span style="color: #CC99FF;">■</span> Púrpura Claro
**Prioridad:** Media
**Perfil:** Inventory & Warehouse

#### Descripción
Actualiza inventario. Realiza movimientos de stock bajo supervisión del gerente de inventario.

#### Responsabilidades
- ✅ Registrar movimientos de stock
- ✅ Crear recuentos de inventario
- ✅ Registrar entradas de productos
- ✅ Registrar salidas de productos
- ✅ Verificar cantidad de productos
- ✅ Reportar discrepancias
- ⚠️ Acceso limitado a reportes
- ⚠️ No puede crear productos

#### Acceso a Funcionalidades
- ✅ Movimientos de Stock
- ✅ Recuentos
- ✅ Consulta de Productos
- ✅ Reportes básicos
- ⚠️ No puede eliminar datos

#### Ejemplo
```bash
php artisan roles:assign 30 inventory-staff
# Usuario actualiza Inventario
```

---

### 9. Personal de Tienda (`shop-staff`)

**Label:** Personal de Tienda
**Color:** <span style="color: #FFCC00;">■</span> Amarillo
**Prioridad:** Media
**Perfil:** Shop

#### Descripción
Asiste en operaciones de tienda. Realiza ventas y atención al cliente bajo supervisión del gerente de tienda.

#### Responsabilidades
- ✅ Registrar ventas
- ✅ Consultar inventario
- ✅ Procesar cobros
- ✅ Ayudar a clientes
- ✅ Empacar productos
- ✅ Reportar problemas
- ⚠️ Acceso limitado a reportes
- ⚠️ No puede anular ventas

#### Acceso a Funcionalidades
- ✅ Módulo de Ventas
- ✅ Consulta de Inventario
- ✅ Caja
- ✅ Información de Clientes
- ⚠️ Acceso limitado a reportes

#### Ejemplo
```bash
php artisan roles:assign 35 shop-staff
# Usuario asiste en Tienda
```

---

## 🔘 Roles Administrativos

### 10. Administrativo (`administrative`)

**Label:** Administrativo
**Color:** <span style="color: #666666;">■</span> Gris
**Prioridad:** Media
**Perfil:** Administrative

#### Descripción
Realiza tareas administrativas. Responsable de documentación, trámites y procesos administrativos del sistema.

#### Responsabilidades
- ✅ Gestionar documentos
- ✅ Gestionar archivos
- ✅ Documentación de correspondencia
- ✅ Realizar trámites administrativos
- ✅ Mantener registros
- ✅ Generar reportes administrativos
- ⚠️ Acceso limitado a datos operacionales

#### Acceso a Funcionalidades
- ✅ Gestión de Documentos
- ✅ Archivo
- ✅ Correspondencia
- ✅ Reportes administrativos
- ⚠️ No acceso a operaciones

#### Ejemplo
```bash
php artisan roles:assign 40 administrative
# Usuario realiza tareas administrativas
```

---

## 📊 Matriz de Acceso

### Por Perfil

```
┌──────────────────────┬────────┬────────┬──────────┬──────────┬──────┬────────────────┐
│ Perfil               │ Manager│ Call   │ Inventory│ Warehouse│ Shop │ Administrative │
├──────────────────────┼────────┼────────┼──────────┼──────────┼──────┼────────────────┤
│ super-admin          │   ✅   │   ✅   │    ✅    │    ✅    │  ✅  │       ✅        │
│ admin                │   ✅   │   ✅   │    ✅    │    ✅    │  ✅  │       ✅        │
│ manager              │   ✅   │   ⚠️   │    ⚠️    │    ⚠️    │  ⚠️  │       ⚠️        │
│ callcenter-manager   │   ⚠️   │   ✅   │    ⚠️    │    ⚠️    │  ⚠️  │       ⚠️        │
│ callcenter-agent     │   ⚠️   │   ✅   │    ⚠️    │    ⚠️    │  ⚠️  │       ⚠️        │
│ inventory-manager    │   ⚠️   │   ⚠️   │    ✅    │    ✅    │  ⚠️  │       ⚠️        │
│ inventory-staff      │   ⚠️   │   ⚠️   │    ✅    │    ✅    │  ⚠️  │       ⚠️        │
│ shop-manager         │   ⚠️   │   ⚠️   │    ⚠️    │    ⚠️    │  ✅  │       ⚠️        │
│ shop-staff           │   ⚠️   │   ⚠️   │    ⚠️    │    ⚠️    │  ✅  │       ⚠️        │
│ administrative       │   ⚠️   │   ⚠️   │    ⚠️    │    ⚠️    │  ⚠️  │       ✅        │
└──────────────────────┴────────┴────────┴──────────┴──────────┴──────┴────────────────┘

Legend:
  ✅ = Acceso completo
  ⚠️  = Acceso limitado (solo lectura o funciones específicas)
  ❌ = Sin acceso
```

---

## 🔐 Permisos Detallados

### Permisos por Rol

| Rol | Total Permisos | Ejemplos |
|-----|---|---|
| super-admin | Todos (45+) | users.create, roles.edit, system.config, ... |
| admin | Todos (45+) | users.create, roles.edit, reportes.view, ... |
| manager | ~15 | users.view, users.create, reportes.manager, ... |
| callcenter-manager | ~10 | calls.view, agents.manage, reportes.callcenter, ... |
| callcenter-agent | ~5 | calls.create, customers.view, tickets.create, ... |
| inventory-manager | ~12 | products.manage, stock.manage, inventory.report, ... |
| inventory-staff | ~8 | stock.update, movements.create, inventory.view, ... |
| shop-manager | ~14 | sales.manage, customers.manage, caja.manage, ... |
| shop-staff | ~8 | sales.create, inventory.view, payments.process, ... |
| administrative | ~6 | documents.manage, files.manage, reports.admin, ... |

---

## 📌 Recomendaciones de Asignación

### Para Empresa Pequeña
```bash
# Dueño
php artisan roles:assign 1 super-admin

# Empleados
php artisan roles:assign 2 shop-manager
php artisan roles:assign 3 inventory-manager
php artisan roles:assign 4 administrative
```

### Para Empresa Mediana
```bash
# Gerencia
php artisan roles:assign 1 super-admin
php artisan roles:assign 2 admin
php artisan roles:assign 3 manager

# Operaciones
php artisan roles:assign 4 shop-manager
php artisan roles:assign 5 inventory-manager
php artisan roles:assign 6 callcenter-manager

# Personal
php artisan roles:assign 7 shop-staff
php artisan roles:assign 8 inventory-staff
php artisan roles:assign 9 callcenter-agent
php artisan roles:assign 10 administrative
```

### Para Empresa Grande
```bash
# Administración
php artisan roles:assign 1 super-admin
php artisan roles:assign 2 admin

# Gerencia
php artisan roles:assign 3 manager
php artisan roles:assign 4 shop-manager
php artisan roles:assign 5 inventory-manager
php artisan roles:assign 6 callcenter-manager

# Personal múltiple de cada área
php artisan roles:assign 7 shop-staff
php artisan roles:assign 8 shop-staff
php artisan roles:assign 9 inventory-staff
php artisan roles:assign 10 inventory-staff
php artisan roles:assign 11 callcenter-agent
php artisan roles:assign 12 callcenter-agent
php artisan roles:assign 13 callcenter-agent
php artisan roles:assign 14 administrative
```

---

## 🔄 Cambiar Rol de Usuario

Para cambiar el rol de un usuario existente:

```bash
# Ver roles actuales
php artisan roles:list --user=5

# Cambiar rol (reemplaza el anterior)
php artisan roles:assign 5 manager

# O en código (Laravel Tinker)
php artisan tinker
>>> $user = User::find(5)
>>> $user->syncRoles(['manager']) # Reemplaza todos los roles
>>> $user->getRoleNames()
```

---

## ⚠️ Notas Importantes

1. **Un usuario puede tener múltiples roles** (aunque no recomendado)
   ```php
   $user->syncRoles(['manager', 'admin']); // Múltiples roles
   ```

2. **Los roles heredan automáticamente permisos**
   - No hay que asignar permisos individualmente a usuarios
   - Solo asignar roles

3. **Super-admin tiene todos los permisos**
   - Puede hacer cualquier cosa en el sistema
   - Úsalo solo para administradores del sistema

4. **Los cambios de rol son inmediatos**
   - No requiere reinicio
   - El usuario ve los cambios en la siguiente acción

5. **La auditoría registra cambios de rol**
   - Quién asignó qué rol y cuándo
   - Útil para compliance y auditoría

---

## 📞 Soporte

Para dudas sobre asignación de roles:
1. Ver descripciones de roles arriba
2. Ejecutar: `php artisan roles:list`
3. Ver matriz de acceso por perfil
4. Consultar documentación: `ROLES_SETUP_GUIDE.md`
