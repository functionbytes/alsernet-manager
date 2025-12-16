# Resumen Ejecutivo: Sistema de Validación de Documentos Faltantes

## 🎯 Objetivo General

Implementar un sistema de validación en TIEMPO REAL que permita:
- Identificar exactamente qué documentos específicos faltan por subir (DNI frontal, trasera, licencia, etc.)
- Permitir que el usuario seleccione el tipo de documento ANTES de subir el archivo
- Mostrar dinámicamente en PrestaShop: documentos ya subidos ✅ + documentos faltantes ⚠️
- Auto-marcar documento como "completo" cuando todos los tipos requeridos estén cargados

---

## 📋 Problema Actual

1. **Backend**: El método `documentUpload()` en API NO guarda el tipo de documento en custom properties
2. **Frontend**: Las variables `$trans` y `$trans_list` en PrestaShop están vacías
3. **Sin validación**: No se valida qué documentos específicos se subieron vs los requeridos
4. **Upload genérico**: Los archivos se suben sin identificar si son DNI frontal, trasera, licencia, etc.

---

## 🏗️ Arquitectura de la Solución

### Flujo Completo Usuario Final

```
1. Usuario entra a página PrestaShop con token
   ↓
2. JavaScript carga estado actual desde API: /api/documents (validate)
   ↓
3. Frontend muestra:
   - ✅ Documentos ya subidos (lista verde)
   - ⚠️ Documentos faltantes (lista naranja con selector)
   ↓
4. Usuario selecciona tipo de documento + sube archivo
   ↓
5. JavaScript envía: file[] + document_types[] al backend
   ↓
6. Backend guarda con custom property 'document_type'
   ↓
7. Backend actualiza JSON field 'uploaded_documents'
   ↓
8. Si todos están completos → marcar como confirmed
   ↓
9. Frontend retorna nuevo estado (documentos faltantes actualizado)
```

---

## 📦 Fases de Implementación

### FASE 1: Backend Model - Métodos de Validación
**Archivo**: `app/Models/Document/Document.php`
**Ubicación**: Agregar después del método `updateUploadedDocumentsJson()` (línea ~400)
**Líneas de código**: ~50 líneas

**Métodos a agregar**:
```
- getMissingDocuments()              → Comparar requeridos vs subidos
- getUploadedDocumentTypes()         → Extraer tipos desde media custom properties
- hasAllRequiredDocuments()          → Verificar si todos están completos
- syncUploadedDocumentsJson()        → Actualizar JSON field tras upload
```

**Impacto**: Bajo riesgo, métodos puramente internos

---

### FASE 2: Backend API - Endpoints de Validación
**Archivo**: `app/Http/Controllers/Api/DocumentsController.php`
**Cambios**:

#### 2.1 Modificar `documentValidates()` (líneas ~165-180)
**Cambio**: Retornar arrays completos en lugar de solo booleanos
```
Retorna:
- uid
- type (tipo de arma)
- label (referencia orden)
- can_upload (booleano)
- required_documents (array de tipos requeridos)
- uploaded_documents (array con info de files subidos)
- missing_documents (array SOLO de tipos faltantes) ← NUEVO
- is_complete (booleano)
```

#### 2.2 Modificar `documentUpload()` (líneas ~230-280)
**Cambio**: Aceptar `document_types[]` array y guardar con custom properties
```
Entrada:
- file[] (array de archivos)
- document_types[] (array de tipos correspondientes)
- uid
- action: 'upload'

Lógica:
1. Validar que hay un tipo para cada archivo
2. Por cada archivo:
   - Eliminar media previa del mismo tipo si existe
   - Subir nuevo con custom property 'document_type'
3. Llamar syncUploadedDocumentsJson()
4. Si hasAllRequiredDocuments() → marcar confirmed_at
5. Retornar nuevo estado actualizado
```

**Impacto**: Cambio en API response, mantiene compatibilidad

---

### FASE 3: Frontend PrestaShop - Controller
**Archivo**: `integrations/prestashop/content/modules/alsernetforms/alsernetforms.php`

