# Navigation Component Architecture

## 🏗️ Flujo de Datos

```
┌─────────────────────────────────────────────────────────┐
│                    User Request                         │
│                  (GET /dashboard)                       │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
        ┌────────────────────────┐
        │  Laravel Route Handler │
        │   (ThemeController)    │
        └────────────┬───────────┘
                     │
                     ▼
    ┌───────────────────────────────────┐
    │    NavService::getNavDataForUser() │
    │    (Backend - Todas las lógicas)   │
    └────────────┬──────────────────────┘
                 │
    ┌────────────┴──────────────┬──────────────────┐
    │                           │                  │
    ▼                           ▼                  ▼
┌──────────────┐    ┌────────────────────┐    ┌──────────┐
│ getMiniItems │    │ getSidebarsForUser │    │findActive│
│  ForUser()   │    │                    │    │SidebarId │
└──────────────┘    └────────────────────┘    └──────────┘
    │                           │                  │
    │        filtrados por      │        análisis │
    │        permisos del       │        de ruta  │
    │        usuario            │        actual   │
    │                           │                 │
    └───────────────┬───────────┴────────────────┘
                    │
                    ▼
    ┌──────────────────────────────────┐
    │  Retorna array procesado:        │
    │  [                               │
    │   'miniItems' => Collection,     │
    │   'sidebars' => array,           │
    │   'activeSidebarId' => string    │
    │  ]                               │
    └────────────┬─────────────────────┘
                 │
                 ▼
    ┌──────────────────────────────────┐
    │   Pasar a Template (nav.blade)   │
    │   Destructuring simplificado     │
    └────────────┬─────────────────────┘
                 │
                 ▼
    ┌──────────────────────────────────┐
    │  Template HTML Rendering         │
    │  (Solo loops, sin lógica)        │
    └────────────┬─────────────────────┘
                 │
                 ▼
    ┌──────────────────────────────────┐
    │   HTML Enviado al Cliente        │
    │   con marca CSS 'selected' en    │
    │   el sidebar activo              │
    └────────────┬─────────────────────┘
                 │
                 ▼
    ┌──────────────────────────────────┐
    │  sidebar-nav.js carga en         │
    │  el navegador del cliente        │
    └────────────┬─────────────────────┘
                 │
                 ▼
    ┌──────────────────────────────────┐
    │  JavaScript Initialization       │
    │  - initializeSidebar()           │
    │  - handleSubmenuClicks()         │
    │  - detectActiveLink()            │
    └────────────┬─────────────────────┘
                 │
                 ▼
    ┌──────────────────────────────────┐
    │  Navegación Funcional en Cliente │
    │  Interacciones: clicks, toggles  │
    │  Persistencia: localStorage      │
    └──────────────────────────────────┘
```

## 🔄 Ciclo Completo: Ejemplo Práctico

### Escenario: Usuario Admin ve dashboard

```
1. REQUEST
   GET /admin/dashboard
   User: John (role: super-admin)

2. BACKEND - NavService::getNavDataForUser()

   a) getMiniItemsForUser() → Filtra por permisos
      Resultado: [
          { id: 'admin', icon: 'fa-cog', tooltip: 'Administración' },
          { id: 'users', icon: 'fa-users', tooltip: 'Usuarios' },
          { id: 'reports', icon: 'fa-chart', tooltip: 'Reportes' }
      ]

   b) getSidebarsForUser() → Obtiene sidebars accesibles
      Resultado: {
          'admin': {
              'sections': [
                  {
                      'title': 'Configuración',
                      'items': [
                          { route: 'admin.settings', label: 'Configuración', icon: 'fa-cog' },
                          { route: 'admin.users', label: 'Usuarios', icon: 'fa-users' },
                          { route: 'admin.roles', label: 'Roles', icon: 'fa-lock' }
                      ]
                  }
              ]
          },
          'users': { ... },
          'reports': { ... }
      }

   c) findActiveSidebarForUser() → Compara rutas
      Current route: admin.settings.index
      → Busca item con route que coincida
      → Encuentra: admin.settings*
      → Retorna: 'admin'

3. TEMPLATE RENDERING
   @php
       ['miniItems' => $miniItems, 'sidebars' => $allSidebars, 'activeSidebarId' => 'admin']
           = NavService::getNavDataForUser();
   @endphp

   HTML resultante:
   <li class="mini-nav-item selected" id="mini-admin">
       <i class="fa fa-cog"></i>
   </li>
   <nav class="sidebar-nav d-block" id="menu-right-admin">
       <li><a href="/admin/settings" class="sidebar-link active">Configuración</a></li>
       ...
   </nav>

4. FRONTEND - JavaScript
   - Detecta que 'admin' está marcado como 'selected'
   - Muestra el sidebar 'admin'
   - Detecta que 'Configuración' es el link activo
   - Lo marca con clase 'active' (rojo/azul)
   - Guarda preferencia en localStorage

5. USER INTERACTION
   - Usuario hace click en 'Usuarios'
   - JavaScript dispara toggleSidebar('users', 'users-mini')
   - Oculta sidebar 'admin'
   - Muestra sidebar 'users'
   - Guarda en localStorage
```

