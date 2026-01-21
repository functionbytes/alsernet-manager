# ⚡ FRONTEND AGENT - MANDATORY RULES

**Critical rules that MUST be followed for ALL frontend work in Alsernet**

---

## ⚙️ CARD STYLING - MANDATORY RULE

**EVERY `.card` element MUST have:**
```html
<div class="card">...</div>
```

**Auto-applied CSS (in style.css):**
```css
.card {
    position: relative !important;
    overflow: hidden !important;
}
```

**Why:**
- ✅ Ensures content doesn't overflow borders
- ✅ Enables absolute positioning of child elements
- ✅ Consistent card behavior across all pages
- ✅ Supports hover effects and animations

**NO EXCEPTIONS** - This applies to ALL cards automatically

---

## 🎨 Color Palette - ALWAYS USE THESE

### Primary Brand Color ⭐
```
#90bb13 !important
```
Used for:
- Table headers
- Primary buttons
- Active navigation
- Links
- Status indicators
- Main borders

### Black & Dark Text
```
#000000  - Pure black
#1f2937  - Dark text
#111827  - Heading text
```

### Gray Scale (Backgrounds, Borders, Subtle Elements)
```
#f9fafb - Page backgrounds
#f3f4f6 - Code blocks, inputs
#e5e7eb - Borders
#d1d5db - Hover borders
#9ca3af - Secondary labels
#6b7280 - Muted text
#374151 - Dark secondary
#111827 - Almost black
```

### Status Colors
```
SUCCESS:  #d1fae5 (bg) / #059669 (text)
DANGER:   #fee2e2 (bg) / #dc2626 (text)
WARNING:  #fef3c7 (bg) / #d97706 (text)
INFO:     #dbeafe (bg) / #1e40af (text)
```

👉 **Full palette with SCSS variables:** `.claude/agents/frontend/color-palette.md`

---

## 📁 File Locations - ALWAYS USE THESE

### CSS/SCSS (PRIMARY)
```
WRITE TO:  public/managers/css/style.scss
LINK IN:   public/managers/css/style.css (compiled)

RULES:
✅ Add ALL custom styles to style.scss
❌ NEVER create new CSS files
❌ NEVER use inline <style> tags (unless critical)
✅ Organize with clear section comments
```

### JavaScript Libraries (CHECK FIRST)
```
LOCATION:  public/managers/libs/

RULES:
✅ ALWAYS check if library exists here FIRST
✅ If it exists → USE IT
❌ NEVER download from CDN
❌ NEVER add CDN links to HTML
✅ If missing → Add to libs/ folder
```

### Blade Templates
```
LOCATION:  resources/views/managers/[section]/
INCLUDES:  resources/views/managers/includes/
LAYOUTS:   resources/views/layouts/managers.blade.php
```

---

## 🚀 Before Starting ANY Frontend Task

### Step 1: Modernize First
- [ ] Check `docs/frontend/components.md`
- [ ] Review `docs/frontend/modernize-complete-index.md`
- [ ] Use Modernize components as base

### Step 2: Check Resources
- [ ] Browse `resources/views/managers/includes/` for partials
- [ ] Look for similar pages to reuse
- [ ] Never duplicate code - always extend

### Step 3: Check Design System
- [ ] Review `docs/frontend/design-rules.md`
- [ ] Check `docs/frontend/layouts.md`
- [ ] Follow Bootstrap 5.3 utilities

### Step 4: Color Palette
- [ ] Reference `.claude/agents/frontend/color-palette.md`
- [ ] Use #90bb13 for primary colors
- [ ] Use gray scale for backgrounds
- [ ] Use status colors for alerts

### Step 5: CSS & JS
- [ ] Check `public/managers/libs/` for libraries
- [ ] Add CSS to `public/managers/css/style.scss`
- [ ] Use SCSS variables from color palette
- [ ] Never create new CSS files

---

## 📝 TEXT FORMATTING - MANDATORY RULE

**ALL text in forms, labels, headings, and observations use SENTENCE CASE**

