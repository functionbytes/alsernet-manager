# Refactorización: process() → Endpoints RESTful

**Fecha:** 2026-01-07
**Estado:** ✅ Completado
**Impacto:** Breaking change compatible hacia atrás (deprecated)

---

## 🎯 Objetivo

Eliminar el endpoint monolítico `process()` con switch hard-coded y reemplazarlo con endpoints RESTful individuales que sigan convenciones HTTP correctas.

---

## 🔴 Problema Antes de la Refactorización

### God Endpoint con Magic Parameter

```php
// ❌ ANTI-PATRÓN: Todo usa POST con parámetro action
POST /api/documents
{
    "action": "verification",  // ← Magic parameter
    "order_id": 123
}

POST /api/documents
{
    "action": "validate",  // ← Debería ser GET
    "uid": "abc123"
}

POST /api/documents
{
    "action": "upload",  // ← Debería ser POST a sub-recurso
    "uid": "abc123",
    "file": [...]
}

POST /api/documents
{
    "action": "delete",  // ← Debería ser DELETE
    "uid": "abc123",
    "doc_type": "dni"
}
```

### Switch Hard-coded en Controller

```php
public function process(Request $request)
{
    $action = $request->input('action');

    switch ($action) {  // ← Viola Open/Closed Principle
        case 'verification':
            return $this->documentVerification($data);
        case 'validate':
            return $this->documentValidates($data);
        case 'request':
            return $this->documentRequests($data);
        case 'upload':
            return $this->documentUpload($request);
        case 'delete':
            return $this->documentDelete($data);
        default:
            return response()->json(['message' => 'Invalid action'], 400);
    }
}
```

### Problemas Identificados

1. ❌ **Violación de semántica HTTP**: Todo usa POST (incluso lecturas)
2. ❌ **God Endpoint**: Un endpoint hace múltiples cosas no relacionadas
3. ❌ **No cacheable**: GET requests podrían usar cache HTTP
4. ❌ **Hard-coded switch**: Agregar acción requiere modificar `process()`
5. ❌ **Difícil de testear**: Tests poco claros (`POST` + magic `action`)
6. ❌ **Documentación confusa**: API doc debe explicar parámetro `action`

---

## ✅ Solución Implementada

### Nuevos Endpoints RESTful

| Acción Deprecated | Endpoint RESTful | Método | Controller Method |
|-------------------|------------------|--------|-------------------|
| `action=verification` | `GET /api/documents/verify?order_id={id}` | GET | `verify()` |
| `action=validate` | `GET /api/documents/{uid}/validation` | GET | `validation()` |
| `action=request` | `POST /api/documents/create` | POST | `store()` |
| `action=upload` | `POST /api/documents/{uid}/files` | POST | `uploadFiles()` |
| `action=delete` | `DELETE /api/documents/{uid}/files/{docType}` | DELETE | `deleteFile()` |

---

## 📋 Archivos Modificados

### 1. DocumentsController.php (Laravel)

#### Nuevos Métodos Creados

```php
/**
 * GET /api/documents/verify?order_id={order_id}
 */
public function verify(Request $request)
{
    $request->validate(['order_id' => 'required|integer']);
    $document = Document::where('order_id', $request->order_id)->first();

    if (!$document) {
        return response()->json(['status' => 'not_found', ...], 404);
    }

    return response()->json([
        'status' => 'success',
        'data' => [
            'uid' => $document->uid,
            'reference' => $document->order_reference,
            'type' => $document->type,
        ],
    ], 200);
}

/**
 * GET /api/documents/{uid}/validation
 */
public function validation($uid)
{
    $document = Document::uid($uid)->first();

    if (!$document) {
        return response()->json(['status' => 'failed', ...], 404);
    }

    // Validar estado, retornar documentos requeridos/subidos/faltantes
    return response()->json([
        'status' => 'success',
        'data' => [
            'uid' => $document->uid,
            'can_upload' => ...,
            'required_documents' => $document->getRequiredDocumentsWithLabels(),
            'uploaded_documents' => $document->getUploadedDocumentsWithDetails(),
            'missing_documents' => $document->getMissingDocuments(),
        ],
    ], 200);
}

/**
 * POST /api/documents/create
 */
public function store(Request $request)
{
    return $this->documentRequests($request->all());
}

/**
 * POST /api/documents/{uid}/files
 */
public function uploadFiles(Request $request, $uid)
{
    $document = Document::uid($uid)->first();
    // ... lógica de subida de archivos
}

/**
 * DELETE /api/documents/{uid}/files/{docType}
 */
public function deleteFile($uid, $docType)
{
    $document = Document::uid($uid)->first();
    // ... lógica de eliminación
}
```

