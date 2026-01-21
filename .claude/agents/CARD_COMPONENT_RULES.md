# 🎴 CARD Component - Mandatory Rules

**All `.card` elements in Alsernet follow these strict rules**

---

## ⚡ MANDATORY CSS (Auto-Applied)

Every `.card` has these properties applied automatically in `public/managers/css/style.css`:

```css
.card {
    position: relative !important;
    overflow: hidden !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 10px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: #ffffff;
}

.card:hover {
    border-color: #90bb13 !important;
    box-shadow: 0 10px 25px rgba(144, 187, 19, 0.08) !important;
    transform: translateY(-2px);
}
```

---

## ✅ How to Use Cards

### Basic Card (Correct)
```html
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Card Title</h5>
        <p class="card-text">Card content here</p>
    </div>
</div>
```

### Card with Header
```html
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Header Title</h5>
    </div>
    <div class="card-body">
        <p>Card body content</p>
    </div>
</div>
```

### Card with Multiple Sections
```html
<div class="card">
    <div class="card-header bg-primary-gradient">
        <h5 class="text-white">Title</h5>
    </div>
    <div class="card-body">
        <!-- Main content -->
    </div>
    <div class="card-footer border-top">
        <!-- Footer content -->
    </div>
</div>
```

---

## 🎨 Color Variants

### Primary Card (Green - Alsernet)
```html
<div class="card">
    <div class="card-header" style="background-color: #90bb13;">
        <h5 class="text-white">Green Header</h5>
    </div>
    <div class="card-body">Content</div>
</div>
```

### Success Card (Green)
```html
<div class="card border-success">
    <div class="card-header bg-success-subtle">
        <h5>Success Message</h5>
    </div>
    <div class="card-body">Content here</div>
</div>
```

### Danger Card (Red)
```html
<div class="card border-danger">
    <div class="card-header bg-danger-subtle">
        <h5>Danger Message</h5>
    </div>
    <div class="card-body">Content here</div>
</div>
```

### Warning Card (Orange)
```html
<div class="card border-warning">
    <div class="card-header bg-warning-subtle">
        <h5>Warning Message</h5>
    </div>
    <div class="card-body">Content here</div>
</div>
```

---

## 📐 Sizing & Layout

### Full Width Card
```html
<div class="card w-100">
    <div class="card-body">
        Full width card
    </div>
</div>
```

### Grid Layout with Cards
```html
<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">Card 1</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">Card 2</div>
        </div>
    </div>
</div>
```

### Nested Cards
```html
<div class="card">
    <div class="card-body">
        <h5>Outer Card</h5>

        <div class="card mt-3" style="margin-bottom: 0;">
            <div class="card-body">
                Nested Card
            </div>
        </div>
    </div>
</div>
```

---

## 🎯 Common Patterns

### Info Card with Icon
```html
<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="material-symbols-rounded text-primary">info</span>
            <h6 class="mb-0">Information</h6>
        </div>
        <p class="text-muted">Description text</p>
    </div>
</div>
```

### Statistics Card
```html
<div class="card text-center">
    <div class="card-body">
        <h3 class="text-primary mb-1">1,234</h3>
        <p class="text-muted mb-0">Total Users</p>
    </div>
</div>
```

### Interactive Card (Clickable)
```html
<div class="card" style="cursor: pointer;">
    <div class="card-body">
        <h5 class="card-title">Click Me</h5>
        <p class="card-text">This card is interactive</p>
    </div>
</div>

<style>
    .card {
        cursor: pointer;
    }
</style>
```

---

## ❌ NEVER Do This With Cards

```html
<!-- ❌ DON'T use inline overflow-hidden -->
<div class="card" style="overflow: hidden;">...</div>

<!-- ❌ DON'T remove position-relative -->
<div class="card" style="position: static;">...</div>

<!-- ❌ DON'T override with !important in inline -->
<div class="card" style="position: absolute !important;">...</div>

<!-- ❌ DON'T use div instead of .card -->
<div style="border: 1px solid; padding: 1rem;">...</div>

<!-- ❌ DON'T add custom styles to .card class -->
<style>
    .card {
        overflow: visible !important; /* WRONG! -->
    }
</style>
```

---

## 📋 Card Structure Best Practices

### Recommended Order
1. `.card` container
2. `.card-header` (optional)
3. `.card-body` (main content)
4. `.card-footer` (optional)

### Example
```html
<div class="card">
    <!-- 1. Header (optional) -->
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Title</h5>
    </div>

    <!-- 2. Body (required) -->
    <div class="card-body">
        <p>Main content goes here</p>
    </div>

    <!-- 3. Footer (optional) -->
    <div class="card-footer border-top bg-light">
        <button class="btn btn-primary">Action</button>
    </div>
</div>
```

---

## 🔄 Card with Transitions

### Hover Effect
```html
<div class="card">
    <!-- Auto-applies: border color change, shadow, transform -->
    <div class="card-body">
        Hover over me
    </div>
</div>
```

The `.card` automatically gets:
- ✅ Border color → #90bb13 on hover
- ✅ Box shadow enhancement
- ✅ Transform: translateY(-2px)
- ✅ All transition: 0.3s ease

---

## 📱 Responsive Cards

### Mobile First
```html
<div class="row g-4">
    <!-- Stack on mobile, 2 columns on tablet, 3 on desktop -->
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="card">
            <div class="card-body">Card 1</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="card">
            <div class="card-body">Card 2</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="card">
            <div class="card-body">Card 3</div>
        </div>
    </div>
</div>
```

---

## 🎓 Reference

**CSS Location:** `public/managers/css/style.css` (lines: Card Styling section)

**Key Properties:**
- `position: relative !important` - Enables absolute positioning of children
- `overflow: hidden !important` - Clips content to border-radius
- `border-radius: 10px` - Rounded corners
- `border: 1px solid #e5e7eb` - Subtle border

**Bootstrap Classes Used:**
- `.card` - Container
- `.card-header` - Header section
- `.card-body` - Main content area
- `.card-footer` - Footer section
- `.card-title` - Title element
- `.card-text` - Text content

---

**NO EXCEPTIONS** - These rules apply to ALL cards in Alsernet ✨

