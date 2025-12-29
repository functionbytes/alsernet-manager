# PrestaShop Modules Guide

**Guía detallada de los 6 módulos personalizados de PrestaShop para conectar con Alsernet**

---

## 📋 Índice

1. [Alsernetauth](#-Alsernetauth) - Autenticación
2. [Alsernetcustomer](#-Alsernetcustomer) - Clientes
3. [Alsernetproducts](#-Alsernetproducts) - Productos
4. [Alsernetshopping](#-Alsernetshopping) - Órdenes
5. [Alsernetcontents](#-Alsernetcontents) - Contenidos
6. [Alsernetforms](#-Alsernetforms) - Formularios

---

## 🔐 Alsernetauth

**Módulo de Autenticación y Autorización**

### Ubicación
```
integrations/prestashop/content/modules/Alsernetauth/
```

### Propósito
Gestionar la autenticación con Alsernet, permitiendo que usuarios de PrestaShop se sincronicen con el sistema central.

### Funcionalidades

```
✅ Login con credenciales de Alsernet
✅ Registro de nuevos usuarios
✅ SSO (Single Sign-On)
✅ Validación de tokens JWT
✅ Sincronización de sesiones
✅ Roles y permisos desde Alsernet
✅ Logout y cierre de sesiones
```

### Flujo de Autenticación

```
1. Usuario accede a PrestaShop
2. Hace clic en "Login"
3. Introduce email/contraseña
4. Alsernetauth envía a Alsernet:
   POST /api/auth/login
   {
     "email": "user@example.com",
     "password": "****"
   }
5. Alsernet responde con JWT
6. PrestaShop guarda token en sesión
7. Token se valida en cada petición
```

### Configuración

**Archivo**: `Alsernetauth/Alsernetauth.php`

```php
// Habilitar/deshabilitar SSO
Configuration::updateValue('Alsernet_AUTH_SSO', true);

// Token expiration time
Configuration::updateValue('Alsernet_AUTH_TOKEN_TTL', 3600); // 1 hora

// Sincronizar roles
Configuration::updateValue('Alsernet_AUTH_SYNC_ROLES', true);

// Auto-crear usuario si no existe
Configuration::updateValue('Alsernet_AUTH_AUTO_CREATE', true);
```

### Endpoints que Consume

```
POST   /api/auth/login              # Autentica usuario
POST   /api/auth/register           # Registra nuevo usuario
POST   /api/auth/verify             # Valida token
POST   /api/auth/refresh            # Renueva token
POST   /api/auth/logout             # Cierra sesión
GET    /api/auth/me                 # Obtiene datos del usuario actual
GET    /api/users/{id}/roles        # Obtiene roles del usuario
```

### Eventos que Dispara

```php
// En PrestaShop se pueden escuchar estos eventos:

$dispatcher->dispatch('Alsernet:auth:login:before',
    ['user' => $user, 'credentials' => $data]);

$dispatcher->dispatch('Alsernet:auth:login:success',
    ['user' => $user, 'token' => $jwt]);

$dispatcher->dispatch('Alsernet:auth:logout',
    ['user' => $user]);

$dispatcher->dispatch('Alsernet:auth:token:expired',
    ['user_id' => $userId]);
```

### Troubleshooting

| Problema | Causa | Solución |
|----------|-------|----------|
| Login no funciona | Alsernet no responde | Verificar conectividad |
| Token expirado constantemente | TTL muy bajo | Aumentar Alsernet_AUTH_TOKEN_TTL |
| Roles no sincronizados | Sync deshabilitado | Habilitar Alsernet_AUTH_SYNC_ROLES |

---

## 👥 Alsernetcustomer

**Módulo de Sincronización de Clientes**

### Ubicación
```
integrations/prestashop/content/modules/Alsernetcustomer/
```

### Propósito
Sincronizar datos de clientes bidireccional entre PrestaShop y Alsernet.

### Funcionalidades

```
✅ Crear cliente en PrestaShop → Alsernet
✅ Actualizar datos del cliente
✅ Sincronizar direcciones (facturación, envío)
✅ Sincronizar teléfono, DNI, compañía
✅ Historial de cambios
✅ Merge de clientes duplicados
✅ Deshabilitar/eliminar clientes
```

### Datos que Sincroniza

**Información personal**:
```
- Email
- Nombre/Apellido
- DNI/Pasaporte
- Teléfono
- Fecha de nacimiento
- Género
```

**Información de dirección**:
```
- Calle y número
- Código postal
- Ciudad/Provincia
- País
- Teléfono de dirección
- Nombre de contacto
```

**Estado**:
```
- Activo/Inactivo
- Newsletter suscrito
- Cliente B2B
```

### Flujo de Sincronización

#### Crear Cliente

```
Cliente se registra en PrestaShop
    ↓
Event: customerCreate
    ↓
Alsernetcustomer detecta evento
    ↓
API Call: POST /api/customers
    ↓
Alsernet crea registro
    ↓
Retorna customer_id a PrestaShop
```

#### Actualizar Cliente

```
Cliente actualiza perfil
    ↓
Event: customerUpdate
    ↓
Verificar qué campos cambiaron
    ↓
API Call: PUT /api/customers/{id}
    ↓
Alsernet actualiza
```

### Configuración

**Archivo**: `Alsernetcustomer/Alsernetcustomer.php`

```php
// Sincronización automática
Configuration::updateValue('Alsernet_CUSTOMER_AUTO_SYNC', true);

// Campos a sincronizar
Configuration::updateValue('Alsernet_CUSTOMER_SYNC_FIELDS', [
    'email', 'firstname', 'lastname', 'phone',
    'id_address_delivery', 'id_address_invoice'
]);

// Crear cliente automáticamente si no existe
Configuration::updateValue('Alsernet_CUSTOMER_AUTO_CREATE', true);

// Intervalo de sincronización (minutos)
Configuration::updateValue('Alsernet_CUSTOMER_SYNC_INTERVAL', 60);
```

### Endpoints que Consume

```
GET    /api/customers                      # Listar clientes
GET    /api/customers/{id}                 # Obtener cliente
POST   /api/customers                      # Crear cliente
PUT    /api/customers/{id}                 # Actualizar cliente
DELETE /api/customers/{id}                 # Eliminar cliente
GET    /api/customers/{id}/addresses       # Obtener direcciones
POST   /api/customers/{id}/addresses       # Crear dirección
PUT    /api/customers/{id}/addresses/{aid} # Actualizar dirección
```

### Eventos

```php
$dispatcher->dispatch('Alsernet:customer:create:before',
    ['customer' => $customer]);

$dispatcher->dispatch('Alsernet:customer:create:success',
    ['customer' => $customer, 'Alsernet_id' => $asnId]);

$dispatcher->dispatch('Alsernet:customer:update:success',
    ['customer' => $customer, 'changes' => $changeSet]);

$dispatcher->dispatch('Alsernet:customer:sync:conflict',
    ['customer_id' => $id, 'local' => $local, 'remote' => $remote]);
```

### Sincronización Inicial

```bash
# Sincronizar todos los clientes existentes
php bin/console Alsernet:sync:customers --full

# Sincronizar solo cambios recientes
php bin/console Alsernet:sync:customers --incremental

# Sincronizar cliente específico
php bin/console Alsernet:sync:customers --id=123
```

---

## 📦 Alsernetproducts

**Módulo de Sincronización de Productos**

### Ubicación
```
integrations/prestashop/content/modules/Alsernetproducts/
```

### Propósito
Sincronizar catálogo de productos desde Alsernet a PrestaShop (mayormente unidireccional).

### Funcionalidades

```
✅ Importar productos de Alsernet
✅ Actualizar precios dinámicos
✅ Sincronizar inventario/stock
✅ Descargar imágenes automáticamente
✅ Sincronizar categorías
✅ Sincronizar atributos (color, talla, etc.)
✅ Sincronizar variantes
✅ Actualizar descripciones
✅ Actualizar SEO (meta tags)
```

### Tipos de Sincronización

#### 1. Full Sync (Inicial)

```bash
# Importar todos los productos desde Alsernet
php bin/console Alsernet:sync:inventaries --full --batch=50

# Procesamiento:
# 1. Obtiene productos de Alsernet (en lotes de 50)
# 2. Crea/actualiza en PrestaShop
# 3. Descarga imágenes
# 4. Genera slugs SEO
# 5. Calcula precios con impuestos
```

#### 2. Incremental Sync (Cambios)

```bash
# Sincronizar solo productos modificados
php bin/console Alsernet:sync:inventaries --incremental

# Se ejecuta cada:
Configuration::updateValue('Alsernet_PRODUCTS_SYNC_INTERVAL', 300); // 5 min
```

#### 3. Price Update (Precios)

```bash
# Actualizar solo precios (más rápido)
php bin/console Alsernet:sync:inventaries --prices-only

# Frequency:
Configuration::updateValue('Alsernet_PRODUCTS_PRICE_INTERVAL', 60); // 1 min
```

#### 4. Stock Update (Inventario)

```bash
# Actualizar solo stock
php bin/console Alsernet:sync:inventaries --stock-only

# Frequency:
Configuration::updateValue('Alsernet_PRODUCTS_STOCK_INTERVAL', 120); // 2 min
```

### Datos que Sincroniza

**Producto**:
```
- SKU (identificador único)
- Nombre
- Descripción larga y corta
- Precio (con impuestos)
- Costo
- Peso
- Ancho/Alto/Profundidad
- Estado (activo/inactivo)
```

**Stock**:
```
- Cantidad disponible
- Cantidad reservada
- Cantidad en almacén
- Alertas de bajo stock
```

**Imágenes**:
```
- Imagen principal
- Galería de imágenes
- Alt text
- Posición
```

**SEO**:
```
- Meta title
- Meta description
- URL slug
- Palabras clave
```

### Configuración

```php
// Habilitar sincronización
Configuration::updateValue('Alsernet_PRODUCTS_ENABLED', true);

// Batch size para importación
Configuration::updateValue('Alsernet_PRODUCTS_BATCH_SIZE', 50);

// Descargar imágenes
Configuration::updateValue('Alsernet_PRODUCTS_DOWNLOAD_IMAGES', true);

// Máximo de imágenes por producto
Configuration::updateValue('Alsernet_PRODUCTS_MAX_IMAGES', 5);

// Generar URL amigables
Configuration::updateValue('Alsernet_PRODUCTS_GENERATE_URLS', true);

// Calcular precios con impuestos
Configuration::updateValue('Alsernet_PRODUCTS_WITH_TAX', true);
```

### Endpoints que Consume

```
GET    /api/products                      # Listar productos
GET    /api/products/{id}                 # Obtener producto
GET    /api/products/{id}/variants        # Obtener variantes
GET    /api/products/{id}/images          # Obtener imágenes
GET    /api/products/{id}/inventory       # Obtener inventario
GET    /api/categories                    # Listar categorías
GET    /api/attributes                    # Listar atributos
```

### Performance

```bash
# El módulo implementa caché inteligente:
- Cache por 1 hora de listados
- Cache por 30 min de detalles
- Cache invalidado por webhooks
- Queue async para descargas de imágenes
```

---

## 🛒 Alsernetshopping

**Módulo de Sincronización de Órdenes**

### Ubicación
```
integrations/prestashop/content/modules/Alsernetshopping/
```

### Propósito
Sincronizar órdenes de compra desde PrestaShop a Alsernet para procesamiento.

### Funcionalidades

```
✅ Enviar nueva orden a Alsernet
✅ Sincronizar estado de pago
✅ Recibir estado de envío
✅ Actualizar número de seguimiento
✅ Notificar cambios al cliente
✅ Historial completo de cambios
✅ Sincronizar devoluciones
✅ Procesar notas internas
```

### Flujo de Orden

```
1. Cliente compra en PrestaShop
   ↓
2. Se crea Orden en PrestaShop
   ↓
3. Event: orderCreate
   ↓
4. Alsernetshopping detecta
   ↓
5. Valida datos de la orden
   ↓
6. API Call: POST /api/orders
   ↓
7. Alsernet recibe orden
   ↓
8. Warehouse procesa
   ↓
9. Envío a Alsernet
   ↓
10. PrestaShop recibe estado
    (via Webhook)
   ↓
11. Actualiza estado en PrestaShop
   ↓
12. Notifica a cliente
```

### Estados de Orden

**Estados en PrestaShop**:
```
Pending Payment    → Esperando pago
Processing         → Procesando en Alsernet
Prepared           → Preparado en almacén
Shipped            → Enviado
Delivered          → Entregado
Cancelled          → Cancelado
Refunded           → Reembolsado
```

**Mapeo a Alsernet**:
```
PrestaShop → Alsernet
pending_payment → awaiting_payment
processing → in_progress
prepared → ready_to_ship
shipped → shipped
delivered → delivered
cancelled → cancelled
```

### Datos que Sincroniza

**Información de orden**:
```
- Order ID
- Order number
- Order date
- Total price
- Subtotal
- Shipping cost
- Tax
- Discount
- Payment method
- Currency
```

**Cliente**:
```
- Customer data
- Billing address
- Shipping address
- Phone/Email
```

**Items**:
```
- Product ID
- SKU
- Quantity
- Price per unit
- Discount per item
```

**Envío**:
```
- Carrier
- Tracking number
- Estimated delivery
- Shipping date
```

### Configuración

```php
// Enviar automáticamente
Configuration::updateValue('Alsernet_SHOPPING_AUTO_SEND', true);

// Enviar cuando se confirma pago
Configuration::updateValue('Alsernet_SHOPPING_SEND_ON_PAYMENT', true);

// Retardo antes de enviar (segundos)
Configuration::updateValue('Alsernet_SHOPPING_SEND_DELAY', 300);

// Sincronizar devoluciones
Configuration::updateValue('Alsernet_SHOPPING_SYNC_RETURNS', true);

// Notificar cliente de cambios
Configuration::updateValue('Alsernet_SHOPPING_NOTIFY_CLIENT', true);
```

### Endpoints que Consume

```
POST   /api/orders                    # Crear orden
GET    /api/orders/{id}               # Obtener orden
PUT    /api/orders/{id}               # Actualizar orden
PUT    /api/orders/{id}/status        # Cambiar estado
GET    /api/orders/{id}/history       # Historial de cambios
POST   /api/orders/{id}/shipments     # Crear envío
PUT    /api/orders/{id}/shipments/{sid} # Actualizar envío
POST   /api/orders/{id}/returns       # Crear devolución
```

### Webhooks que Recibe

```
order.payment_confirmed    → Pago confirmado
order.prepared            → Preparado en almacén
order.shipped             → Despachado
order.delivered           → Entregado
order.cancelled           → Cancelado
shipment.created          → Nuevo envío
shipment.tracking_updated → Actualizar tracking
```

---

## 📄 Alsernetcontents

**Módulo de Sincronización de Contenidos**

### Ubicación
```
integrations/prestashop/content/modules/Alsernetcontents/
```

### Propósito
Sincronizar contenidos estáticos/CMS desde Alsernet a PrestaShop.

### Funcionalidades

```
✅ Importar páginas CMS
✅ Importar políticas (privacidad, términos)
✅ Importar bloques de contenido
✅ Importar FAQs
✅ Importar información de empresa
✅ Actualizar información de contacto
✅ Sincronizar banners
```

### Tipos de Contenido

**Páginas**:
```
- Página "Quiénes somos"
- Página "Contacto"
- Página "Envíos"
- Página de políticas
```

**Políticas**:
```
- Política de privacidad
- Términos y condiciones
- Política de devoluciones
- Aviso legal
```

**Bloques**:
```
- Footer information
- Company info
- Social media links
- Newsletter signup
```

### Configuración

```php
// Sincronización automática
Configuration::updateValue('Alsernet_CONTENTS_AUTO_SYNC', true);

// Intervalo de sincronización (minutos)
Configuration::updateValue('Alsernet_CONTENTS_SYNC_INTERVAL', 1440); // 24 horas

// Lenguajes a sincronizar
Configuration::updateValue('Alsernet_CONTENTS_LANGUAGES', ['es', 'en', 'fr']);
```

### Endpoints que Consume

```
GET    /api/pages                   # Listar páginas
GET    /api/pages/{id}              # Obtener página
GET    /api/policies                # Listar políticas
GET    /api/contents                # Listar bloques de contenido
GET    /api/company-info            # Información de empresa
```

---

## 📋 Alsernetforms

**Módulo de Formularios Personalizados**

### Ubicación
```
integrations/prestashop/content/modules/Alsernetforms/
```

### Propósito
Formularios personalizados con validación y integración con CRM.

### Funcionalidades

```
✅ Formulario de contacto
✅ Solicitud de cotización
✅ Validación personalizada
✅ Integración con CRM/Leads
✅ Notificaciones por email
✅ Guardado de leads en Alsernet
✅ CAPTCHA anti-spam
```

### Tipos de Formularios

**Contacto**:
```
- Nombre
- Email
- Teléfono
- Asunto
- Mensaje
```

**Cotización**:
```
- Nombre empresa
- Email
- Teléfono
- Productos interesados
- Cantidad
- Mensaje especial
```

### Configuración

```php
// Habilitar captcha
Configuration::updateValue('Alsernet_FORMS_CAPTCHA', true);

// Tipo captcha
Configuration::updateValue('Alsernet_FORMS_CAPTCHA_TYPE', 'recaptcha'); // o 'hcaptcha'

// Enviar a CRM/Leads
Configuration::updateValue('Alsernet_FORMS_SEND_TO_CRM', true);

// Email de notificación
Configuration::updateValue('Alsernet_FORMS_NOTIFY_EMAIL', 'admin@company.com');
```

### Endpoints que Consume

```
POST   /api/leads                   # Crear lead
POST   /api/contact-requests        # Crear solicitud de contacto
GET    /api/captcha/verify          # Verificar captcha
```

---

## 🔄 Sincronización Programada (Cron Jobs)

### Configurar Cron Jobs

En cPanel o servidor:

```bash
# Ejecutar cada 5 minutos
*/5 * * * * /usr/bin/php /path/to/prestashop/bin/console Alsernet:sync:prices

# Ejecutar cada 15 minutos
*/15 * * * * /usr/bin/php /path/to/prestashop/bin/console Alsernet:sync:stock

# Ejecutar cada hora
0 * * * * /usr/bin/php /path/to/prestashop/bin/console Alsernet:sync:inventaries:incremental

# Ejecutar cada 2 horas
0 */2 * * * /usr/bin/php /path/to/prestashop/bin/console Alsernet:sync:customers
```

---

## 📊 Monitoreo

### Ver logs

```bash
# Todas las sincronizaciones
tail -f storage/logs/Alsernet-sync.log

# Módulo específico
tail -f storage/logs/Alsernet-inventaries.log
tail -f storage/logs/Alsernet-customers.log
tail -f storage/logs/Alsernet-orders.log
```

### Dashboard de módulos

```
Admin > Modules > Alsernet > Dashboard

Muestra:
- Estado de cada módulo
- Última sincronización
- Próxima sincronización
- Errores recientes
- Estadísticas
```

---

**Última actualización**: Noviembre 30, 2025
**Estado**: Producción ✅
