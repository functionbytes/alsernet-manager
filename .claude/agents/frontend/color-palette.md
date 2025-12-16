# 🎨 Color Palette - Alsernet Frontend

**Official color palette for all frontend components, tablas, alerts, y diseños en Alsernet.**

---

## 🌿 Primary Color - Brand Green

```scss
$primary: #90bb13 !important;
$primary-rgb: 144, 187, 19;
```

**Usage:**
- Table headers (th) backgrounds
- Primary buttons
- Active navigation items
- Links and highlights
- Icon colors
- Borders on primary elements

**Hex:** `#90bb13`
**RGB:** `rgb(144, 187, 19)`
**CSS Class:** `.text-primary`, `.bg-primary`, `.btn-primary`

---

## ⬛ Neutral Colors - Black & Grays

### Black (Text, Headings, Dark Elements)
```scss
$black: #000000;
$text-dark: #1f2937;
$text-primary: #111827;
```

**Usage:**
- Main text color
- Headings (h1-h6)
- Strong text
- Dark backgrounds for contrast

### Grays (Backgrounds, Borders, Subtle Text)
```scss
$gray-50:   #f9fafb;  // Lightest - subtle backgrounds
$gray-100:  #f3f4f6;  // Light - code blocks, inputs
$gray-200:  #e5e7eb;  // Borders
$gray-300:  #d1d5db;  // Hover states
$gray-400:  #9ca3af;  // Secondary text
$gray-500:  #6b7280;  // Muted text
$gray-600:  #4b5563;  // Dark secondary
$gray-700:  #374151;  // Very dark text
$gray-800:  #1f2937;  // Almost black
$gray-900:  #111827;  // Black
```

**Usage by Shade:**
- `$gray-50` - Page backgrounds, subtle card backgrounds
- `$gray-100` - Code blocks, input backgrounds, disabled states
- `$gray-200` - Borders, separators, disabled button backgrounds
- `$gray-300` - Hover borders, subtle dividers
- `$gray-400` - Secondary labels, muted text
- `$gray-500` - Placeholder text, secondary descriptions
- `$gray-600` - Secondary headings
- `$gray-700` - Body text alternative
- `$gray-800` - Strong secondary text
- `$gray-900` - Primary black text

---

## 🟢 Status Colors

### Success (Green)
```scss
$success-light:   #d1fae5;  // Background
$success-medium:  #10b981;  // Text/Icon
$success-dark:    #059669;  // Dark variant

// Usage
.bg-success-subtle { background-color: #d1fae5; }
.text-success { color: #059669; }
.border-success { border-color: #10b981; }
```

**Usage:**
- ✅ Check marks, enabled states
- Active/Running status
- Positive alerts
- Valid form fields

### Danger (Red)
```scss
$danger-light:   #fee2e2;  // Background
$danger-medium:  #ef4444;  // Text/Icon
$danger-dark:    #dc2626;  // Dark variant

// Usage
.bg-danger-subtle { background-color: #fee2e2; }
.text-danger { color: #dc2626; }
.border-danger { border-color: #ef4444; }
```

**Usage:**
- ❌ Errors, failed states
- Deletions, warnings
- Invalid form fields
- Critical alerts

### Warning (Orange/Amber)
```scss
$warning-light:   #fef3c7;  // Background
$warning-medium:  #f59e0b;  // Text/Icon
$warning-dark:    #d97706;  // Dark variant

// Usage
.bg-warning-subtle { background-color: #fef3c7; }
.text-warning { color: #d97706; }
.border-warning { border-color: #f59e0b; }
```

**Usage:**
- ⚠️ Warnings, pending states
- Caution alerts
- Processing/loading states
- Attention needed

### Info (Blue)
```scss
$info-light:   #dbeafe;  // Background
$info-medium:  #3b82f6;  // Text/Icon
$info-dark:    #1e40af;  // Dark variant

// Usage
.bg-info-subtle { background-color: #dbeafe; }
.text-info { color: #1e40af; }
.border-info { border-color: #3b82f6; }
```

**Usage:**
- ℹ️ Information messages
- Help/tips
- Informational alerts
- Secondary actions

---

## 📊 Component-Specific Color Usage

### Tables
```scss
// Table Header
table thead th {
    background-color: $gray-50;    // Light gray background
    color: $gray-700;              // Dark gray text
    border-bottom: 2px solid $primary;  // GREEN border
}

// Table Body
table tbody tr {
    border-bottom: 1px solid $gray-200;  // Gray border
}

table tbody tr:hover {
    background-color: rgba($primary, 0.04);  // Subtle green tint on hover
}

// Table Cells
table td {
    color: $text-primary;
}
```

### Buttons
```scss
// Primary Button (Green)
.btn-primary {
    background-color: $primary;
    border-color: $primary;
    color: white;
}

.btn-primary:hover {
    background-color: darken($primary, 10%);
    border-color: darken($primary, 10%);
}

// Secondary Button (Gray)
.btn-secondary {
    background-color: $gray-200;
    border-color: $gray-300;
    color: $gray-700;
}

.btn-secondary:hover {
    background-color: $gray-300;
    border-color: $gray-400;
}

// Outline Button
.btn-outline-primary {
    border-color: $primary;
    color: $primary;
}

.btn-outline-primary:hover {
    background-color: rgba($primary, 0.1);
    border-color: $primary;
}
```

