# Módulo Documents

Módulo autocontenido para la gestión de documentos con sistema de permisos dual (Spatie Permission + ValidatorGroup).

## 📋 Descripción

Este módulo encapsula toda la funcionalidad relacionada con documentos, incluyendo:
- Gestión de documentos por perfil (Super-admin, Manager, Administrative, etc.)
- Sistema de permisos dual:
  - **Spatie Permission**: Permisos basados en roles
  - **ValidatorGroup**: Configuraciones basadas en grupos de validadores
- Workflow multi-etapa de validación
- Acciones de email configurables por grupo
- Integración con sistema ERP

## 🏗️ Arquitectura

```
Modules/Documents/
├── app/
│   ├── Entities/                    # Modelos (migrará desde app/Models/Document)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Managers/           # Controladores para perfil Manager
│   │   │   ├── Administratives/    # Controladores para perfil Administrative
│   │   │   └── Api/                # Controladores API
│   │   ├── Middleware/             # Middleware personalizado
│   │   └── Requests/               # Form Requests de validación
│   ├── Policies/
│   │   └── DocumentPolicy.php      # Policy de autorización
│   ├── Services/
│   │   └── PermissionService.php   # Lógica centralizada de permisos
│   └── Providers/
│       ├── DocumentsServiceProvider.php
│       ├── EventServiceProvider.php
│       └── RouteServiceProvider.php
├── config/
│   └── config.php                  # Configuración del módulo
├── database/
│   ├── migrations/                 # Migraciones de documentos
│   └── seeders/                    # Seeders de permisos
├── resources/
│   └── views/
│       ├── managers/               # Vistas para Manager
│       ├── administratives/        # Vistas para Administrative
│       └── components/             # Componentes reutilizables
├── routes/
│   ├── web.php
│   ├── api.php
│   ├── managers/                   # Rutas específicas de managers
│   └── administratives/            # Rutas específicas de administratives
└── tests/
    ├── Feature/
    └── Unit/
```

## 🔑 Sistema de Permisos

### Permisos Spatie (Role-Based)

Formato: `{profile}.documents.{action}`

Ejemplos:
- `administrative.documents.manage`
- `administrative.documents.send-custom-email`
- `manager.documents.view`

### Configuración ValidatorGroup (Group-Based)

Categoría: `email_actions`

Opciones configurables:
- `enable_initial_request`
- `enable_missing_docs`
- `enable_reminder`
- `enable_upload_confirmation`
- `enable_approval`
- `enable_rejection`
- `enable_custom_email`

### Lógica de Autorización

El módulo utiliza un sistema de autorización de dos capas:

1. **Super-admin**: Acceso TOTAL a todas las funciones
2. **Otros roles**: Requieren AMBOS:
   - Permiso Spatie (`$user->can('administrative.documents.manage')`)
   - Configuración de ValidatorGroup (si aplica a acciones de email)

## 🚀 Uso

### En Controladores

```php
use App\Models\Document\Document;
use Modules\Documents\Services\PermissionService;

class DocumentsController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    public function manage(Document $document)
    {
        // Autorizar usando Policy
        $this->authorize('manage', [$document, 'administrative']);

        // Obtener acciones de email disponibles para el usuario
        $emailConfig = $this->permissionService->getEmailActionsConfig(
            auth()->user(),
            'administrative'
        );

        return view('documents::administratives.manage', [
            'document' => $document,
            'documentConfig' => $emailConfig
        ]);
    }

    public function sendCustomEmail(Document $document)
    {
        // Verificar permiso específico
        $this->authorize('sendCustomEmail', [$document, 'administrative']);

        // Lógica para enviar email...
    }
}
```

### En Vistas (Blade)

```blade
@inject('permissionService', 'Modules\Documents\Services\PermissionService')

@php
    $availableActions = $permissionService->getAvailableActions(
        auth()->user(),
        'administrative'
    );
@endphp

{{-- Mostrar botones solo si tiene permiso --}}
@if(in_array('send-custom-email', $availableActions))
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customEmailModal">
        Correo personalizado
    </button>
@endif

{{-- Super-admin ve TODO --}}
@role('super-admin')
    <div class="alert alert-info">
        Modo Super-admin: Acceso completo
    </div>
@endrole
```

