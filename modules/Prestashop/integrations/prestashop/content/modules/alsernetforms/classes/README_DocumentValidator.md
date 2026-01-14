# DocumentValidator - Guía de Uso

## 📚 Índice

- [Introducción](#introducción)
- [Instalación y Configuración](#instalación-y-configuración)
- [Uso Básico](#uso-básico)
- [Ejemplos Avanzados](#ejemplos-avanzados)
- [API Reference](#api-reference)
- [Manejo de Errores](#manejo-de-errores)
- [Integración con Templates](#integración-con-templates)
- [Testing](#testing)
- [FAQ](#faq)

---

## Introducción

`DocumentValidator` es una clase wrapper que proporciona validación de documentos **con resiliencia ante fallos** del servidor Laravel. Implementa el patrón **Circuit Breaker** para garantizar que ninguna petición se pierde incluso cuando el backend está temporalmente no disponible.

### ¿Por qué usar DocumentValidator?

❌ **ANTES** (sin resiliencia):
```php
// Si el servidor está caído → Error 500
// Petición se pierde → Usuario frustrado
$validation = Order::validateDniDocuments($uid);
```

✅ **AHORA** (con resiliencia):
```php
// Si servidor caído → Guarda en BD como "pending"
// Cron procesa automáticamente cuando servidor vuelve
$validator = new DocumentValidator();
$validation = $validator->validateDocuments($uid, 'corta');
```

### Características Clave

| Característica | Descripción |
|----------------|-------------|
| 🔌 **Circuit Breaker** | Evita bombardear servidor caído |
| 📋 **Queue System** | Guarda peticiones pendientes en BD |
| 🔄 **Auto Retry** | Cron procesa automáticamente |
| 🌍 **i18n Ready** | Traducciones automáticas |
| 📊 **Normalized Response** | Estructura consistente siempre |

---

## Instalación y Configuración

### 1. Verificar Dependencias

Asegúrate de que existen las clases requeridas:

```bash
# Verificar estructura de archivos
ls -la classes/
# Debe mostrar:
# - ApiManager.php
# - DocumentValidator.php
# - EndpointAvailabilityChecker.php
# - loggers/DocumentsEndpointLogger.php
```

### 2. Verificar Tablas de Base de Datos

```sql
-- Verificar que las tablas existen
SHOW TABLES LIKE '%alsernet%';

-- Debe mostrar:
-- ps_alsernet_forms_requests
-- ps_alsernet_endpoint_health
```

Si faltan, reinstala el módulo o ejecuta:
```bash
mysql -u user -p database < sql/install.sql
```

### 3. Configurar Cron Job

```bash
# Editar crontab
crontab -e

# Añadir (ejecutar cada 5 minutos)
*/5 * * * * /usr/bin/php /path/to/prestashop/modules/alsernetforms/cron/process-pending-requests.php >> /var/log/prestashop-pending.log 2>&1
```

### 4. Verificar Health Endpoints en Laravel

Asegúrate de que Laravel tiene configurados estos endpoints:

```bash
# Verificar desde PrestaShop
curl https://webadminpruebas.a-alvarez.com/api/health/ping
curl https://webadminpruebas.a-alvarez.com/api/health/documents
```

---

## Uso Básico

### Ejemplo Mínimo

```php
<?php
// Incluir la clase
include_once(_PS_MODULE_DIR_ . 'alsernetforms/classes/DocumentValidator.php');

// Crear instancia
$validator = new DocumentValidator();

// Validar documentos
$result = $validator->validateDocuments('ORDER-12345', 'dni');

// Verificar resultado
if ($result['status'] === 'success') {
    echo "Validación exitosa!";
} elseif ($result['status'] === 'pending') {
    echo "Servidor no disponible. Petición guardada.";
} else {
    echo "Error: " . $result['message'];
}
```

### Ejemplo con Contexto Completo

```php
<?php
$validator = new DocumentValidator();

$result = $validator->validateDocuments(
    'abc123xyz',                    // UID del pedido
    'corta',                        // Tipo de documento
    [
        'customer_id' => $this->context->customer->id,
        'order_reference' => 'ORD-2025-001',
        'cart_id' => $this->context->cart->id,
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ]
);
```

### Integración en alsernetforms.php

**ANTES** (línea 365):
```php
case 'documents':
    $token = Tools::getValue('token');
    $uid = extractUid($token);

    // ⚠️ Sin resiliencia
    $validation = Order::validateDniDocuments($uid);
```

**DESPUÉS** (recomendado):
```php
case 'documents':
    // Incluir DocumentValidator
    include_once(dirname(__FILE__).'/classes/DocumentValidator.php');

    // Extraer UID del token
    $token = Tools::getValue('token');
    $uid = strpos($token, '?token=') !== false
        ? trim(explode('?token=', $token)[1] ?? '')
        : trim($token);

    // Determinar tipo de documento
    $documentType = Tools::getValue('document_type') ?? 'dni';

    // Validar con resiliencia
    $validator = new DocumentValidator();
    $validation = $validator->validateDocuments($uid, $documentType, [
        'customer_id' => $this->context->customer->id ?? null,
        'order_reference' => $uid
    ]);

    // Generar traducciones
    list($trans_remember, $trans_list) = $this->generateDocumentListOnly(
        $uid,
        $validation['type']
    );

    // Asignar a Smarty
    $this->context->smarty->assign([
        'uid' => $uid,
        'trans' => $trans_remember,
        'trans_list' => $trans_list,
        'label' => $validation['label'],
        'status' => $validation['status'],
        'type' => $validation['type'],
        'upload' => $validation['upload'],
        'required_documents' => $validation['data']['required_documents'] ?? [],
        'uploaded_documents' => $validation['data']['uploaded_documents'] ?? [],
        'missing_documents' => $validation['data']['missing_documents'] ?? [],
    ]);

    // Mostrar mensaje si servidor no disponible
    if ($validation['status'] === 'pending') {
        $this->context->smarty->assign([
            'server_unavailable' => true,
            'pending_message' => $validation['message'],
            'request_id' => $validation['request_id'] ?? null
        ]);
    }

    return $this->fetch('module:alsernetforms/views/templates/hook/forms/documents/document.tpl');
```

---

## Ejemplos Avanzados

### 1. Verificar Estado de Petición Pendiente

```php
<?php
// Usuario recibió request_id cuando servidor estaba caído
$requestId = $_SESSION['pending_validation_id'] ?? null;

if ($requestId) {
    $validator = new DocumentValidator();
    $status = $validator->checkPendingRequestStatus($requestId);

    if ($status) {
        switch ($status['status']) {
            case 'success':
                // ✅ Ya procesada exitosamente
                $response = json_decode($status['response'], true);
                echo "Validación completada: " . json_encode($response);
                unset($_SESSION['pending_validation_id']);
                break;

            case 'pending':
                // ⏳ Aún pendiente
                $progress = "{$status['retry_count']}/{$status['max_retries']}";
                echo "Pendiente (intento {$progress})";
                echo "Próximo reintento: {$status['next_retry_at']}";
                break;

            case 'failed':
                // ❌ Falló después de todos los reintentos
                echo "Error permanente: {$status['last_error']}";
                unset($_SESSION['pending_validation_id']);
                break;
        }
    } else {
        echo "Petición no encontrada en BD";
    }
}
```

### 2. Ver Todas las Validaciones Pendientes de un Pedido

```php
<?php
$validator = new DocumentValidator();
$uid = 'ORDER-12345';

// Obtener todas las peticiones pendientes para este pedido
$pendingRequests = $validator->getPendingRequestsForUid($uid);

if (!empty($pendingRequests)) {
    echo "<h3>Validaciones Pendientes para {$uid}</h3>";
    echo "<ul>";

    foreach ($pendingRequests as $request) {
        $payload = json_decode($request['payload'], true);
        $docType = $payload['document_type'] ?? 'unknown';

        echo "<li>";
        echo "  <strong>Tipo:</strong> {$docType}<br>";
        echo "  <strong>Estado:</strong> {$request['status']}<br>";
        echo "  <strong>Reintentos:</strong> {$request['retry_count']}/{$request['max_retries']}<br>";
        echo "  <strong>Creado:</strong> {$request['created_at']}<br>";
        echo "  <strong>Próximo intento:</strong> {$request['next_retry_at']}<br>";
        echo "</li>";
    }

    echo "</ul>";
} else {
    echo "No hay validaciones pendientes para este pedido";
}
```

### 3. Bloquear Checkout si Hay Validaciones Pendientes

```php
<?php
// En el controller del checkout
class CheckoutController extends FrontController
{
    public function initContent()
    {
        parent::initContent();

        $validator = new DocumentValidator();
        $orderId = $this->context->cart->id;

        // Verificar si hay validaciones pendientes
        $pending = $validator->getPendingRequestsForUid($orderId);

        if (!empty($pending)) {
            // Bloquear checkout
            $this->errors[] = $this->trans(
                'Your document validation is pending. Please wait a few minutes and try again.',
                [],
                'Shop.Notifications.Error'
            );

            $this->redirectWithNotifications($this->context->link->getPageLink('cart'));
        }
    }
}
```

### 4. Dashboard de Administración

```php
<?php
// Ver estadísticas de peticiones pendientes
include_once(_PS_MODULE_DIR_ . 'alsernetforms/classes/PendingRequestsProcessor.php');

$processor = new PendingRequestsProcessor();
$stats = $processor->getPendingStats();

echo "<h2>Peticiones Pendientes por Tipo</h2>";
echo "<table>";
echo "<tr><th>Tipo</th><th>Estado</th><th>Cantidad</th><th>Más antigua</th><th>Max reintentos</th></tr>";

foreach ($stats as $stat) {
    echo "<tr>";
    echo "<td>{$stat['endpoint_type']}</td>";
    echo "<td>{$stat['status']}</td>";
    echo "<td>{$stat['count']}</td>";
    echo "<td>{$stat['oldest']}</td>";
    echo "<td>{$stat['max_retries']}</td>";
    echo "</tr>";
}

echo "</table>";
```

---

## API Reference

### validateDocuments()

```php
public function validateDocuments(
    string $uid,
    ?string $documentType = null,
    array $additionalContext = []
): array
```

**Parámetros:**
- `$uid` (string): Identificador único del pedido/token
- `$documentType` (string|null): Tipo de documento ('corta', 'rifle', 'escopeta', 'dni')
- `$additionalContext` (array): Contexto adicional opcional

**Retorna:**
```php
[
    'status' => 'success|pending|error',
    'type' => 'corta|rifle|escopeta|dni',
    'label' => 'Etiqueta traducida',
    'upload' => true|false,
    'data' => [
        'required_documents' => [...],
        'uploaded_documents' => [...],
        'missing_documents' => [...]
    ],
    'message' => 'Mensaje descriptivo',
    'request_id' => 123,        // Solo si status='pending'
    'reason' => 'Timeout',      // Solo si status='pending'
    'error' => 'Error details'  // Solo si status='error'
]
```

---

### checkPendingRequestStatus()

```php
public function checkPendingRequestStatus(int $requestId): array|false
```

**Parámetros:**
- `$requestId` (int): ID de la petición retornado por validateDocuments()

**Retorna:**
- Array con datos completos de la petición si existe
- `false` si no se encuentra

---

### getPendingRequestsForUid()

```php
public function getPendingRequestsForUid(string $uid): array
```

**Parámetros:**
- `$uid` (string): UID del pedido

**Retorna:**
- Array de peticiones pendientes (puede estar vacío)

---

## Manejo de Errores

### Tipos de Estado

| Estado | Significado | Acción Recomendada |
|--------|-------------|-------------------|
| `success` | Validación exitosa | Proceder con flujo normal |
| `pending` | Servidor no disponible | Mostrar mensaje de espera, guardar request_id |
| `error` | Error en validación | Mostrar error al usuario, logging |

### Ejemplo de Manejo Completo

```php
<?php
$validator = new DocumentValidator();
$result = $validator->validateDocuments($uid, $type, $context);

switch ($result['status']) {
    case 'success':
        // ✅ Todo bien
        $uploaded = $result['data']['uploaded_documents'];
        $missing = $result['data']['missing_documents'];

        if (empty($missing)) {
            // Todos los documentos completos
            $this->context->smarty->assign('documents_complete', true);
        } else {
            // Faltan algunos documentos
            $this->context->smarty->assign('missing_docs', $missing);
        }
        break;

    case 'pending':
        // ⏳ Servidor no disponible
        $_SESSION['pending_validation_id'] = $result['request_id'];
        $_SESSION['pending_validation_time'] = time();

        $this->context->smarty->assign([
            'server_unavailable' => true,
            'message' => $this->trans(
                'The validation server is temporarily unavailable. Your request has been saved and will be processed automatically.',
                [],
                'Modules.Alsernetforms.Shop'
            ),
            'reason' => $result['reason']
        ]);

        // Log para monitorización
        PrestaShopLogger::addLog(
            "DocumentValidator: Server unavailable for UID {$uid}. Request #{$result['request_id']}",
            2,  // Warning
            null,
            'DocumentValidator',
            $result['request_id']
        );
        break;

    case 'error':
        // ❌ Error en validación
        $this->errors[] = $this->trans(
            'There was an error validating your documents: %error%',
            ['%error%' => $result['message']],
            'Shop.Notifications.Error'
        );

        // Log de error
        PrestaShopLogger::addLog(
            "DocumentValidator: Error for UID {$uid}: {$result['error']}",
            3,  // Error
            null,
            'DocumentValidator'
        );
        break;
}
```

---

## Integración con Templates

### Template Smarty (document.tpl)

```smarty
{if $status == 'success'}
    <div class="alert alert-success">
        <i class="fa fa-check-circle"></i>
        {l s='Documents validated successfully' mod='alsernetforms'}
    </div>

    {if $missing_documents}
        <div class="alert alert-warning">
            <h4>{l s='Missing documents:' mod='alsernetforms'}</h4>
            <ul>
                {foreach from=$missing_documents item=doc}
                    <li>{$doc}</li>
                {/foreach}
            </ul>
        </div>
    {/if}

{elseif $status == 'pending'}
    <div class="alert alert-info">
        <i class="fa fa-clock-o"></i>
        <strong>{l s='Server temporarily unavailable' mod='alsernetforms'}</strong>
        <p>{$pending_message}</p>
        <p><small>{l s='Your request ID:' mod='alsernetforms'} #{$request_id}</small></p>
        <p><small>{l s='We will process your validation automatically when the server is available.' mod='alsernetforms'}</small></p>
    </div>

{elseif $status == 'error'}
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-triangle"></i>
        <strong>{l s='Validation error' mod='alsernetforms'}</strong>
        <p>{$message}</p>
    </div>
{/if}

{* Formulario de subida (solo si upload=true) *}
{if $upload}
    <form id="document-upload-form" method="post" enctype="multipart/form-data">
        {* ... campos del formulario ... *}
    </form>
{else}
    <div class="alert alert-warning">
        {l s='Document upload is currently disabled. Please try again later.' mod='alsernetforms'}
    </div>
{/if}
```

---

## Testing

### Test Manual desde Terminal

```php
<?php
// test_document_validator.php

require_once(__DIR__ . '/config/config.inc.php');
require_once(__DIR__ . '/modules/alsernetforms/classes/DocumentValidator.php');

$validator = new DocumentValidator();

// Test 1: Validación básica
echo "Test 1: Validación básica\n";
$result = $validator->validateDocuments('TEST-001', 'dni');
echo "Status: {$result['status']}\n";
echo "Type: {$result['type']}\n";
echo "Label: {$result['label']}\n";
echo "Upload: " . ($result['upload'] ? 'YES' : 'NO') . "\n\n";

// Test 2: Con contexto completo
echo "Test 2: Con contexto completo\n";
$result = $validator->validateDocuments('TEST-002', 'corta', [
    'customer_id' => 123,
    'order_reference' => 'ORD-TEST-002',
    'ip_address' => '127.0.0.1'
]);
echo "Status: {$result['status']}\n";
if (isset($result['request_id'])) {
    echo "Request ID: {$result['request_id']}\n";
}
echo "\n";

// Test 3: Verificar petición pendiente
if (isset($result['request_id'])) {
    echo "Test 3: Verificar estado de petición pendiente\n";
    $status = $validator->checkPendingRequestStatus($result['request_id']);
    echo "Found: " . ($status ? 'YES' : 'NO') . "\n";
    if ($status) {
        echo "Status: {$status['status']}\n";
        echo "Retry count: {$status['retry_count']}/{$status['max_retries']}\n";
    }
    echo "\n";
}

// Test 4: Ver peticiones pendientes por UID
echo "Test 4: Peticiones pendientes para TEST-002\n";
$pending = $validator->getPendingRequestsForUid('TEST-002');
echo "Count: " . count($pending) . "\n";
foreach ($pending as $req) {
    echo "  - Request #{$req['id_alsernetforms_request']}: {$req['status']}\n";
}
```

Ejecutar:
```bash
php test_document_validator.php
```

---

## FAQ

### ¿Qué pasa si el servidor Laravel nunca vuelve?

El sistema intentará 3 veces con delays exponenciales (1min, 5min, 15min). Después de 3 intentos fallidos, la petición se marca como `failed` permanentemente.

### ¿Puedo cambiar el número máximo de reintentos?

Sí, editando `DocumentsEndpointLogger.php`:

```php
$this->db->insert('alsernet_forms_requests', [
    'max_retries' => 5,  // Cambiar de 3 a 5
    // ... otros campos
]);
```

### ¿Cómo monitorear peticiones pendientes?

Usa el método `getPendingStats()` del `PendingRequestsProcessor`:

```php
$processor = new PendingRequestsProcessor();
$stats = $processor->getPendingStats();
```

O consulta SQL directa:

```sql
SELECT
    endpoint_type,
    status,
    COUNT(*) as count,
    MIN(created_at) as oldest
FROM ps_alsernet_forms_requests
WHERE status IN ('pending', 'server_unavailable')
GROUP BY endpoint_type, status;
```

### ¿Puedo forzar el procesamiento de pendientes?

Sí, ejecuta manualmente el cron:

```bash
php modules/alsernetforms/cron/process-pending-requests.php
```

### ¿Qué pasa si hay múltiples peticiones para el mismo UID?

Todas se procesan independientemente. Usa `getPendingRequestsForUid()` para verlas todas y tomar decisiones (ej: bloquear checkout hasta que todas se completen).

---

## Changelog

### v1.0.0 (2025-01-06)
- ✨ Versión inicial con Circuit Breaker Pattern
- ✨ Sistema de queue para peticiones pendientes
- ✨ Auto-retry con exponential backoff
- ✨ Soporte i18n completo
- ✨ Métodos de tracking: `checkPendingRequestStatus()` y `getPendingRequestsForUid()`

---

## Soporte

Para más información, consulta:
- [DOCUMENTATION.md](../DOCUMENTATION.md) - Documentación completa del sistema
- [README_PENDING_REQUESTS.md](../README_PENDING_REQUESTS.md) - Sistema de peticiones pendientes
- [HEALTH_CHECK_INTEGRATION.md](../HEALTH_CHECK_INTEGRATION.md) - Health checks de Laravel

**Desarrollado por:** Alsernet Development Team
**Versión:** 1.0.0
**Fecha:** 2025-01-06
