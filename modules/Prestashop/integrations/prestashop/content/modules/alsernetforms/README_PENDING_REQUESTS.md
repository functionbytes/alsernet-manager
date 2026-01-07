# Sistema de Gestión de Peticiones Pendientes

## 📋 Descripción General

Este sistema permite gestionar peticiones a endpoints externos que pueden quedar pendientes cuando el servidor no está disponible. Implementa un **Circuit Breaker pattern** para proteger contra cascadas de fallos y un sistema de **reintentos con backoff exponencial** para procesar automáticamente las peticiones pendientes.

## 🏗️ Arquitectura del Sistema

### Componentes Principales

```
┌─────────────────────────────────────────────────────────────────┐
│                     PrestaShop Module                           │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐     │
│  │ alsernetforms│───>│DocumentValida│───>│  ApiManager  │     │
│  │     .php     │    │    tor.php   │    │   .php       │     │
│  └──────────────┘    └──────────────┘    └──────┬───────┘     │
│                                                   │             │
│                      ┌────────────────────────────┘             │
│                      │                                          │
│       ┌──────────────▼─────────┐    ┌──────────────────────┐  │
│       │EndpointAvailability    │    │ DocumentsEndpoint    │  │
│       │  Checker.php           │    │   Logger.php         │  │
│       └────────────────────────┘    └──────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │
                    ┌─────────▼──────────┐
                    │  External API      │
                    │  (webadminpruebas) │
                    └────────────────────┘
                              │
                              │ (si falla o no disponible)
                              │
        ┌─────────────────────▼────────────────────┐
        │   Cron Job (cada 5-15 minutos)          │
        │  ┌──────────────────────────────┐       │
        │  │PendingRequestsProcessor.php  │       │
        │  └──────────────────────────────┘       │
        │         Procesa peticiones               │
        │         pendientes automáticamente       │
        └──────────────────────────────────────────┘
```

### Flujo de Datos

1. **Usuario solicita validación de documentos** → `alsernetforms.php` case 'documents'
2. **DocumentValidator verifica disponibilidad** → `EndpointAvailabilityChecker`
3. **Si servidor disponible** → Procesa inmediatamente
4. **Si servidor NO disponible** → Registra como "pending" en BD
5. **Cron ejecuta cada X minutos** → `PendingRequestsProcessor`
6. **Procesador verifica servidor** → Si disponible, procesa pendientes
7. **Actualiza estado** → "success", "failed", o reprograma próximo intento

## 📁 Estructura de Archivos

```
integrations/prestashop/content/modules/alsernetforms/
│
├── alsernetforms.php                           # Módulo principal (actualizado)
│
├── classes/
│   ├── ApiManager.php                          # Gestor de peticiones HTTP (actualizado)
│   ├── DocumentValidator.php                   # Wrapper para validación (NUEVO)
│   ├── EndpointAvailabilityChecker.php         # Verificador de disponibilidad (NUEVO)
│   ├── PendingRequestsProcessor.php            # Procesador de peticiones (NUEVO)
│   │
│   └── loggers/
│       ├── EndpointLoggerInterface.php         # Interfaz base
│       ├── DefaultEndpointLogger.php           # Logger genérico
│       ├── DocumentsEndpointLogger.php         # Logger específico (NUEVO)
│       ├── SubscriptionEndpointLogger.php      # Logger de suscripciones
│       └── FormEndpointLogger.php              # Logger de formularios
│
├── cron/
│   └── process-pending-requests.php            # Script cron (NUEVO)
│
└── sql/
    └── install.sql                             # Schema de BD (NUEVO)
```

## 🗄️ Estructura de Base de Datos

### Tabla: `alsernet_forms_requests`

Almacena todas las peticiones HTTP realizadas a endpoints externos.