### En Gates (Manual)

```php
use Illuminate\Support\Facades\Gate;
use Modules\Documents\Services\PermissionService;

$permissionService = app(PermissionService::class);

if ($permissionService->can(auth()->user(), 'manage', 'administrative')) {
    // Usuario puede gestionar documentos
}
```

## 📚 Servicios Principales

### PermissionService

Servicio centralizado para verificar permisos.

**Métodos principales:**

- `can(User $user, string $action, ?string $profile = null): bool`
  - Verifica si usuario tiene acceso a una acción específica

- `getAvailableActions(User $user, ?string $profile = null): array`
  - Retorna lista de acciones disponibles para el usuario

- `getEmailActionsConfig(User $user, ?string $profile = null): array`
  - Retorna configuración de acciones de email con permisos aplicados

- `isInValidatorGroup(User $user): bool`
  - Verifica si usuario está en un ValidatorGroup activo

- `getUserValidatorGroups(User $user): Collection`
  - Retorna los grupos de validador del usuario

### DocumentPolicy

Policy de Laravel para autorización de documentos.

**Métodos principales:**

- `viewAny(User $user, ?string $profile = null): bool`
- `view(User $user, Document $document, ?string $profile = null): bool`
- `manage(User $user, Document $document, ?string $profile = null): bool`
- `update(User $user, Document $document, ?string $profile = null): bool`
- `delete(User $user, Document $document, ?string $profile = null): bool`
- `sendCustomEmail(User $user, Document $document, ?string $profile = null): bool`
- `sendNotification(User $user, Document $document, ?string $profile = null): bool`
- `approveStage(User $user, Document $document, ?string $profile = null): bool`
- `rejectStage(User $user, Document $document, ?string $profile = null): bool`

## 🧪 Testing

```bash
# Ejecutar tests del módulo
php artisan test Modules/Document/tests

# Test específico
php artisan test Modules/Document/tests/Feature/PermissionServiceTest.php
```

## 📦 Migraciones Pendientes

- [ ] Migrar modelos desde `app/Models/Document` a `Modules/Documents/app/Entities`
- [ ] Migrar controladores a sus carpetas por perfil
- [ ] Migrar vistas y componentes
- [ ] Migrar rutas a archivos por perfil
- [ ] Crear tests de permisos
- [ ] Actualizar namespaces en toda la aplicación

## 🔧 Configuración

El módulo se autoconfigura al estar habilitado. Para personalizar:

```php
// config/documents.php (después de publicar)

return [
    'default_profile' => 'administrative',
    'enable_workflow' => true,
    'max_upload_size' => 10240, // KB
    // ...
];
```

## 📝 Notas

- El módulo está diseñado para coexistir con el core de Laravel sin conflictos
- Los namespaces siguen el patrón `Modules\Documents\`
- Super-admin siempre tiene acceso completo
- Las configuraciones de ValidatorGroup se fusionan con lógica OR (el más permisivo gana)

## 👥 Perfiles Soportados

- `super-admin` - Acceso completo sin restricciones
- `admin` - Casi completo
- `manager` - Gestión general
- `administrative` - Tareas administrativas
- `accounting` - Contabilidad
- `callcenter` - Operación call center
- `warehouse` - Inventario
- `weapons` - Usuarios de armas
- `shop` - Operaciones de tienda
- `support` - Soporte técnico

## 🛠️ Desarrollo

Para agregar una nueva acción de documento:

1. Agregar permiso en seeder: `administrative.documents.nueva-accion`
2. Agregar acción en `PermissionService::getAllActions()`
3. Agregar método en `DocumentPolicy` si es necesario
4. Actualizar vistas para verificar permiso

## 📄 Licencia

Propietario - Uso interno