#### 3.1 Cambio en caso 'documents' (líneas 313-332)
**Acción**: Asignar `$trans` y `$trans_list` correctamente
```php
// Reemplazar línea ~323-324
// De: $trans = $trans_list = '';
// A: list($trans, $trans_list) = $this->generateDocumentListOnly($uid, $validation['type']);

// Agregar al assign Smarty:
'trans' => $trans,
'trans_list' => $trans_list,
'required_documents' => $validation['data']['required_documents'] ?? [],
'uploaded_documents' => $validation['data']['uploaded_documents'] ?? [],
'missing_documents' => $validation['data']['missing_documents'] ?? [],
```

#### 3.2 Agregar método helper (después de línea 454)
**Método**: `generateDocumentListOnly($documentNumber, $documentType)`
**Propósito**: Generar listas HTML traducidas por tipo de arma
**Retorna**: Array `[$trans_remember, $trans_list]`

**Casos soportados**: corta, rifle, escopeta, dni, default
**Ejemplo**:
```php
'corta' → [
  "REMEMBER: In order to ship...",
  "<ul><li>Photocopy of ID...</li>..."
]
```

**Impacto**: Bajo - solo generación de HTML, sin cambios en BD

---

### FASE 4: Frontend PrestaShop - Template
**Archivo**: `integrations/prestashop/content/modules/alsernetforms/views/templates/hook/forms/documents/gun.tpl`

#### 4.1 Agregar sección: Documentos ya subidos (línea ~30)
```smarty
{if $uploaded_documents && count($uploaded_documents) > 0}
  <div class="alert alert-success">
    <h5>✓ Documents already received</h5>
    <ul>
      {foreach from=$uploaded_documents key=docType item=docInfo}
        <li>
          <strong>{$docType}</strong> - {$docInfo.file_name}
          <small>{$docInfo.created_at}</small>
        </li>
      {/foreach}
    </ul>
  </div>
{/if}
```

#### 4.2 Agregar sección: Documentos faltantes (línea ~50)
```smarty
{if $missing_documents && count($missing_documents) > 0}
  <div class="alert alert-warning">
    <h5>⚠ Missing documents</h5>
    <ul>
      {foreach from=$missing_documents key=docType item=docLabel}
        <li data-doc-type="{$docType}">{$docLabel}</li>
      {/foreach}
    </ul>
  </div>
{/if}
```

#### 4.3 Agregar selector de tipo (antes del dropzone, línea ~40)
```smarty
<div class="mb-3">
  <label for="document-type-select">
    Document type <span class="text-danger">*</span>
  </label>
  <select class="form-control" id="document-type-select" required>
    <option value="">-- Select document type --</option>
    {if $missing_documents}
      {foreach from=$missing_documents key=docType item=docLabel}
        <option value="{$docType}">{$docLabel}</option>
      {/foreach}
    {/if}
  </select>
</div>
```

**Impacto**: Presentación de datos, sin lógica backend

---

### FASE 5: Frontend PrestaShop - JavaScript
**Archivo**: `integrations/prestashop/content/modules/alsernetforms/views/js/front/documents.js`

#### 5.1 Agregar al document.ready (línea ~10)
```javascript
if (!$('#documents').length) return;
loadDocumentStatus();  // ← NUEVO
// Resto del código...
```

#### 5.2 Agregar variables globales (después de document.ready)
```javascript
var fileDocumentTypes = {};  // Trackear tipo seleccionado por archivo
```

#### 5.3 Agregar función: `loadDocumentStatus()`
**Propósito**: Cargar estado inicial de documentos desde API
**Llamada**: AJAX POST a `/api/documents` con `action=validate`
**Actualiza**: Listas de documentos subidos/faltantes + selector

