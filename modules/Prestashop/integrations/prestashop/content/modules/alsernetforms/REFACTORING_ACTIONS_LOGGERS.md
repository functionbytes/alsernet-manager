# Refactorización Arquitectónica: Actions + Loggers Unificados

**Fecha:** 2026-01-07
**Versión:** 2.0
**Estado:** ✅ Completado

---

## 🎯 Resumen Ejecutivo

Esta refactorización unifica la gestión de logging y actions, eliminando duplicación de código y mejorando significativamente la arquitectura del módulo `alsernetforms`.

### Problemas Resueltos

1. ❌ **Duplicación Actions ↔ Loggers**: Ambos tenían implementaciones específicas por tipo
2. ❌ **ApiManager sobrecargado**: Mezclaba HTTP + Logging + Logger Selection
3. ❌ **DocumentValidator duplicaba lógica**: No usaba DocumentAction
4. ❌ **Hard-coded logger selection**: `getLoggerForType()` violaba Open/Closed Principle

### Solución Implementada

✅ **Actions gestionan su propio logging** vía Factory Method Pattern
✅ **ApiManager solo HTTP** (`sendRequestWithoutLogging()`)
✅ **Eliminada duplicación** entre DocumentValidator y DocumentAction
✅ **Agregar nuevos tipos** solo requiere crear nueva Action

---

## 📊 Comparación Antes/Después

| Aspecto | Antes | Después |
|---------|-------|---------|
| **Responsabilidades ApiManager** | HTTP + Logging + Logger Selection | Solo HTTP |
| **Agregar nuevo tipo** | Action + Logger + Modificar `getLoggerForType()` | Solo Action |
| **Duplicación código** | DocumentValidator vs DocumentAction | Eliminada |
| **Acoplamiento** | Alto (ApiManager conoce todos los loggers) | Bajo (cada Action su logger) |
| **Extensibilidad** | Cerrada (modificar ApiManager) | Abierta (heredar BaseAction) |

---

## 🏗️ Nueva Arquitectura

### Flujo Completo

```
alsernetforms.php (case 'documents')
    ↓
DocumentAction.validateToken()
    ↓
BaseAction.execute()  ← Template Method Pattern
    ├─ 1. logger.logRequest() → INSERT tracking
    ├─ 2. availabilityChecker.isEndpointAvailable()
    │   └─ Si NO disponible:
    │       ├─ logger.markAsServerUnavailable()
    │       └─ RETURN status='pending'
    ├─ 3. apiManager.sendRequestWithoutLogging() → SOLO HTTP
    │   └─ httpClient.request()
    ├─ 4. logger.updateRequestLog() → UPDATE tracking
    └─ 5. mapResponse() → Normalización
```

### Componentes Principales

#### 1. **BaseAction** (Abstract)

**Responsabilidades:**
- ✅ Gestiona logging completo (inicio + fin)
- ✅ Verifica disponibilidad (Circuit Breaker)
- ✅ Coordina HTTP request
- ✅ Define flujo Template Method

**Métodos abstractos que subclases implementan:**
```php
abstract protected function createLogger();  // Factory Method
abstract protected function mapResponse(array $response);
```

**Ejemplo de implementación:**
```php
class DocumentAction extends BaseAction
{
    protected function createLogger()
    {
        return new DocumentsEndpointLogger;  // Específico
    }

    protected function mapResponse(array $response)
    {
        // Transformar respuesta HTTP a estructura normalizada
        return [
            'status' => $response['status'],
            'data' => [...],
            'error' => ...,
        ];
    }
}
```

#### 2. **ApiManager** (Simplificado)

**Responsabilidades:**
- ✅ Solo HTTP requests
- ❌ NO logging (eliminado)
- ❌ NO logger selection (eliminado)

**Nuevo método principal:**
```php
public function sendRequestWithoutLogging($method, $endpoint, $data, $headers)
{
    $httpResponse = $this->httpClient->request($method, $url, $data, $headers);

    return [
        'status' => $httpCode,
        'message' => ...,
        'response' => $responseData,
        'error' => null,
    ];
}
```

**Métodos deprecated:**
```php
/**
 * @deprecated Use Actions with sendRequestWithoutLogging() instead
 */
public function sendRequest(...) { ... }

/**
 * @deprecated Use Actions with createLogger() Factory Method instead
 */
private function getLoggerForType($type) { ... }
```