#### Método process() Deprecated

```php
/**
 * @deprecated Use individual RESTful endpoints instead
 * @see verify() for verification
 * @see validation() for validation
 * @see store() for creating documents
 * @see uploadFiles() for uploading files
 * @see deleteFile() for deleting files
 */
public function process(Request $request)
{
    // Log uso de endpoint deprecated
    \Log::warning('Deprecated endpoint used: POST /api/documents with action parameter', [
        'action' => $request->input('action'),
        'ip' => $request->ip(),
    ]);

    // Delegar a nuevos métodos RESTful
    switch ($action) {
        case 'verification':
            return $this->verify($request);
        case 'validate':
            return $this->validation($request->input('uid'));
        case 'request':
            return $this->store($request);
        case 'upload':
            return $this->uploadFiles($request, $request->input('uid'));
        case 'delete':
            return $this->deleteFile($request->input('uid'), $request->input('doc_type'));
        default:
            return response()->json([
                'message' => 'Invalid action. Use RESTful endpoints instead.',
            ], 400);
    }
}
```

### 2. routes/api.php (Laravel)

```php
Route::middleware('throttle:60,1')->group(function () {
    // ⚠️ DEPRECATED: Mantener para backward compatibility
    Route::post('/', [DocumentsController::class, 'process'])->name('process');

    // ✅ NEW: Endpoints RESTful individuales
    Route::get('/verify', [DocumentsController::class, 'verify'])->name('verify');
    Route::get('/{uid}/validation', [DocumentsController::class, 'validation'])->name('validation');
    Route::post('/create', [DocumentsController::class, 'store'])->name('store');
    Route::post('/{uid}/files', [DocumentsController::class, 'uploadFiles'])->name('files.upload');
    Route::delete('/{uid}/files/{docType}', [DocumentsController::class, 'deleteFile'])->name('files.delete');
});
```

### 3. DocumentAction.php (PrestaShop)

**REFACTORIZADO** para usar nuevo endpoint GET:

```php
public function validateToken($token, array $context = [])
{
    // ✅ NUEVO: Usa GET /api/documents/{uid}/validation
    $url = rtrim($this->apiManager->getBaseUrl(), '/') . '/' .
           ltrim($this->endpoint, '/') . '/' . $token . '/validation';

    // Iniciar tracking
    $requestId = $this->logger->logRequest('GET', $url, $context);

    // Verificar disponibilidad
    $availability = $this->checkAvailability($url);

    if (!$availability['available']) {
        $this->logger->markAsServerUnavailable($requestId, ...);
        return ['status' => 'pending', ...];
    }

    // Enviar GET request (sin payload)
    $httpResponse = $this->apiManager->sendRequestWithoutLogging(
        'GET',
        $this->endpoint . '/' . $token . '/validation',
        [],  // Sin payload para GET
        []
    );

    // Actualizar tracking
    $this->logger->updateRequestLog($requestId, ...);

    return $this->mapResponse($httpResponse);
}
```

---

## 🎯 Beneficios Logrados

### 1. Semántica HTTP Correcta

