# Layouts Modernize - Plantillas Predefinidas

Estructura y patrones de layouts disponibles en Modernize para diferentes tipos de páginas.

---

## 🏠 Layout Principal (Master Layout)

Estructura base para todas las páginas del admin:

```html
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Alsernet</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Tabler Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tabler-icons@2.0.0/tabler-icons.css">

  <!-- Custom Styles -->
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
  <!-- Header/Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
      <a class="navbar-brand" href="/">
        <i class="fa fa-cube"></i> Alsernet
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="#">Notifications</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
              <img src="/avatar.jpg" alt="User" class="rounded-circle" width="32">
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
              <li><a class="dropdown-item" href="#">Profile</a></li>
              <li><a class="dropdown-item" href="#">Settings</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#">Logout</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <nav class="col-lg-3 col-xl-2 d-lg-block bg-light sidebar">
        <div class="position-sticky">
          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link active" href="/">
                <i class="fa fa-gauge-high me-2"></i>Dashboard
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/products">
                <i class="fa fa-box me-2"></i>Products
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/orders">
                <i class="fa fa-shopping-cart me-2"></i>Orders
              </a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                <i class="ti ti-settings me-2"></i>Settings
              </a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">General</a></li>
                <li><a class="dropdown-item" href="#">Security</a></li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>

      <!-- Main Content -->
      <main class="col-lg-9 col-xl-10 ms-sm-auto px-4">
        <div class="d-flex justify-content-between align-items-center my-4">
          <h1>Page Title</h1>
          <button class="btn btn-primary">Action</button>
        </div>

        <!-- Page Content -->
        <div class="row">
          <!-- Content goes here -->
        </div>
      </main>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-light mt-5 py-3">
    <div class="container-fluid text-center">
      <p class="text-muted mb-0">&copy; 2025 Alsernet. All rights reserved.</p>
    </div>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

---

## 📊 Layout Dashboard

Para paneles con estadísticas y gráficos:

```html
<div class="row mb-4">
  <!-- Stat Cards (KPIs) -->
  <div class="col-md-6 col-lg-3 mb-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div>
            <h6 class="card-title text-muted mb-0">Total Sales</h6>
            <h3 class="mb-0">$42,500</h3>
            <small class="text-success">
              <i class="fa fa-arrow-trend-up"></i> +12.5% from last month
            </small>
          </div>
          <div class="ms-auto">
            <i class="fa fa-shopping-cart fs-2 text-primary opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-6 col-lg-3 mb-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div>
            <h6 class="card-title text-muted mb-0">Total Users</h6>
            <h3 class="mb-0">8,245</h3>
            <small class="text-success">
              <i class="fa fa-arrow-trend-up"></i> +8% from last month
            </small>
          </div>
          <div class="ms-auto">
            <i class="fa fa-user fs-2 text-success opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- More stat cards... -->
</div>

<!-- Charts Row -->
<div class="row mb-4">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent border-0">
        <h5 class="card-title mb-0">Sales Overview</h5>
      </div>
      <div class="card-body">
        <!-- ApexChart here -->
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent border-0">
        <h5 class="card-title mb-0">Top Products</h5>
      </div>
      <div class="card-body">
        <!-- List here -->
      </div>
    </div>
  </div>
</div>
```

---

## 📋 Layout Lista/Tabla

Para páginas con listados de datos:

```html
<div class="card border-0 shadow-sm">
  <!-- Header con búsqueda y acciones -->
  <div class="card-header bg-transparent border-bottom">
    <div class="row align-items-center">
      <div class="col">
        <h5 class="card-title mb-0">Products</h5>
      </div>
      <div class="col-auto">
        <div class="input-group input-group-sm">
          <input type="text" class="form-control" placeholder="Search...">
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-magnifying-glass"></i>
          </button>
        </div>
      </div>
      <div class="col-auto ms-2">
        <button class="btn btn-primary btn-sm">
          <i class="fa fa-plus me-1"></i>Add New
        </button>
      </div>
    </div>
  </div>

  <!-- Tabla -->
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>
              <input class="form-check-input" type="checkbox">
            </th>
            <th>Product Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <input class="form-check-input" type="checkbox">
            </td>
            <td><strong>Product Name</strong></td>
            <td>Electronics</td>
            <td>$299.99</td>
            <td>
              <span class="badge bg-info">45 units</span>
            </td>
            <td>
              <span class="badge bg-success">Active</span>
            </td>
            <td>
              <button class="btn btn-sm btn-primary" title="Edit">
                <i class="fa fa-pen-to-square"></i>
              </button>
              <button class="btn btn-sm btn-danger" title="Delete">
                <i class="fa fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Pagination -->
  <div class="card-footer bg-light">
    <nav aria-label="Page navigation">
      <ul class="pagination mb-0">
        <li class="page-item"><a class="page-link" href="#">Previous</a></li>
        <li class="page-item"><a class="page-link" href="#">1</a></li>
        <li class="page-item active"><a class="page-link" href="#">2</a></li>
        <li class="page-item"><a class="page-link" href="#">3</a></li>
        <li class="page-item"><a class="page-link" href="#">Next</a></li>
      </ul>
    </nav>
  </div>
