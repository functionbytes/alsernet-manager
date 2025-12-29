# 🗂️ ARQUITECTURA DE RUTAS, CONTROLADORES Y CRUDs POR PERFILES

**Proyecto:** WebAdmin - A-Álvarez
**Framework:** Laravel 11.42
**Fecha:** 2025-11-17
**Enfoque:** Ruteo modular basado en perfiles (Roles)

---

## 📑 TABLA DE CONTENIDOS

1. [Descripción General](#descripción-general)
2. [Jerarquía de Perfiles/Roles](#jerarquía-de-perfilesroles)
3. [Estructura de Perfiles](#estructura-de-perfiles)
4. [Archivos de Rutas](#archivos-de-rutas)
5. [Sistema de Middlewares](#sistema-de-middlewares)
6. [Generación de CRUDs](#generación-de-cruds)
7. [Controladores por Módulo](#controladores-por-módulo)
8. [Flujos Complejos](#flujos-complejos)
9. [Seguridad y Autorización](#seguridad-y-autorización)
10. [Ejemplo Práctico Paso a Paso](#ejemplo-práctico-paso-a-paso)

---

## 🎯 Descripción General

El proyecto **webadmin** implementa un sistema de **ruteo modular basado en roles** donde cada perfil tiene:

- ✅ **Archivo de rutas propio** (`routes/{perfil}.php`)
- ✅ **Set de controladores especializados** (`app/Http/Controllers/{Perfil}/`)
- ✅ **Middleware de autenticación y autorización**
- ✅ **Permisos granulares por recurso y acción**
- ✅ **CRUDs explícitos** (no usa Route::resource)

### Características Clave:

| Característica | Detalles |
|---|---|
| **Total de Rutas** | 200+ distribuidas en 5 perfiles |
| **Perfiles** | Manager, CallCenter, Inventories, Administratives, Shop |
| **Controladores** | 120+ organizados por módulo |
| **Middleware** | 4 principales + 3 especializados |
| **Permiso Sistema** | Spatie/laravel-permission (granular) |
| **HTTP Methods** | GET (visualizar/eliminar), POST (crear/actualizar) |
| **Identificadores** | UID (slug) en lugar de ID numérico |

---

## 👥 Jerarquía de Perfiles/Roles

```
┌────────────────────────────────────────────────────────┐
│         SUPER ADMIN (Super Administrador)              │
│    ✓ Acceso total a todos los módulos                 │
│    ✓ Puede crear roles y permisos                     │
│    ✓ Gestiona usuarios y configuración global         │
└──────────────────────┬─────────────────────────────────┘
                       │
         ┌─────────────┼─────────────┐
         │             │             │
    ┌────▼────┐   ┌───▼───┐   ┌────▼────┐
    │  ADMIN  │   │CLIENT │   │  API    │
    └────┬────┘   └───────┘   └────┬────┘
         │                          │
    ┌────┴──────────────┬────────────┴────────────────┐
    │                   │                             │
┌──▼──────────┐  ┌──────▼──────┐  ┌────────▼─────────┐
│  MANAGER    │  │ CALLCENTER  │  │   INVENTORIES    │
│  (Campaigns,│  │ (Support,   │  │  (Stock Control) │
│  Products,  │  │  Returns)   │  │                  │
│  Automations)   │             │  │                  │
└──┬──────────┘  └──┬───────────┘  └───┬──────────────┘
   │                │                  │
   │            ┌───▼──────┐      ┌────▼─────┐
   │            │CallCenter │      │Inventory │
   │            │Manager    │      │Manager   │
   │            └───┬───────┘      └─────┬────┘
   │                │                    │
   │            ┌───▼──────┐      ┌──────▼──────┐
   │            │CallCenter │      │Inventory    │
   │            │Agent      │      │Staff        │
   │            └───────────┘      └─────────────┘
   │
┌──▼─────────────────────┬────────────────────┐
│                        │                    │
│   ┌──────────────┐     │    ┌─────────┐     │
│   │ADMINISTRATIVE │    │    │  SHOP   │     │
│   │(Orders, Docs)│    │    │(Store)  │     │
│   └──────────────┘    │    └────┬────┘     │
│                        │         │          │
│                        │    ┌────▼─────┐    │
│                        │    │Shop Staff │    │
│                        │    └───────────┘    │
```

### Matriz de Acceso

| Rol | Manager | CallCenter | Inventories | Administrative | Shop |
|-----|---------|-----------|-------------|----------------|------|
| **Super Admin** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Manager** | ✅ | ❌ | ❌ | ❌ | ❌ |
| **CallCenter Manager** | ❌ | ✅ | ❌ | ❌ | ❌ |
| **CallCenter Agent** | ❌ | ✅ (limitado) | ❌ | ❌ | ❌ |
| **Inventory Manager** | ❌ | ❌ | ✅ | ❌ | ❌ |
| **Admin (General)** | ✅ | ✅ | ✅ | ✅ | ✅ |

---

## 🏗️ Estructura de Perfiles

### Perfil 1: MANAGER (Gestión Central)

**Archivo de Rutas:** `routes/managers.php`
**Prefijo:** `/manager`
**Middleware:** `auth` + `check.roles.permissions:manager`
**Controladores:** 63 (en `app/Http/Controllers/Managers/`)

**Módulos incluidos:**
- 🏪 **Shops** (Tiendas) - 3 controladores
- 📦 **Products** (Productos) - 4 controladores
- 📧 **Campaigns** (Campañas Email) - 1 controlador con 150+ métodos
- 🎫 **Tickets** (Soporte) - 6 controladores
- 👤 **Roles & Permissions** - 2 controladores
- ⚙️ **Settings** - 11 controladores
- 🤖 **Automations** - 1 controlador con 50 métodos
- 📋 **Subscribers** - 5 controladores
- 📧 **Maillists** - 3 controladores
- 🖼️ **Templates** - 1 controlador
- 📦 **Inventaries** - 1 controlador
- ❓ **FAQs** - 2 controladores
- 💬 **Livechat** - 2 controladores
- 👥 **Users** - 4 controladores
- 🎯 **Events** - 1 controlador
- 🔔 **Notifications** - 1 controlador
- 📊 **Pulse** - Monitoreo (1 ruta)

**Rutas por Módulo:**

```
/manager/
├── /campaigns              (70+ rutas)
├── /automations            (50+ rutas)
├── /maillists              (80+ rutas)
├── /tickets                (25+ rutas)
├── /products               (15+ rutas)
├── /shops                  (20+ rutas)
├── /subscribers            (30+ rutas)
├── /templates              (20+ rutas)
├── /roles                  (10 rutas)
├── /permissions            (10 rutas)
├── /settings               (20+ rutas)
└── /inventaries            (15+ rutas)
```

---

### Perfil 2: CALLCENTER (Centro de Contacto)

**Archivo de Rutas:** `routes/callcenters.php`
**Prefijo:** `/callcenter`
**Middleware:** `auth` + `check.roles.permissions:callcenter`
**Controladores:** 38 (en `app/Http/Controllers/Callcenters/`)

**Módulos incluidos:**
- 🔄 **Returns** (Devoluciones) - 7 controladores especializados
- 🎫 **Tickets** - 2 controladores
- ❓ **FAQs** - 2 controladores
- 👤 **Users** - 5 controladores
- ⚙️ **Settings** - 3 controladores

**Rutas por Módulo:**

```
/callcenter/
├── /returns                (50+ rutas complejas)
│   ├── CRUD básico
│   ├── Validación de órdenes
│   ├── Procesamiento
│   ├── Gestión de estado
│   ├── Comunicación
│   ├── Pagos
│   └── Logística
├── /tickets                (10+ rutas)
├── /faqs                   (5+ rutas)
└── /settings               (10+ rutas)
```

**Nota Especial - Sistema de Devoluciones:**

El módulo Returns es el más complejo con flujos de 14+ pasos:
1. Validar orden en ERP
2. Crear solicitud
3. Seleccionar productos
4. Revisar
5. Confirmar
6. Aprobar/Rechazar
7. Asignar staff
8. Agregar comentarios
9. Subir archivos
10. Procesar pago
11. Generar etiqueta de envío
12. Seguimiento logístico
13. Escaneo de código de barras
14. Cierre/Completación

---

### Perfil 3: INVENTORIES (Gestión de Inventario)

**Archivo de Rutas:** `routes/warehouses.php`
**Prefijo:** `/inventarie`
**Middleware:** `auth` + `roles:inventaries`
**Controladores:** 9 (en `app/Http/Controllers/Inventaries/`)

**Módulos incluidos:**
- 📦 **Inventories** - Conteos de inventario
- 📍 **Locations** - Ubicaciones de almacén
- 🏷️ **Barcodes** - Códigos de barras

**Rutas por Módulo:**

```
/inventarie/
├── /inventaries            (15+ rutas)
│   ├── CRUD básico
│   ├── Conteo automático (ERP)
│   ├── Conteo manual
│   └── Validaciones por ubicación
├── /locations              (10+ rutas)
└── /barcodes               (5+ rutas)
```

**Características Especiales:**

- Modalidades de conteo: Automática (sincroniza con ERP) o Manual (ingreso manual)
- Validación por ubicación y producto
- Generación de reportes de discrepancias
- Lectura de códigos de barras

---

### Perfil 4: ADMINISTRATIVE (Administración)

**Archivo de Rutas:** `routes/administratives.php`
**Prefijo:** `/administrative`
**Middleware:** `auth` + `roles:administratives`
**Controladores:** 2 (en `app/Http/Controllers/Administratives/`)

**Módulos incluidos:**
- 📋 **Orders/Documents** - Gestión de órdenes y documentos
- 📊 **Dashboard** - Dashboard administrativo

**Rutas por Módulo:**

```
/administrative/
├── / (dashboard)           (1 ruta)
└── /orders                 (15+ rutas)
    ├── CRUD de órdenes
    ├── Gestión de archivos
    └── Reportes
```

---

### Perfil 5: SHOP (Tiendas E-commerce)

**Archivo de Rutas:** `routes/shops.php`
**Prefijo:** `/shop`
**Middleware:** `auth` + `check.roles.permissions:shop`
**Controladores:** 3 (en `app/Http/Controllers/Shops/`)

**Módulos incluidos:**
- ⚙️ **Settings** - Configuración de tienda
- 👥 **Subscribers** - Gestión de clientes/suscriptores

**Rutas por Módulo:**

```
/shop/
├── / (dashboard)           (1 ruta)
├── /settings               (5+ rutas)
└── /subscribers            (10+ rutas)
    ├── CRUD de suscriptores
    ├── Listas de emails
    └── Logs de actividad
```

---

## 📂 Archivos de Rutas

### routes/web.php - Base y Autenticación

```php
// Punto de entrada público
Route::get('/', [LoginController::class, 'showLoginForm'])->name('index');

// Autenticación
Route::post('/login', [LoginController::class, 'login'])->name('auth.login');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// Reset de contraseña
Route::group(['prefix' => 'password'], function () {
    Route::get('/reset', [ForgotPasswordController::class, 'showLinkRequest']);
    Route::post('/reset', [ResetPasswordController::class, 'reset']);
});

// Recursos públicos
Route::get('/files/{uid}/{name?}', [FileController::class, 'serve']);
Route::get('/thumbs/{uid}/{name?}', [FileController::class, 'thumbnail']);
Route::get('assets/{dirname}/{basename}', [AssetController::class, 'serve']);
```

### routes/managers.php - Perfil Manager

**Estructura Base:**

```php
Route::middleware(['auth', 'check.roles.permissions:manager'])->group(function () {
    Route::prefix('manager')->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('manager.dashboard');

        // Cada módulo agrupado por prefix
        Route::group(['prefix' => 'shops'], function () {
            // Rutas CRUD
        });

        Route::group(['prefix' => 'inventaries'], function () {
            // Rutas CRUD
        });

        // ... más módulos
    });
});
```

**Módulo Campaigns - Ejemplo Completo:**

```php
Route::group(['prefix' => 'campaigns'], function () {
    // CRUD básico
    Route::get('/', [CampaignsController::class, 'index'])
        ->name('manager.campaigns');
    Route::get('/create', [CampaignsController::class, 'create'])
        ->name('manager.campaigns.create');
    Route::post('/store', [CampaignsController::class, 'store'])
        ->name('manager.campaigns.store');
    Route::get('/view/{uid}', [CampaignsController::class, 'view'])
        ->name('manager.campaigns.view');
    Route::get('/edit/{uid}', [CampaignsController::class, 'edit'])
        ->name('manager.campaigns.edit');
    Route::patch('/{uid}/update', [CampaignsController::class, 'update'])
        ->name('manager.campaigns.update');
    Route::get('/destroy/{uid}', [CampaignsController::class, 'destroy'])
        ->name('manager.campaigns.destroy');

    // Configuración paso a paso
    Route::match(['get', 'post'], '/{uid}/setup',
        [CampaignsController::class, 'setup'])
        ->name('manager.campaigns.setup');
    Route::match(['get', 'post'], '/{uid}/template',
        [CampaignsController::class, 'template'])
        ->name('manager.campaigns.template');
    Route::match(['get', 'post'], '/{uid}/recipients',
        [CampaignsController::class, 'recipients'])
        ->name('manager.campaigns.recipients');
    Route::match(['get', 'post'], '/{uid}/schedule',
        [CampaignsController::class, 'schedule'])
        ->name('manager.campaigns.schedule');

    // Webhooks (20+ rutas)
    Route::get('/{uid}/webhooks', [CampaignsController::class, 'webhooks']);
    Route::post('/{uid}/webhooks/add', [CampaignsController::class, 'webhooksAdd']);
    Route::get('/{uid}/webhooks/list', [CampaignsController::class, 'webhooksList']);
    Route::match(['get', 'post'], '/webhooks/{webhook_uid}/edit',
        [CampaignsController::class, 'webhooksEdit']);
    Route::post('/webhooks/{webhook_uid}/delete',
        [CampaignsController::class, 'webhooksDelete']);
    Route::match(['get', 'post'], 'automation/{uid}/webhooks/{webhook_uid}/test',
        [CampaignsController::class, 'webhooksTest']);

    // Tracking y análisis
    Route::get('/{uid}/tracking-log', [CampaignsController::class, 'trackingLog']);
    Route::get('/{uid}/open-log', [CampaignsController::class, 'openLog']);
    Route::get('/{uid}/click-log', [CampaignsController::class, 'clickLog']);
    Route::get('/{uid}/bounce-log', [CampaignsController::class, 'bounceLog']);
    Route::get('/{uid}/feedback-log', [CampaignsController::class, 'feedbackLog']);
    Route::get('/{uid}/unsubscribe-log', [CampaignsController::class, 'unsubscribeLog']);

    // Análisis
    Route::get('/{uid}/chart24h', [CampaignsController::class, 'chart24h']);
    Route::get('/{uid}/chart', [CampaignsController::class, 'chart']);
    Route::get('/{uid}/overview', [CampaignsController::class, 'overview']);
    Route::get('/{uid}/links', [CampaignsController::class, 'links']);
});
```

### routes/callcenters.php - Perfil CallCenter

```php
Route::middleware(['auth', 'check.roles.permissions:callcenter'])->group(function () {
    Route::prefix('callcenter')->group(function () {

        Route::get('/', [DashboardController::class, 'dashboard'])
            ->name('callcenter.dashboard');

        // Módulo Returns (Devoluciones) - El más complejo
        Route::prefix('returns')->group(function () {
            // CRUD básico
            Route::get('/', [ReturnController::class, 'index']);
            Route::get('/create', [ReturnController::class, 'create']);
            Route::post('/store', [ReturnController::class, 'store']);
            Route::post('/update/{id}', [ReturnController::class, 'update']);
            Route::get('/edit/{uid}', [ReturnController::class, 'edit']);
            Route::get('/show/{id}', [ReturnController::class, 'show']);

            // Validación y procesamiento
            Route::post('/validateorder', [ReturnController::class, 'validateOrder']);
            Route::post('/proceed-to-generate', [ReturnController::class, 'proceedToGenerate']);
            Route::get('/generate/{uid}', [ReturnController::class, 'generate']);
            Route::post('/validate-inventaries', [ReturnController::class, 'validate']);
            Route::get('/available-inventaries/{orderId}', [ReturnController::class, 'getAvailableProducts']);

            // Flujo de devolución
            Route::get('/review/{returnId}', [ReturnsController::class, 'review']);
            Route::post('/confirm/{returnId}', [ReturnsController::class, 'confirm']);
            Route::get('/success/{returnId}', [ReturnsController::class, 'success']);

            // Gestión de estado
            Route::post('/{id}/status', [ReturnController::class, 'updateStatus']);
            Route::post('/{id}/approve', [ReturnController::class, 'approve']);
            Route::post('/{id}/reject', [ReturnController::class, 'reject']);
            Route::post('/{id}/assign', [ReturnController::class, 'assign']);
            Route::post('/{id}/cancel', [ReturnController::class, 'cancel']);

            // Comunicación
            Route::post('/{id}/discussion', [ReturnController::class, 'addDiscussion']);
            Route::post('/{id}/attachment', [ReturnController::class, 'uploadAttachment']);

            // Pagos
            Route::get('/{id}/payments', [ReturnController::class, 'getPayments']);
            Route::post('/{id}/payment', [ReturnController::class, 'addPayment']);

            // Documentos
            Route::get('/export', [ReturnController::class, 'export']);
            Route::get('/{id}/pdf', [ReturnController::class, 'downloadPDF']);
            Route::get('/document/{id}/download', [ReturnController::class, 'downloadDocument']);

            // Operaciones masivas
            Route::post('/bulk-update', [ReturnController::class, 'bulkUpdate']);

            // Logística
            Route::get('/{id}/tracking', [ReturnController::class, 'getTrackingStatus']);
            Route::post('/{id}/cancel-pickup', [ReturnController::class, 'cancelPickup']);
            Route::post('/carrier-time-slots', [ReturnController::class, 'getCarrierTimeSlots']);
            Route::post('/inpost-lockers', [ReturnController::class, 'getNearbyInPostLockers']);
            Route::post('/scan-barcode', [ReturnController::class, 'scanBarcode']);
        });

        // Otros módulos...
    });
});
```

---

## 🔒 Sistema de Middlewares

### 1. Middleware Principal: CheckRolesAndPermissions

**Ubicación:** `app/Http/Middleware/CheckRolesAndPermissions.php`

**Propósito:** Validación multinivel de acceso

```php
class CheckRolesAndPermissions
{
    /**
     * Flujo de validación:
     * 1. ¿Usuario autenticado?
     * 2. ¿Es super-admin? (acceso total)
     * 3. ¿Tiene rol permitido para este módulo?
     * 4. ¿Tiene permiso para esta acción específica?
     */
    public function handle(Request $request, Closure $next, $roleType = null)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // BYPASS: Super-admin tiene acceso total
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        // Mapeo de roles por módulo
        $roleMapping = [
            'manager'        => ['admin', 'manager'],
            'callcenter'     => ['callcenter-manager', 'callcenter-agent'],
            'inventarie'     => ['inventory-manager', 'inventory-staff'],
            'shop'           => ['shop-manager', 'shop-staff'],
            'administrative' => ['administrative'],
        ];

        // Verificar rol básico
        if ($roleType && isset($roleMapping[$roleType])) {
            if (!$user->hasAnyRole($roleMapping[$roleType])) {
                abort(403, 'No autorizado para este módulo');
            }
        }

        // Verificar permisos específicos
        $this->checkSpecificPermissions($request, $user, $roleType);

        return $next($request);
    }

    /**
     * Validar permisos por acción
     *
     * Mapeo automático:
     * - callcenter.returns.index    → returns.view
     * - callcenter.returns.approve  → returns.status.approve
     * - callcenter.returns.destroy  → returns.delete
     */
    private function checkSpecificPermissions(Request $request, $user, $roleType)
    {
        $routeName = $request->route()?->getName();
        if (!$routeName) return;

        // Extraer {resource}.{action}
        // De: callcenter.returns.approve
        // Extraer: returns, approve

        $internalRoute = str($routeName)->after("{$roleType}.")->toString();
        $segments = explode('.', $internalRoute);

        $resource = $segments[0] ?? null;
        $action = $segments[1] ?? null;

        // Mapeo de acciones a permisos
        $actionToPermission = [
            'index'      => 'view',
            'show'       => 'view',
            'create'     => 'create',
            'store'      => 'create',
            'edit'       => 'update',
            'update'     => 'update',
            'destroy'    => 'delete',
            'approve'    => 'status.approve',
            'reject'     => 'status.reject',
            'assign'     => 'assign',
        ];

        $suffix = $actionToPermission[$action] ?? $action;
        $permission = "{$resource}.{$suffix}";

        if (!$user->can($permission)) {
            abort(403, "No tienes permiso: {$permission}");
        }
    }
}
```

### 2. Middleware RoleMiddleware

```php
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check() || !$request->user()->hasRole($role)) {
            abort(403, 'No autorizado');
        }
        return $next($request);
    }
}
```

### 3. Middleware CheckReturnAccess

```php
class CheckReturnAccess
{
    /**
     * Valida que el usuario tenga acceso a esta devolución
     * - Super-admin: acceso total
     * - CallCenter manager/agent: acceso a devoluciones asignadas
     * - Cliente: solo su propia devolución
     */
    public function handle(Request $request, Closure $next)
    {
        $returnId = $request->route('id') ?? $request->route('uid');
        $return = ReturnRequest::findOrFail($returnId);
        $user = Auth::user();

        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        // Verificar propiedad o asignación
        if ($return->user_id === $user->id || $return->assigned_to === $user->id) {
            return $next($request);
        }

        abort(403, 'No tienes acceso a esta devolución');
    }
}
```

### 4. Middleware Authenticate

```php
class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
```

---

## 🔄 Generación de CRUDs

### Patrón Estándar CRUD

Todos los CRUDs siguen este patrón (sin usar `Route::resource()`):

```php
Route::group(['prefix' => '{resource}'], function () {
    // READ - Listar todos
    Route::get('/', [ResourceController::class, 'index'])
        ->name('module.resource');

    // CREATE - Mostrar formulario
    Route::get('/create', [ResourceController::class, 'create'])
        ->name('module.resource.create');

    // CREATE - Guardar en BD
    Route::post('/store', [ResourceController::class, 'store'])
        ->name('module.resource.store');

    // READ - Ver uno
    Route::get('/view/{uid}', [ResourceController::class, 'view'])
        ->name('module.resource.view');

    // UPDATE - Mostrar formulario
    Route::get('/edit/{uid}', [ResourceController::class, 'edit'])
        ->name('module.resource.edit');

    // UPDATE - Guardar cambios
    Route::post('/update', [ResourceController::class, 'update'])
        ->name('module.resource.update');

    // DELETE
    Route::get('/destroy/{uid}', [ResourceController::class, 'destroy'])
        ->name('module.resource.destroy');
});
```

### Estructura del Controlador CRUD

```php
namespace App\Http\Controllers\Managers;

use App\Models\Resource;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    /**
     * READ - Listar todos con paginación
     * GET /manager/resource/
     */
    public function index(Request $request)
    {
        $resources = Resource::query()
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', '%' . $request->search . '%')
            )
            ->when($request->sort, fn($q) =>
                $q->orderBy($request->sort, $request->order ?? 'asc')
            )
            ->paginate(paginationNumber());

        return view('managers.views.resource.index', [
            'resources' => $resources
        ]);
    }

    /**
     * CREATE - Mostrar formulario vacío
     * GET /manager/resource/create
     */
    public function create()
    {
        return view('managers.views.resource.create');
    }

    /**
     * CREATE - Guardar en BD
     * POST /manager/resource/store
     */
    public function store(Request $request)
    {
        // Validar
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:resources,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Guardar
        $resource = Resource::create($request->only('name', 'email'));

        // Log actividad
        activity()
            ->causedBy(Auth::user())
            ->performedOn($resource)
            ->log('created');

        return response()->json([
            'success' => true,
            'message' => 'Recurso creado correctamente',
            'data' => $resource
        ]);
    }

    /**
     * READ - Ver detalle de uno
     * GET /manager/resource/view/{uid}
     */
    public function view($uid)
    {
        $resource = Resource::where('uid', $uid)->findOrFail();

        return response()->json($resource);
    }

    /**
     * UPDATE - Mostrar formulario con datos
     * GET /manager/resource/edit/{uid}
     */
    public function edit($uid)
    {
        $resource = Resource::where('uid', $uid)->findOrFail();

        return view('managers.views.resource.edit', [
            'resource' => $resource
        ]);
    }

    /**
     * UPDATE - Guardar cambios en BD
     * POST /manager/resource/update
     */
    public function update(Request $request)
    {
        // Validar
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:resources,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:resources,email,' . $request->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Actualizar
        $resource = Resource::findOrFail($request->id);
        $resource->update($request->only('name', 'email'));

        // Log actividad
        activity()
            ->causedBy(Auth::user())
            ->performedOn($resource)
            ->log('updated');

        return response()->json([
            'success' => true,
            'message' => 'Recurso actualizado correctamente'
        ]);
    }

    /**
     * DELETE
     * GET /manager/resource/destroy/{uid}
     */
    public function destroy($uid)
    {
        $resource = Resource::where('uid', $uid)->findOrFail();

        // Verificar que no tenga dependencias
        if ($resource->has_dependencies()) {
            return redirect()->back()
                ->with('error', 'No puedes eliminar este recurso');
        }

        // Log actividad antes de eliminar
        activity()
            ->causedBy(Auth::user())
            ->performedOn($resource)
            ->log('deleted');

        $resource->delete();

        return redirect()->route('manager.resource')
            ->with('success', 'Recurso eliminado correctamente');
    }
}
```

### Notas Importantes del Patrón

1. **Sin usar Route::resource()**: Todo es explícito
2. **GET para destroy**: Facilita UX sin formularios especiales
3. **Response JSON**: APIs retornan JSON
4. **Responses View**: Formularios retornan vistas HTML
5. **Validación explícita**: Validator con mensajes personalizados
6. **Logs de actividad**: Cada CRUD loguea con Spatie Activity Log
7. **UID en lugar de ID**: Seguridad (no expone secuencia de IDs)

---

## 👨‍💼 Controladores por Módulo

### MANAGERS (63 controladores)

#### Shops (5 controladores)
```
app/Http/Controllers/Managers/
├── Shops/ShopsController.php              (CRUD tiendas)
├── Shops/Locations/LocationsController.php (CRUD ubicaciones)
├── Shops/Locations/BarcodeController.php  (Códigos de barras)
├── Shops/Locations/ReportController.php   (Reportes)
└── Shops/Locations/ResumenController.php  (Resumen)

Métodos principales:
- ShopsController:
  * index()      → Listar tiendas
  * create()     → Form crear
  * store()      → Guardar
  * edit()       → Form editar
  * update()     → Guardar cambios
  * view()       → Ver detalle
  * destroy()    → Eliminar
```

#### Products (4 controladores)
```
├── Products/ProductsController.php        (CRUD productos)
├── Products/BarcodeController.php         (Códigos de barras)
├── Products/LocationsController.php       (Ubicaciones)
└── Products/ReportController.php          (Reportes)

Métodos principales:
- ProductsController:
  * index()      → Listar productos
  * create()     → Form crear
  * store()      → Guardar
  * edit()       → Form editar
  * update()     → Guardar cambios
  * view()       → Ver detalle
  * destroy()    → Eliminar
```

#### Campaigns (1 controlador - 150+ métodos)
```
└── Campaigns/CampaignsController.php

Métodos principales:
- CRUD básico:
  * index()            → Listar campañas
  * create()           → Form crear
  * store()            → Guardar
  * view()             → Ver detalle
  * edit()             → Form editar
  * update()           → Guardar cambios
  * destroy()          → Eliminar

- Configuración (Wizard):
  * setup()            → Datos básicos
  * template()         → Plantilla de email
  * recipients()       → Destinatarios y segmentación
  * schedule()         → Programación de envío

- Webhooks (20+ métodos):
  * webhooks()         → Ver webhooks
  * webhooksAdd()      → Agregar webhook
  * webhooksList()     → Listar webhooks
  * webhooksEdit()     → Editar webhook
  * webhooksDelete()   → Eliminar webhook
  * webhooksTest()     → Testear webhook

- Tracking (6 métodos):
  * trackingLog()      → Log de envíos
  * openLog()          → Log de aperturas
  * clickLog()         → Log de clicks
  * bounceLog()        → Log de rechazos
  * feedbackLog()      → Log de feedback
  * unsubscribeLog()   → Log de desuscripciones

- Análisis (3 métodos):
  * chart()            → Gráficos de rendimiento
  * chart24h()         → Últimas 24 horas
  * overview()         → Resumen general
```

#### Tickets (6 controladores)
```
├── Tickets/TicketsController.php          (CRUD tickets)
├── Tickets/CategoriesController.php       (Categorías)
├── Tickets/PrioritiesController.php       (Prioridades)
├── Tickets/StatusController.php           (Estados)
├── Tickets/GroupsController.php           (Grupos de asignación)
└── Tickets/CannedsController.php          (Respuestas predefinidas)
```

#### Roles & Permissions (2 controladores)
```
├── Roles/RoleController.php               (CRUD roles)
└── Permissions/PermissionController.php   (CRUD permisos)

Métodos principales:
- RoleController:
  * index()            → Listar roles
  * create()           → Form crear
  * store()            → Guardar
  * edit()             → Form editar
  * update()           → Guardar cambios
  * destroy()          → Eliminar
  * permissions()      → Ver permisos del rol
  * updatePermissions()→ Asignar permisos
```

#### Settings (11 controladores)
```
├── Settings/SettingsController.php
├── Settings/EmailsSettingsController.php
├── Settings/TicketsSettingsController.php
├── Settings/HoursSettingsController.php
├── Settings/MantenanceSettingsController.php
├── Settings/LiveSettingsController.php
├── Settings/LangsController.php
├── Settings/CategoriesController.php
├── Settings/AnalyticsSettingsController.php
├── Settings/ContactsController.php
├── Settings/MetaSettingsController.php
└── Settings/PixelSettingsController.php
```

#### Automations (1 controlador - 50 métodos)
```
└── Automations/AutomationsController.php

Métodos principales:
- CRUD básico:
  * index()            → Listar automaciones
  * create()           → Form crear
  * store()            → Guardar
  * view()             → Ver detalle
  * edit()             → Form editar
  * update()           → Guardar cambios

- Builder visual (20+ métodos):
  * builder()          → Constructor visual
  * addTrigger()       → Agregar disparador
  * addAction()        → Agregar acción
  * addWait()          → Agregar espera
  * addCondition()     → Agregar condición
  * deleteNode()       → Eliminar nodo
  * testAutomation()   → Testear flujo
  * getPreview()       → Vista previa

- Ejecución:
  * publish()          → Publicar automación
  * pause()            → Pausar
  * resume()           → Reanudar
  * getStats()         → Estadísticas
  * getLogs()          → Logs de ejecución
```

#### Otros Módulos (30+ controladores más)
- **Subscribers** (5) - Gestión de clientes
- **Maillists** (3) - Listas de correo
- **Templates** (1) - Plantillas de email
- **Inventaries** (1) - Inventarios
- **Faqs** (2) - Preguntas frecuentes
- **Livechat** (2) - Chat en vivo
- **Users** (4) - Gestión de usuarios
- **Events** (1) - Eventos
- **Notifications** (1) - Notificaciones

---

### CALLCENTERS (38 controladores)

#### Returns (7 controladores especializados)
```
app/Http/Controllers/Callcenters/
├── Returns/ReturnsController.php           (CRUD + flujo completo)
├── Returns/ComponentController.php         (Componentes de devolución)
├── Returns/InspectionController.php        (Inspecciones)
├── Returns/PdfDocumentController.php       (Generación de PDFs)
├── Returns/ReturnCommunicationController.php (Comunicaciones)
├── Returns/ReturnCostController.php        (Cálculo de costos)
└── Returns/ReturnTrackingController.php    (Seguimiento logístico)

Métodos principales de ReturnsController:
- CRUD básico:
  * index()              → Listar devoluciones
  * create()             → Form crear
  * store()              → Guardar
  * show()               → Ver detalle
  * edit()               → Form editar
  * update()             → Guardar cambios
  * destroy()            → Eliminar

- Validación:
  * validateOrder()      → Validar orden en ERP
  * proceedToGenerate()  → Procesar y mostrar productos
  * generate()           → Generar devolución base
  * validateProducts()   → Validar productos
  * getAvailableProducts()→ Obtener productos devolvibles

- Flujo de devolución:
  * review()             → Revisar antes de confirmar
  * confirm()            → Confirmar devolución
  * success()            → Página de éxito

- Gestión de estado:
  * updateStatus()       → Cambiar estado
  * approve()            → Aprobar
  * reject()             → Rechazar
  * assign()             → Asignar a inspector
  * cancel()             → Cancelar

- Comunicación:
  * addDiscussion()      → Agregar comentario
  * uploadAttachment()   → Subir archivo

- Pagos:
  * getPayments()        → Listar pagos
  * addPayment()         → Agregar pago

- Documentos:
  * export()             → Exportar a Excel/CSV
  * downloadPDF()        → Descargar PDF
  * downloadDocument()   → Descargar documento

- Operaciones masivas:
  * bulkUpdate()         → Actualizar múltiples

- Logística:
  * getTrackingStatus()  → Estado de seguimiento
  * cancelPickup()       → Cancelar recogida
  * getCarrierTimeSlots()→ Horarios del transportista
  * getNearbyInPostLockers()→ Puntos de recogida cercanos
  * scanBarcode()        → Escanear código de barras
```

#### Tickets (2 controladores)
```
├── Tickets/TicketsController.php
└── Tickets/CommentsController.php

Métodos principales:
- TicketsController:
  * index()      → Listar tickets
  * create()     → Form crear
  * store()      → Guardar
  * edit()       → Form editar
  * view()       → Ver detalle

- CommentsController:
  * view()       → Ver comentarios
  * postComment()→ Agregar comentario
```

#### FAQs (2 controladores)
```
├── Faqs/FaqsController.php
└── Faqs/CategoriesController.php
```

#### Users (5 controladores)
```
├── Users/UsersController.php
├── Users/ActivitysController.php
├── Users/CertificatesController.php
├── Users/ManagementController.php
└── Users/ResultsController.php
```

#### Settings (3 controladores)
```
├── Settings/SettingsController.php
├── Settings/NotificationSettingsController.php
└── Settings/NotificationController.php
```

---

### INVENTARIES (9 controladores)

```
app/Http/Controllers/Inventaries/
├── Inventaries/InventariesController.php   (CRUD inventarios)
├── Inventaries/LocationsController.php     (Ubicaciones)
├── Locations/BarcodeController.php         (Códigos de barras)
├── Locations/LocationsController.php       (Más ubicaciones)
├── Locations/ProductsController.php        (Productos en ubicación)
├── Products/BarcodeController.php          (Códigos de barras de productos)
├── Products/LocationsController.php        (Ubicaciones de productos)
├── Products/ProductsController.php         (Productos)
└── DashboardController.php                 (Dashboard)

Métodos principales de InventariesController:
- CRUD básico:
  * index()         → Listar inventarios
  * create()        → Form crear
  * edit()          → Form editar
  * update()        → Guardar cambios
  * view()          → Ver detalle
  * destroy()       → Eliminar

- Operaciones especiales:
  * close()         → Cerrar inventario
  * arrange()       → Organizar items
  * content()       → Ver contenido
  * report()        → Generar reporte

- Validaciones:
  * validateLocation()   → Validar ubicación
  * validateProduct()    → Validar producto
  * validateGenerate()   → Validar generación

- Modalidades de conteo:
  * modalitie()         → Mostrar opciones
  * automatic()         → Conteo automático (ERP)
  * manual()            → Conteo manual
```

---

### ADMINISTRATIVES (2 controladores)

```
app/Http/Controllers/Administratives/
├── DashboardController.php                 (Dashboard)
└── Orders/DocumentsController.php          (CRUD documentos)

Métodos principales de DocumentsController:
- CRUD básico:
  * index()         → Listar documentos
  * create()        → Form crear
  * store()         → Guardar
  * edit()          → Form editar
  * update()        → Guardar cambios
  * view()          → Ver detalle
  * destroy()       → Eliminar

- Gestión de archivos:
  * storeFiles()    → Subir archivo
  * deleteFiles()   → Eliminar archivo
  * getFiles()      → Descargar archivo
```

---

### SHOPS (3 controladores)

```
app/Http/Controllers/Shops/
├── DashboardController.php                 (Dashboard)
├── Settings/SettingsController.php         (CRUD configuración)
└── Subscribers/SubscribersController.php   (CRUD suscriptores)

Métodos principales de SubscribersController:
- CRUD básico:
  * index()      → Listar suscriptores
  * create()     → Form crear
  * store()      → Guardar
  * edit()       → Form editar
  * update()     → Guardar cambios
  * view()       → Ver detalle

- Operaciones especiales:
  * lists()      → Listas del suscriptor
  * logs()       → Logs de actividad
```

---

## 🔄 Flujos Complejos

### Flujo 1: Devolución Completa (CallCenter)

```mermaid
1. CREATE - Crear solicitud
   GET  /callcenter/returns/create
   ↓
2. VALIDATE - Validar orden en ERP
   POST /callcenter/returns/validateorder
   ↓
3. GENERATE - Generar devolución base
   GET  /callcenter/returns/generate/{uid}
   ↓
4. SELECT - Seleccionar productos
   POST /callcenter/returns/proceed-to-generate
   ↓
5. STORE - Guardar devolución
   POST /callcenter/returns/store
   ↓
6. REVIEW - Revisar datos
   GET  /callcenter/returns/review/{returnId}
   ↓
7. CONFIRM - Confirmar
   POST /callcenter/returns/confirm/{returnId}
   ↓
8. APPROVE/REJECT - Aprobar o rechazar
   POST /callcenter/returns/{id}/approve
   POST /callcenter/returns/{id}/reject
   ↓
9. ASSIGN - Asignar a inspector
   POST /callcenter/returns/{id}/assign
   ↓
10. DISCUSS - Agregar comentarios
    POST /callcenter/returns/{id}/discussion
    ↓
11. ATTACH - Subir evidencia
    POST /callcenter/returns/{id}/attachment
    ↓
12. PAYMENT - Procesar reembolso
    POST /callcenter/returns/{id}/payment
    ↓
13. TRACK - Seguimiento logístico
    GET  /callcenter/returns/{id}/tracking
    ↓
14. PDF - Descargar documento
    GET  /callcenter/returns/{id}/pdf
```

**Middleware Aplicados en Cada Paso:**
- `auth` - Verificar autenticación
- `check.roles.permissions:callcenter` - Verificar rol
- `check.return.access` - Verificar acceso a devolución
- `permission:returns.{action}` - Verificar permiso específico

---

### Flujo 2: Campañas de Email (Manager)

```
1. CREATE - Crear campaña
   GET  /manager/campaigns/create
   POST /manager/campaigns/store
   ↓
2. TEMPLATE - Seleccionar template
   GET/POST /manager/campaigns/{uid}/template
   ↓
3. RECIPIENTS - Seleccionar destinatarios
   GET/POST /manager/campaigns/{uid}/recipients
   ↓
4. SETUP - Configurar asunto, remitente
   GET/POST /manager/campaigns/{uid}/setup
   ↓
5. WEBHOOKS - Agregar webhooks de tracking
   GET/POST /manager/campaigns/{uid}/webhooks
   POST     /manager/campaigns/{uid}/webhooks/add
   ↓
6. SCHEDULE - Programar envío
   GET/POST /manager/campaigns/{uid}/schedule
   ↓
7. SEND - Enviar campaña
   POST /manager/campaigns/{uid}/run
   ↓
8. TRACK - Monitorear resultados
   GET /manager/campaigns/{uid}/tracking-log
   GET /manager/campaigns/{uid}/open-log
   GET /manager/campaigns/{uid}/click-log
   ↓
9. ANALYZE - Ver análisis
   GET /manager/campaigns/{uid}/chart
   GET /manager/campaigns/{uid}/overview
```

---

### Flujo 3: Conteo de Inventario (Inventaries)

```
1. CREATE - Crear inventario
   GET  /inventarie/inventaries/create
   POST /inventarie/inventaries/store
   ↓
2. SELECT MODE - Elegir modalidad
   GET /inventarie/inventaries/locations/modalitie/{location}
   ├─ AUTOMATIC
   │  ├─ GET /inventarie/inventaries/locations/modalitie/automatic/{location}
   │  └─ (Sincroniza desde ERP)
   │
   └─ MANUAL
      ├─ GET /inventarie/inventaries/locations/modalitie/manual/{location}
      ├─ POST /inventarie/inventaries/locations/validate/location
      ├─ POST /inventarie/inventaries/locations/validate/product
      └─ (Ingreso manual de cantidades)
   ↓
3. VALIDATE - Validar por ubicación
   POST /inventarie/inventaries/locations/validate/location
   ↓
4. CLOSE LOCATION - Cerrar conteo
   POST /inventarie/inventaries/locations/close
   ↓
5. UPDATE - Guardar cambios
   POST /inventarie/inventaries/update
   ↓
6. REPORT - Generar reporte
   GET /inventarie/inventaries/report/{id}
```

---

## 🔐 Seguridad y Autorización

### Sistema de Permisos (Spatie/laravel-permission)

#### Estructura de Permisos

```
{recurso}.{acción}

Ejemplos:
returns.view              → Ver devoluciones
returns.create            → Crear devoluciones
returns.update            → Actualizar devoluciones
returns.delete            → Eliminar devoluciones
returns.status.approve    → Aprobar devoluciones
returns.status.reject     → Rechazar devoluciones
returns.assign            → Asignar devolución
returns.discussion.add    → Agregar comentarios
returns.attachment.upload → Subir archivos
returns.payment.add       → Agregar pagos
returns.export            → Exportar devoluciones

products.view
products.create
products.update
products.delete

campaigns.view
campaigns.create
campaigns.update
campaigns.send
campaigns.webhooks.manage
campaigns.tracking.view
```

#### Asignación de Permisos a Roles

**Usando el RoleController:**

```php
// 1. Acceder a la página de permisos del rol
GET /manager/roles/permissions/{roleId}

// 2. Seleccionar permisos a asignar
// (Vista interactiva con checkboxes)

// 3. Guardar asignación
POST /manager/roles/permissions/update
Body: {
    "id": 1,
    "permissions": [1, 3, 5, 7, ...]
}
```

#### Verificación de Permisos en Rutas

**Opción 1: Middleware en ruta**
```php
Route::post('/{id}/approve', [ReturnController::class, 'approve'])
    ->middleware('permission:returns.status.approve');
```

**Opción 2: Validación en controlador**
```php
public function approve(Request $request, $id)
{
    if (!Auth::user()->can('returns.status.approve')) {
        abort(403);
    }
    // ... código
}
```

**Opción 3: Directive en Blade**
```blade
@can('returns.status.approve')
    <button>Aprobar</button>
@endcan
```

---

### Niveles de Acceso

#### Nivel 1: Autenticación
- Middleware `auth` → ¿Usuario logueado?

#### Nivel 2: Rol Base
- Middleware `roles:role` → ¿Tiene rol permitido?
- Middleware `check.roles.permissions:type` → ¿Tiene rol del módulo?

#### Nivel 3: Permiso Específico
- Middleware `permission:resource.action` → ¿Puede hacer la acción?
- `$user->can('resource.action')` en controlador

#### Nivel 4: Pertenencia
- Middleware `check.return.access` → ¿Propietario/asignado?
- Validar que recurso pertenece a usuario

---

## 💡 Ejemplo Práctico Paso a Paso

### Scenario: Agregar Nueva Funcionalidad CRUD

**Requisito:** Crear un CRUD de "Carrriers" (Transportistas) en el módulo Manager

#### Paso 1: Crear el Modelo

```php
php artisan make:model Carrier -m

// app/Models/Carrier.php
class Carrier extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'country',
    ];
}
```

#### Paso 2: Crear Migración

```php
// database/migrations/XXXX_create_carriers_table.php
Schema::create('carriers', function (Blueprint $table) {
    $table->id();
    $table->string('uid')->unique();
    $table->string('name');
    $table->string('email');
    $table->string('phone');
    $table->string('address');
    $table->string('city');
    $table->string('country');
    $table->timestamps();
    $table->softDeletes();
});
```

```bash
php artisan migrate
```

#### Paso 3: Crear Controlador

```bash
php artisan make:controller Managers/Carriers/CarrierController
```

```php
// app/Http/Controllers/Managers/Carriers/CarrierController.php
namespace App\Http\Controllers\Managers\Carriers;

use App\Models\Carrier;
use Illuminate\Http\Request;

class CarrierController extends Controller
{
    public function index(Request $request)
    {
        $carriers = Carrier::query()
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', '%' . $request->search . '%')
            )
            ->paginate(paginationNumber());

        return view('managers.views.carriers.index', [
            'carriers' => $carriers
        ]);
    }

    public function create()
    {
        return view('managers.views.carriers.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:carriers',
            'email' => 'required|email|unique:carriers',
            'phone' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'country' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $carrier = Carrier::create([
            'uid' => Str::uuid(),
            ...$request->only('name', 'email', 'phone', 'address', 'city', 'country')
        ]);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($carrier)
            ->log('created');

        return response()->json([
            'success' => true,
            'message' => 'Transportista creado'
        ]);
    }

    public function edit($uid)
    {
        $carrier = Carrier::where('uid', $uid)->firstOrFail();
        return view('managers.views.carriers.edit', ['carrier' => $carrier]);
    }

    public function update(Request $request)
    {
        $carrier = Carrier::findOrFail($request->id);
        $carrier->update($request->only('name', 'email', 'phone', 'address', 'city', 'country'));

        activity()
            ->causedBy(Auth::user())
            ->performedOn($carrier)
            ->log('updated');

        return response()->json(['success' => true]);
    }

    public function destroy($uid)
    {
        $carrier = Carrier::where('uid', $uid)->firstOrFail();

        activity()
            ->causedBy(Auth::user())
            ->performedOn($carrier)
            ->log('deleted');

        $carrier->delete();
        return redirect()->route('manager.carriers');
    }
}
```

#### Paso 4: Agregar Rutas

```php
// routes/managers.php

Route::group(['prefix' => 'carriers'], function () {
    Route::get('/', [CarrierController::class, 'index'])
        ->name('manager.carriers');
    Route::get('/create', [CarrierController::class, 'create'])
        ->name('manager.carriers.create');
    Route::post('/store', [CarrierController::class, 'store'])
        ->name('manager.carriers.store');
    Route::get('/edit/{uid}', [CarrierController::class, 'edit'])
        ->name('manager.carriers.edit');
    Route::post('/update', [CarrierController::class, 'update'])
        ->name('manager.carriers.update');
    Route::get('/destroy/{uid}', [CarrierController::class, 'destroy'])
        ->name('manager.carriers.destroy');
});
```

#### Paso 5: Crear Permisos

```php
// database/seeders/PermissionSeeder.php

Permission::create(['name' => 'carriers.view']);
Permission::create(['name' => 'carriers.create']);
Permission::create(['name' => 'carriers.update']);
Permission::create(['name' => 'carriers.delete']);

// O crear manualmente en la BD o través de:
// GET /manager/permissions/create
// POST /manager/permissions/store
```

#### Paso 6: Asignar Permisos a Roles

```php
// database/seeders/RoleSeeder.php

$managerRole = Role::where('name', 'manager')->first();
$managerRole->givePermissionTo([
    'carriers.view',
    'carriers.create',
    'carriers.update',
    'carriers.delete',
]);

// O a través de UI:
// GET /manager/roles/permissions/{roleId}
```

#### Paso 7: Crear Vistas

```blade
{{-- resources/views/managers/views/carriers/index.blade.php --}}
@extends('layouts.core')

@section('content')
<div class="container">
    <h1>Transportistas</h1>

    @can('carriers.create')
    <a href="{{ route('manager.carriers.create') }}" class="btn">Nuevo</a>
    @endcan

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($carriers as $carrier)
            <tr>
                <td>{{ $carrier->name }}</td>
                <td>{{ $carrier->email }}</td>
                <td>{{ $carrier->phone }}</td>
                <td>
                    @can('carriers.update')
                    <a href="{{ route('manager.carriers.edit', $carrier->uid) }}">Editar</a>
                    @endcan

                    @can('carriers.delete')
                    <a href="{{ route('manager.carriers.destroy', $carrier->uid) }}">Eliminar</a>
                    @endcan
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $carriers->links() }}
</div>
@endsection
```

#### Paso 8: Listo

Ahora tienes un CRUD completo con:
- ✅ Rutas definidas
- ✅ Controlador CRUD
- ✅ Modelo Eloquent
- ✅ Migraciones
- ✅ Permisos granulares
- ✅ Vistas Blade
- ✅ Auditoría de cambios
- ✅ Validación de datos
- ✅ Control de acceso

---

## 📊 Resumen de Arquitectura

### Números Clave

| Métrica | Cantidad |
|---------|----------|
| **Perfiles/Roles** | 5 principales |
| **Rutas totales** | 200+ |
| **Controladores** | 120+ |
| **Módulos** | 15+ |
| **Modelos** | 50+ |
| **Permisos** | 100+ |
| **Middlewares** | 7+ |
| **Métodos CRUD** | 7 por recurso (index, create, store, view, edit, update, destroy) |

### Patrones Implementados

1. **Modularidad** - Rutas y controladores por perfil
2. **CRUD Explícito** - Sin Route::resource()
3. **Seguridad Multinivel** - Autenticación → Rol → Permiso → Pertenencia
4. **Auditoría Completa** - Logs con Spatie Activity Log
5. **Identificadores Universales** - UID en lugar de ID
6. **Respuestas JSON** - APIs modernas
7. **Validación Explícita** - Validator class

---

**Documento generado automáticamente**
**Framework:** Laravel 11.42
**Fecha:** 2025-11-17