```sql
CREATE TABLE `ps_alsernet_forms_requests` (
    `id_alsernetforms_request` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `endpoint_type` VARCHAR(50) NOT NULL,        -- 'documents', 'subscription', 'form', etc.
    `method` VARCHAR(10) NOT NULL,               -- 'GET', 'POST', 'PUT', 'DELETE'
    `url` TEXT NOT NULL,                         -- URL completa del endpoint
    `payload` LONGTEXT NULL,                     -- JSON con datos enviados
    `response` LONGTEXT NULL,                    -- JSON con respuesta recibida
    `status` ENUM('pending', 'processing', 'success', 'failed', 'server_unavailable'),
    `retry_count` INT(11) DEFAULT 0,             -- Número de reintentos realizados
    `max_retries` INT(11) DEFAULT 3,             -- Máximo de reintentos permitidos
    `last_error` TEXT NULL,                      -- Último error registrado
    `created_at` DATETIME NOT NULL,              -- Fecha de creación
    `synced_at` DATETIME NULL,                   -- Fecha de sincronización exitosa
    `next_retry_at` DATETIME NULL,               -- Próxima fecha de reintento
    INDEX `idx_status` (`status`),
    INDEX `idx_endpoint_type` (`endpoint_type`),
    INDEX `idx_next_retry` (`next_retry_at`)
);
```

### Tabla: `alsernet_endpoint_health`

Rastrea la salud y disponibilidad de endpoints externos.

```sql
CREATE TABLE `ps_alsernet_endpoint_health` (
    `id_endpoint_health` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `endpoint_url` VARCHAR(255) NOT NULL,
    `endpoint_type` VARCHAR(50) NOT NULL,
    `is_available` TINYINT(1) DEFAULT 1,         -- 1 = disponible, 0 = no disponible
    `last_check_at` DATETIME NOT NULL,           -- Última verificación
    `last_success_at` DATETIME NULL,             -- Último éxito
    `last_failure_at` DATETIME NULL,             -- Último fallo
    `consecutive_failures` INT(11) DEFAULT 0,    -- Fallos consecutivos
    `response_time_ms` INT(11) NULL,             -- Tiempo de respuesta en ms
    UNIQUE KEY `unique_endpoint` (`endpoint_url`, `endpoint_type`),
    INDEX `idx_availability` (`is_available`)
);
```

## 🔧 Instalación

### 1. Reinstalar o Actualizar el Módulo

Si el módulo ya está instalado, debes actualizarlo para crear las nuevas tablas:

**Opción A: Desde el Panel de Administración**
```
1. Ve a Módulos > Módulos y Servicios
2. Busca "Alsernet - Formularios"
3. Haz clic en "Reinstalar" o "Actualizar"
```

**Opción B: Desde Terminal**
```bash
# Eliminar e instalar de nuevo (CUIDADO: elimina datos)
php bin/console prestashop:module uninstall alsernetforms
php bin/console prestashop:module install alsernetforms
```

**Opción C: Crear las tablas manualmente**
```bash
# Si no quieres perder datos, ejecuta este SQL en tu base de datos
mysql -u usuario -p nombre_bd < integrations/prestashop/content/modules/alsernetforms/sql/install.sql
```

### 2. Configurar Cron Job

El sistema necesita un cron job para procesar peticiones pendientes automáticamente.

**Opción A: Crontab del Sistema (Recomendado)**
```bash
# Editar crontab
crontab -e

# Añadir esta línea para ejecutar cada 5 minutos
*/5 * * * * /usr/bin/php /ruta/completa/prestashop/modules/alsernetforms/cron/process-pending-requests.php >> /var/log/prestashop-pending-requests.log 2>&1
```

**Opción B: Módulo CronJobs de PrestaShop**
```
1. Instala el módulo "Cron tasks manager" desde PrestaShop Addons
2. Añade una nueva tarea:
   - Descripción: "Process pending API requests"
   - Frecuencia: Cada 5 minutos
   - URL o Comando: https://tu-tienda.com/modules/alsernetforms/cron/process-pending-requests.php?secure_key=TU_CLAVE_SEGURA
```

**Opción C: Servicio systemd (Linux)**
```bash
# Crear archivo de servicio
sudo nano /etc/systemd/system/prestashop-pending-requests.service

# Contenido:
[Unit]
Description=PrestaShop Pending Requests Processor
After=network.target

[Service]
Type=oneshot
User=www-data
ExecStart=/usr/bin/php /ruta/prestashop/modules/alsernetforms/cron/process-pending-requests.php

[Install]
WantedBy=multi-user.target

# Crear timer para ejecutar cada 5 minutos
sudo nano /etc/systemd/system/prestashop-pending-requests.timer

# Contenido:
[Unit]
Description=Run PrestaShop Pending Requests every 5 minutes

[Timer]
OnBootSec=5min
OnUnitActiveSec=5min

[Install]
WantedBy=timers.target

# Habilitar y arrancar
sudo systemctl enable prestashop-pending-requests.timer
sudo systemctl start prestashop-pending-requests.timer
```

