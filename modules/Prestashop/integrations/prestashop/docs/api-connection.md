# API Connection Guide - PrestaShop ↔ Alsernet

**Cómo configurar y usar la conexión API entre PrestaShop y Alsernet**

---

## 📋 Tabla de Contenidos

- [Requisitos](#requisitos)
- [Configuración Inicial](#configuración-inicial)
- [Obtener Credenciales](#obtener-credenciales)
- [Autenticación](#autenticación)
- [Manejo de Errores](#manejo-de-errores)
- [Troubleshooting](#troubleshooting)

---

## Requisitos

### Del Lado de Alsernet (Laravel)

```
✅ Alsernet 3.0+ instalado
✅ Laravel Sanctum configurado
✅ API Key generada
✅ HTTPS habilitado (producción)
✅ CORS configurado para PrestaShop
```

### Del Lado de PrestaShop

```
✅ PrestaShop 1.6+ instalado
✅ PHP CLI (para cron jobs)
✅ cURL habilitado
✅ SSL Certificate (HTTPS)
✅ Módulos Alsernet instalados
```

---

## Configuración Inicial

### 1. Obtener URL y Credenciales de Alsernet

En Alsernet, accede a:
```
Admin Panel > API Settings > Generate Token
```

Esto te generará:
- **API URL**: `https://Alsernet.com/api`
- **API Key**: `key_1234567890abcdef`
- **API Secret**: `secret_abcdefghijklmnop`
- **Webhook Secret**: `webhook_xyz789`

### 2. Configurar en PrestaShop

#### Opción A: Vía Admin Panel

```
1. Accede a: Admin > Módulos > Alsernet
2. Busca "Alsernet Auth" module
3. Haz clic en "Configurar"
4. Completa:
   - API URL: https://Alsernet.com/api
   - API Key: key_1234567890abcdef
   - API Secret: secret_abcdefghijklmnop
   - Webhook Secret: webhook_xyz789
   - Enable SSL Verification: ✓
5. Haz clic en "Guardar"
```

#### Opción B: Vía Archivo de Configuración

Edita `integrations/prestashop/content/app/config/parameters.php`:

```php
<?php
return [
    'Alsernet' => [
        'api_url' => 'https://Alsernet.com/api',
        'api_key' => 'key_1234567890abcdef',
        'api_secret' => 'secret_abcdefghijklmnop',
        'webhook_secret' => 'webhook_xyz789',
        'timeout' => 30,
        'verify_ssl' => true,
        'debug' => false,
    ],
];
```

---

## Obtener Credenciales

### Paso a Paso en Alsernet

```bash
# 1. Login en Alsernet admin
https://Alsernet.com/admin

# 2. Ir a Settings > API
Settings > Integrations > API Keys

# 3. Crear nueva API Key
Button "Generate New Key"

# Seleccionar:
- Name: "PrestaShop Integration"
- Scopes: inventaries, customers, orders, auth
- Expires: 1 year (o Never)

# 4. Copiar credenciales:
[Show Credentials Button]
- Key: key_xxxxxx
- Secret: secret_yyyyyy
- Webhook URL: https://prestashop.com/modules/Alsernetwebhook/validate.php
- Webhook Secret: webhook_zzzzz
```

### En PrestaShop

```bash
# Verificar credenciales
php bin/console Alsernet:verify-credentials

# Output:
# ✅ API Connection: OK
# ✅ Authentication: OK
# ✅ Webhook: Configured
```

---

## Autenticación

### JWT (JSON Web Token)

Todos los módulos usan **JWT** para autenticarse con Alsernet.

#### Token Request

```bash
curl -X POST https://Alsernet.com/api/auth/login \
  -H "Content-Type: application/json" \
  -H "X-API-Key: key_1234567890abcdef" \
  -H "X-API-Secret: secret_abcdefghijklmnop" \
  -d '{
    "grant_type": "client_credentials",
    "client_id": "prestashop",
    "client_secret": "secret_xyz"
  }'
```

#### Token Response

```json
{
  "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

#### Usar Token en Peticiones

```bash
curl -X GET https://Alsernet.com/api/products \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..." \
  -H "X-API-Key: key_1234567890abcdef"
```

### Refresh Token

```bash
curl -X POST https://Alsernet.com/api/auth/refresh \
  -H "Authorization: Bearer {REFRESH_TOKEN}"
```

---

## Tipos de Peticiones

### GET - Obtener datos

```bash
# Listar productos
GET /api/inventaries
GET /api/inventaries?page=1&per_page=20&filter[sku]=ABC*

# Obtener un producto
GET /api/inventaries/123

# Con inclusos
GET /api/inventaries/123?include=variants,images,categories
```

### POST - Crear datos

```bash
POST /api/customers
Content-Type: application/json

{
  "email": "customer@example.com",
  "firstname": "Juan",
  "lastname": "Pérez",
  "phone": "+34 666 777 888",
  "active": true
}
```

### PUT - Actualizar datos

```bash
PUT /api/customers/123
Content-Type: application/json

{
  "firstname": "Juan",
  "lastname": "Pérez García",
  "phone": "+34 666 777 888"
}
```

### DELETE - Eliminar datos

```bash
DELETE /api/customers/123
```

---

## Manejo de Errores

### Códigos de Error HTTP

| Código | Significado | Solución |
|--------|-------------|----------|
| **200** | OK | Éxito |
| **201** | Created | Recurso creado |
| **204** | No Content | Éxito sin contenido |
| **400** | Bad Request | Valida datos enviados |
| **401** | Unauthorized | Verifica API Key/Secret |
| **403** | Forbidden | Verifica permisos |
| **404** | Not Found | Recurso no existe |
| **422** | Unprocessable Entity | Valida datos |
| **429** | Too Many Requests | Rate limit exceeded |
| **500** | Server Error | Contacta soporte |

### Respuesta de Error Típica

```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "email": ["Email already exists"],
    "firstname": ["First name is required"]
  }
}
```

### Retry Logic

```php
// PrestaShop modules implementan reintentos automáticos
// Configuración en config/parameters.php

'retry' => [
    'enabled' => true,
    'max_attempts' => 3,
    'backoff' => 'exponential', // 1s, 2s, 4s
    'on_status' => [429, 500, 502, 503, 504]
]
```

---

## Rate Limiting

### Límites por Endpoint

| Endpoint | Límite | Ventana |
|----------|--------|---------|
| `/api/auth/*` | 10/min | 1 minuto |
| `/api/customers*` | 100/min | 1 minuto |
| `/api/products*` | 200/min | 1 minuto |
| `/api/orders*` | 100/min | 1 minuto |

### Headers de Rate Limit

```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1609459200
```

---

## Webhooks

### Configurar Webhook

En Alsernet > Settings > Webhooks:

```
URL: https://prestashop.com/modules/Alsernetwebhook/validate.php
Secret: webhook_xyz789
Events:
  ☑ order.created
  ☑ order.updated
  ☑ product.updated
  ☑ customer.created
  ☑ customer.updated
```

### Validar Webhook Signature

```php
// En PrestaShop webhook handler

$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
$payload = file_get_contents('php://input');
$secret = Configuration::get('Alsernet_WEBHOOK_SECRET');

// Calcular firma esperada
$expected = hash_hmac('sha256', $payload, $secret);

// Validar
if (!hash_equals($signature, $expected)) {
    http_response_code(401);
    die('Invalid signature');
}
```

---

## Testing

### Verificar Conexión

```bash
# Desde PrestaShop root
php bin/console Alsernet:test-connection

# Output:
# ✅ Connected to Alsernet
# ✅ Authentication: Successful
# ✅ API Version: 3.0.1
# ✅ Database: Connected
```

### Probar Endpoint

```bash
php bin/console Alsernet:test:customers:list

# Output:
# Fetching customers from Alsernet...
# ✅ Retrieved 150 customers
# Sample: Juan Pérez (ID: 123)
```

### Ver Logs

```bash
# Logs de API
tail -f storage/logs/Alsernet-api.log

# Logs de sincronización
tail -f storage/logs/Alsernet-sync.log

# Logs de webhooks
tail -f storage/logs/Alsernet-webhook.log
```

---

## Troubleshooting

### "Connection refused"

**Causa**: Alsernet no está accesible

```bash
# Verificar URL
curl -I https://Alsernet.com/api/health

# Si falla, verificar:
1. ¿URL correcta en config?
2. ¿Firewall bloqueando?
3. ¿DNS resolviendo?
4. ¿SSL Certificate válido?
```

### "Invalid API Key"

**Causa**: API Key incorrecta o expirada

```bash
# Generar nueva en Alsernet
Admin > API > Generate New Key

# Actualizar en PrestaShop config/parameters.php
'api_key' => 'new_key_xyz',
```

### "Token expired"

**Causa**: JWT token expirado (normal, se auto-renueva)

```bash
# Si ocurre frecuentemente, aumentar timeout:
'timeout' => 60, // segundos

# O aumentar token expiration en Alsernet:
Admin > API Settings > Token Expiration: 7200
```

### Rate Limit Exceeded

**Causa**: Demasiadas peticiones

```php
// Aumentar espera entre sincronizaciones
'Alsernet' => [
    'sync_interval' => 3600, // 1 hora en lugar de 30 min
    'batch_size' => 50,      // Procesar 50 items por vez
]
```

---

## Security Best Practices

```
✅ Usar HTTPS siempre
✅ Guardar API Secret en archivo .env (gitignored)
✅ Rotar API Keys cada 90 días
✅ Usar webhooks en lugar de polling cuando sea posible
✅ Validar firmas de webhooks
✅ Loguear todas las peticiones de API
✅ Limitar acceso a endpoints por IP
✅ Usar VPN/Tunnel en producción
```

---

**Última actualización**: Noviembre 30, 2025
**Estado**: Producción ✅