### Rule:
- ✅ First letter UPPERCASE
- ✅ All other letters LOWERCASE
- ✅ NO TITLE CASE (Cada Palabra En Mayuscula)
- ✅ NO ALL CAPS (TODO MAYUSCULAS)
- ✅ NO camelCase or CamelCase

### Examples - CORRECT ✅
```
Configuración de base de datos
Tipo de conexión
Nombre de la base de datos
Puerto de conexión
Selecciona un tipo de conexión
Mínimo 3 caracteres
Este espacio está diseñado para configurar...
```

### Examples - WRONG ❌
```
CONFIGURACIÓN DE BASE DE DATOS
Configuración De Base De Datos
TipoDeBD
tipo de conexion (missing capitals)
```

### Applies To:
- ✅ Form labels
- ✅ Headings (h1-h6)
- ✅ Button text
- ✅ Placeholder text
- ✅ Error messages
- ✅ Help text
- ✅ All UI text

---

## 📝 FORM VALIDATION - MANDATORY RULE

**EVERY `<form>` element MUST use jQuery Validate with rules and messages**

### Step 1: Initialize jQuery Validate
```javascript
$(document).ready(function() {
    $('#formId').validate({
        rules: {
            fieldName: {
                required: true,
                minlength: 3,
                maxlength: 50
            },
            email: {
                required: true,
                email: true
            },
            port: {
                required: true,
                number: true,
                min: 1,
                max: 65535
            }
        },
        messages: {
            fieldName: {
                required: 'Este campo es obligatorio',
                minlength: 'Mínimo 3 caracteres',
                maxlength: 'Máximo 50 caracteres'
            },
            email: {
                required: 'El email es obligatorio',
                email: 'Ingresa un email válido'
            },
            port: {
                required: 'El puerto es obligatorio',
                number: 'Debe ser un número',
                min: 'Mínimo 1',
                max: 'Máximo 65535'
            }
        },
        submitHandler: function(form) {
            // Si es válido, enviar
            form.submit();
        }
    });
});
```

### Step 2: HTML Structure
```html
<form id="formId" method="POST">
    <div class="mb-3">
        <label for="fieldName">Field Name</label>
        <input type="text" class="form-control" id="fieldName" name="fieldName" required>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
```

### Key Rules for Forms:
- ✅ **ALWAYS** use `$('#form').validate({rules: {...}, messages: {...}})`
- ✅ Define `required`, `minlength`, `maxlength` for text inputs
- ✅ Define `email` for email fields
- ✅ Define `number`, `min`, `max` for numeric inputs
- ✅ Provide **Spanish messages** for all validation rules
- ✅ Use `submitHandler` to prevent default form submission if needed
- ✅ Add `.required()` attribute to HTML for HTML5 fallback
- ✅ Add error classes automatically (error class added by jQuery Validate)

### Common Validation Rules:
```javascript
// Text fields
name: {
    required: true,
    minlength: 2,
    maxlength: 100
}

// Email
email: {
    required: true,
    email: true
}

// Numbers (ports, ages, etc)
port: {
    required: true,
    number: true,
    min: 1,
    max: 65535
}

// Passwords
password: {
    required: true,
    minlength: 8
}

// URLs
url: {
    required: true,
    url: true
}

// Phone
phone: {
    required: true,
    phoneUS: true // or other phone patterns
}

// Select dropdowns
select: {
    required: true
}
```

### CSS Classes Applied by jQuery Validate:
```css
.error {} /* Applied to invalid fields */
label.error {} /* Applied to error messages */

/* Add to style.scss: */
input.error,
select.error,
textarea.error {
    border-color: #dc2626 !important;
    background-color: #fee2e2;
}

label.error {
    color: #dc2626;
    font-size: 0.875rem;
    display: block;
    margin-top: 0.25rem;
}
```

---

## 📝 JAVASCRIPT - ALWAYS USE JQUERY

**MANDATORY RULE:** All JavaScript must use **jQuery** unless absolutely impossible

