# Arquitectura API-First - Módulo de Documentos

## 📋 Tabla de Contenidos
- [Resumen](#resumen)
- [Estructura de Archivos](#estructura-de-archivos)
- [Vistas Blade sin Componentes PHP](#vistas-blade-sin-componentes-php)
- [Endpoints API Disponibles](#endpoints-api-disponibles)
- [Uso de Modales](#uso-de-modales)
- [Patrones JavaScript](#patrones-javascript)
- [Detección Automática de Perfil](#detección-automática-de-perfil)

---

## Resumen

Este módulo implementa una **arquitectura API-first** donde:

- ✅ **Vistas Blade** contienen solo HTML estático
- ✅ **JavaScript/AJAX** carga datos dinámicos desde endpoints API
- ✅ **Backend** detecta automáticamente el perfil del usuario
- ❌ **NO usar** componentes Blade que extiendan `Component`
- ❌ **NO enviar** datos desde controladores PHP a vistas

### Ventajas

1. **Desacoplamiento** - Frontend y backend independientes
2. **Reutilización** - Los endpoints sirven para web, mobile, SPA
3. **Testing** - APIs fácilmente testeables
4. **Performance** - Carga bajo demanda solo cuando se necesita
5. **Mantenibilidad** - Lógica centralizada en controladores API

---

## Estructura de Archivos

```
Modules/Documents/
├── app/
│   └── Http/
│       └── Controllers/
│           └── Api/
│               └── DocumentValidationController.php  ← Endpoints API
├── resources/
│   └── views/
│       └── components/
│           └── modals/
│               ├── approve-stage.blade.php          ← HTML puro
│               ├── reject-stage.blade.php
│               ├── approval.blade.php
│               ├── rejection.blade.php
│               ├── reminder.blade.php
│               ├── initial-request.blade.php
│               ├── missing-docs.blade.php
│               ├── custom-email.blade.php
│               ├── confirm-delete.blade.php
│               ├── confirm-missing-docs.blade.php
│               └── upload-confirmation.blade.php
└── routes/
    └── api.php                                       ← Rutas API
```

**❌ NO CREAR:**
```
Modules/Documents/app/View/Components/   ← NO usar esta carpeta
```

---

## Vistas Blade sin Componentes PHP

### ❌ Enfoque ANTERIOR (incorrecto)

```php
// app/View/Components/ApproveStageModal.php
class ApproveStageModal extends Component {
    public $nextGroup;
    public $nextGroupUsers;

    public function __construct(Document $document) {
        $this->nextGroup = ...;  // Datos cargados en PHP
        $this->nextGroupUsers = ...;
    }
}
```

```blade
{{-- approve-stage.blade.php --}}
<select>
    @foreach($nextGroupUsers as $user)
        <option>{{ $user->name }}</option>
    @endforeach
</select>
```

```blade
{{-- Uso en página --}}
<x-documents::approve-stage-modal :document="$document" />
```

### ✅ Enfoque ACTUAL (correcto)

**NO HAY clase Component PHP**

```blade
{{-- approve-stage.blade.php --}}
<div class="modal" id="approveStageModal">
    <select id="assignToUser">
        <option>Cargando...</option>
    </select>
</div>

@push('scripts')
<script>
    $('#approveStageModal').on('show.bs.modal', function() {
        // Cargar datos via AJAX
        $.ajax({
            url: "{{ route('api.documents.next-stage-info', $document->uid) }}",
            success: function(response) {
                let options = '';
                response.users.forEach(user => {
                    options += `<option value="${user.id}">${user.name}</option>`;
                });
                $('#assignToUser').html(options);
            }
        });
    });
</script>
@endpush
```

```blade
{{-- Uso en página --}}
@include('documents::components.modals.approve-stage', ['document' => $document])
```

---

## Endpoints API Disponibles

### Acciones de Validación (POST)

| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `/api/documents/{uid}/approve-stage` | POST | Aprobar etapa actual |
| `/api/documents/{uid}/reject-stage` | POST | Rechazar etapa actual |
| `/api/documents/{uid}/send-approval` | POST | Enviar email de aprobación |
| `/api/documents/{uid}/send-rejection` | POST | Enviar email de rechazo |
| `/api/documents/{uid}/send-custom-email` | POST | Enviar email personalizado |
| `/api/documents/{uid}/send-reminder` | POST | Enviar recordatorio |
| `/api/documents/{uid}/request-initial-documents` | POST | Solicitar documentos iniciales |
| `/api/documents/{uid}/request-missing-documents` | POST | Solicitar documentos faltantes |

### Datos Dinámicos (GET)

| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `/api/documents/{uid}/next-stage-info` | GET | Info del siguiente grupo de validación |
| `/api/documents/custom-email-template` | GET | Plantilla de email personalizado |
| `/api/documents/{uid}/action-history` | GET | Historial de acciones |
| `/api/documents/{uid}/email-history` | GET | Historial de emails enviados |
| `/api/documents/{uid}/status-timeline` | GET | Timeline de cambios de estado |

### Ejemplo de Respuesta

**GET `/api/documents/{uid}/next-stage-info`**

```json
{
  "success": true,
  "next_stage": 2,
  "next_group_key": "commercial",
  "next_group_label": "Commercial",
  "users": [
    {
      "id": 15,
      "name": "Juan Pérez",
      "is_primary": true
    },
    {
      "id": 22,
      "name": "María García",
      "is_primary": false
    }
  ]
}
```

---

## Uso de Modales

### Incluir Modal en Página

```blade
{{-- resources/views/administrative/documents/manage.blade.php --}}

<div class="container">
    <h1>Gestión de Documento</h1>

    <button data-bs-toggle="modal" data-bs-target="#approveStageModal">
        Aprobar Etapa
    </button>
</div>

{{-- Incluir todos los modales al final --}}
@include('documents::components.modals.approve-stage', ['document' => $document])
@include('documents::components.modals.reject-stage', ['document' => $document])
@include('documents::components.modals.custom-email', ['document' => $document])
```

### Ciclo de Vida del Modal

```javascript
// 1. Usuario hace clic en botón
<button data-bs-toggle="modal" data-bs-target="#approveStageModal">

// 2. Bootstrap dispara evento show.bs.modal
$('#approveStageModal').on('show.bs.modal', function() {
    // 3. JavaScript carga datos via AJAX
    $.ajax({ url: '/api/documents/xxx/next-stage-info' });
});

// 4. Usuario interactúa con el modal (completa formulario)

// 5. Usuario hace clic en "Aprobar"
$('#btnConfirmApprove').on('click', function() {
    // 6. JavaScript envía acción via AJAX
    $.ajax({
        url: '/api/documents/xxx/approve-stage',
        method: 'POST',
        data: { comments: '...', assigned_user_id: 15 }
    });
});

// 7. Backend procesa y retorna respuesta
// 8. Frontend muestra toastr y recarga página
```

---

## Patrones JavaScript

### Patrón 1: Cargar Datos al Abrir Modal

```javascript
$('#modalId').on('show.bs.modal', function() {
    $.ajax({
        url: "{{ route('api.documents.endpoint', $document->uid) }}",
        method: 'GET',
        success: function(response) {
            if (response.success) {
                // Poblar elementos del DOM
                $('#selectElement').html(options);
                $('#infoDiv').html(content);
            }
        },
        error: function(xhr) {
            toastr.error('Error al cargar datos', 'Error');
        }
    });
});
```

### Patrón 2: Enviar Acción desde Modal

```javascript
$('#btnAction').on('click', function() {
    const $btn = $(this);
    const data = {
        field1: $('#input1').val(),
        field2: $('#input2').val(),
        _token: '{{ csrf_token() }}'
    };

    // Validación básica
    if (!data.field1) {
        toastr.warning('Campo requerido', 'Atención');
        return;
    }

    // Loading state
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

    $.ajax({
        url: "{{ route('api.documents.action', $document->uid) }}",
        method: 'POST',
        data: data,
        success: function(response) {
            if (response.success) {
                toastr.success(response.message, 'Éxito');
                $('#modalId').modal('hide');
                setTimeout(() => location.reload(), 1500);
            }
        },
        error: function(xhr) {
            toastr.error(xhr.responseJSON?.message || 'Error', 'Error');
        },
        complete: function() {
            $btn.prop('disabled', false).html('Guardar');
        }
    });
});
```

### Patrón 3: Limpiar Modal al Cerrar

```javascript
$('#modalId').on('hidden.bs.modal', function() {
    // Limpiar formularios
    $('#input1').val('');
    $('#input2').val('');
    $('#select1').val('');

    // Resetear botones
    $('#btnAction').prop('disabled', false).html('Guardar');

    // Ocultar elementos dinámicos
    $('#dynamicContainer').hide();
});
```

---

## Detección Automática de Perfil

### Backend - DocumentValidationController.php

```php
protected function getUserProfile(): string
{
    $user = auth()->user();

    if ($user->hasRole('super-admin') || $user->hasRole('manager')) {
        return 'manager';
    }

    if ($user->hasRole('administrative')) {
        return 'administrative';
    }

    if ($user->hasRole('weapons')) {
        return 'weapons';
    }

    if ($user->hasRole('accounting')) {
        return 'accounting';
    }

    return 'administrative'; // fallback
}
```

### Uso en Métodos API

```php
public function approveStage(Request $request, string $uid): JsonResponse
{
    $document = Document::where('uid', $uid)->firstOrFail();

    // Detectar perfil automáticamente
    $profile = $this->getUserProfile();

    // Validar permisos según perfil
    $this->authorize('approve', $document);

    // Ejecutar acción...
    $result = $this->actionService->approveStage($document, ...);

    return response()->json([
        'success' => true,
        'message' => 'Documento aprobado',
        'document' => $result,
    ]);
}
```

### Ventajas

- ✅ **Una sola ruta** por acción (no `administrative.approve`, `weapons.approve`, etc.)
- ✅ **Permisos centralizados** en policies de Laravel
- ✅ **Filtrado automático** según rol del usuario
- ✅ **Mantenimiento simple** - cambios en un solo lugar

---

## Ejemplo Completo

### 1. Vista Blade (approve-stage.blade.php)

```blade
<div class="modal fade" id="approveStageModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Aprobar Etapa</h5>
            </div>
            <div class="modal-body">
                <div id="nextStageInfo">
                    <i class="fas fa-spinner fa-spin"></i> Cargando...
                </div>

                <select id="assignToUser" style="display: none;">
                    <option>Cargando...</option>
                </select>

                <textarea id="comments" placeholder="Comentarios opcionales"></textarea>
            </div>
            <div class="modal-footer">
                <button id="btnApprove" class="btn btn-primary">Aprobar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $('#approveStageModal').on('show.bs.modal', function() {
        $.ajax({
            url: "{{ route('api.documents.next-stage-info', $document->uid) }}",
            success: function(response) {
                $('#nextStageInfo').html(`Siguiente: ${response.next_group_label}`);

                if (response.users.length > 0) {
                    let options = '<option value="">Todo el grupo</option>';
                    response.users.forEach(user => {
                        options += `<option value="${user.id}">${user.name}</option>`;
                    });
                    $('#assignToUser').html(options).show();
                }
            }
        });
    });

    $('#btnApprove').on('click', function() {
        $.ajax({
            url: "{{ route('api.documents.approve-stage', $document->uid) }}",
            method: 'POST',
            data: {
                comments: $('#comments').val(),
                assigned_user_id: $('#assignToUser').val(),
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                toastr.success('Documento aprobado');
                location.reload();
            }
        });
    });
</script>
@endpush
```

### 2. Uso en Página

```blade
{{-- administrative/documents/manage.blade.php --}}

@extends('layouts.administrative')

@section('content')
    <div class="container">
        <h1>Documento #{{ $document->uid }}</h1>

        <button class="btn btn-success"
                data-bs-toggle="modal"
                data-bs-target="#approveStageModal">
            Aprobar Etapa
        </button>
    </div>

    @include('documents::components.modals.approve-stage', ['document' => $document])
@endsection
```

### 3. Endpoint API

```php
// DocumentValidationController.php

public function approveStage(Request $request, string $uid): JsonResponse
{
    $document = Document::where('uid', $uid)->firstOrFail();
    $profile = $this->getUserProfile(); // ← Detección automática

    $this->authorize('approve', $document);

    $validated = $request->validate([
        'comments' => 'nullable|string',
        'assigned_user_id' => 'nullable|exists:users,id',
    ]);

    $result = $this->actionService->approveStage(
        $document,
        $validated['comments'],
        $validated['assigned_user_id']
    );

    return response()->json([
        'success' => true,
        'message' => 'Documento aprobado exitosamente',
        'document' => $result,
    ]);
}
```

### 4. Ruta API

```php
// routes/api.php

Route::prefix('api/documents')
    ->middleware(['auth', 'throttle:60,1'])
    ->group(function () {
        Route::post('/{uid}/approve-stage', [DocumentValidationController::class, 'approveStage'])
            ->name('api.documents.approve-stage');

        Route::get('/{uid}/next-stage-info', [DocumentValidationController::class, 'getNextStageInfo'])
            ->name('api.documents.next-stage-info');
    });
```

---

## Resumen de Cambios

| Aspecto | ❌ Antes | ✅ Ahora |
|---------|---------|---------|
| **Componentes** | Clases PHP en `app/View/Components/` | Solo vistas Blade en `resources/views/` |
| **Datos** | Pasados desde componente PHP | Cargados via AJAX desde API |
| **Inclusión** | `<x-documents::modal />` | `@include('documents::modals.modal')` |
| **Rutas** | Duplicadas por perfil | Una sola ruta, backend detecta perfil |
| **Lógica** | En componente PHP + vista | En controlador API + JavaScript |

---

## Próximos Pasos

1. ✅ Crear todas las vistas Blade de modales
2. ✅ Crear endpoints API en `DocumentValidationController`
3. ✅ Agregar rutas en `routes/api.php`
4. ⏳ Actualizar vistas de perfiles (administrative, weapons, accounting) para usar `@include`
5. ⏳ Eliminar archivos duplicados de modales en carpetas de perfiles
6. ⏳ Testing de endpoints API
7. ⏳ Testing de flujo completo en cada perfil

---

**Fecha:** 2025-12-29
**Autor:** Claude Code
**Módulo:** Documents (nwidart/laravel-modules v12)