#### 3. **DocumentValidator** (Refactorizado)

**Antes:**
```php
public function validateDocuments($uid, $documentType, $context)
{
    // ❌ Llamaba directamente a ApiManager (duplicaba lógica)
    $response = $this->apiManager->sendRequest('POST', $endpoint, $payload, ...);
    return $this->parseValidationResponse($response, ...);
}
```

**Después:**
```php
public function validateDocuments($uid, $documentType, $context)
{
    // ✅ Delega a DocumentAction (elimina duplicación)
    $actionResponse = $this->documentAction->validateToken($uid, $context);
    return $this->parseValidationResponse($actionResponse, ...);
}
```

---

## 🔄 Patrones de Diseño Implementados

### 1. **Template Method Pattern**

**Clase:** `BaseAction.execute()`

**Propósito:** Define el esqueleto del algoritmo, delegando pasos específicos a subclases.

```php
final protected function execute(array $payload, array $context = [])
{
    // 1. Logging start (común a todos)
    $requestId = $this->logger->logRequest('POST', $url, $payload);

    // 2. Availability check (común a todos)
    if (!$this->checkAvailability($url)) {
        $this->logger->markAsServerUnavailable(...);
        return ['status' => 'pending', ...];
    }

    // 3. HTTP request (común a todos)
    $httpResponse = $this->apiManager->sendRequestWithoutLogging(...);

    // 4. Logging end (común a todos)
    $this->logger->updateRequestLog($requestId, ...);

    // 5. Response mapping (específico de cada Action)
    return $this->mapResponse($httpResponse);  // ← Polimorfismo
}
```

### 2. **Factory Method Pattern**

**Método:** `createLogger()`

**Propósito:** Cada Action decide qué logger crear sin que BaseAction lo sepa.

```php
// BaseAction
abstract protected function createLogger();

// DocumentAction
protected function createLogger()
{
    return new DocumentsEndpointLogger;  // Específico de documentos
}

// FormAction
protected function createLogger()
{
    return new DefaultEndpointLogger('form');  // Tipo como parámetro (Fase 2)
}

// Otro ejemplo (hipotético)
protected function createLogger()
{
    return new DefaultEndpointLogger('subscription');  // Tipo como parámetro (Fase 2)
}
```

### 3. **Single Responsibility Principle (SRP)**

**Antes:**
- ApiManager: HTTP + Logging + Logger Selection (3 responsabilidades ❌)

**Después:**
- ApiManager: Solo HTTP ✅
- BaseAction: Coordinación de flujo ✅
- Actions específicas: Lógica de negocio + Logger selection ✅
- Loggers: Solo tracking en BD ✅

### 4. **Open/Closed Principle (OCP)**

**Antes:**
```php
// Agregar nuevo tipo requiere modificar getLoggerForType() ❌
private function getLoggerForType($type)
{
    switch ($type) {
        case 'documents': return new DocumentsEndpointLogger;
        case 'form': return new FormEndpointLogger;
        case 'NUEVO_TIPO': return new NuevoEndpointLogger;  // ← Modificar clase
    }
}
```

**Después:**
```php
// Solo crear nueva Action (no modificar código existente) ✅
class NuevoAction extends BaseAction
{
    protected function createLogger()
    {
        return new NuevoEndpointLogger;  // ← Solo heredar
    }

    protected function mapResponse(array $response) { ... }
}
```

---

## 📝 Guía de Migración

### Para desarrolladores: Cómo crear una nueva Action

**Paso 1:** Crear logger específico (opcional, puede usar `DefaultEndpointLogger`)

```php
// loggers/MiNuevoEndpointLogger.php
class MiNuevoEndpointLogger extends DefaultEndpointLogger
{
    protected function getType()
    {
        return 'mi_tipo';
    }
}
```

**Paso 2:** Crear Action heredando de BaseAction

```php
// Actions/MiNuevoAction.php
include_once dirname(__FILE__).'/BaseAction.php';
include_once dirname(__FILE__).'/../loggers/MiNuevoEndpointLogger.php';

class MiNuevoAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();
        $this->endpoint = 'api/mi-endpoint';
        $this->actionType = 'mi_tipo';
    }

    // Factory Method: decide qué logger usar
    protected function createLogger()
    {
        return new MiNuevoEndpointLogger;
    }

    // Método público de negocio
    public function procesarAlgo(array $datos, array $context = [])
    {
        $payload = ['action' => 'procesar', 'data' => $datos];
        return $this->execute($payload, $context);
    }

    // Mapeo de respuesta específico
    protected function mapResponse(array $response)
    {
        return [
            'status' => $response['status'],
            'data' => [
                'resultado' => $response['response']['resultado'] ?? null,
                // ... campos específicos
            ],
            'error' => $response['message'] ?? null,
        ];
    }
}
```

