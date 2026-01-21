# Reglas de Diseño - Estándares Alsernet

Guía de estándares visuales y de código para mantener consistencia en toda la plataforma.

---

## 📏 Espaciado (Spacing)

Usar la escala Bootstrap de espaciado:

```
$spacer: 1rem (16px)

0 = 0
1 = 0.25rem (4px)
2 = 0.5rem (8px)
3 = 1rem (16px)  ← DEFAULT
4 = 1.5rem (24px)
5 = 3rem (48px)
```

### Uso en Código

```html
<!-- Margin Bottom -->
<div class="mb-0">No margin</div>
<div class="mb-2">8px margin</div>
<div class="mb-3">16px margin</div>
<div class="mb-4">24px margin</div>
<div class="mb-5">48px margin</div>

<!-- Padding -->
<div class="p-2">8px padding all</div>
<div class="px-3">16px padding x-axis</div>
<div class="py-4">24px padding y-axis</div>

<!-- Gap (Flex) -->
<div class="d-flex gap-2">
  <button class="btn btn-primary">Button 1</button>
  <button class="btn btn-secondary">Button 2</button>
</div>
```

### Reglas Comunes

| Elemento | Margin Bottom | Padding |
|----------|---|---|
| Encabezado (H1-H6) | `mb-3` | N/A |
| Párrafo | `mb-2` | N/A |
| Card Body | N/A | `p-4` |
| Card Content | N/A | `p-3` |
| Form Group | `mb-3` | N/A |
| Button Group | N/A | `gap-2` |
| Row (grid) | `mb-4` | N/A |

---

## 🎨 Colores y Fondo

### Colores Primarios

```css
/* Bootstrap Standard Colors */
--bs-primary: #90bb13;      /* Botones principales, enlaces */
--bs-secondary: #6C757D;    /* Elementos secundarios */
--bs-success: #13C672;      /* Estados positivos */
--bs-danger: #FA896B;       /* Errores, peligro */
--bs-warning: #FEC90F;      /* Advertencias */
--bs-info: #39B8E0;         /* Información */
--bs-light: #F3F5FA;        /* Fondos claros */
--bs-dark: #1a1a1a;         /* Fondos oscuros */
```

### Uso de Colores

```html
<!-- Texto -->
<p class="text-primary">Primary text</p>
<p class="text-muted">Muted text</p>
<p class="text-secondary">Secondary</p>

<!-- Fondo -->
<div class="bg-light">Light background</div>
<div class="bg-primary text-white">Primary background</div>

<!-- Badges -->
<span class="badge bg-success">Success</span>
<span class="badge bg-danger">Error</span>

<!-- Alerts -->
<div class="alert alert-success">Success message</div>
<div class="alert alert-danger">Error message</div>
```

### Modo Oscuro

```html
<!-- Automático en data-bs-theme="dark" -->
[data-bs-theme="dark"] {
  --bs-bg-rgb: 26, 26, 26;
  --bs-text-rgb: 255, 255, 255;
}
```

---

## 🔤 Tipografía

### Jerarquía de Títulos

```html
<!-- H1: Títulos de página principal -->
<h1>Dashboard</h1>

<!-- H2: Secciones principales -->
<h2>Recent Orders</h2>

<!-- H3: Subsecciones -->
<h3>Order Details</h3>

<!-- H4: Títulos de card -->
<h4 class="card-title">Card Title</h4>

<!-- H5: Subtítulos -->
<h5>Subtitle</h5>

<!-- H6: Etiquetas -->
<h6>Label</h6>
```

### Tamaños de Fuente

```html
<!-- Display Sizes (grandes) -->
<div class="display-1">Display 1</div>
<div class="display-2">Display 2</div>

<!-- Lead (resaltar párrafo) -->
<p class="lead">This is important text</p>

<!-- Small text -->
<small class="text-muted">Help text</small>

<!-- Monospace (código) -->
<code>variable_name</code>
```

### Pesos de Fuente

```html
<!-- Normal: 400 (default) -->
<p>Normal text</p>

<!-- Bold: 600 -->
<strong>Bold text</strong>

<!-- Light: 300 -->
<p class="fw-light">Light text</p>
```

---

## 🖼️ Bordes y Sombras

### Bordes (Border)