#### 5.4 Agregar función: `updateDocumentLists(data)`
**Propósito**: Actualizar UI con nuevo estado
**Modifica**:
- Lista de documentos subidos (#uploaded-documents-list)
- Lista de documentos faltantes (#missing-documents-list)
- Selector de tipo (#document-type-select)

#### 5.5 Agregar función: `getDocumentLabel(docType)`
**Propósito**: Mapear tipos técnicos a etiquetas legibles
**Mapeo**: `'dni_frontal' → 'DNI - Cara frontal'`, etc.

#### 5.6 Modificar evento Dropzone: `addedfile`
**Cambio**: Validar que se haya seleccionado tipo antes de subir
```javascript
const selectedType = $('#document-type-select').val();
if (!selectedType) {
  alert('Please select document type first');
  dz.removeFile(file);
  return;
}
fileDocumentTypes[file.name] = selectedType;
// Mostrar etiqueta visual del tipo...
// Resetear selector
```

#### 5.7 Modificar `submitHandler` en validación de formulario
**Cambio**: Construir FormData con arrays de tipos
```javascript
files.forEach(file => {
  formData.append('file[]', file);
  const docType = fileDocumentTypes[file.name] || 'documento';
  formData.append('document_types[]', docType);
});
```

**Cambio en éxito**: Si `is_complete === true`, mostrar confirmación en lugar de alertar

**Impacto**: Interactividad frontend, validación en cliente

---

### FASE 6: Asegurar Sincronización en Controller Administrativo
**Archivo**: `app/Http/Controllers/Administratives/Documents/DocumentsController.php`
**Línea**: ~1017 en método `adminUploadDocument()`

**Cambio**: Agregar después de subir archivo
```php
// Actualizar JSON de documentos subidos
$document->syncUploadedDocumentsJson();
```

**Impacto**: Consistencia de datos cuando admin sube documentos

---

## 🔗 Archivos Críticos Modificados

### Backend (3 archivos):
| Archivo | Cambios | Líneas |
|---------|---------|--------|
| `app/Models/Document/Document.php` | Agregar 4 métodos | ~50 líneas nuevas |
| `app/Http/Controllers/Api/DocumentsController.php` | Modificar 2 métodos | ~150 líneas totales |
| `app/Http/Controllers/Administratives/Documents/DocumentsController.php` | Agregar sync | 1 línea |

### Frontend PrestaShop (3 archivos):
| Archivo | Cambios | Secciones |
|---------|---------|-----------|
| `integrations/prestashop/content/modules/alsernetforms/alsernetforms.php` | 1 cambio + 1 método | 2 cambios |
| `integrations/prestashop/content/modules/alsernetforms/views/templates/hook/forms/documents/gun.tpl` | 3 secciones nuevas | +50 líneas |
| `integrations/prestashop/content/modules/alsernetforms/views/js/front/documents.js` | 5+ nuevas funciones | +150 líneas |

---

## 🧪 Plan de Testing

### Backend Testing (5 tests):
- [ ] Crear documento tipo 'corta' y verificar que `required_documents` se autocompleta
- [ ] Subir DNI frontal con `document_type='dni_frontal'` y verificar custom property
- [ ] Verificar que `uploaded_documents` JSON se actualiza
- [ ] Verificar que `getMissingDocuments()` retorna solo los que faltan
- [ ] Completar todos los documentos y verificar que `confirmed_at` se marca automáticamente

### Frontend Testing (6 tests):
- [ ] Abrir página con token válido y verificar que carga estado
- [ ] Verificar que selector solo muestra documentos faltantes
- [ ] Validar que no permite subir sin seleccionar tipo (alerta)
- [ ] Subir documento y verificar que aparece en lista "Documents already received"
- [ ] Verificar que desaparece de lista de faltantes
- [ ] Subir todos los documentos y verificar que muestra confirmación

### Casos Edge (3 tests):
- [ ] Subir documento del mismo tipo dos veces → debe reemplazar
- [ ] Recargar página después de subir parcialmente → debe mantener estado
- [ ] Documento ya confirmado → no debe permitir más uploads

---

## 📊 Orden de Implementación Recomendado

```
1. FASE 1: Backend Model          (30 min)
   ↓
2. FASE 2: Backend API             (45 min)
   ↓
3. FASE 3: Frontend Controller     (20 min)
   ↓
4. FASE 4: Frontend Template       (15 min)
   ↓
5. FASE 5: Frontend JavaScript     (60 min)
   ↓
6. FASE 6: Admin Controller        (5 min)
   ↓
7. Testing Manual                  (60 min)
   ↓
8. Ajustes y Refinamiento          (30 min)
```

**Total Estimado**: ~4 horas

---

## ✅ Checklist de Implementación

### FASE 1 - Model
- [ ] Agregar método `getMissingDocuments()`
- [ ] Agregar método `getUploadedDocumentTypes()`
- [ ] Agregar método `hasAllRequiredDocuments()`
- [ ] Agregar método `syncUploadedDocumentsJson()`
- [ ] Probar métodos en Tinker

### FASE 2 - API
- [ ] Modificar `documentValidates()` para retornar documentos
- [ ] Modificar `documentUpload()` para aceptar `document_types[]`
- [ ] Agregar validación de tipos por archivo
- [ ] Implementar reemplazo automático de documentos del mismo tipo
- [ ] Probar endpoints con Postman/cURL

### FASE 3 - PrestaShop Controller
- [ ] Cambiar asignación de `$trans` y `$trans_list`
- [ ] Agregar método `generateDocumentListOnly()`
- [ ] Verificar que las variables llegan al template

### FASE 4 - PrestaShop Template
- [ ] Agregar sección de documentos subidos
- [ ] Agregar sección de documentos faltantes
- [ ] Agregar selector de tipo de documento
- [ ] Verificar HTML en navegador (F12)

### FASE 5 - JavaScript
- [ ] Agregar `loadDocumentStatus()` al ready
- [ ] Agregar función `updateDocumentLists()`
- [ ] Agregar función `getDocumentLabel()`
- [ ] Modificar evento Dropzone `addedfile`
- [ ] Modificar `submitHandler`
- [ ] Probar carga inicial de estado
- [ ] Probar validación de selector
- [ ] Probar tracking de tipos

### FASE 6 - Admin
- [ ] Agregar `syncUploadedDocumentsJson()` tras upload

### Testing
- [ ] Ejecutar 5 tests backend
- [ ] Ejecutar 6 tests frontend
- [ ] Probar 3 casos edge
- [ ] Verificar responsive design
- [ ] Prueba end-to-end completa

---

## 🔐 Consideraciones de Seguridad

1. **Validación de Tipos**: Backend debe validar que los tipos enviados son válidos para el documento
2. **Authorization**: Solo usuario propietario del documento puede subir documentos adicionales
3. **CSRF Protection**: Todos los POST/PUT/DELETE incluyen `X-CSRF-TOKEN`
4. **File Validation**: Validar extensiones y tipos MIME en backend

---

## 🎓 Conceptos Técnicos Clave

### Spatie MediaLibrary Custom Properties
```php
// Guardar tipo con archivo
$media = $document->addMedia($file)
    ->withCustomProperties(['document_type' => 'dni_frontal'])
    ->toMediaCollection('documents');

// Recuperar tipo desde media
$type = $media->getCustomProperty('document_type');
```

### JSON Fields en Base de Datos
```php
// required_documents: {tipo: "etiqueta"}
$required = $document->required_documents; // Array asociativo

// uploaded_documents: {tipo: {file_name, size, url, created_at}}
$uploaded = $document->uploaded_documents; // Array anidado
```

### Validación Bidireccional
- **Frontend**: JavaScript valida que selector está seleccionado
- **Backend**: API valida que tipos son válidos y corresponden a archivo
- **Modelo**: Métodos de comparación validan estado actual

---

## 📝 Notas Finales

- **No requiere migración**: Usa campos JSON existentes + custom properties
- **Mantiene compatibilidad**: API response anterior sigue funcionando
- **Reversible**: No hay cambios destructivos en BD
- **Sin dependencias nuevas**: Solo usa librerías existentes (Spatie Media, jQuery Validation)
- **Testeable**: Métodos modelo son pure functions fáciles de testear

---

**Archivo creado**: `.claude/reference/project/document-validation-implementation-summary.md`
**Próximo paso**: Ejecutar las 6 fases según orden de implementación