### Guidelines:
- ✅ **ALWAYS** use `$(document).ready(function() { ... })`
- ✅ **ALWAYS** use jQuery selectors `$('#id')`, `$('.class')`
- ✅ **ALWAYS** use `.on()` for event handlers instead of vanilla JS
- ✅ **ALWAYS** use `.addClass()`, `.removeClass()`, `.toggleClass()`
- ✅ **ALWAYS** use `.val()`, `.text()`, `.html()` for DOM manipulation
- ✅ Use `.ajax()` for HTTP requests instead of fetch
- ✅ Use `.animate()` for animations instead of CSS animations
- ✅ Use `.fadeIn()`, `.fadeOut()`, `.slideUp()`, `.slideDown()`

### NEVER Do This (Vanilla JS):
```javascript
// ❌ WRONG - Vanilla JavaScript
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('field').value = 'text';
    document.querySelector('.btn').classList.add('active');
});

// ✅ CORRECT - jQuery
$(document).ready(function() {
    $('#field').val('text');
    $('.btn').addClass('active');
});
```

### jQuery Form Validation Example:
```javascript
$(document).ready(function() {
    // Validate form on submit
    $('#myForm').validate({
        rules: {
            username: {
                required: true,
                minlength: 3
            },
            email: {
                required: true,
                email: true
            }
        },
        messages: {
            username: {
                required: 'Username es obligatorio',
                minlength: 'Mínimo 3 caracteres'
            },
            email: {
                required: 'Email es obligatorio',
                email: 'Email inválido'
            }
        }
    });

    // Alternatively, validate on blur/change
    $('#username').on('blur', function() {
        if ($(this).val().length < 3) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
});
```

### Form Validation Libraries (Preferred Order):
1. **jQuery Validate** (jquery.validate.js) - Standard, lightweight
2. **Select2** (for dropdowns) - Already in public/managers/libs/
3. **jQuery UI** (for date/time pickers) - If needed
4. **Bootbox** (for modals) - Already available

---

## ✅ Checklist for EVERY Request

Before implementing:
- [ ] Using Modernize components?
- [ ] Reusing existing partials?
- [ ] Using #90bb13 for primary?
- [ ] CSS goes to style.scss?
- [ ] JS libraries in public/managers/libs/?
- [ ] Color palette referenced?
- [ ] Responsive design checked?
- [ ] Accessibility considered?
- [ ] All text in Sentence case (no TITLE CASE)?
- [ ] No icons in labels/h5?
- [ ] All styles in style.scss (no inline)?

### ✅ Checklist for FORMS (MANDATORY)
- [ ] Form has an `id` attribute?
- [ ] Using jQuery Validate plugin?
- [ ] All fields have `name` attributes?
- [ ] All required fields have `required` attribute?
- [ ] Rules defined for each field?
- [ ] Messages in Spanish for each rule?
- [ ] `submitHandler` defined if needed?
- [ ] All text inputs have `minlength`/`maxlength`?
- [ ] Email fields have `email` rule?
- [ ] Number fields have `number`, `min`, `max` rules?
- [ ] Error CSS styling added to style.scss?
- [ ] Bootstrap validation classes applied?

---

## ❌ NEVER Do This

**These are absolute rules - NO EXCEPTIONS:**

```
❌ Create new CSS files - add to style.scss
❌ Use inline <style> tags (except in critical cases)
❌ Download libraries from CDN
❌ Add CDN links to HTML
❌ Use colors other than #90bb13 for primary
❌ Invent new color schemes
❌ Duplicate partial code
❌ Ignore existing layout structure
❌ Build components from scratch if Modernize has them
❌ Put CSS/JS inline unless absolutely critical
❌ Add ICONS to labels, h5, headings - Text only!
❌ Add inline styles - All styles go to style.scss
❌ Use TITLE CASE in text - Use Sentence case
❌ Use ALL CAPS in text - Use Sentence case only
❌ Use camelCase in labels - Use Sentence case

❌ Use vanilla JavaScript - ALWAYS use jQuery
❌ Use document.addEventListener() - Use $.on() instead
❌ Use document.getElementById() - Use $('#id') instead
❌ Use classList for DOM - Use .addClass()/.removeClass() instead
❌ Create forms without jQuery Validate
❌ Define form validation without rules and messages
❌ Use English messages - Always use Spanish
❌ Skip submitHandler in jQuery Validate
❌ Forget to add minlength/maxlength to text inputs
❌ Forget to add email rule to email inputs
❌ Forget to add min/max rules to number inputs
```

