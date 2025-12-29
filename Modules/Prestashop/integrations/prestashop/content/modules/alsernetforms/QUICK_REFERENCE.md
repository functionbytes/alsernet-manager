# Guía de Referencia Rápida

## 🚀 Inicio Rápido (5 minutos)

```bash
# 1. Verificar health endpoints Laravel
curl https://webadminpruebas.a-alvarez.com/api/health/documents

# 2. Reinstalar módulo PrestaShop
Panel Admin → Módulos → "Alsernet - Formularios" → Reinstalar

# 3. Configurar cron
crontab -e
*/5 * * * * /usr/bin/php /ruta/alsernetforms/cron/process-pending-requests.php >> /var/log/prestashop-pending.log 2>&1

# 4. Modificar alsernetforms.php (ver DOCUMENTATION.md)

# 5. Probar
curl https://webadminpruebas.a-alvarez.com/api/health/ping
```

---

## 📡 Endpoints Health Check

| URL | Uso | Tiempo |
|-----|-----|--------|
| `/api/health/ping` | Ping rápido | <10ms |
| `/api/health` | Check completo | <100ms |
| `/api/health/documents` | Check documentos | <50ms |

**Respuesta healthy:**
```json
{"status": "healthy", "checks": {...}}
```

**Respuesta unhealthy (503):**
```json
{"status": "unhealthy", "checks": {...}}
```

---

## 💻 Código PHP Esencial

### Validar Documentos con Disponibilidad

```php
include_once(dirname(__FILE__).'/classes/DocumentValidator.php');

$validator = new DocumentValidator();
$result = $validator->validateDocuments($uid, 'corta', [
    'customer_id' => 123
]);

// $result['status']: 'success', 'pending', 'error'
```

### Verificar Disponibilidad Manualmente

```php
include_once(dirname(__FILE__).'/classes/EndpointAvailabilityChecker.php');

$checker = new EndpointAvailabilityChecker();
$available = $checker->isEndpointAvailable($url, 'documents');

if ($available['available']) {
    // Servidor disponible
} else {
    // Servidor no disponible: $available['reason']
}
```

### Procesar Peticiones Pendientes (Cron)

```php
include_once(dirname(__FILE__).'/classes/PendingRequestsProcessor.php');

$processor = new PendingRequestsProcessor();
$stats = $processor->process();

echo "Procesadas: {$stats['successful']}\n";
echo "Fallidas: {$stats['failed']}\n";
echo "Saltadas: {$stats['skipped']}\n";
```

---

## 🗄️ SQL Útiles

### Ver Peticiones Pendientes

```sql
SELECT * FROM ps_alsernet_forms_requests
WHERE status IN ('pending', 'server_unavailable')
ORDER BY created_at DESC
LIMIT 20;
```

### Estadísticas por Estado

```sql
SELECT status, COUNT(*) as total
FROM ps_alsernet_forms_requests
GROUP BY status;
```

### Salud de Endpoints

```sql
SELECT
    endpoint_type,
    is_available,
    consecutive_failures,
    last_check_at
FROM ps_alsernet_endpoint_health
ORDER BY consecutive_failures DESC;
```

### Limpiar Peticiones Antiguas

```sql
DELETE FROM ps_alsernet_forms_requests
WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
AND status IN ('success', 'failed');
```

---

## 🔧 Troubleshooting Rápido

### Cron no ejecuta
```bash
# Ver crontab
crontab -l

# Ver logs
tail -f /var/log/prestashop-pending.log

# Ejecutar manual
php cron/process-pending-requests.php
```

### Health endpoint 404
```bash
# Verificar rutas Laravel
php artisan route:list | grep health

# Limpiar cache
php artisan route:clear
```

### Servidor siempre "unhealthy"
```bash
# Test manual
curl -v https://webadminpruebas.a-alvarez.com/api/health/documents

# Ver tabla health
SELECT * FROM ps_alsernet_endpoint_health;
```

### Peticiones no se guardan
```sql
-- Verificar tablas existen
SHOW TABLES LIKE '%alsernet%';

-- Ver si hay registros
SELECT COUNT(*) FROM ps_alsernet_forms_requests;
```

---

## ⚙️ Configuración Común

### Cambiar Frecuencia Cron

```bash
# Cada 2 minutos (más frecuente)
*/2 * * * * php cron/process-pending-requests.php

# Cada 10 minutos (menos frecuente)
*/10 * * * * php cron/process-pending-requests.php
```