### 3. Configurar Clave de Seguridad (Opcional pero Recomendado)

Si ejecutas el cron via HTTP, configura una clave de seguridad:

```php
// Añadir en el panel de administración de PrestaShop o directamente en BD
Configuration::updateValue('ALSERNETFORMS_CRON_SECURE_KEY', 'tu_clave_super_segura_aqui');
```

## 💻 Uso del Sistema

### Modificar `alsernetforms.php` para usar el nuevo sistema

**ANTES (código actual en línea 313-336):**
```php
case 'documents':
    $token = Tools::getValue('token');
    $uid = strpos($token, '?token=') !== false
        ? trim(explode('?token=', $token)[1] ?? '')
        : trim($token);

    $validation = Order::validateDniDocuments($uid);

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

    return $this->fetch('module:alsernetforms/views/templates/hook/forms/documents/document.tpl');
```

**DESPUÉS (código actualizado con verificación de disponibilidad):**
```php
case 'documents':
    // Incluir la clase DocumentValidator
    include_once(dirname(__FILE__).'/classes/DocumentValidator.php');

    $token = Tools::getValue('token');
    $uid = strpos($token, '?token=') !== false
        ? trim(explode('?token=', $token)[1] ?? '')
        : trim($token);

    // NUEVO: Usar DocumentValidator en lugar de Order::validateDniDocuments
    // Esto verificará automáticamente si el servidor está disponible
    $validator = new DocumentValidator();

    // Determinar el tipo de documento (puedes obtenerlo del contexto o de la orden)
    $documentType = $this->getDocumentTypeFromUid($uid); // Implementar según tu lógica

    // Validar con verificación de disponibilidad del servidor
    $validation = $validator->validateDocuments($uid, $documentType, [
        'customer_id' => $this->context->customer->id,
        'order_reference' => $uid
    ]);

    // Generar traducciones
    list($trans_remember, $trans_list) = $this->generateDocumentListOnly($uid, $validation['type']);

    // Asignar variables a Smarty
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

    // NUEVO: Si el servidor no está disponible, mostrar mensaje al usuario
    if ($validation['status'] === 'pending') {
        $this->context->smarty->assign([
            'server_unavailable' => true,
            'pending_message' => $validation['message'],
            'request_id' => $validation['request_id'] ?? null
        ]);
    }

    return $this->fetch('module:alsernetforms/views/templates/hook/forms/documents/document.tpl');
```

### Función auxiliar para obtener tipo de documento

Añade este método a la clase `Alsernetforms`:

```php
/**
 * Obtiene el tipo de documento según el UID/token
 * Implementa tu lógica según cómo determines el tipo
 */
private function getDocumentTypeFromUid($uid)
{
    // Ejemplo 1: Si el tipo está en la orden
    // $order = new Order(Order::getOrderByCartId((int)$uid));
    // return $order->getDocumentType();

    // Ejemplo 2: Si el tipo está en un parámetro GET
    $type = Tools::getValue('document_type');
    if ($type) {
        return $type;
    }

    // Ejemplo 3: Valor Por defecto
    return 'dni';
}
```

## 📊 Monitorización y Estadísticas

### Ver estado de peticiones pendientes

```php
// En un controlador o página de admin
include_once(_PS_MODULE_DIR_ . 'alsernetforms/classes/PendingRequestsProcessor.php');
include_once(_PS_MODULE_DIR_ . 'alsernetforms/classes/loggers/DocumentsEndpointLogger.php');

$processor = new PendingRequestsProcessor();
$logger = new DocumentsEndpointLogger();

// Estadísticas generales
$stats = $processor->getPendingStats();
print_r($stats);

// Estadísticas de documentos (últimas 24 horas)
$docStats = $logger->getStats();
print_r($docStats);
```

### Ver estado de salud de endpoints

```php
include_once(_PS_MODULE_DIR_ . 'alsernetforms/classes/EndpointAvailabilityChecker.php');

$checker = new EndpointAvailabilityChecker();
$healthStats = $checker->getHealthStats();
print_r($healthStats);
```

### Consulta SQL para ver peticiones pendientes