**Paso 3:** Usar la nueva Action

```php
// En alsernetforms.php o donde sea necesario
include_once dirname(__FILE__).'/classes/Actions/MiNuevoAction.php';

$action = new MiNuevoAction;
$result = $action->procesarAlgo(['campo' => 'valor'], [
    'customer_id' => $customerId,
    'ip_address' => $_SERVER['REMOTE_ADDR'],
]);

if ($result['status'] === 'success') {
    // Procesar resultado exitoso
} elseif ($result['status'] === 'pending') {
    // Servidor no disponible, guardado para retry
} else {
    // Error
}
```

**¡Y listo!** No necesitas modificar:
- ❌ ApiManager
- ❌ BaseAction
- ❌ Ninguna otra Action existente

---

## 🧪 Testing

### Cómo testear Actions

```php
// tests/DocumentActionTest.php
class DocumentActionTest extends PHPUnit\Framework\TestCase
{
    public function testValidateTokenSuccess()
    {
        $action = new DocumentAction;

        $result = $action->validateToken('test-token-123', [
            'customer_id' => 1,
        ]);

        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('request_id', $result);
    }

    public function testValidateTokenPending()
    {
        // Simular servidor no disponible
        // (requiere mock de EndpointAvailabilityChecker)
        $this->markTestIncomplete('Implementar mock de availability checker');
    }
}
```

---

## 🚀 Beneficios Logrados

### 1. **Mantenibilidad**
- ✅ Código más limpio y organizado
- ✅ Cada clase tiene una sola responsabilidad
- ✅ Fácil entender el flujo completo

### 2. **Extensibilidad**
- ✅ Agregar nuevo tipo: solo crear nueva Action (1 archivo)
- ✅ No modificar código existente (Open/Closed)
- ✅ Reúso completo de BaseAction

### 3. **Testabilidad**
- ✅ Actions fáciles de testear (mock logger, mock availability)
- ✅ ApiManager solo HTTP (fácil mock httpClient)
- ✅ Separación de concerns facilita unit tests

### 4. **Eliminación de Duplicación**
- ✅ DocumentValidator → DocumentAction (no duplica validación)
- ✅ Loggers reutilizados vía Factory Method
- ✅ Circuit Breaker centralizado en BaseAction

### 5. **Reducción de Acoplamiento**
- ✅ ApiManager no conoce loggers específicos
- ✅ Actions autocontenidas (logger + mapeo)
- ✅ Fácil cambiar implementaciones sin afectar otros componentes

---

## 📚 Archivos Modificados

### Nuevos Archivos
- ✅ `classes/Actions/BaseAction.php` - Refactorizado con logging interno
- ✅ `classes/ApiManager.php` - Agregado `sendRequestWithoutLogging()`

### Archivos Actualizados
- ✅ `classes/Actions/DocumentAction.php` - Implementado `createLogger()`
- ✅ `classes/Actions/FormAction.php` - Implementado `createLogger()`
- ✅ `classes/Actions/ChatAction.php` - Implementado `createLogger()`
- ✅ `classes/DocumentValidator.php` - Refactorizado para usar DocumentAction
- ✅ `classes/ApiManager.php` - Deprecated `sendRequest()` y `getLoggerForType()`

### Archivos Sin Cambios
- ✅ `classes/loggers/*` - Todos los loggers mantienen su interfaz
- ✅ `classes/HttpClient.php` - Sin cambios
- ✅ `classes/EndpointAvailabilityChecker.php` - Sin cambios

---

## ⚠️ Breaking Changes

### Para código legacy que usa `ApiManager.sendRequest()` directamente

**Antes:**
```php
$apiManager = new ApiManager;
$response = $apiManager->sendRequest('POST', 'api/endpoint', $data, 'documents', [], true);
```

**Ahora (Deprecated pero aún funciona):**
El método sigue funcionando pero está marcado como `@deprecated`.

