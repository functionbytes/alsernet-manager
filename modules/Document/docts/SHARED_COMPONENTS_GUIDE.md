# Guía de Componentes Compartidos - Módulo Documents

## Problema Resuelto

Anteriormente teníamos **archivos duplicados** para cada perfil:
- `administratives/documents/modals/approve-stage.blade.php`
- `weapons/documents/modals/approve-stage.blade.php`
- `accountings/documents/modals/approve-stage.blade.php`

Los archivos eran **idénticos** excepto por las rutas (eg. `administrative.documents.approve-stage` vs `weapons.documents.approve-stage`).

## Solución: Componentes Blade Compartidos

Ahora tenemos **UN SOLO archivo fuente** que funciona para todos los perfiles:

```
Modules/Documents/
├── app/View/Components/
│   ├── ApproveStageModal.php      # Clase del componente
│   └── RejectStageModal.php       # Clase del componente
└── resources/views/components/
    └── modals/
        ├── approve-stage.blade.php  # Vista del componente
        └── reject-stage.blade.php   # Vista del componente
```

## Cómo Usar los Componentes

### TODOS los perfiles usan la MISMA sintaxis

```blade
{{-- Antes (archivos duplicados por perfil): --}}
@include('documents::administratives.documents.modals.approve-stage', ['document' => $document])
@include('documents::weapons.documents.modals.approve-stage', ['document' => $document])
@include('documents::accountings.documents.modals.approve-stage', ['document' => $document])

{{-- Ahora (componente único para TODOS los perfiles): --}}
<x-documents::approve-stage-modal :document="$document" />
```

**✨ Magia del Backend:** El componente usa rutas API genéricas que detectan automáticamente el perfil del usuario autenticado.

```javascript
// EN EL COMPONENTE:
url: "{{ route('api.documents.approve-stage', $document->uid) }}"

// EN EL BACKEND (DocumentValidationController):
protected function getUserProfile(): string {
    // Detecta automáticamente si el usuario es:
    // - administrative
    // - weapons
    // - accounting
    // - manager

    // Y filtra/valida según corresponda
}
```

**NO necesitas pasar el perfil** - El backend lo detecta automáticamente basándose en el usuario autenticado.

## Componentes Disponibles

### Modales

| Componente | Uso | Parámetros |
|------------|-----|------------|
| `<x-documents::approve-stage-modal>` | Modal de aprobación de etapa | `$document`, `$profile` |
| `<x-documents::reject-stage-modal>` | Modal de rechazo | `$document`, `$profile` |

### Includes (Pendientes de crear)

| Componente Planeado | Reemplaza |
|---------------------|-----------|
| `<x-documents::action-history>` | `includes/action-history.blade.php` |
| `<x-documents::document-notes>` | `includes/document-notes.blade.php` |
| `<x-documents::status-timeline>` | `includes/status-timeline.blade.php` |
| `<x-documents::email-history>` | `includes/email-history.blade.php` |

## Crear un Nuevo Componente Compartido

### 1. Crear la Clase del Componente

```php
<?php
// modules/Document/app/View/Components/MiComponente.php

namespace Modules\Documents\View\Components;

use Illuminate\View\Component;
use Modules\Documents\Entities\Document;

class MiComponente extends Component
{
    public Document $document;
    public string $profile;

    public function __construct(Document $document, string $profile)
    {
        $this->document = $document;
        $this->profile = $profile;
    }

    public function render()
    {
        return view('documents::components.mi-componente');
    }
}
```

### 2. Crear la Vista del Componente

```blade
{{-- Modules/Documents/resources/views/components/mi-componente.blade.php --}}

<div class="mi-componente">
    <!-- HTML idéntico para todos los perfiles -->
    <h5>{{ $document->title }}</h5>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // JavaScript con ruta dinámica
        $.ajax({
            url: "{{ route($profile . '.documents.mi-ruta', $document->uid) }}",
            method: 'POST',
            // ...
        });
    });
</script>
@endpush
```

### 3. Usar el Componente

```blade
<x-documents::mi-componente :document="$document" profile="administrative" />
```

## Beneficios

### Antes (Duplicado)
- ❌ 3 archivos idénticos por cada modal/include (x3 perfiles)
- ❌ Cambios requieren editar 3 archivos
- ❌ Inconsistencias entre perfiles
- ❌ Difícil mantenimiento

### Ahora (Compartido)
- ✅ 1 archivo fuente por componente
- ✅ Cambios en un solo lugar
- ✅ Consistencia garantizada
- ✅ Mantenimiento sencillo
- ✅ Auto-registro con namespace `documents::`

## Registro Automático

Los componentes se registran automáticamente en `DocumentsServiceProvider.php`:

```php
Blade::componentNamespace(
    config('modules.namespace').'\\'.$this->name.'\\View\\Components',
    $this->nameLower
);
```

Esto permite usarlos con el prefijo `documents::`:
- `ApproveStageModal` → `<x-documents::approve-stage-modal>`
- `RejectStageModal` → `<x-documents::reject-stage-modal>`
- `MiComponente` → `<x-documents::mi-componente>`

## Plan de Migración

1. ✅ Crear componentes para modales principales (approve, reject)
2. ⏳ Crear componentes para includes (action-history, notes, timeline)
3. ⏳ Actualizar vistas de `administrative` para usar componentes
4. ⏳ Actualizar vistas de `weapons` para usar componentes
5. ⏳ Actualizar vistas de `accountings` para usar componentes
6. ⏳ Eliminar archivos duplicados
7. ✅ Documentación completa

## Notas Técnicas

- **Namespace:** `Modules\Documents\View\Components`
- **View Path:** `documents::components.{nombre}`
- **Convención:** Nombres en PascalCase para clases, kebab-case para tags
- **Parámetros:** Siempre incluir `$document` y `$profile`
- **Rutas:** Usar `route($profile . '.documents.{ruta}')` en JavaScript