## 🔐 Validación de Permisos - Flujo

```
NavService::userCanAccessItem($item, $user)

┌──────────────────────────┐
│   Item del Menú          │
│   {                      │
│     route: 'users.edit', │
│     permission: 'users.edit|users.admin' ← Múltiples permisos (OR)
│   }                      │
└────────┬─────────────────┘
         │
         ▼
┌──────────────────────────────┐
│ ¿Está el campo 'permission'? │
└─────────┬──────────────────┬─┘
          │ NO               │ SÍ
          ▼                  ▼
      ✅ PERMITIR      ┌──────────────────────┐
                       │ Parsear permisos     │
                       │ Split por '|'        │
                       │ ['users.edit',       │
                       │  'users.admin']      │
                       └────────┬─────────────┘
                                │
                                ▼
                       ┌──────────────────────┐
                       │ For each permission: │
                       │ $user->can($perm)?   │
                       └────────┬─────────────┘
                                │
                    ┌───────────┴───────────┐
                    │ SÍ encontrado         │ NO encontrado
                    ▼                       ▼
                 ✅ PERMITIR          ⛔ DENEGAR
```

## 📊 Estructura de Datos

### Salida de getNavDataForUser()

```php
[
    'miniItems' => Collection {
        [0] => [
            'id' => 'admin',
            'icon' => 'fa-duotone fa-cog',
            'tooltip' => 'Administración del Sistema',
            'sidebar_id' => 'admin',
            'order' => 1
        ],
        [1] => [
            'id' => 'users',
            'icon' => 'fa-duotone fa-users',
            'tooltip' => 'Gestión de Usuarios',
            'sidebar_id' => 'users',
            'order' => 2
        ],
        // ... más items
    },

    'sidebars' => [
        'admin' => [
            'sections' => [
                [
                    'title' => 'Configuración General',
                    'items' => [
                        [
                            'route' => 'admin.settings.general',
                            'label' => 'Configuración',
                            'icon' => 'fa-duotone fa-gear',
                            'permission' => 'admin.settings.view'
                        ],
                        // ... más items
                    ]
                ],
                [
                    'title' => 'Seguridad',
                    'items' => [
                        [
                            'route' => 'admin.security.logs',
                            'label' => 'Logs de Seguridad',
                            'icon' => 'fa-duotone fa-shield',
                            'permission' => 'admin.security.view'
                        ]
                    ]
                ]
            ]
        ],
        'users' => [
            // ... estructura similar
        ]
    ],

    'activeSidebarId' => 'admin'  // Determinado por ruta actual
]
```

## 🛡️ Seguridad

### Multi-nivel de Protección

```
Nivel 1: Backend (NavService)
├─ getMiniItemsForUser() filtra por permisos
├─ getSidebarsForUser() filtra por permisos
├─ userCanAccessItem() valida cada item
└─ Resultado: Solo items permitidos llegan al template

Nivel 2: Template (nav.blade.php)
├─ @if($canAccessItem) - Re-validación
├─ No renderiza items sin permisos
└─ HTML nunca expone items restringidos

Nivel 3: Frontend (sidebar-nav.js)
├─ Validación de elementos DOM
├─ Manejo seguro de eventos
└─ Sin lógica de permisos (delegado al backend)

Nivel 4: Servidor
├─ Route middleware verifica sesión
├─ Controller re-valida antes de responder
└─ Nunca confía en datos del cliente
```

## 🎯 Flujo de Determinación del Sidebar Activo

```
findActiveSidebarForUser($sidebars, $user)

FOR each sidebar {
    IF sidebar has 'sections' {
        FOR each section {
            FOR each item {
                IF userCanAccessItem(item, user) {
                    IF request()->routeIs(item.route . '*') {
                        RETURN sidebar.id  ← Encontrado
                    }
                }
            }
        }
    } ELSE {
        // Legacy structure
        FOR each item {
            IF userCanAccessItem(item, user) {
                IF request()->routeIs(item.route . '*') {
                    RETURN sidebar.id  ← Encontrado
                }
            }
        }
    }
}

IF no sidebar found {
    // Fallback: usar el primero disponible
    RETURN first(sidebars).id
}
```

## 💡 Ejemplos de Rutas Que Coinciden

```
Ruta configurada en item: 'admin.users'
Parámetro en comparación: 'admin.users*'

Solicitudes que COINCIDEN:
✅ /admin/users              → admin.users
✅ /admin/users/1            → admin.users.show
✅ /admin/users/1/edit       → admin.users.edit
✅ /admin/users/create       → admin.users.create
✅ /admin/users/import       → admin.users.import

Solicitudes que NO coinciden:
❌ /admin                    → admin.dashboard
❌ /admin/roles              → admin.roles
❌ /users                    → users.index (otra ruta)
```

---

**Propósito**: Documento de referencia arquitectónica
**Versión**: 1.0
**Actualizado**: 2026-01-19