**Migración recomendada:**
```php
$action = new DocumentAction;  // O la Action apropiada
$result = $action->validateToken($token, $context);
```

---

## 🔄 Fase 2: Simplificación de Loggers (2026-01-07)

### Problema Identificado

Después de la refactorización inicial, se detectó **redundancia en los loggers**:

```php
// FormEndpointLogger.php - 10 líneas solo para cambiar tipo
class FormEndpointLogger extends DefaultEndpointLogger
{
    protected function getType() { return 'form'; }  // ← Solo esto
}

// SubscriptionEndpointLogger.php - 12 líneas solo para cambiar tipo
class SubscriptionEndpointLogger extends DefaultEndpointLogger
{
    protected function getType() { return 'subscription'; }  // ← Solo esto
}
```

**Análisis:**
- ❌ Crear archivo completo solo para cambiar una cadena
- ❌ Dos archivos (22 líneas) sin lógica de negocio
- ❌ Agregar nuevo tipo requiere crear archivo

### Solución: Tipo como Parámetro

**DefaultEndpointLogger refactorizado:**

```php
class DefaultEndpointLogger implements EndpointLoggerInterface
{
    protected $db;
    protected $type;  // ✅ NUEVO

    public function __construct($type = 'default')  // ✅ NUEVO
    {
        $this->db = \Db::getInstance();
        $this->type = $type;  // ✅ NUEVO
    }

    protected function getType()
    {
        return $this->type;  // ✅ Retorna tipo inyectado
    }
}
```

**Actions simplificadas:**

```php
// FormAction.php
protected function createLogger()
{
    return new DefaultEndpointLogger('form');  // ✅ Simple
}

// DocumentAction.php - MANTIENE su logger específico
protected function createLogger()
{
    return new DocumentsEndpointLogger;  // ✅ Lógica específica (circuit breaker, reintentos)
}
```

### Resultado

| Aspecto | Antes (Fase 1) | Después (Fase 2) |
|---------|----------------|------------------|
| **Archivos loggers** | 4 archivos | 2 archivos |
| **Líneas de código** | 203 líneas | 181 líneas |
| **Agregar nuevo tipo** | Crear clase nueva | Pasar parámetro |
| **Loggers específicos** | FormEndpointLogger, SubscriptionEndpointLogger | ❌ Eliminados |
| **Loggers complejos** | DocumentsEndpointLogger | ✅ Mantenido |

### Archivos Eliminados

- ❌ `classes/loggers/FormEndpointLogger.php` - Redundante
- ❌ `classes/loggers/SubscriptionEndpointLogger.php` - Redundante

### Archivos Mantenidos

- ✅ `classes/loggers/DefaultEndpointLogger.php` - Ahora acepta tipo como parámetro
- ✅ `classes/loggers/DocumentsEndpointLogger.php` - Tiene lógica específica valiosa:
  - `markAsServerUnavailable()` - Circuit breaker
  - `incrementRetryCount()` - Sistema de reintentos
  - `getPendingRequests()` - Cola de peticiones
  - `getStats()` - Estadísticas de documentos

### Cuándo Crear Logger Específico

**✅ Crear subclase cuando:**
- Tienes métodos adicionales específicos del dominio
- Necesitas circuit breaker o reintentos personalizados
- Requieres estadísticas o queries específicas
- Ejemplo: `DocumentsEndpointLogger` (140 líneas de lógica)

**❌ NO crear subclase cuando:**
- Solo cambias el tipo de endpoint
- No tienes lógica adicional
- Solo registras peticiones estándar
- Solución: `new DefaultEndpointLogger('tipo')`

---

## 🔮 Próximos Pasos

### Opcional - Mejoras Futuras

1. **Eliminar `sendRequest()` deprecated** una vez migrado todo el código legacy
2. **Agregar tests unitarios** para todas las Actions
3. **Documentar** endpoint `/api/health/{type}` en Laravel
4. **Implementar retry automático** desde cron job para peticiones pending

---

## 📞 Contacto

Para dudas sobre esta refactorización:
- Revisar este documento
- Consultar código en `classes/Actions/`
- Ver ejemplos en `DocumentAction`, `FormAction`, `ChatAction`

---

**Última actualización:** 2026-01-07
**Autor:** Claude Code AI
**Versión:** 2.1 (Fase 2: Simplificación de Loggers)
