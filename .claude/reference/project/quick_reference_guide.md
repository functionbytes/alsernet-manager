# Guía Rápida de Referencia - Sistemas de Plantillas

## 📋 Índice

1. [Estructura de Archivos](#estructura)
2. [Comparativa de Sistemas](#comparativa)
3. [Variables por Sistema](#variables)
4. [Rutas Principales](#rutas)
5. [Glosario](#glosario)

---

## 🗂️ ESTRUCTURA DE ARCHIVOS

### Mercosan - Email Templates
```
platform/core/setting/
├── src/Http/Controllers/
│   ├── EmailTemplateController.php
│   ├── EmailTemplateSettingController.php
│   ├── EmailTemplatePreviewController.php
│   ├── EmailTemplateRestoreController.php
│   ├── EmailTemplateStatusController.php
│   ├── EmailTemplateIframeController.php
│   ├── EmailTestController.php
│   └── EmailSettingController.php
├── resources/views/
│   ├── email-templates/
│   │   ├── index.blade.php
│   │   ├── edit.blade.php
│   │   └── preview.blade.php
│   └── email-settings/
│       └── index.blade.php
└── helpers/helpers.php
```

### Mercosan - Invoice Templates
```
platform/plugins/ecommerce/
├── src/Http/Controllers/Settings/
│   └── InvoiceTemplateSettingController.php
├── src/Supports/
│   ├── InvoiceHelper.php
│   └── TwigExtension.php
├── src/Models/
│   └── Invoice.php
├── resources/templates/
│   └── invoice.tpl (Twig template)
└── resources/views/
    └── invoice-template/
        └── settings.blade.php
```

### Mercosan - Shipping Label Templates
```
platform/plugins/ecommerce/
├── src/Http/Controllers/Settings/
│   └── ShippingLabelTemplateSettingController.php
├── resources/templates/
│   └── shipping-label.tpl (Twig template)
└── resources/views/
    └── shipping-label-template/
        └── settings.blade.php
```

### Alsernet - Actual (Email Personalizado)
```
/Users/functionbytes/Function/Coding/Alsernet/
├── app/
│   ├── Mail/Documents/
│   │   └── DocumentCustomMail.php
│   ├── Http/Controllers/Administratives/Orders/
│   │   └── DocumentsController.php (método sendCustomEmail)
│   └── Services/Documents/
│       └── DocumentActionService.php (método logCustomEmail)
├── resources/views/
│   ├── administratives/views/orders/documents/
│   │   └── manage.blade.php (modal + JS)
│   └── mailers/documents/
│       ├── layouts/
│       │   └── document.blade.php (master layout)
│       ├── custom.blade.php
│       ├── notification.blade.php
│       ├── reminder.blade.php
│       ├── uploaded.blade.php
│       └── missing.blade.php
├── routes/
│   └── administratives.php (POST /{uid}/send-custom-email)
└── docs/
    ├── MERCOSAN_EMAIL_TEMPLATES_ANALYSIS.md
    ├── MERCOSAN_SPECIALIZED_TEMPLATES_ANALYSIS.md
    ├── IMPLEMENTATION_PLAN_EMAIL_TEMPLATES.md
    └── QUICK_REFERENCE_GUIDE.md (este archivo)
```

---

## 📊 COMPARATIVA DE SISTEMAS

### Email Templates (Mercosan)
```
Ubicación: platform/core/setting/
Almacenamiento: DB (settings table) + Storage
Motor: Twig
Salida: HTML/Text
Preview: Live split-panel
Editor: CodeMirror + HTML
Variables: Dinámicas por módulo
Reutilización: Sí, muy alta
Extensibilidad: 8+ hooks
```

### Invoice Templates (Mercosan)
```
Ubicación: platform/plugins/ecommerce/
Almacenamiento: File (storage/app/templates)
Motor: Twig
Salida: PDF (A4)
Preview: PDF viewer
Editor: CodeMirror
Variables: Complejas (nested arrays)
Reutilización: Por orden/cliente
Extensibilidad: 5+ hooks
```

### Shipping Label Templates (Mercosan)
```
Ubicación: platform/plugins/ecommerce/
Almacenamiento: File (storage/app/templates)
Motor: Twig
Salida: PDF (4x6" térmica)
Preview: PDF viewer
Editor: CodeMirror
Variables: Específicas de envío
Reutilización: Por shipment
Extensibilidad: Hooks disponibles
```

### Email Personalizado (Alsernet - Actual)
```
Ubicación: app/Mail + resources/views
Almacenamiento: None (en vivo)
Motor: Blade
Salida: HTML
Preview: Modal en browser
Editor: Textarea
Variables: Hardcoded en template
Reutilización: No
Extensibilidad: No
```

---

## 🎨 VARIABLES POR SISTEMA

### Email Templates - Variables Globales
```twig
{{ site_title }}
{{ site_logo }}
{{ site_copyright }}
{{ current_year }}
{{ current_date }}
{{ unsubscribe_url }}
{{ support_email }}
{{ support_phone }}
```

### Email Templates - Módulo Documents (Alsernet)
```twig
{{ customer_name }}
{{ customer_email }}
{{ order_id }}
{{ order_reference }}
{{ document_type }}
{{ upload_link }}
{{ expiration_date }}
```

### Invoice Templates
```twig
{{ company.name }}
{{ company.address }}
{{ company.tax_id }}
{{ invoice.invoice_number }}
{{ invoice.invoice_date }}
{{ invoice.due_date }}
{{ customer.name }}
{{ customer.email }}
{% for item in invoice.items %}
  {{ item.product_name }}
  {{ item.quantity }}
  {{ item.price | price_format }}
{% endfor %}
{{ invoice.total | price_format }}
{{ payment.method }}
```

### Shipping Label Templates
```twig
{{ shipment.tracking_number }}
{{ shipment.carrier }}
{{ from.name }}
{{ from.address }}
{{ to.name }}
{{ to.address }}
{{ package.weight }}
{{ package.dimensions }}
{{ generate_qrcode(shipment.tracking_number) }}
{{ generate_barcode(shipment.tracking_number) }}
```

---

## 🛣️ RUTAS PRINCIPALES

### Mercosan - Email Templates
```
GET  /admin/settings/email/
GET  /admin/settings/email/templates
GET  /admin/settings/email/templates/{type}/{module}/{template}/edit
POST /admin/settings/email/templates/{type}/{module}/{template}
GET  /admin/settings/email/templates/{type}/{module}/{template}/preview
POST /admin/settings/email/templates/{type}/{module}/{template}/restore
POST /admin/settings/email/templates/{type}/{module}/{template}/status
POST /admin/settings/email/test
```

### Mercosan - Invoice Templates
```
GET  /admin/ecommerce/settings/invoice-template
POST /admin/ecommerce/settings/invoice-template
GET  /admin/ecommerce/settings/invoice-template/preview
POST /admin/ecommerce/settings/invoice-template/generate
```

### Mercosan - Shipping Label Templates
```
GET  /admin/ecommerce/settings/shipping-label-template
POST /admin/ecommerce/settings/shipping-label-template
GET  /admin/ecommerce/settings/shipping-label-template/preview
POST /admin/ecommerce/settings/shipping-label-template/generate
```

### Alsernet - Actual
```
GET  /administrative/orders/documents/{uid}/manage
POST /administrative/orders/documents/{uid}/send-custom-email
```

### Alsernet - Futuro (según plan)
```
GET  /administrative/email-templates/
GET  /administrative/email-templates/{template}/edit
POST /administrative/email-templates/{template}
GET  /administrative/email-templates/{template}/preview
POST /administrative/email-templates/{template}/restore

GET  /administrative/document-templates/
GET  /administrative/document-templates/{template}/edit
POST /administrative/document-templates/{template}
GET  /administrative/document-templates/{template}/preview
POST /administrative/document-templates/{template}/generate
```

---

## 📚 GLOSARIO

| Término | Definición | Ejemplo |
|---------|-----------|---------|
| **Template** | Plantilla de contenido reutilizable | `invoice.tpl`, `email_confirmation.html` |
| **Twig** | Motor de plantillas PHP | `{{ variable }}`, `{% for %}` |
| **Layout** | Plantilla base que hereda otras | `document.blade.php` (header + footer) |
| **Variable** | Placeholder dinámico en template | `{{ customer_name }}` |
| **Hook/Filter** | Punto de extensión | `apply_filters('email_variables', $vars)` |
| **Rendering** | Proceso de convertir template a HTML/PDF | Template + Data → HTML |
| **Inlining** | Convertir CSS externo en estilos inline | Para compatibilidad email |
| **Mailable** | Clase para enviar email en Laravel | `DocumentCustomMail extends Mailable` |
| **DOMPDF** | Librería para generar PDF | Usado en Invoice/Shipping templates |
| **QR Code** | Código QR generado dinámicamente | Para tracking |
| **Barcode** | Código de barras generado | Para shipping/tracking |
| **Seeder** | Script para poblar datos iniciales | EmailTemplateSeeder |
| **Migration** | Script para crear/modificar DB | create_email_templates_table |

---

## 🔍 BÚSQUEDA RÁPIDA

### ¿Cómo se almacenan las plantillas?

**Mercosan Email:**
- DB: `settings` table (key-value)
- Storage: `/storage/app/email-templates/`

**Mercosan Invoice/Shipping:**
- File: `/storage/app/templates/ecommerce/`

**Alsernet Actual:**
- Blade files: `/resources/views/mailers/documents/`

### ¿Cómo se renderizan?

**Mercosan:**
- Twig engine: `$twig->render($template, $variables)`

**Alsernet:**
- Blade: `view('mailers.documents.custom', $data)`

### ¿Dónde se editan?

**Mercosan:**
- Web UI con CodeMirror
- `/admin/settings/email/templates/{id}/edit`

**Alsernet Actual:**
- Modal con textarea
- `/administrative/orders/documents/{uid}/manage`

### ¿Cómo se prevsualizan?

**Mercosan:**
- AJAX → Preview controller → Rendered HTML/PDF

**Alsernet:**
- Textarea preview en vivo (Vanilla JS)

---

## 📖 Documentos de Referencia

1. **MERCOSAN_EMAIL_TEMPLATES_ANALYSIS.md**
   - Análisis detallado del sistema de email templates
   - Arquitectura, controllers, rutas, variables

2. **MERCOSAN_SPECIALIZED_TEMPLATES_ANALYSIS.md**
   - Análisis de Invoice y Shipping Label templates
   - Estructuras de datos, variables específicas, PDF generation

3. **IMPLEMENTATION_PLAN_EMAIL_TEMPLATES.md**
   - Plan paso a paso para implementar en Alsernet
   - Code snippets, migrations, models

4. **QUICK_REFERENCE_GUIDE.md**
   - Este documento
   - Referencia rápida y glosario

---

## 🚀 Próximos Pasos

### Corto Plazo (1-2 semanas)
- [ ] Review de documentación
- [ ] Implementar Migration de email_templates
- [ ] Crear Model + Controller
- [ ] Crear Views del gestor

### Mediano Plazo (2-4 semanas)
- [ ] Implementar Twig rendering
- [ ] Crear template seeder
- [ ] Preview en vivo
- [ ] Testing

### Largo Plazo (4-6 semanas)
- [ ] Document Templates
- [ ] PDF generation
- [ ] QR codes
- [ ] Advanced features

---

## 💬 Notas Importantes

1. **Mercosan usa Twig**, Alsernet usa Blade → Considerar migration gradual
2. **Email templates almacenadas en DB**, mejor para customización
3. **PDF templates almacenadas en files**, mejor para performance
4. **Ambos sistemas usan hooks** para extensibilidad
5. **CodeMirror es editor estándar** en Mercosan
6. **Bootstrap 5** para UI en Mercosan, Alsernet usa Bootstrap 4

---

**Guía creada:** Noviembre 27, 2025
**Actualizada:** Última revisión de arquitectura Mercosan
**Audiencia:** Developers Alsernet
**Categoría:** Referencia técnica
