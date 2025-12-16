# Plantilla Modernize Bootstrap Admin - Referencia Completa

## 📋 Descripción General

**Modernize** es un panel administrativo profesional basado en Bootstrap 5.3, diseñado para aplicaciones empresariales modernas. Es la plantilla base para todo diseño UI/UX en Alsernet.

**URL Oficial:** https://bootstrapdemos.adminmart.com/modernize/dist/

---

## 🎨 Características Principales

| Característica | Descripción |
|---|---|
| **Framework Base** | Bootstrap 5.3 |
| **Iconografía** | Tabler Icons + Solar Icons |
| **Tema** | Light/Dark mode configurable |
| **Responsividad** | Mobile-first, 100% responsive |
| **Componentes** | 50+ componentes reutilizables |
| **Gráficos** | ApexCharts integrado |
| **Animaciones** | Transiciones CSS suaves |

---

## 🏗️ Estructura Arquitectónica

```
Modernize/
├── Layout Principal
│   ├── Header/Navbar (con notificaciones, usuario, idioma)
│   ├── Sidebar (navegación principal)
│   ├── Content Area (área de trabajo)
│   └── Footer (información adicional)
├── Dashboards
│   ├── Modern
│   ├── eCommerce
│   ├── NFT
│   ├── Crypto
│   ├── General
│   └── Music
├── Apps Integradas
│   ├── Calendar
│   ├── Kanban
│   ├── Chat
│   ├── Email
│   ├── Notes
│   ├── Contact
│   └── Invoice
├── Páginas de Autenticación
│   ├── Login
│   ├── Register
│   ├── Forgot Password
│   └── Two Steps Verification
└── Frontend Pages
    ├── Homepage
    ├── About
    ├── Contact
    └── Blog
```

---

## 🎯 Paleta de Colores

### Colores Primarios
- **Primary:** `#90bb13` (Azul moderno)
- **Secondary:** `#6C757D` (Gris neutral)
- **Success:** `#13C672` (Verde éxito)
- **Warning:** `#FEC90F` (Amarillo alerta)
- **Danger:** `#FA896B` (Rojo error)
- **Info:** `#39B8E0` (Cian información)

### Modo Oscuro
Los colores se adaptan automáticamente al tema oscuro. El contraste se mantiene para accesibilidad.

---

## 📐 Sistema de Grid

Utiliza Bootstrap 5.3 Grid System:
```html
<div class="row">
  <div class="col-lg-6 col-md-12"></div>
  <div class="col-lg-6 col-md-12"></div>
</div>
```

**Breakpoints:**
- `xs` (< 576px)
- `sm` (≥ 576px)
- `md` (≥ 768px)
- `lg` (≥ 992px)
- `xl` (≥ 1200px)
- `xxl` (≥ 1400px)

---

## 🔤 Tipografía

| Elemento | Fuente | Tamaño | Peso |
|---|---|---|---|
| **H1** | System Font | 36px | 600 |
| **H2** | System Font | 28px | 600 |
| **H3** | System Font | 24px | 600 |
| **Body** | System Font | 14px | 400 |
| **Small** | System Font | 12px | 400 |

---

## 💫 Animaciones y Transiciones

- **Duration:** 300ms (estándar)
- **Easing:** ease-in-out
- **Efectos disponibles:**
  - Fade in/out
  - Slide up/down
  - Scale
  - Rotate

---

## 🔗 Iconografía Disponible

### Tabler Icons (Principal)
- 1000+ iconos disponibles
- Consistentes y escalables
- Uso: `<i class="fa fa-icons"></i>`

### Solar Icons (Alternativa)
- Iconos modernos y coloridos
- Uso: `<i class="icon-icon-name"></i>`

**Ejemplo:**
```html
<i class="fa fa-gauge-high"></i>  <!-- Dashboard -->
<i class="ti ti-settings"></i>   <!-- Configuración -->
<i class="fa fa-user></i>       <!-- Usuario -->
```

---

## 🎛️ Modo Oscuro/Claro

El sistema detecta automáticamente la preferencia del usuario:

```html
<!-- Selector manual -->
<button onclick="toggleTheme()">Toggle Theme</button>

<!-- CSS condicional -->
[data-bs-theme="dark"] {
  background: #1a1a1a;
  color: #ffffff;
}
```

---

## 📱 Componentes Disponibles

Ver archivo **[components.md](./components.md)** para documentación completa de:
- Buttons
- Cards
- Forms
- Tables
- Modals
- Alerts
- Y más...

---

## 🎯 Layouts Predefinidos

Ver archivo **[layouts.md](./layouts.md)** para:
- Estructura de dashboard
- Páginas de autenticación
- Páginas de aplicación
- Plantillas personalizadas

---

## 📐 Reglas de Diseño

Ver archivo **[design-rules.md](./design-rules.md)** para:
- Espaciado (padding, margin)
- Bordes y sombras
- Consistencia visual
- Mejores prácticas

---

## 🚀 Uso en Alsernet

Cuando necesites diseñar un componente nuevo:

1. **Consulta primero** esta documentación
2. **Basate en componentes existentes** de Modernize
3. **Mantén consistencia** de colores, espaciado, tipografía
4. **Usa clases Bootstrap** directamente cuando sea posible
5. **Evita CSS custom** si existe equivalente en Bootstrap

---

## 📝 Notas Importantes

- ✅ Modernize usa **Bootstrap 5.3 CDN o local**
- ✅ Todos los componentes son **100% responsivos**
- ✅ Compatible con **navegadores modernos**
- ✅ Accesibilidad **WCAG 2.1 AA**
- ⚠️ No modificar estilos base sin documentar cambios

---

## 🔗 Recursos Relacionados

- [Bootstrap 5.3 Oficial](https://getbootstrap.com/)
- [Tabler Icons](https://tabler-icons.io/)
- [ApexCharts](https://apexcharts.com/)
- Plantilla: https://bootstrapdemos.adminmart.com/modernize/dist/