</div>
```

---

## 📝 Layout Formulario

Para páginas de creación/edición:

```html
<div class="row">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent border-bottom">
        <h5 class="card-title mb-0">Create Product</h5>
      </div>

      <div class="card-body">
        <form>
          <!-- Nombre -->
          <div class="mb-3">
            <label for="productName" class="form-label">Product Name</label>
            <input type="text" class="form-control" id="productName" placeholder="Enter product name" required>
          </div>

          <!-- Descripción -->
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" rows="4" placeholder="Product description"></textarea>
          </div>

          <!-- Fila: Categoría y Precio -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="category" class="form-label">Category</label>
              <select class="form-select" id="category" required>
                <option selected>Select category</option>
                <option>Electronics</option>
                <option>Clothing</option>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label for="price" class="form-label">Price</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" class="form-control" id="price" placeholder="0.00" required>
              </div>
            </div>
          </div>

          <!-- Fila: Stock y Estado -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="stock" class="form-label">Stock Quantity</label>
              <input type="number" class="form-control" id="stock" placeholder="0" required>
            </div>

            <div class="col-md-6 mb-3">
              <label for="status" class="form-label">Status</label>
              <select class="form-select" id="status">
                <option selected>Active</option>
                <option>Inactive</option>
              </select>
            </div>
          </div>

          <!-- Botones -->
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="fa fa-checkme-2"></i>Save Product
            </button>
            <button type="reset" class="btn btn-secondary">
              <i class="fa fa-xmark me-2"></i>Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Sidebar Info -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-transparent border-bottom">
        <h5 class="card-title mb-0">Help</h5>
      </div>
      <div class="card-body">
        <p class="text-muted small">
          Enter the product details carefully. All fields are required.
        </p>
      </div>
    </div>
  </div>
</div>
```

---

## 🔐 Layout Autenticación

Para login, registro, recuperar contraseña:

```html
<div class="container-fluid">
  <div class="row justify-content-center align-items-center min-vh-100">
    <div class="col-md-6 col-lg-4">
      <div class="card border-0 shadow-lg">
        <div class="card-body p-5">
          <!-- Logo -->
          <div class="text-center mb-4">
            <i class="fa fa-cube fs-1 text-primary"></i>
            <h4 class="mt-2">Welcome to Alsernet</h4>
          </div>

          <!-- Formulario Login -->
          <form>
            <div class="mb-3">
              <label for="email" class="form-label">Email Address</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-0">
                  <i class="fa fa-envelope></i>
                </span>
                <input type="email" class="form-control border-start-0" id="email" placeholder="you@example.com" required>
              </div>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-0">
                  <i class="fa fa-lock"></i>
                </span>
                <input type="password" class="form-control border-start-0" id="password" placeholder="••••••••" required>
              </div>
            </div>

            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="remember">
              <label class="form-check-label" for="remember">
                Remember me
              </label>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">
              Sign In
            </button>

            <hr>

            <p class="text-center text-muted mb-0">
              Don't have an account?
              <a href="/register">Sign up here</a>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
```

---

## 📱 Layout Responsive

Patrones para respuesta en mobile:

```html
<!-- Responsive Grid -->
<div class="row">
  <div class="col-12 col-sm-6 col-md-4 col-lg-3">
    <!-- Ocupa 12 cols en xs, 6 en sm, 4 en md, 3 en lg -->
  </div>
</div>

<!-- Responsive Table (Stack en mobile) -->
<div class="table-responsive">
  <table class="table">
    <!-- Tabla que se hace horizontal en mobile -->
  </table>
</div>

<!-- Responsive Flex -->
<div class="d-flex flex-column flex-md-row">
  <!-- Columna en mobile, fila en desktop -->
</div>
```

---

## 🎯 Patrones de Componentes por Página

| Página | Componentes | Layout |
|--------|------------|--------|
| **Dashboard** | Cards KPI, Charts, Tables | Grid 3-4 columnas |
| **Lista** | Tabla, búsqueda, acciones | Full width con sidebar |
| **Crear/Editar** | Formularios, validación | 2 columnas (form + info) |
| **Login** | Formulario simple | Centrado, full height |
| **Perfil** | Cards, formularios | 2 columnas |
| **Configuración** | Tabs, formularios | Tabs + contenido |

---

## 💡 Tips de Implementación

1. **Siempre usa cards** para agrupar contenido
2. **Responsive primero** - diseña mobile first
3. **Consistencia de spacing** - usa `mb-3`, `p-4`, etc.
4. **Shadow y bordes** - usa `shadow-sm` para profundidad
5. **Colores coherentes** - sigue paleta de Modernize
6. **Iconos relevantes** - cada acción debe tener icono