```php
// ✅ GET para consultas (cacheable, idempotente)
GET /api/documents/verify?order_id=123
GET /api/documents/{uid}/validation

// ✅ POST para creación
POST /api/documents/create

// ✅ POST para sub-recursos
POST /api/documents/{uid}/files

// ✅ DELETE para eliminación
DELETE /api/documents/{uid}/files/{docType}
```

### 2. URLs Auto-Documentadas

```php
// ❌ Antes: ¿Qué hace este endpoint?
POST /api/documents

// ✅ Después: Claro y explícito
GET /api/documents/verify
GET /api/documents/{uid}/validation
POST /api/documents/{uid}/files
DELETE /api/documents/{uid}/files/{docType}
```

### 3. Cacheable (GET endpoints)

```php
// Laravel puede usar cache HTTP
Route::get('/{uid}/validation', ...)
    ->middleware('cache.headers:public;max_age=60');
```

### 4. Tests Más Legibles

```php
// ❌ Antes
$this->post('/api/documents', ['action' => 'validate', 'uid' => '123'])
    ->assertStatus(200);

// ✅ Después
$this->get('/api/documents/123/validation')
    ->assertStatus(200);
```

### 5. Extensible (Open/Closed Principle)

```php
// ✅ Fácil agregar nuevos endpoints sin modificar process()
Route::get('/{uid}/summary', [DocumentsController::class, 'summary']);
Route::patch('/{uid}/status', [DocumentsController::class, 'updateStatus']);
```

---

## 📊 Comparación Antes/Después

| Aspecto | Antes | Después |
|---------|-------|---------|
| **Endpoints** | 1 (POST /) | 5 específicos |
| **Verbos HTTP** | ❌ Solo POST | ✅ GET/POST/DELETE |
| **Semántica** | ❌ Depende de action | ✅ URL explica acción |
| **Cacheable** | ❌ No | ✅ Sí (GET) |
| **Agregar acción** | Modificar switch | Nueva ruta |
| **Tests** | ❌ POST + action | ✅ Método + URL |
| **Logging** | ❌ Oculto en switch | ✅ Explícito por endpoint |

---

## 🔄 Estrategia de Migración

### Fase 1: Implementación (✅ COMPLETADO)

- ✅ Crear nuevos métodos RESTful en DocumentsController
- ✅ Agregar nuevas rutas en api.php
- ✅ Deprecar `process()` con logging
- ✅ Actualizar PrestaShop DocumentAction para validateToken()
- ✅ Actualizar JavaScript (documents.js) para upload/delete/validate

### Fase 2: Migración (🔄 EN PROGRESO)

- ⏳ Monitorear logs de uso de endpoint deprecated
- ⏳ Comunicar a equipos sobre nuevos endpoints
- ⏳ Testear endpoints en producción

### Fase 3: Eliminación (📅 6 MESES DESPUÉS)

- ⏳ Verificar que no hay tráfico a `process()`
- ⏳ Eliminar ruta `POST /` con `process()`
- ⏳ Eliminar método `process()` del controller
- ⏳ Actualizar documentación API

---

## ⚠️ Breaking Changes

### Para Clientes Externos (PrestaShop)

#### Verificación de Documento

**Antes:**
```php
POST /api/documents
{
    "action": "verification",
    "order_id": 123
}
```

**Después:**
```php
GET /api/documents/verify?order_id=123
```

#### Validación de Documento

**Antes:**
```php
POST /api/documents
{
    "action": "validate",
    "uid": "abc123"
}
```

**Después:**
```php
GET /api/documents/abc123/validation
```

#### Subida de Archivos

**Antes:**
```php
POST /api/documents
{
    "action": "upload",
    "uid": "abc123",
    "file": [...],
    "document_types": [...]
}
```

**Después:**
```php
POST /api/documents/abc123/files
{
    "file": [...],
    "document_types": [...]
}
```

#### Eliminación de Archivo

