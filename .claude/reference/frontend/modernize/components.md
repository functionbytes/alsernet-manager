# Componentes Modernize - Referencia Visual

Documentación de componentes Bootstrap 5.3 disponibles en la plantilla Modernize.

---

## 🔘 Botones

### Variantes Básicas

```html
<!-- Primary Button -->
<button class="btn btn-primary">Primary Button</button>

<!-- Secondary Button -->
<button class="btn btn-secondary">Secondary</button>

<!-- Success Button -->
<button class="btn btn-success">Success</button>

<!-- Danger Button -->
<button class="btn btn-danger">Delete</button>

<!-- Warning Button -->
<button class="btn btn-warning">Warning</button>

<!-- Info Button -->
<button class="btn btn-info">Information</button>
```

### Tamaños

```html
<button class="btn btn-primary btn-sm">Small</button>
<button class="btn btn-primary">Medium (default)</button>
<button class="btn btn-primary btn-lg">Large</button>
```

### Estados

```html
<!-- Outline (Delineado) -->
<button class="btn btn-outline-primary">Outline</button>

<!-- Disabled -->
<button class="btn btn-primary" disabled>Disabled</button>

<!-- Loading State -->
<button class="btn btn-primary" disabled>
  <span class="spinner-border spinner-border-sm me-2"></span>
  Loading...
</button>
```

### Con Iconos

```html
<button class="btn btn-primary">
  <i class="fa fa-plus me-2"></i>Agregar
</button>

<button class="btn btn-danger">
  <i class="fa fa-trash me-2"></i>Eliminar
</button>
```

**Recomendación:** Siempre agregar iconos relevantes a botones principales.

---

## 🎴 Cards

### Card Básico

```html
<div class="card">
  <div class="card-header">
    <h5 class="card-title">Card Title</h5>
  </div>
  <div class="card-body">
    <p class="card-text">Card content goes here</p>
  </div>
</div>
```

### Card con Imagen

```html
<div class="card">
  <img src="/image.jpg" class="card-img-top" alt="...">
  <div class="card-body">
    <h5 class="card-title">Card with Image</h5>
    <p class="card-text">Description here</p>
    <button class="btn btn-primary">Action</button>
  </div>
</div>
```

### Card de Estadística (Dashboard)

```html
<div class="card">
  <div class="card-body">
    <div class="d-flex align-items-center">
      <div>
        <h4 class="card-title">1,234</h4>
        <p class="text-muted mb-0">Total Orders</p>
      </div>
      <div class="ms-auto">
        <i class="fa fa-shopping-cart fs-3 text-primary"></i>
      </div>
    </div>
  </div>
</div>
```

### Card Horizontal

```html
<div class="card">
  <div class="row g-0">
    <div class="col-md-4">
      <img src="/image.jpg" class="card-img-top" alt="...">
    </div>
    <div class="col-md-8">
      <div class="card-body">
        <h5 class="card-title">Horizontal Card</h5>
        <p class="card-text">Content here</p>
      </div>
    </div>
  </div>
</div>
```

---

## 📋 Formularios

### Input Básico

```html
<div class="mb-3">
  <label for="inputName" class="form-label">Name</label>
  <input type="text" class="form-control" id="inputName" placeholder="Enter name">
</div>
```

### Input con Icono

```html
<div class="mb-3">
  <label for="inputEmail" class="form-label">Email</label>
  <div class="input-group">
    <span class="input-group-text">
      <i class="fa fa-envelope></i>
    </span>
    <input type="email" class="form-control" id="inputEmail" placeholder="Email">
  </div>
</div>
```

### Select/Dropdown

```html
<div class="mb-3">
  <label for="selectCategory" class="form-label">Category</label>
  <select class="form-select" id="selectCategory">
    <option selected>Choose...</option>
    <option value="1">Option 1</option>
    <option value="2">Option 2</option>
  </select>
</div>
```

### Checkbox

```html
<div class="form-check">
  <input class="form-check-input" type="checkbox" id="agree">
  <label class="form-check-label" for="agree">
    I agree to the terms
  </label>
</div>
```

### Radio Button

```html
<div class="form-check">
  <input class="form-check-input" type="radio" name="option" id="option1" value="option1">
  <label class="form-check-label" for="option1">
    Option 1
  </label>
</div>
```

### Textarea

```html
<div class="mb-3">
  <label for="message" class="form-label">Message</label>
  <textarea class="form-control" id="message" rows="4"></textarea>
</div>
```

### Validación de Formulario

```html
<form class="needs-validation" novalidate>
  <div class="mb-3">
    <label for="inputEmail" class="form-label">Email</label>
    <input type="email" class="form-control is-invalid" id="inputEmail" required>
    <div class="invalid-feedback">
      Please provide a valid email.
    </div>
  </div>
</form>
```

