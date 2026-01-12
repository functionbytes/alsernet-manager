# Integración con Health Check de Laravel

## 🎯 Nueva Arquitectura con Health Endpoints

El sistema ahora utiliza **endpoints dedicados de health check** en Laravel en lugar de verificar directamente los endpoints de negocio. Esto mejora la eficiencia y sigue las mejores prácticas de arquitectura de microservicios.

## 🔄 Flujo Actualizado

```
┌─────────────────────────────────────────────────────────────────┐
│                  PrestaShop (Cliente)                           │
│                                                                 │
│  1. Usuario solicita validar documentos                        │
│     │                                                           │
│     v                                                           │
│  2. DocumentValidator.validateDocuments($uid)                  │
│     │                                                           │
│     v                                                           │
│  3. EndpointAvailabilityChecker.isEndpointAvailable()          │
│     │                                                           │
│     │  ┌──────────────────────────────────────┐                │
│     └──> GET /api/health/documents             │                │
│        │  (health check, NO validación)       │                │
│        └──────────────────────────────────────┘                │
└─────────────────────────┬───────────────────────────────────────┘
                          │
                          v
┌─────────────────────────────────────────────────────────────────┐
│                Laravel (Servidor)                               │
│                                                                 │
│  HealthCheckController::documentsHealth()                      │
│  ├─ Verifica: Base de datos                                    │
│  ├─ Verifica: Sistema de archivos (storage)                    │
│  └─ Responde: {"status": "healthy", "checks": {...}}           │
│                                                                 │
│  ┌─────────────────────────────────────────┐                   │
│  │ SI status = "healthy":                  │                   │
│  │   PrestaShop procesa validación real    │                   │
│  │   POST /api/documents (con datos)       │                   │
│  │                                          │                   │
│  │ SI status = "unhealthy":                │                   │
│  │   PrestaShop guarda como "pending"      │                   │
│  │   Cron procesará más tarde             │                   │
│  └─────────────────────────────────────────┘                   │
└─────────────────────────────────────────────────────────────────┘
```

## 📡 Endpoints de Health Check Disponibles

### 1. Health Check General
```http
GET /api/health
```

**Respuesta exitosa (200):**
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
      "debug_mode": false,
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

**Respuesta con fallos (503):**
```json
{
  "status": "unhealthy",
  "timestamp": "2025-12-23T10:30:00+00:00",
  "service": "Manager",
  "response_time_ms": 89.45,
  "checks": {
    "application": {
      "status": "healthy",
      "message": "Application is running"
    },
    "database": {
      "status": "unhealthy",
      "message": "Database connection failed",
      "connection": "pgsql",
      "error": "SQLSTATE[HY000] [2002] Connection refused"
    },
    "cache": {
      "status": "healthy",
      "message": "Cache is working"
    }
  }
}
```

### 2. Health Check para Documentos
```http
GET /api/health/documents
```

Verifica específicamente los servicios necesarios para procesar documentos:
- Base de datos (lectura/escritura)
- Sistema de archivos (storage)

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

### 3. Ping Simple
```http
GET /api/health/ping
```

Verifica solo que la aplicación responde (muy rápido, <10ms).

**Respuesta:**
```json
{
  "status": "ok",
  "timestamp": "2025-12-23T10:30:00+00:00",
  "service": "Manager"
}
```

### 4. Health Check Detallado (Solo Debug)
```http
GET /api/health/detailed
```

⚠️ **Solo disponible cuando `APP_DEBUG=true`**

Incluye información adicional:
- Versión PHP y Laravel
- Uso de memoria
- Espacio en disco
- Todos los checks del health general

## 🔧 Configuración en PrestaShop

El `EndpointAvailabilityChecker` está configurado para usar estos endpoints automáticamente:

```php
// En EndpointAvailabilityChecker.php
private $healthEndpointUrl = 'https://webadminpruebas.a-alvarez.com/api/health/';

// Mapeo automático por tipo:
// 'documents' → /api/health/documents
// 'subscription', 'form', 'default' → /api/health
```

## ✅ Ventajas de Esta Arquitectura

### 1. **Eficiencia**
- Health check responde en <50ms (vs 200-500ms de endpoint real)
- No ejecuta lógica de negocio innecesaria
- Menos carga en base de datos

### 2. **Granularidad**
- Detecta exactamente qué servicio falló (DB, cache, storage)
- Mensajes de error descriptivos
- Facilita debugging

### 3. **Estándar de Industria**
```
GET /health → Estándar RFC 7231 (Health Check Response Format)
```

### 4. **Reutilizable**
- Herramientas de monitoring (Prometheus, Datadog, etc.) pueden usarlo
- Load balancers pueden usar para health checks
- Útil para debugging manual