### Ajustar Reintentos

```php
// PendingRequestsProcessor.php
private function calculateNextRetry($retryCount) {
    // Más rápido: [30, 60, 120, 300, 600]
    // Normal:    [60, 300, 900, 1800, 3600]
    // Más lento: [300, 900, 1800, 3600, 7200]
    $delays = [60, 300, 900, 1800, 3600];
    ...
}
```

### Ajustar Circuit Breaker

```php
// EndpointAvailabilityChecker.php
private $failureThreshold = 3;  // Fallos antes de marcar como down
private $recoveryCheckInterval = 300;  // Segundos entre reintentos
```

---

## 📊 Monitorización

### Dashboard Básico (SQL)

```sql
-- Resumen general
SELECT
    'Pendientes' as tipo,
    COUNT(*) as total
FROM ps_alsernet_forms_requests
WHERE status IN ('pending', 'server_unavailable')

UNION ALL

SELECT
    'Exitosas hoy',
    COUNT(*)
FROM ps_alsernet_forms_requests
WHERE status = 'success'
AND synced_at >= CURDATE()

UNION ALL

SELECT
    'Fallidas hoy',
    COUNT(*)
FROM ps_alsernet_forms_requests
WHERE status = 'failed'
AND created_at >= CURDATE();
```

### Estadísticas PHP

```php
// Ver pendientes
$processor = new PendingRequestsProcessor();
$stats = $processor->getPendingStats();
print_r($stats);

// Ver salud endpoints
$checker = new EndpointAvailabilityChecker();
$health = $checker->getHealthStats();
print_r($health);
```

---

## 🎯 Flujo de Estados

```
pending → processing → success
   ↓           ↓
   ↓      failed (retry < max)
   ↓           ↓
   └─> server_unavailable
            ↓
       (cron reintenta)
            ↓
       pending again
```

**Estados:**
- `pending`: Esperando procesamiento
- `processing`: Procesándose ahora
- `success`: Procesada exitosamente
- `failed`: Falló (alcanzó max_retries)
- `server_unavailable`: Servidor no disponible temporalmente

---

## 📁 Archivos Clave

```
alsernetforms/
├── classes/
│   ├── DocumentValidator.php           ← Wrapper principal
│   ├── EndpointAvailabilityChecker.php ← Verifica disponibilidad
│   ├── PendingRequestsProcessor.php    ← Procesa pendientes
│   ├── ApiManager.php                  ← Gestiona HTTP
│   └── loggers/
│       └── DocumentsEndpointLogger.php ← Logging documentos
│
├── cron/
│   └── process-pending-requests.php    ← Script cron
│
├── sql/
│   └── install.sql                     ← Schemas BD
│
├── DOCUMENTATION.md                    ← Doc completa
├── HEALTH_CHECK_INTEGRATION.md         ← Health checks
└── QUICK_REFERENCE.md                  ← Esta guía
```

---

## 🔑 Conceptos Clave

### Circuit Breaker
- Tras **3 fallos** consecutivos → marca endpoint como "no disponible"
- Espera **5 minutos** antes de reintentar
- Evita bombardear servidor caído

### Exponential Backoff
- Reintento 1: **1 minuto**
- Reintento 2: **5 minutos**
- Reintento 3: **15 minutos**
- Reintento 4: **30 minutos**
- Reintento 5+: **60 minutos**

### Health Check
- **No ejecuta lógica de negocio**
- Solo verifica servicios críticos (DB, storage)
- Responde en <100ms
- Estándar HTTP para monitoring

---

## ✅ Checklist de Implementación

- [ ] Health endpoints responden en Laravel
- [ ] Tablas creadas en PrestaShop BD
- [ ] Cron configurado y ejecutándose
- [ ] `alsernetforms.php` modificado
- [ ] Función `getDocumentTypeFromUid()` implementada
- [ ] Probado con servidor simulado caído
- [ ] Logs del cron funcionando
- [ ] Monitorización configurada

---

## 🆘 Soporte

**Documentación completa:** Ver `DOCUMENTATION.md`

**Health checks detallados:** Ver `HEALTH_CHECK_INTEGRATION.md`

**Sistema base:** Ver `README_PENDING_REQUESTS.md`

**Comandos útiles:**
```bash
# Ver documentación
cat DOCUMENTATION.md | less

# Buscar en docs
grep -r "palabra clave" *.md
```