**Antes:**
```php
POST /api/documents
{
    "action": "delete",
    "uid": "abc123",
    "doc_type": "dni"
}
```

**Después:**
```php
DELETE /api/documents/abc123/files/dni
```

---

## 📝 Ejemplo de Uso desde PrestaShop

### Validar Token (Refactorizado)

```php
// PrestaShop: alsernetforms.php
case 'documents':
    $token = Tools::getValue('token');

    include_once dirname(__FILE__).'/classes/Actions/DocumentAction.php';

    $documentAction = new DocumentAction;

    // ✅ NUEVO: Usa GET /api/documents/{token}/validation
    $validation = $documentAction->validateToken($token, [
        'customer_id' => $this->context->customer->id ?? null,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);

    $tokenData = $validation['data'] ?? [];
    $uid = $tokenData['uid'] ?? null;
    $documentType = $tokenData['document_type'] ?? 'dni';

    // ... resto del código
```

---

## 📝 Migración JavaScript (Frontend PrestaShop)

### Validar Token (documents.js)

**Antes:**
```javascript
$.ajax({
    url: 'https://webadminpruebas.a-alvarez.com/api/documents',
    type: 'POST',
    data: {
        action: 'validate',
        uid: uid
    },
    success: function(response) { ... }
});
```

**Después:**
```javascript
// ✅ NEW: RESTful GET endpoint
$.ajax({
    url: 'https://webadminpruebas.a-alvarez.com/api/documents/' + uid + '/validation',
    type: 'GET',
    success: function(response) { ... }
});
```

### Subir Archivo (documents.js)

**Antes:**
```javascript
formData.append('action', 'upload');
formData.append('uid', $('#uid').val());
formData.append('file[]', fileData.file);
formData.append('document_types[]', fileData.docType);

$.ajax({
    url: 'https://webadminpruebas.a-alvarez.com/api/documents',
    type: 'POST',
    data: formData,
    ...
});
```

**Después:**
```javascript
const uid = $('#uid').val();
formData.append('file[]', fileData.file);
formData.append('document_types[]', fileData.docType);

// ✅ NEW: RESTful POST endpoint (uid en URL)
$.ajax({
    url: 'https://webadminpruebas.a-alvarez.com/api/documents/' + uid + '/files',
    type: 'POST',
    data: formData,
    ...
});
```

### Eliminar Archivo (documents.js)

**Antes:**
```javascript
$.ajax({
    url: 'https://webadminpruebas.a-alvarez.com/api/documents',
    type: 'POST',
    data: {
        action: 'delete',
        uid: $('#uid').val(),
        doc_type: docType
    },
    ...
});
```

**Después:**
```javascript
const uid = $('#uid').val();

// ✅ NEW: RESTful DELETE endpoint
$.ajax({
    url: 'https://webadminpruebas.a-alvarez.com/api/documents/' + uid + '/files/' + docType,
    type: 'DELETE',
    ...
});
```

---

## 🚀 Próximos Pasos

1. ✅ Implementar nuevos endpoints RESTful (COMPLETADO)
2. ✅ Deprecar `process()` con logging (COMPLETADO)
3. ✅ Actualizar PrestaShop `validateToken()` (COMPLETADO)
4. ✅ Actualizar JavaScript para upload/delete/validate (COMPLETADO)
5. ⏳ Monitorear logs durante 6 meses
6. ⏳ Eliminar endpoint deprecated

---

## 📚 Documentación Relacionada

- **Refactorización Actions+Loggers PrestaShop**: `REFACTORING_ACTIONS_LOGGERS.md`
- **Arquitectura BaseAction**: `classes/Actions/README.md`
- **API Documentation**: Actualizar con nuevos endpoints

---

**Última actualización:** 2026-01-07
**Autor:** Claude Code AI
**Versión:** 1.1
**Estado:** ✅ Implementación completada (Backend + Frontend), pendiente monitoreo y eliminación