### Alerts
```scss
// Success Alert
.alert-success {
    background-color: $success-light;
    border: 1px solid #a7f3d0;
    color: $success-dark;
}

// Danger Alert
.alert-danger {
    background-color: $danger-light;
    border: 1px solid #fecaca;
    color: $danger-dark;
}

// Warning Alert
.alert-warning {
    background-color: $warning-light;
    border: 1px solid #fcd34d;
    color: $warning-dark;
}

// Info Alert
.alert-info {
    background-color: $info-light;
    border: 1px solid #93c5fd;
    color: $info-dark;
}
```

### Badges
```scss
// Primary Badge (Green)
.badge-primary {
    background-color: rgba($primary, 0.15);
    color: $primary;
    border: 1px solid rgba($primary, 0.3);
}

// Status Badges
.badge-success {
    background-color: $success-light;
    color: $success-dark;
    border: 1px solid #a7f3d0;
}

.badge-danger {
    background-color: $danger-light;
    color: $danger-dark;
    border: 1px solid #fecaca;
}

.badge-warning {
    background-color: $warning-light;
    color: $warning-dark;
    border: 1px solid #fcd34d;
}

.badge-info {
    background-color: $info-light;
    color: $info-dark;
    border: 1px solid #93c5fd;
}
```

### Cards
```scss
// Card Container
.card {
    background-color: #ffffff;
    border: 1px solid $gray-200;
    border-radius: 10px;
}

.card:hover {
    border-color: $primary;
    box-shadow: 0 10px 25px rgba($primary, 0.08);
}

// Card Header
.card-header {
    background-color: $gray-50;
    border-bottom: 1px solid $gray-200;
    color: $text-primary;
}

// Card Body
.card-body {
    color: $text-primary;
}
```

### Navigation
```scss
// Nav Link
.nav-link {
    color: $gray-600;
}

.nav-link:hover {
    color: $primary;
}

.nav-link.active {
    color: $primary;
    border-bottom: 3px solid $primary;
}

// Tab Pills
.nav-pills .nav-link.active {
    background-color: $primary;
    color: white;
}
```

### Form Elements
```scss
// Input Focus
input:focus,
textarea:focus,
select:focus {
    border-color: $primary;
    box-shadow: 0 0 0 3px rgba($primary, 0.1);
}

// Form Label
label {
    color: $text-primary;
    font-weight: 600;
}

// Placeholder
::placeholder {
    color: $gray-400;
}

// Disabled State
:disabled,
.disabled {
    background-color: $gray-100;
    color: $gray-400;
    cursor: not-allowed;
}
```

---

## 🎨 SCSS Variables Template

Add this to the top of `public/managers/css/style.scss`:

```scss
/* ===== Alsernet Color Palette ===== */

// Primary Brand Color
$primary: #90bb13 !important;
$primary-rgb: 144, 187, 19;

// Black & Neutrals
$black: #000000;
$text-dark: #1f2937;
$text-primary: #111827;

// Grays (50 = light, 900 = dark)
$gray-50:   #f9fafb;
$gray-100:  #f3f4f6;
$gray-200:  #e5e7eb;
$gray-300:  #d1d5db;
$gray-400:  #9ca3af;
$gray-500:  #6b7280;
$gray-600:  #4b5563;
$gray-700:  #374151;
$gray-800:  #1f2937;
$gray-900:  #111827;

// Status Colors
$success-light:   #d1fae5;
$success-medium:  #10b981;
$success-dark:    #059669;

$danger-light:    #fee2e2;
$danger-medium:   #ef4444;
$danger-dark:     #dc2626;

$warning-light:   #fef3c7;
$warning-medium:  #f59e0b;
$warning-dark:    #d97706;

$info-light:      #dbeafe;
$info-medium:     #3b82f6;
$info-dark:       #1e40af;

// Utility
$border-light: 1px solid $gray-200;
$border-primary: 1px solid $primary;
```

---

## 📍 File Location

**Primary SCSS file:**
```
public/managers/css/style.scss
```

**CSS compiled output:**
```
public/managers/css/style.css (auto-compiled from style.scss)
```

**Import in Blade templates:**
```blade
<!-- Use compiled CSS -->
<link rel="stylesheet" href="{{ asset('managers/css/style.css') }}">
```

**Compile SCSS to CSS:**
```bash
# If using Laravel Mix/Vite
npm run dev    # Development
npm run build  # Production
```

---

## ✅ Checklist for Frontend Agent

Before creating any component:
- [ ] Use `$primary` (#90bb13) for main actions
- [ ] Use `$gray-*` for backgrounds and borders
- [ ] Use status colors (success/danger/warning/info) for alerts
- [ ] Put all CSS in `public/managers/css/style.scss`
- [ ] Add SCSS variables at top of file
- [ ] Check `public/managers/libs/` for existing libraries
- [ ] Document any new libraries used

---

## 🔄 How to Use in Frontend Agent Requests

**When requesting a component:**
```
"Create a table that:
- Uses primary color #90bb13 for header
- Uses gray backgrounds for rows
- Shows green badges for success status
- Shows red badges for errors
- Reference color-palette.md for exact colors"
```

**Or simply:**
```
"Create a form with Modernize + Alsernet colors
- Primary: #90bb13
- Use color-palette.md for reference"
```

---

**Last Updated:** 30 de Noviembre de 2025
**Version:** 1.0
**Status:** Production Ready ✅