```sql
-- Ver todas las peticiones pendientes
SELECT
    id_alsernetforms_request,
    endpoint_type,
    status,
    retry_count,
    created_at,
    next_retry_at,
    last_error
FROM ps_alsernet_forms_requests
WHERE status IN ('pending', 'server_unavailable')
ORDER BY created_at DESC;

-- Ver salud de endpoints
SELECT
    endpoint_url,
    endpoint_type,
    is_available,
    consecutive_failures,
    last_check_at,
    response_time_ms
FROM ps_alsernet_endpoint_health
ORDER BY is_available ASC, consecutive_failures DESC;
```

## 🔍 Troubleshooting

### El cron no se ejecuta

1. Verifica que el archivo tiene permisos de ejecución:
```bash
chmod +x integrations/prestashop/content/modules/alsernetforms/cron/process-pending-requests.php
```

2. Verifica que el crontab está configurado correctamente:
```bash
crontab -l
```

3. Verifica los logs:
```bash
tail -f /var/log/prestashop-pending-requests.log
```

### Peticiones no se procesan

1. Verifica que las tablas existen:
```sql
SHOW TABLES LIKE '%alsernet%';
```

2. Verifica que hay peticiones pendientes:
```sql
SELECT COUNT(*) FROM ps_alsernet_forms_requests WHERE status IN ('pending', 'server_unavailable');
```

3. Ejecuta el procesador manualmente para ver errores:
```bash
php integrations/prestashop/content/modules/alsernetforms/cron/process-pending-requests.php
```

### Servidor siempre marcado como no disponible

1. Verifica la configuración de URL en `ApiManager.php`:
```php
private $apiBaseUrl = 'https://webadminpruebas.a-alvarez.com/';
```

2. Fuerza una verificación manual:
```php
$checker = new EndpointAvailabilityChecker();
$result = $checker->forceCheck('https://webadminpruebas.a-alvarez.com/api/orders/validate-documents', 'documents');
print_r($result);
```

3. Verifica conectividad desde el servidor:
```bash
curl -I https://webadminpruebas.a-alvarez.com/api/orders/validate-documents
```

## 🎯 Configuración Avanzada

### Ajustar tiempos de reintento

Edita `PendingRequestsProcessor.php`:

```php
private function calculateNextRetry($retryCount)
{
    // ACTUAL: 1min, 5min, 15min, 30min, 60min
    $delays = [60, 300, 900, 1800, 3600];

    // MODIFICAR según necesidades:
    // Más agresivo: $delays = [30, 60, 120, 300, 600];
    // Más conservador: $delays = [300, 900, 1800, 3600, 7200];

    $delayIndex = min($retryCount - 1, count($delays) - 1);
    $delay = $delays[$delayIndex];
    return date('Y-m-d H:i:s', time() + $delay);
}
```

### Ajustar threshold de circuit breaker

Edita `EndpointAvailabilityChecker.php`:

```php
private $failureThreshold = 3; // Cambiar a 5 o más para ser menos sensible
private $recoveryCheckInterval = 300; // 5 minutos, cambiar a 600 para 10 minutos
```

### Ajustar máximo de reintentos

Edita `DocumentsEndpointLogger.php` al crear peticiones:

```php
$this->db->insert('alsernet_forms_requests', [
    'max_retries' => 5, // CAMBIAR este valor (Por defecto es 3)
    // ... otros campos
]);
```

## 📚 Referencias

- **Circuit Breaker Pattern**: https://martinfowler.com/bliki/CircuitBreaker.html
- **Exponential Backoff**: https://en.wikipedia.org/wiki/Exponential_backoff
- **PrestaShop Cron**: https://devdocs.prestashop.com/1.7/modules/concepts/cron/

## 📝 Notas Importantes

1. **Las peticiones pendientes NO se eliminan automáticamente**. El cron las procesa cuando el servidor vuelve a estar disponible.

2. **El sistema usa HEAD requests** para verificar disponibilidad, minimizando la carga en el servidor.

3. **Después de 3 fallos consecutivos**, el endpoint se marca como no disponible y no se verificará durante 5 minutos.

4. **Las peticiones antiguas** (>30 días) se limpian automáticamente si configuras el parámetro en el cron.

5. **El procesador tiene un límite de tiempo** de 5 minutos para evitar bloqueos. Ajusta si es necesario.
