# Sistema de Gestión de Peticiones Pendientes - Documentación Completa

## 📋 Índice

1. [Introducción](#introducción)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Guía de Instalación Rápida](#guía-de-instalación-rápida)
4. [Componentes del Sistema](#componentes-del-sistema)
5. [Endpoints de Health Check](#endpoints-de-health-check)
6. [Integración en PrestaShop](#integración-en-prestashop)
7. [Configuración Avanzada](#configuración-avanzada)
8. [Monitorización](#monitorización)
9. [Troubleshooting](#troubleshooting)
10. [Referencias Adicionales](#referencias-adicionales)

---

## Introducción

### ¿Qué problema resuelve este sistema?

Cuando PrestaShop necesita validar documentos de pedidos, hace peticiones al servidor Laravel. Si el servidor está temporalmente no disponible (mantenimiento, caída, sobrecarga), las peticiones fallan y **se pierden los documentos**.

**Este sistema soluciona el problema:**
- ✅ Detecta automáticamente cuando el servidor no está disponible
- ✅ Guarda las peticiones como "pendientes" en base de datos
- ✅ Un cron job procesa automáticamente las peticiones pendientes cuando el servidor vuelve
- ✅ Usa **Circuit Breaker pattern** para no sobrecargar servidores caídos
- ✅ Implementa **backoff exponencial** para reintentos inteligentes

### ¿Cómo funciona?

```
┌──────────────────────────────────────────────────────────────┐
│ 1. Usuario solicita validar documentos                      │
│    ↓                                                         │
│ 2. PrestaShop verifica: ¿Servidor disponible?              │
│    ├─ SÍ  → Procesa inmediatamente                         │
│    └─ NO  → Guarda como "pending" en BD                    │
│               ↓                                              │
│ 3. Cron ejecuta cada 5 minutos                             │
│    ├─ Lee peticiones pendientes                            │
│    ├─ Verifica servidor disponible                         │
│    ├─ Procesa las que puede                                │
│    └─ Reprograma las que aún no puede                      │
└──────────────────────────────────────────────────────────────┘
```

---

## Arquitectura del Sistema

### Diagrama de Componentes

```
┌─────────────────────────────────────────────────────────────┐
│                    PRESTASHOP                               │
│                                                             │
│  ┌──────────────────┐     ┌─────────────────────────┐     │
│  │ alsernetforms.   │────>│ DocumentValidator       │     │
│  │ php              │     └──────────┬──────────────┘     │
│  │ (case documents) │                │                     │
│  └──────────────────┘                v                     │
│                           ┌─────────────────────────┐      │
│                           │ ApiManager              │      │
│                           └──────────┬──────────────┘      │
│                                      │                     │
│              ┌───────────────────────┼──────────────┐      │
│              │                       │              │      │
│              v                       v              v      │
│  ┌─────────────────────┐ ┌──────────────┐ ┌──────────────┐│
│  │EndpointAvailability │ │ Documents    │ │Pending       ││
│  │Checker              │ │ Endpoint     │ │Requests      ││
│  │                     │ │ Logger       │ │Processor     ││
│  └──────────┬──────────┘ └──────────────┘ └──────┬───────┘│
│             │                                     │        │
│             └─────────────────┬───────────────────┘        │
│                               │                            │
│                               v                            │
│                    ┌─────────────────────┐                 │
│                    │ Base de Datos       │                 │
│                    │ - requests table    │                 │
│                    │ - health table      │                 │
│                    └─────────────────────┘                 │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           │ HTTP GET /api/health/documents
                           │
┌──────────────────────────▼──────────────────────────────────┐
│                      LARAVEL                                │
│                                                             │
│  ┌──────────────────────────────────────────────────┐      │
│  │ HealthCheckController                            │      │
│  │                                                  │      │
│  │  ┌─────────────────────────────────────┐        │      │
│  │  │ GET /api/health/ping                │        │      │
│  │  │ → {status: "ok"}                    │        │      │
│  │  └─────────────────────────────────────┘        │      │
│  │                                                  │      │
│  │  ┌─────────────────────────────────────┐        │      │
│  │  │ GET /api/health                     │        │      │
│  │  │ → Verifica: App + DB + Cache        │        │      │
│  │  └─────────────────────────────────────┘        │      │
│  │                                                  │      │
│  │  ┌─────────────────────────────────────┐        │      │
│  │  │ GET /api/health/documents           │        │      │
│  │  │ → Verifica: DB + Storage            │        │      │
│  │  └─────────────────────────────────────┘        │      │
│  └──────────────────────────────────────────────────┘      │
└─────────────────────────────────────────────────────────────┘
```

### Patrones de Diseño Implementados

1. **Circuit Breaker Pattern** 🔌
   - Evita bombardear servidor caído
   - Tras 3 fallos → espera 5 minutos antes de reintentar
   - Protege contra cascadas de fallos

2. **Strategy Pattern** 📋
   - Diferentes loggers por tipo de endpoint
   - Fácil extensión para nuevos tipos

3. **Exponential Backoff** ⏱️
   - Reintentos: 1min → 5min → 15min → 30min → 60min
   - Equilibra rapidez vs protección

4. **Facade Pattern** 🎭
   - `DocumentValidator` simplifica API compleja
   - Una llamada hace todo el trabajo

---

## Guía de Instalación Rápida

### Paso 1: Crear Tablas en PrestaShop

**Opción A: Reinstalar el módulo**
```
Panel Admin → Módulos → Buscar "Alsernet - Formularios" → Reinstalar
```

**Opción B: SQL Manual**
```bash
mysql -u usuario -p prestashop_db < integrations/prestashop/content/modules/alsernetforms/sql/install.sql
```

**Verificar:**
```sql
SHOW TABLES LIKE '%alsernet%';
-- Debe mostrar:
-- ps_alsernet_forms_requests
-- ps_alsernet_endpoint_health
```

### Paso 2: Verificar Laravel Health Endpoints

```bash
# Ping
curl https://webadminpruebas.a-alvarez.com/api/health/ping

# Health documentos
curl https://webadminpruebas.a-alvarez.com/api/health/documents

# Health completo
curl https://webadminpruebas.a-alvarez.com/api/health
```

**Respuesta esperada:**
```json
{
  "status": "healthy",
  "service": "documents-validation",
  "checks": {
    "database": {"status": "healthy"},
    "storage": {"status": "healthy"}
  }
}
```

### Paso 3: Configurar Cron Job

**Crontab (Recomendado):**
```bash
crontab -e

# Añadir (ejecutar cada 5 minutos):
*/5 * * * * /usr/bin/php /ruta/completa/prestashop/modules/alsernetforms/cron/process-pending-requests.php >> /var/log/prestashop-pending.log 2>&1
```

**Verificar cron:**
```bash
# Ver crontab actual
crontab -l

# Ver logs
tail -f /var/log/prestashop-pending.log
```

### Paso 4: Modificar alsernetforms.php

**Ubicación:** `integrations/prestashop/content/modules/alsernetforms/alsernetforms.php`

**Buscar línea 318 (aproximadamente):**
```php
case 'documents':
    $token = Tools::getValue('token');
    $uid = strpos($token, '?token=') !== false
        ? trim(explode('?token=', $token)[1] ?? '')
        : trim($token);

    // CAMBIAR ESTA LÍNEA:
    $validation = Order::validateDniDocuments($uid);
```

**Reemplazar por:**
```php
case 'documents':
    // Incluir DocumentValidator
    include_once(dirname(__FILE__).'/classes/DocumentValidator.php');

    $token = Tools::getValue('token');
    $uid = strpos($token, '?token=') !== false
        ? trim(explode('?token=', $token)[1] ?? '')
        : trim($token);

    // Crear instancia del validador
    $validator = new DocumentValidator();

    // Obtener tipo de documento (implementar según tu lógica)
    $documentType = $this->getDocumentTypeFromUid($uid);

    // Validar con verificación automática de disponibilidad
    $validation = $validator->validateDocuments($uid, $documentType, [
        'customer_id' => $this->context->customer->id ?? null,
        'order_reference' => $uid
    ]);

    // El resto del código permanece igual...
    list($trans_remember, $trans_list) = $this->generateDocumentListOnly($uid, $validation['type']);

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

    // NUEVO: Mostrar mensaje si servidor no disponible
    if ($validation['status'] === 'pending') {
        $this->context->smarty->assign([
            'server_unavailable' => true,
            'pending_message' => $validation['message'] ?? 'Server temporarily unavailable',
            'request_id' => $validation['request_id'] ?? null
        ]);
    }

    return $this->fetch('module:alsernetforms/views/templates/hook/forms/documents/document.tpl');
```

**Añadir método auxiliar:**
```php
/**
 * Obtiene el tipo de documento según el UID
 * IMPLEMENTAR según tu lógica de negocio
 */
private function getDocumentTypeFromUid($uid)
{
    // Opción 1: Desde parámetro GET
    $type = Tools::getValue('document_type');
    if ($type) {
        return $type;
    }

    // Opción 2: Desde la orden
    // $order = new Order(Order::getOrderByCartId((int)$uid));
    // return $order->getDocumentType();

    // Opción 3: Por defecto
    return 'dni';
}
```

### Paso 5: Probar el Sistema

**1. Simular servidor caído:**
```bash
# En Laravel, apagar temporalmente
php artisan down
```

**2. Intentar validar documento desde PrestaShop:**
```
La petición debe guardarse como "pending"
```

**3. Verificar en base de datos:**
```sql
SELECT * FROM ps_alsernet_forms_requests
WHERE status = 'pending'
ORDER BY created_at DESC
LIMIT 5;
```

**4. Levantar servidor:**
```bash
php artisan up
```

**5. Ejecutar cron manualmente:**
```bash
php integrations/prestashop/content/modules/alsernetforms/cron/process-pending-requests.php
```

**6. Verificar que se procesó:**
```sql
SELECT * FROM ps_alsernet_forms_requests
WHERE status = 'success'
ORDER BY synced_at DESC
LIMIT 5;
```

---

## Componentes del Sistema

### 1. EndpointAvailabilityChecker.php

**Responsabilidad:** Verificar disponibilidad del servidor antes de enviar peticiones.

**Características:**
- Circuit Breaker: Tras 3 fallos → espera 5 minutos
- Health Check: Usa `/api/health/documents`
- Cacheo: Evita verificar múltiples veces en poco tiempo

**Métodos principales:**
```php
// Verificar disponibilidad
$checker->isEndpointAvailable($url, 'documents');

// Forzar verificación (ignora cache)
$checker->forceCheck($url, 'documents');

// Ver estadísticas
$checker->getHealthStats();
```

### 2. DocumentValidator.php

**Responsabilidad:** Wrapper simplificado para validar documentos.

**Uso:**
```php
$validator = new DocumentValidator();
$result = $validator->validateDocuments($uid, $documentType, [
    'customer_id' => 123,
    'order_reference' => 'ORD-456'
]);

// $result['status'] puede ser:
// - 'success': Validación exitosa
// - 'pending': Servidor no disponible, guardado para después
// - 'error': Error en validación
```

### 3. DocumentsEndpointLogger.php

**Responsabilidad:** Registrar peticiones con contexto de documentos.

**Métodos:**
```php
// Registrar petición
$logger->logDocumentRequest('POST', $url, $data, [
    'uid' => $uid,
    'document_type' => 'corta'
]);

// Marcar como no disponible
$logger->markAsServerUnavailable($requestId, $reason, $nextRetryAt);

// Obtener pendientes
$pending = $logger->getPendingRequests(50);

// Estadísticas
$stats = $logger->getStats();
```

### 4. PendingRequestsProcessor.php

**Responsabilidad:** Procesar peticiones pendientes (ejecutado por cron).

**Características:**
- Procesa en lotes de 50
- Timeout de 5 minutos
- Backoff exponencial
- Limpia peticiones antiguas

**Métodos:**
```php
$processor = new PendingRequestsProcessor();

// Procesar pendientes
$stats = $processor->process();

// Ver estadísticas
$pending = $processor->getPendingStats();

// Limpiar antiguas (>30 días)
$deleted = $processor->cleanupOldRequests(30);
```

### 5. ApiManager.php

**Responsabilidad:** Gestionar peticiones HTTP con verificación de disponibilidad.

**Uso:**
```php
$apiManager = new ApiManager();

$response = $apiManager->sendRequest(
    'POST',                    // Método
    '/api/orders/validate',    // Endpoint
    ['uid' => $uid],          // Datos
    'documents',              // Tipo
    [],                       // Headers
    true                      // Verificar disponibilidad
);

// $response['status'] puede ser:
// - 'success': Petición exitosa
// - 'pending': Servidor no disponible
// - 'error': Error en petición
```

---

## Endpoints de Health Check

### Tabla Resumen

| Endpoint | Método | Auth | Tiempo | Verifica |
|----------|--------|------|--------|----------|
| `/api/health/ping` | GET | No | <10ms | Solo app |
| `/api/health` | GET | No | <100ms | App + DB + Cache |
| `/api/health/documents` | GET | No | <50ms | DB + Storage |
| `/api/health/detailed` | GET | No* | ~150ms | Todo + métricas |

*Solo disponible con `APP_DEBUG=true`

### GET /api/health/ping

**Uso:** Verificación rápida de que la app responde.

**Respuesta:**
```json
{
  "status": "ok",
  "timestamp": "2025-12-23T10:30:00+00:00",
  "service": "Manager"
}
```

### GET /api/health

**Uso:** Verificación completa de todos los servicios.

**Respuesta (healthy):**
```json
{
  "status": "healthy",
  "timestamp": "2025-12-23T10:30:00+00:00",
  "service": "Manager",
  "version": "1.0.0",
  "response_time_ms": 45.23,
  "checks": {
    "application": {
      "status": "healthy",
      "message": "Application is running",
      "environment": "production"
    },
    "database": {
      "status": "healthy",
      "message": "Database connection is working",
      "connection": "pgsql",
      "response_time_ms": 12.5
    },
    "cache": {
      "status": "healthy",
      "message": "Cache is working",
      "driver": "redis",
      "response_time_ms": 8.3
    }
  }
}
```

**Respuesta (unhealthy) - HTTP 503:**
```json
{
  "status": "unhealthy",
  "checks": {
    "database": {
      "status": "unhealthy",
      "message": "Database connection failed",
      "error": "SQLSTATE[HY000] Connection refused"
    }
  }
}
```

### GET /api/health/documents

**Uso:** Verificación específica para endpoints de documentos.

**Respuesta:**
```json
{
  "status": "healthy",
  "service": "documents-validation",
  "timestamp": "2025-12-23T10:30:00+00:00",
  "checks": {
    "database": {
      "status": "healthy",
      "message": "Database connection is working",
      "response_time_ms": 10.2
    },
    "storage": {
      "status": "healthy",
      "message": "Storage is writable",
      "path": "/var/www/manager/storage",
      "response_time_ms": 5.8
    }
  }
}
```

---

## Integración en PrestaShop

### Flujo Completo

```php
// 1. Usuario accede a página de documentos
case 'documents':

    // 2. Obtener UID del token
    $token = Tools::getValue('token');
    $uid = extractUid($token);

    // 3. Crear validador
    $validator = new DocumentValidator();

    // 4. Validar (verifica disponibilidad automáticamente)
    $validation = $validator->validateDocuments($uid, 'corta', [
        'customer_id' => $this->context->customer->id
    ]);

    // 5. Interpretar resultado
    switch ($validation['status']) {
        case 'success':
            // Servidor disponible, validación completada
            $canUpload = true;
            $message = 'Please upload your documents';
            break;

        case 'pending':
            // Servidor no disponible, petición en cola
            $canUpload = false;
            $message = 'Server temporarily unavailable. Your request has been queued.';
            break;

        case 'error':
            // Error en validación
            $canUpload = false;
            $message = $validation['message'];
            break;
    }

    // 6. Asignar a template
    $this->context->smarty->assign([
        'can_upload' => $canUpload,
        'message' => $message,
        'validation' => $validation
    ]);
```

### Verificar Estado de Petición Pendiente

```php
// Si guardaste el request_id
$requestId = $validation['request_id'] ?? null;

if ($requestId) {
    $validator = new DocumentValidator();
    $status = $validator->checkPendingRequestStatus($requestId);

    echo "Estado: " . $status['status'];
    echo "Reintentos: " . $status['retry_count'];
    echo "Próximo intento: " . $status['next_retry_at'];
}
```

### Ver Peticiones Pendientes para un Pedido

```php
$validator = new DocumentValidator();
$pendingRequests = $validator->getPendingRequestsForUid($uid);

foreach ($pendingRequests as $request) {
    echo "ID: " . $request['id_alsernetforms_request'];
    echo "Estado: " . $request['status'];
    echo "Creado: " . $request['created_at'];
    echo "Próximo intento: " . $request['next_retry_at'];
}
```

---

## Configuración Avanzada

### Ajustar Tiempos de Reintento

**Archivo:** `classes/PendingRequestsProcessor.php`

```php
private function calculateNextRetry($retryCount)
{
    // ACTUAL: 1min, 5min, 15min, 30min, 60min
    $delays = [60, 300, 900, 1800, 3600];

    // MÁS AGRESIVO (recuperación rápida):
    // $delays = [30, 60, 120, 300, 600];

    // MÁS CONSERVADOR (menos carga en servidor):
    // $delays = [300, 900, 1800, 3600, 7200];

    $delayIndex = min($retryCount - 1, count($delays) - 1);
    return date('Y-m-d H:i:s', time() + $delays[$delayIndex]);
}
```

### Ajustar Circuit Breaker

**Archivo:** `classes/EndpointAvailabilityChecker.php`

```php
// Más tolerante (espera más fallos):
private $failureThreshold = 5;  // En vez de 3

// Recuperación más rápida:
private $recoveryCheckInterval = 180;  // 3 minutos en vez de 5
```

### Ajustar Máximo de Reintentos

**Archivo:** `classes/loggers/DocumentsEndpointLogger.php`

```php
// Al crear petición, cambiar max_retries
$this->db->insert('alsernet_forms_requests', [
    'max_retries' => 5,  // En vez de 3 Por defecto
    // ... otros campos
]);
```

### Cambiar Tamaño de Lote del Procesador

**Archivo:** `classes/PendingRequestsProcessor.php`

```php
// Procesar más peticiones por ejecución
private $batchSize = 100;  // En vez de 50

// Aumentar timeout si procesas más
private $maxExecutionTime = 600;  // 10 minutos en vez de 5
```

---

## Monitorización

### Consultas SQL Útiles

**Peticiones pendientes:**
```sql
SELECT
    endpoint_type,
    status,
    COUNT(*) as total,
    MIN(created_at) as oldest,
    MAX(retry_count) as max_retries
FROM ps_alsernet_forms_requests
WHERE status IN ('pending', 'server_unavailable')
GROUP BY endpoint_type, status;
```

**Salud de endpoints:**
```sql
SELECT
    endpoint_type,
    is_available,
    consecutive_failures,
    last_check_at,
    AVG(response_time_ms) as avg_response_time
FROM ps_alsernet_endpoint_health
GROUP BY endpoint_type, is_available
ORDER BY consecutive_failures DESC;
```

**Tasa de éxito (últimas 24h):**
```sql
SELECT
    endpoint_type,
    status,
    COUNT(*) as count,
    (COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (PARTITION BY endpoint_type)) as percentage
FROM ps_alsernet_forms_requests
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY endpoint_type, status;
```

### Desde PHP

**Estadísticas generales:**
```php
include_once(_PS_MODULE_DIR_ . 'alsernetforms/classes/PendingRequestsProcessor.php');

$processor = new PendingRequestsProcessor();
$stats = $processor->getPendingStats();

foreach ($stats as $stat) {
    echo "{$stat['endpoint_type']} ({$stat['status']}): {$stat['count']} pending\n";
}
```

**Salud de endpoints:**
```php
include_once(_PS_MODULE_DIR_ . 'alsernetforms/classes/EndpointAvailabilityChecker.php');

$checker = new EndpointAvailabilityChecker();
$health = $checker->getHealthStats();

foreach ($health as $stat) {
    $availability = ($stat['available'] / $stat['total']) * 100;
    echo "{$stat['endpoint_type']}: {$availability}% available\n";
}
```

**Estadísticas de documentos:**
```php
include_once(_PS_MODULE_DIR_ . 'alsernetforms/classes/loggers/DocumentsEndpointLogger.php');

$logger = new DocumentsEndpointLogger();
$stats = $logger->getStats();

foreach ($stats as $stat) {
    echo "{$stat['status']}: {$stat['count']} requests (avg retries: {$stat['avg_retries']})\n";
}
```

### Herramientas de Monitoring Externas

**Uptime Robot:**
```
Monitor Type: HTTP(s)
URL: https://webadminpruebas.a-alvarez.com/api/health/ping
Interval: 5 minutes
Keyword: "ok"
```

**Prometheus:**
```yaml
scrape_configs:
  - job_name: 'laravel-health'
    metrics_path: '/api/health'
    static_configs:
      - targets: ['webadminpruebas.a-alvarez.com']
```

**Healthchecks.io:**
```bash
# Añadir al final del cron script
curl -fsS --retry 3 https://hc-ping.com/your-uuid-here
```

---

## Troubleshooting

### ❌ El cron no se ejecuta

**Síntoma:** Peticiones pendientes no se procesan.

**Diagnóstico:**
```bash
# 1. Verificar crontab
crontab -l | grep alsernetforms

# 2. Ver logs del cron
tail -f /var/log/prestashop-pending.log

# 3. Ejecutar manualmente
php integrations/prestashop/content/modules/alsernetforms/cron/process-pending-requests.php
```

**Soluciones:**
- Verificar permisos: `chmod +x cron/process-pending-requests.php`
- Verificar path de PHP: `which php`
- Verificar path absoluto al script en crontab
- Ver logs del sistema: `grep CRON /var/log/syslog`

### ❌ Peticiones no se guardan como pendientes

**Síntoma:** Cuando servidor cae, peticiones se pierden.

**Diagnóstico:**
```php
// Verificar que DocumentValidator se está usando
error_log("Using DocumentValidator: " . (class_exists('DocumentValidator') ? 'YES' : 'NO'));

// Ver resultado de validación
error_log("Validation status: " . $validation['status']);
```

**Soluciones:**
- Verificar que modificaste `alsernetforms.php` correctamente
- Verificar que las tablas existen: `SHOW TABLES LIKE '%alsernet%'`
- Verificar includes: `include_once(dirname(__FILE__).'/classes/DocumentValidator.php')`

### ❌ Servidor siempre marcado como "unhealthy"

**Síntoma:** Todas las peticiones se guardan como pendientes.

**Diagnóstico:**
```bash
# Verificar health endpoint manualmente
curl -v https://webadminpruebas.a-alvarez.com/api/health/documents

# Ver qué URL está usando el checker
grep 'healthEndpointUrl' integrations/prestashop/content/modules/alsernetforms/classes/EndpointAvailabilityChecker.php
```

**Soluciones:**
- Verificar URL en `EndpointAvailabilityChecker.php`
- Verificar conectividad: `ping webadminpruebas.a-alvarez.com`
- Verificar certificado SSL
- Ver tabla health: `SELECT * FROM ps_alsernet_endpoint_health`
- Forzar re-check: `$checker->forceCheck($url, 'documents')`

### ❌ Health endpoint retorna 404

**Síntoma:** `/api/health/documents` no existe.

**Diagnóstico:**
```bash
# Verificar rutas en Laravel
php artisan route:list | grep health

# Verificar que el controlador existe
ls -la app/Http/Controllers/Api/HealthCheckController.php
```

**Soluciones:**
- Verificar que añadiste las rutas en `routes/api.php`
- Limpiar cache de rutas: `php artisan route:clear`
- Verificar namespace del controlador

### ❌ Demasiadas peticiones pendientes acumuladas

**Síntoma:** Miles de peticiones sin procesar.

**Diagnóstico:**
```sql
SELECT COUNT(*) FROM ps_alsernet_forms_requests WHERE status = 'pending';
```

**Soluciones:**
```bash
# 1. Aumentar frecuencia del cron
*/2 * * * * ...  # Cada 2 minutos

# 2. Aumentar batch size
# En PendingRequestsProcessor.php:
private $batchSize = 100;

# 3. Ejecutar procesador manualmente varias veces
for i in {1..10}; do
    php cron/process-pending-requests.php
    sleep 10
done
```

### ❌ Peticiones se marcan como "failed" prematuramente

**Síntoma:** Peticiones llegan a max_retries muy rápido.

**Diagnóstico:**
```sql
SELECT
    id_alsernetforms_request,
    retry_count,
    max_retries,
    created_at,
    last_error
FROM ps_alsernet_forms_requests
WHERE status = 'failed'
ORDER BY created_at DESC
LIMIT 10;
```

**Soluciones:**
- Aumentar `max_retries` en DocumentsEndpointLogger
- Aumentar delays en backoff exponencial
- Aumentar `failureThreshold` en circuit breaker

---

## Referencias Adicionales

### Documentos Relacionados

1. **README_PENDING_REQUESTS.md** - Documentación principal del sistema
2. **HEALTH_CHECK_INTEGRATION.md** - Detalles de integración con health checks
3. **sql/install.sql** - Schemas de base de datos

### Patrones de Diseño

- **Circuit Breaker:** https://martinfowler.com/bliki/CircuitBreaker.html
- **Exponential Backoff:** https://en.wikipedia.org/wiki/Exponential_backoff
- **Health Check API:** https://tools.ietf.org/html/draft-inadarei-api-health-check

### Herramientas Útiles

- **PrestaShop Cron:** https://devdocs.prestashop.com/1.7/modules/concepts/cron/
- **Laravel Health Check:** https://github.com/antonioribeiro/health
- **Uptime Monitoring:** https://uptimerobot.com

---

## Resumen de Comandos

```bash
# === INSTALACIÓN ===

# 1. Crear tablas
mysql -u user -p db < sql/install.sql

# 2. Verificar health endpoints
curl https://webadminpruebas.a-alvarez.com/api/health/documents

# 3. Configurar cron
crontab -e
# Añadir: */5 * * * * /usr/bin/php /ruta/cron/process-pending-requests.php >> /var/log/prestashop-pending.log 2>&1


# === MONITORIZACIÓN ===

# Ver logs del cron
tail -f /var/log/prestashop-pending.log

# Ejecutar procesador manualmente
php cron/process-pending-requests.php

# Ver peticiones pendientes
mysql -u user -p -e "SELECT COUNT(*) FROM ps_alsernet_forms_requests WHERE status='pending'"


# === DEBUGGING ===

# Test health endpoint
curl -v https://webadminpruebas.a-alvarez.com/api/health/documents

# Ver rutas Laravel
php artisan route:list | grep health

# Limpiar cache Laravel
php artisan cache:clear && php artisan route:clear


# === MANTENIMIENTO ===

# Limpiar peticiones antiguas (manual)
mysql -u user -p db -e "DELETE FROM ps_alsernet_forms_requests WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY) AND status IN ('success', 'failed')"

# Ver estadísticas
mysql -u user -p db -e "SELECT endpoint_type, status, COUNT(*) FROM ps_alsernet_forms_requests GROUP BY endpoint_type, status"
```

---

**Versión:** 1.0.0
**Última actualización:** 2025-12-23
**Autor:** Sistema de Gestión de Peticiones Pendientes
**Soporte:** Ver documentos adicionales para más detalles
