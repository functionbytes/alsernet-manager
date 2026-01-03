# Sistema de Navegación Modular

Este módulo implementa un sistema de navegación dinámica que permite inyectar items de menú en la navegación principal sin modificar archivos del core.

## Arquitectura

### 1. Archivo de Configuración
**Ubicación:** `Modules/Documents/config/navigation.php`

Define la estructura del menú que se inyectará:

```php
return [
    'sidebar' => [
        'mini_nav_id' => 'mini-7',           // ID del icono principal
        'sidebar_nav_id' => 'sidebarnav-config', // ID del sidebar
        'insert_after' => 'Comentarios & Notas',  // Después de qué sección
        'section' => [
            'title' => 'Documentos',
            'permission' => null,
            'items' => [
                [
                    'label' => 'Configuraciones',
                    'route' => 'manager.backups.documents.configurations',
                    'permission' => 'documents:view',
                ],
                // ... más items
            ],
        ],
    ],
];
```

### 2. View Composer
**Ubicación:** `Modules/Documents/app/Http/ViewComposers/NavigationComposer.php`

Responsable de:
- Leer la configuración de navegación
- Validar permisos de cada item
- Construir la estructura de datos para la vista
- Inyectar la variable `$documentsNavigation` en la vista

**Características:**
- ✅ Validación de permisos por item
- ✅ Super-admin bypass automático
- ✅ Detección de ruta activa
- ✅ Lazy loading (solo se carga cuando se necesita)

### 3. Registro en Service Provider
**Ubicación:** `Modules/Documents/app/Providers/DocumentsServiceProvider.php`

```php
protected function registerViewComposers(): void
{
    view()->composer(
        'theme.components.nav',
        \Modules\Documents\app\Http\ViewComposers\NavigationComposer::class
    );
}
```

### 4. Vista de Navegación
**Ubicación:** `resources/views/managers/includes/nav.blade.php`

Consume la variable inyectada:

```blade
@if(isset($documentsNavigation) && !empty($documentsNavigation['items']))
<li class="nav-small-cap">
    <span class="hide-menu">{{ $documentsNavigation['title'] }}</span>
</li>
@foreach($documentsNavigation['items'] as $item)
<li class="sidebar-item">
    <a href="{{ $item['url'] }}" class="sidebar-link {{ $item['active'] ? 'active' : '' }}">
        <span class="hide-menu">{{ $item['label'] }}</span>
    </a>
</li>
@endforeach
@endif
```

## Sistema de Permisos

### Validación a Nivel de Sección
```php
'section' => [
    'title' => 'Documentos',
    'permission' => 'documents:manage', // Si el usuario no tiene este permiso, no ve nada
    'items' => [...],
],
```

### Validación a Nivel de Item
```php
[
    'label' => 'Tipos de documento',
    'route' => 'manager.backups.documents.types',
    'permission' => 'document_types:view', // Solo ve este item si tiene el permiso
],
```

### Super-Admin Bypass
Los usuarios con rol `super-admin` ven todos los items automáticamente.

## Ventajas del Sistema

### 1. Desacoplamiento
- El módulo no toca archivos del core
- La navegación es autodescriptiva
- Fácil de mantener y extender

### 2. Reutilizable
Otros módulos pueden implementar el mismo patrón:

```php
// En OtroModuloServiceProvider.php
protected function registerViewComposers(): void
{
    view()->composer(
        'theme.components.nav',
        \Modules\OtroModulo\app\Http\ViewComposers\NavigationComposer::class
    );
}
```

### 3. Seguro
- Validación de permisos centralizada
- No hay hardcodeo de rutas o permisos
- Fácil de auditar

### 4. Flexible
- Se pueden agregar/quitar items desde la configuración
- Se pueden cambiar permisos sin tocar código
- Se pueden publicar configuraciones personalizadas

## Publicar Configuración

Para permitir que los usuarios personalicen la navegación:

```bash
php artisan vendor:publish --tag=config --force
```

Esto publicará `config/documents/navigation.php` que el usuario puede editar.

## Ejemplo de Uso para Otros Módulos

### 1. Crear configuración
```php
// modules/MiModulo/config/navigation.php
return [
    'sidebar' => [
        'section' => [
            'title' => 'Mi Módulo',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'mimodulo.dashboard', 'permission' => null],
            ],
        ],
    ],
];
```

### 2. Crear View Composer
```php
// modules/MiModulo/app/Http/ViewComposers/NavigationComposer.php
namespace Modules\MiModulo\app\Http\ViewComposers;

class NavigationComposer
{
    public function compose(View $view): void
    {
        $config = config('mimodulo.navigation', []);
        // ... lógica similar al DocumentsModule
        $view->with('miModuloNavigation', $items);
    }
}
```

### 3. Registrar en Service Provider
```php
protected function registerViewComposers(): void
{
    view()->composer(
        'theme.components.nav',
        \Modules\MiModulo\app\Http\ViewComposers\NavigationComposer::class
    );
}
```

### 4. Actualizar vista
```blade
@if(isset($miModuloNavigation) && !empty($miModuloNavigation['items']))
    <!-- Renderizar items -->
@endif
```

## Debugging

### Ver qué se está inyectando
```blade
@if(isset($documentsNavigation))
    @dump($documentsNavigation)
@endif
```

### Verificar permisos
```php
// En NavigationComposer::hasPermission()
\Log::info('Checking permission: ' . $permission);
```

### Cache de configuración
Si cambias la configuración y no se refleja:
```bash
php artisan config:clear
php artisan view:clear
```

## Mejoras Futuras

- [ ] Sistema de orden/prioridad para módulos
- [ ] Soporte para sub-menús anidados
- [ ] Iconos dinámicos por item
- [ ] Cache de permisos para mejor performance
- [ ] Event-driven navigation (eventos para registrar items)
- [ ] Breadcrumbs automáticos basados en navegación

---

**Ventaja clave:** Con este sistema, desinstalar el módulo Documents automáticamente elimina su sección del menú sin dejar código huérfano.
