# 📚 Documentación Frontend - Plantilla Modernize Bootstrap

Guía completa y estructurada de la plantilla Modernize para Alsernet. Diseñada para ser utilizada por Context7 y agentes de IA.

---

## 🗂️ ESTRUCTURA DE CARPETAS

```
docs/frontend/
├── README.md (TÚ ESTÁS AQUÍ)
├── MASTER_INDEX.md
│
├── AGENTE-FRONTEND-ESPECIFICACION-JQUERY.md  ← ⭐ ARQUITECTURA FINAL
│
├── components/              ← COMPONENTES UI
│   ├── component-library-detailed.md    (Biblioteca completa con HTML)
│   ├── quick-component-search.md        (Búsqueda rápida)
│   └── COMPONENT_LIBRARY_SETUP.txt
│
├── design/                  ← SISTEMA DE DISEÑO
│   ├── design-rules.md      (Colores, espaciado, tipografía)
│   └── modernize-overview.md (Overview de la plantilla)
│
├── layouts/                 ← ESTRUCTURAS DE PÁGINA
│   └── layouts.md           (Layouts listos para copiar)
│
├── patterns/                ← ⭐ PATRONES JQUERY (NUEVO)
│   ├── ajax-patterns.md     (GET, POST, uploads, errores)
│   ├── real-time-patterns.md (Echo, WebSockets, broadcasting)
│   ├── form-patterns.md     (Validación, dinámico, cascadas)
│   ├── modal-table-patterns.md (Bootstrap modals, DataTables)
│   └── cache-storage-patterns.md (localStorage, IndexedDB)
│
├── reference/               ← REFERENCIA RÁPIDA
│   ├── components.md        (Componentes básicos)
│   ├── modernize-complete-index.md (Índice de URLs)
│   └── MODERNIZE-COMPLETE-COMPONENTS.md
│
└── analysis/                ← DOCUMENTOS DE ANÁLISIS (ARCHIVOS)
    ├── ALL-PAGES-ANALYZED.md
    ├── all-pages-complete-catalog.md
    └── ...
```

---

## 🎯 GUÍA RÁPIDA POR USO

### ✅ "Necesito un componente específico"
**Ir a:** `components/quick-component-search.md`
- Búsqueda rápida de componentes
- Tabla de "Qué necesito" → "Qué componente usar"
- 100+ componentes indexados

### ✅ "Necesito código HTML listo para copiar"
**Ir a:** `components/component-library-detailed.md`
- 93+ componentes con HTML exacto
- UI Components, Formularios, Tablas, Charts, Auth
- Listo para copiar y pegar

### ✅ "Necesito estructura de página completa"
**Ir a:** `layouts/layouts.md`
- Master layout (header, sidebar, footer)
- Dashboard, Listados, Formularios
- Responsive patterns

### ✅ "¿Qué colores/iconos/espaciado debo usar?"
**Ir a:** `design/design-rules.md`
- Paleta de colores permitidos
- Sistema de iconos (Tabler Icons)
- Escala de espaciado Bootstrap

### ✅ "¿Cuál es la filosofía de Modernize?"
**Ir a:** `design/modernize-overview.md`
- Características principales
- Paleta de colores
- Sistema de grid
- Tipografía y animaciones

### ✅ "Necesito encontrar una URL de demostración"
**Ir a:** `reference/modernize-complete-index.md`
- Índice maestro con TODAS las URLs
- Links directos a cada página/app
- Acceso a demostración Modernize

### ✅ "¿Cómo hago AJAX con jQuery?"
**Ir a:** `patterns/ajax-patterns.md`
- GET, POST, PUT, DELETE requests
- File uploads con progress bar
- Error handling y validación
- Batch requests y retry logic

### ✅ "Cómo implementar tiempo real (WebSockets)"
**Ir a:** `patterns/real-time-patterns.md`
- Laravel Echo setup y configuración
- Public, Private, Presence channels
- Listeners y event handling
- Real-time dashboard y tablas

### ✅ "¿Cómo valido formularios?"
**Ir a:** `patterns/form-patterns.md`
- Validación cliente-side
- Validación en tiempo real
- Manejo de errores del servidor
- Campos dinámicos y cascadas

### ✅ "¿Cómo manejo modales y tablas?"
**Ir a:** `patterns/modal-table-patterns.md`
- Bootstrap modals open/close
- DataTables con servidor-side
- Inline editing
- Bulk actions y bulk delete

### ✅ "¿Cómo cacheo datos en el cliente?"
**Ir a:** `patterns/cache-storage-patterns.md`
- localStorage para preferencias
- IndexedDB para datasets grandes
- Sync cache con servidor
- Offline fallback

### ✅ "¿Cuál es la arquitectura final?"
**Ir a:** `AGENTE-FRONTEND-ESPECIFICACION-JQUERY.md`
- Stack completo (Blade + jQuery + Real-time)
- 41 capacidades del agente
- Estructura de carpetas
- Guías de implementación