```html
<!-- Borde completo -->
<div class="border">Border all sides</div>

<!-- Borde específico -->
<div class="border-top">Border top only</div>
<div class="border-start">Border left only</div>

<!-- Sin borde -->
<div class="border-0">No border</div>

<!-- Color de borde -->
<div class="border border-primary">Colored border</div>
<div class="border border-success">Success border</div>

<!-- Radio de borde -->
<div class="rounded">4px radius</div>
<div class="rounded-lg">8px radius</div>
<div class="rounded-circle">Fully rounded</div>
```

### Sombras (Shadow)

```html
<!-- Sin sombra -->
<div class="card border-0">No shadow</div>

<!-- Sombra pequeña -->
<div class="card border-0 shadow-sm">Small shadow</div>

<!-- Sombra normal -->
<div class="card border-0 shadow">Normal shadow</div>

<!-- Sombra grande -->
<div class="card border-0 shadow-lg">Large shadow</div>
```

### Patrón Recomendado para Cards

```html
<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent border-bottom">
    <h5 class="card-title mb-0">Title</h5>
  </div>
  <div class="card-body">
    Content here
  </div>
</div>
```

---

## 🎛️ Iconografía

### Iconos Tabler (Principal)

```html
<!-- Dashboard -->
<i class="fa fa-gauge-high"></i>

<!-- Acciones CRUD -->
<i class="fa fa-plus"></i>        <!-- Add -->
<i class="fa fa-pen-to-square"></i>        <!-- Edit -->
<i class="fa fa-trash"></i>       <!-- Delete -->
<i class="fa fa-eye></i>         <!-- View -->

<!-- Navegación -->
<i class="fa fa-house"></i>        <!-- Home -->
<i class="fa fa-arrow-left"></i>  <!-- Back -->
<i class="fa fa-bars></i>        <!-- Menu -->

<!-- Estados -->
<i class="fa fa-check></i>       <!-- Success -->
<i class="fa fa-xmark"></i>           <!-- Error -->
<i class="fa fa-exclamation></i>       <!-- Warning -->
<i class="fa fa-circle-info"></i> <!-- Info -->

<!-- Usuario -->
<i class="fa fa-user></i>        <!-- Profile -->
<i class="fa fa-cubeut"></i>      <!-- Logout -->

<!-- Búsqueda y Filtro -->
<i class="fa fa-magnifying-glass"></i>      <!-- Search -->
<i class="fa fa-filter"></i>      <!-- Filter -->

<!-- Comercio -->
<i class="fa fa-shopping-cart"></i>   <!-- Cart -->
<i class="fa fa-box"></i>         <!-- Product -->
<i class="fa fa-coins"></i>           <!-- Price -->
<i class="fa fa-truck"></i>           <!-- Shipping -->

<!-- Utilidades -->
<i class="fa fa-download"></i>    <!-- Download -->
<i class="fa fa-upload"></i>      <!-- Upload -->
<i class="fa fa-arrows-rotate"></i>     <!-- Reload -->
<i class="ti ti-settings"></i>    <!-- Settings -->
```

### Tamaño de Iconos

```html
<!-- Pequeño: 16px (default) -->
<i class="fa fa-gauge-high"></i>

<!-- Pequeño explícito -->
<i class="fa fa-gauge-high fs-6"></i>

<!-- Normal -->
<i class="fa fa-gauge-high fs-5"></i>

<!-- Grande -->
<i class="fa fa-gauge-high fs-3"></i>

<!-- Extra grande -->
<i class="fa fa-gauge-high fs-1"></i>
```

### Iconos en Contexto

```html
<!-- En botones -->
<button class="btn btn-primary">
  <i class="fa fa-plus me-2"></i>Add New
</button>

<!-- En navegación -->
<a class="nav-link">
  <i class="fa fa-gauge-high me-2"></i>Dashboard
</a>

<!-- En tablas (acciones) -->
<td>
  <button class="btn btn-sm btn-primary" title="Edit">
    <i class="fa fa-pen-to-square"></i>
  </button>
</td>

<!-- Solo icono con tooltip -->
<button class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Delete">
  <i class="fa fa-trash"></i>
</button>
```

---

## 🔘 Botones

### Reglas de Diseño

1. **Color principal para acciones primarias** (Submit, Save, Create)
2. **Color secundario para acciones neutrales** (Cancel, Back)
3. **Color danger solo para peligrosos** (Delete, Logout)
4. **Siempre incluir icono relevante**
5. **Agrupar con `gap-2`**
6. **Mínimo 44px altura en mobile**