---

## 📊 Tables - Standard Format

**ALWAYS use this format:**
```html
<table class="table table-hover">
  <!-- Header with PRIMARY color #90bb13 -->
  <thead>
    <tr style="background-color: #90bb13;">
      <th class="text-white">Column 1</th>
      <th class="text-white">Column 2</th>
    </tr>
  </thead>
  <!-- Body with gray borders -->
  <tbody>
    <tr class="border-bottom border-gray-200">
      <td>Data 1</td>
      <td>Data 2</td>
    </tr>
  </tbody>
</table>
```

Then add to `public/managers/css/style.scss`:
```scss
$primary: #90bb13 !important;
$gray-200: #e5e7eb;

table thead th {
    background-color: $primary !important;
    color: white;
    font-weight: 700;
}

table tbody tr {
    border-bottom: 1px solid $gray-200;
}

table tbody tr:hover {
    background-color: rgba($primary, 0.04);
}
```

---

## 🎨 Alerts - Standard Format

**ALWAYS use this format:**

```html
<!-- Success -->
<div class="alert" style="background-color: #d1fae5; border: 1px solid #a7f3d0; color: #059669;">
    <span class="material-symbols-rounded">check_circle</span>
    Success message
</div>

<!-- Danger -->
<div class="alert" style="background-color: #fee2e2; border: 1px solid #fecaca; color: #dc2626;">
    <span class="material-symbols-rounded">cancel</span>
    Error message
</div>

<!-- Warning -->
<div class="alert" style="background-color: #fef3c7; border: 1px solid #fcd34d; color: #d97706;">
    <span class="material-symbols-rounded">warning</span>
    Warning message
</div>

<!-- Info -->
<div class="alert" style="background-color: #dbeafe; border: 1px solid #93c5fd; color: #1e40af;">
    <span class="material-symbols-rounded">info</span>
    Info message
</div>
```

---

## 📚 Key Documentation References

- **Full Color Palette:** `.claude/agents/frontend/color-palette.md`
- **Frontend Design Rules:** `.claude/agents/frontend/frontend-design.md`
- **Modernize Template:** `docs/frontend/components.md`
- **Bootstrap Classes:** `docs/frontend/layouts.md`

---

## 🔄 Example: Creating a Component

**CORRECT WAY:**

1. Check Modernize → Use card component
2. Check partials → Reuse `managers/includes/card.blade.php`
3. Check color palette → Use #90bb13 for header
4. Add CSS to style.scss → Use SCSS variables
5. Check public/managers/libs/ → Use existing jQuery

**WRONG WAY:**
```
❌ Create custom CSS file
❌ Download Bootstrap from CDN
❌ Invent new colors
❌ Use inline <style> tag
❌ Create new partial instead of extending
```

---

## 🎯 Quick Reference

| Need | Location |
|------|----------|
| Color values? | `.claude/agents/frontend/color-palette.md` |
| CSS file? | `public/managers/css/style.scss` |
| Libraries? | `public/managers/libs/` (check first!) |
| Components? | `docs/frontend/components.md` |
| Layouts? | `docs/frontend/layouts.md` |
| Colors? | #90bb13 (primary), grays, status colors |
| Icons? | Material Symbols Rounded |
| Framework? | Bootstrap 5.3 + Modernize |

---

**Version:** 1.0
**Last Updated:** 30 de Noviembre de 2025
**Status:** MANDATORY RULES ⚡