### ✅ "¿Qué librerías jQuery hay disponibles?"
**Ir a:** `JQUERY_LIBRARIES_COMPLETE.md`
- 24+ librerías jQuery documentadas
- Ubicación en `public/managers/libs/`
- Ejemplos de uso para cada librería
- Quick reference (cuál usar cuándo)

### ✅ "¿Cómo valido formularios?"
**Ir a:** `patterns/jquery-validate-patterns.md`
- 10 patrones de validación
- jQuery Validate (principal)
- Validación server-side
- Bootstrap 5 integration
- Ejemplos completos

---

## 🚀 Flujos de Trabajo Común

### Scenario 1: Crear una tabla de productos

```
1. Ve a: components/quick-component-search.md
   ↓
2. Busca "Table" o "Listado"
   ↓
3. Copia HTML de: components/component-library-detailed.md
   ↓
4. Adapta con: design/design-rules.md (colores, espaciado)
   ↓
5. Valida responsive en: design/design-rules.md
```

### Scenario 2: Crear página de formulario

```
1. Ve a: layouts/layouts.md
   ↓
2. Copia "Layout Formulario"
   ↓
3. Agrega componentes de: components/component-library-detailed.md
   ↓
4. Revisa: design/design-rules.md para estándares
```

### Scenario 3: Crear dashboard

```
1. Ve a: layouts/layouts.md → "Layout Dashboard"
   ↓
2. Copia estructura base
   ↓
3. Agrega cards/charts de: components/component-library-detailed.md
   ↓
4. Valida con: design/design-rules.md
```

---

## 🎨 Valores de Diseño Rápidos

**Colores:**
```
#90bb13 (Primario)   #13C672 (Éxito)   #FA896B (Peligro)
#FEC90F (Advertencia) #39B8E0 (Info)   #6C757D (Gris)
```

**Iconos:** Tabler Icons → `ti ti-{icon-name}`

**Espaciado:** Bootstrap Scale → `mb-2`, `p-3`, `gap-2`

**Responsive:**
```
xs: <576px    sm: ≥576px    md: ≥768px
lg: ≥992px    xl: ≥1200px   xxl: ≥1400px
```

---

## 🔗 URLs Clave

| Recurso | Link |
|---------|------|
| **Demo Modernize** | https://bootstrapdemos.adminmart.com/modernize/dist/main/index.html |
| **Tabler Icons** | https://tabler-icons.io/ |
| **Bootstrap Docs** | https://getbootstrap.com/docs/5.3/ |

---

## ✅ Validación Rápida

Antes de completar, verifica:
- ✓ Colores en paleta permitida
- ✓ Espaciado es Bootstrap (mb-2, p-3, etc.)
- ✓ Tipografía consistente
- ✓ Responsive (móvil, tablet, desktop)
- ✓ Iconos de Tabler Icons
- ✓ Sin CSS custom innecesario

---

## ⭐ IMPORTANTE: Stack Final jQuery

**Alsernet usa jQuery para TODO el DOM y AJAX:**

```
Blade Templates    ← Server-side (Laravel)
    ↓
jQuery            ← DOM manipulation + AJAX (Heavy usage)
    ↓
Bootstrap 5.3     ← UI Components (Modernize)
    ↓
Laravel Echo      ← Real-time WebSockets
    ↓
DataTables        ← Advanced tables (jQuery plugin)
    ↓
Toastr            ← Notifications (jQuery plugin)
```

**NO usamos:**
- ❌ Vue.js
- ❌ HTMX
- ❌ React
- ❌ Complex Vanilla JS

**SÍ usamos mucho:**
- ✅ $.ajax() / $.get() / $.post()
- ✅ jQuery DOM manipulation
- ✅ Event delegation
- ✅ jQuery plugins
- ✅ Bootstrap modal via jQuery
- ✅ DataTables with AJAX

---

## 📌 Para Agentes de IA

Esta documentación está optimizada para ser usada por agentes y Context7:

1. **Consulta modular:** Cada carpeta trata un tema específico
2. **Sin redundancia:** Archivos de análisis separados del código operativo
3. **Código listo:** `components/` contiene HTML para copiar directamente
4. **Índices claros:** `quick-component-search.md` es una tabla de búsqueda
5. **Referencias:** `design/` contiene valores exactos (colores, espaciado)
6. **Patrones jQuery:** `patterns/` contiene 50+ ejemplos de código
7. **Casos reales:** Cada patrón incluye ejemplos de Alsernet (warehouse, returns, tickets)

**Cómo usar este documento:**
- Lee `README.md` (este archivo) primero
- Lee `AGENTE-FRONTEND-ESPECIFICACION-JQUERY.md` para entender arquitectura
- Consulta `patterns/` para código listo para copiar
- Usa `quick-component-search.md` para búsquedas de componentes
- Copia HTML de `component-library-detailed.md`
- Valida con `design-rules.md`
- Revisa `patterns/ajax-patterns.md` para cualquier operación con servidor