### 5. **Seguridad**
- No expone datos sensibles de negocio
- Sin autenticación requerida (solo verifica disponibilidad)
- Sin rate limiting (para no bloquear checks)

## 🧪 Pruebas

### Desde terminal (curl)
```bash
# Health check general
curl https://webadminpruebas.a-alvarez.com/api/health

# Health check de documentos
curl https://webadminpruebas.a-alvarez.com/api/health/documents

# Ping simple
curl https://webadminpruebas.a-alvarez.com/api/health/ping

# Con headers verbose
curl -i https://webadminpruebas.a-alvarez.com/api/health
```

### Desde PHP (PrestaShop)
```php
include_once(_PS_MODULE_DIR_ . 'alsernetforms/classes/EndpointAvailabilityChecker.php');

$checker = new EndpointAvailabilityChecker();

// Verificar disponibilidad para documentos
$result = $checker->isEndpointAvailable(
    'https://webadminpruebas.a-alvarez.com/api/orders/validate-documents',
    'documents'
);

print_r($result);
/*
Array (
    [available] => true
    [reason] => null
    [response_time_ms] => 42.15
    [health_data] => Array (
        [status] => healthy
        [service] => documents-validation
        [checks] => Array (
            [database] => Array (
                [status] => healthy
                [message] => Database connection is working
            )
            [storage] => Array (
                [status] => healthy
                [message] => Storage is writable
            )
        )
    )
)
*/
```

### Desde navegador
```
https://webadminpruebas.a-alvarez.com/api/health
https://webadminpruebas.a-alvarez.com/api/health/documents
https://webadminpruebas.a-alvarez.com/api/health/ping
```

## 🔍 Debugging

### Ver qué health endpoint se está usando
```php
// Añadir temporalmente en EndpointAvailabilityChecker.php
echo "Health URL: " . $this->getHealthEndpointForType('documents');
// Output: https://webadminpruebas.a-alvarez.com/api/health/documents
```

### Forzar una verificación manual
```php
$checker = new EndpointAvailabilityChecker();
$result = $checker->forceCheck(
    'https://webadminpruebas.a-alvarez.com/api/orders/validate-documents',
    'documents'
);
print_r($result);
```

### Ver logs de health checks en Laravel
```bash
# Si añades logging en HealthCheckController
tail -f storage/logs/laravel.log | grep "health"
```

## 🎯 Mejores Prácticas

### 1. Usar health check específico cuando sea posible
```php
// ✅ BIEN: Usa health check específico
$result = $checker->isEndpointAvailable($url, 'documents');

// ⚠️ OK pero menos específico: Usa health check general
$result = $checker->isEndpointAvailable($url, 'default');
```

### 2. No abusar de health checks
```php
// ❌ MAL: Verificar en cada petición
foreach ($orders as $order) {
    $checker->isEndpointAvailable(...); // Circuit breaker cachea esto
    processOrder($order);
}

// ✅ BIEN: Verificar una vez, procesar lote
if ($checker->isEndpointAvailable(...)) {
    foreach ($orders as $order) {
        processOrder($order);
    }
}
```

### 3. Monitorear health checks
```php
// Ver estadísticas de salud
$stats = $checker->getHealthStats();
foreach ($stats as $stat) {
    echo "{$stat['endpoint_type']}: ";
    echo "{$stat['available']}/{$stat['total']} available\n";
}
```

## 📊 Monitoreo con Herramientas Externas

### Prometheus
```yaml
# prometheus.yml
scrape_configs:
  - job_name: 'laravel-health'
    metrics_path: '/api/health'
    static_configs:
      - targets: ['webadminpruebas.a-alvarez.com']
```

### Uptime Robot
```
Monitor Type: HTTP(s)
URL: https://webadminpruebas.a-alvarez.com/api/health/ping
Interval: 5 minutes
```

### Datadog
```javascript
// datadog-agent check
{
  "init_config": {},
  "instances": [{
    "name": "laravel_health",
    "url": "https://webadminpruebas.a-alvarez.com/api/health",
    "timeout": 5
  }]
}
```

## 🚀 Próximos Pasos

1. ✅ **Implementado**: Endpoints de health check en Laravel
2. ✅ **Implementado**: Integración con EndpointAvailabilityChecker
3. ⏳ **Pendiente**: Añadir más checks específicos según necesidades
4. ⏳ **Pendiente**: Integrar con herramientas de monitoring
5. ⏳ **Pendiente**: Añadir métricas de performance a health checks

## 📝 Notas Importantes

- Los health endpoints **NO requieren autenticación** (son públicos)
- Los health endpoints **NO tienen rate limiting** (para no bloquear monitoring)
- Responden en **<100ms** en condiciones normales
- Si un check falla, el status general es "unhealthy" (503)
- El circuit breaker cachea resultados por tipo, no por URL específica