```html
<!-- Acción principal -->
<button class="btn btn-primary">
  <i class="fa fa-checkme-2"></i>Save
</button>

<!-- Acción secundaria -->
<button class="btn btn-secondary">
  <i class="fa fa-xmark me-2"></i>Cancel
</button>

<!-- Acción peligrosa -->
<button class="btn btn-danger">
  <i class="fa fa-trash me-2"></i>Delete
</button>

<!-- Grupo de botones -->
<div class="d-flex gap-2">
  <button class="btn btn-primary">Save</button>
  <button class="btn btn-secondary">Cancel</button>
</div>
```

---

## 📊 Tablas

### Estructura Recomendada

```html
<div class="card border-0 shadow-sm">
  <!-- Header con búsqueda y acciones -->
  <div class="card-header bg-transparent border-bottom">
    <div class="row align-items-center">
      <div class="col">
        <h5 class="card-title mb-0">Data Title</h5>
      </div>
      <div class="col-auto">
        <!-- Búsqueda -->
        <div class="input-group input-group-sm">
          <input type="text" class="form-control" placeholder="Search...">
          <button class="btn btn-outline-secondary">
            <i class="fa fa-magnifying-glass"></i>
          </button>
        </div>
      </div>
      <div class="col-auto ms-2">
        <!-- Botón agregar -->
        <button class="btn btn-primary btn-sm">
          <i class="fa fa-plus me-1"></i>Add
        </button>
      </div>
    </div>
  </div>

  <!-- Tabla responsiva -->
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th><input class="form-check-input" type="checkbox"></th>
            <th>Column 1</th>
            <th>Column 2</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><input class="form-check-input" type="checkbox"></td>
            <td><strong>Data</strong></td>
            <td>Value</td>
            <td><span class="badge bg-success">Active</span></td>
            <td>
              <button class="btn btn-sm btn-primary">
                <i class="fa fa-pen-to-square"></i>
              </button>
              <button class="btn btn-sm btn-danger">
                <i class="fa fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Footer con paginación -->
  <div class="card-footer bg-light">
    <nav aria-label="Pagination">
      <ul class="pagination mb-0">
        <!-- Pagination items -->
      </ul>
    </nav>
  </div>
</div>
```

---

## 📝 Formularios

### Estructura Recomendada

```html
<form>
  <!-- Campo simple -->
  <div class="mb-3">
    <label for="fieldName" class="form-label">Label</label>
    <input type="text" class="form-control" id="fieldName" required>
  </div>

  <!-- Campo con descripción -->
  <div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control" id="email" required>
    <small class="d-block mt-2 text-muted">We'll never share your email.</small>
  </div>

  <!-- Campo con icono -->
  <div class="mb-3">
    <label for="search" class="form-label">Search</label>
    <div class="input-group">
      <span class="input-group-text bg-light border-0">
        <i class="fa fa-magnifying-glass"></i>
      </span>
      <input type="text" class="form-control border-start-0" id="search">
    </div>
  </div>

  <!-- Dos campos en fila -->
  <div class="row">
    <div class="col-md-6 mb-3">
      <label for="firstName" class="form-label">First Name</label>
      <input type="text" class="form-control" id="firstName">
    </div>
    <div class="col-md-6 mb-3">
      <label for="lastName" class="form-label">Last Name</label>
      <input type="text" class="form-control" id="lastName">
    </div>
  </div>

  <!-- Botones -->
  <div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">Save</button>
    <button type="reset" class="btn btn-secondary">Clear</button>
  </div>
</form>
```

---

## ✅ Checklist de Diseño

Antes de pasar un componente a producción:

- [ ] Colores están dentro de la paleta de Modernize
- [ ] Espaciado usa escala Bootstrap (0-5)
- [ ] Tipografía es consistente con jerarquía
- [ ] Bordes y sombras son `border-0 shadow-sm`
- [ ] Todos los botones tienen iconos relevantes
- [ ] Responsive funciona en mobile (< 576px)
- [ ] Modo oscuro se ve bien
- [ ] Accesibilidad: labels en formularios, alt text en imágenes
- [ ] No hay CSS custom innecesario
- [ ] Documentado en esta guía si es componente nuevo

---

## 🔗 Recursos de Referencia

- [Bootstrap 5.3 Utilities](https://getbootstrap.com/docs/5.3/utilities/)
- [Tabler Icons Complete List](https://tabler-icons.io/)
- [Color Palette](./modernize-overview.md#-paleta-de-colores)
- [Components Reference](./components.md)