---

## 📊 Tablas

### Tabla Básica

```html
<div class="table-responsive">
  <table class="table">
    <thead>
      <tr>
        <th>Column 1</th>
        <th>Column 2</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Data 1</td>
        <td>Data 2</td>
        <td>
          <button class="btn btn-sm btn-primary">Edit</button>
          <button class="btn btn-sm btn-danger">Delete</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
```

### Tabla Striped (Rayas)

```html
<table class="table table-striped table-hover">
  <!-- Contenido -->
</table>
```

### Tabla Bordered (Bordes)

```html
<table class="table table-bordered">
  <!-- Contenido -->
</table>
```

---

## 🔔 Alertas

### Alert Básica

```html
<div class="alert alert-primary">
  <i class="fa fa-circle-info me-2"></i>
  This is a primary alert
</div>
```

### Variantes

```html
<div class="alert alert-success">Success message</div>
<div class="alert alert-danger">Danger message</div>
<div class="alert alert-warning">Warning message</div>
<div class="alert alert-info">Info message</div>
```

### Alert Dismissible (Cerrable)

```html
<div class="alert alert-primary alert-dismissible fade show">
  <strong>Success!</strong> Message content
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
```

---

## 🪟 Modales

### Modal Básico

```html
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalExample">
  Open Modal
</button>

<div class="modal fade" id="modalExample" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modal Title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Modal content here
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save</button>
      </div>
    </div>
  </div>
</div>
```

### Modal Sizes

```html
<!-- Large Modal -->
<div class="modal-dialog modal-lg"></div>

<!-- Small Modal -->
<div class="modal-dialog modal-sm"></div>

<!-- Extra Large Modal -->
<div class="modal-dialog modal-xl"></div>
```

---

## 🏷️ Badges

### Variantes

```html
<span class="badge bg-primary">Primary</span>
<span class="badge bg-success">Success</span>
<span class="badge bg-danger">Danger</span>
<span class="badge bg-warning text-dark">Warning</span>
<span class="badge bg-info">Info</span>
```

### Badges Pill (Redondeados)

```html
<span class="badge rounded-pill bg-primary">Primary</span>
<span class="badge rounded-pill bg-success">Active</span>
```

---

## 📍 Breadcrumb (Navegación)

```html
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Home</a></li>
    <li class="breadcrumb-item"><a href="#">Library</a></li>
    <li class="breadcrumb-item active">Data</li>
  </ol>
</nav>
```

---

## 💬 Tooltips y Popovers

### Tooltip

```html
<button class="btn btn-primary"
  data-bs-toggle="tooltip"
  data-bs-placement="top"
  title="This is a tooltip">
  Hover me
</button>
```

### Popover

```html
<button class="btn btn-primary"
  data-bs-toggle="popover"
  data-bs-placement="right"
  title="Popover Title"
  data-bs-content="Popover content here">
  Click me
</button>
```

---

## ⏳ Spinners (Cargando)

### Spinner Básico

```html
<div class="spinner-border" role="status">
  <span class="visually-hidden">Loading...</span>
</div>
```

### Variantes

```html
<!-- Spinner Coloreado -->
<div class="spinner-border text-primary"></div>
<div class="spinner-border text-success"></div>

<!-- Spinner Creciente -->
<div class="spinner-grow" role="status">
  <span class="visually-hidden">Loading...</span>
</div>

<!-- Tamaño Pequeño -->
<div class="spinner-border spinner-border-sm"></div>
```

---

## 🔉 Progress Bars

### Progress Básico

```html
<div class="progress">
  <div class="progress-bar" role="progressbar" style="width: 50%"></div>
</div>
```

### Progress Coloreado

```html
<div class="progress">
  <div class="progress-bar bg-success" style="width: 100%"></div>
</div>
```

### Progress Striped

```html
<div class="progress">
  <div class="progress-bar progress-bar-striped" style="width: 75%"></div>
</div>
```

---

## 📌 Offcanvas (Panel Lateral)

```html
<button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample">
  Open Sidebar
</button>

<div class="offcanvas offcanvas-start" id="offcanvasExample">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Sidebar Title</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    Sidebar content here
  </div>
</div>
```

---

## 🔗 Notas Importantes

- Todos los componentes requieren **Bootstrap 5.3 CSS**
- Los componentes interactivos necesitan **Bootstrap 5.3 JS**
- Usa clases de **utilidad Bootstrap** para espaciado rápido
- Mantén consistencia de **colores y tamaños**
- Siempre incluye **iconos relevantes** en botones y etiquetas
