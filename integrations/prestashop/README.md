# PrestaShop Integration with Alsernet

**Integración de PrestaShop 1.6+ con Alsernet (Laravel 12) vía API**

Este módulo contiene el código personalizado de PrestaShop que conecta con Alsernet, permitiendo sincronización de productos, clientes, órdenes y contenidos.

---

## 📋 Tabla de Contenidos

- [Descripción](#descripción)
- [Estructura](#estructura)
- [Módulos Personalizados](#módulos-personalizados)
- [Configuración](#configuración)
- [API Connection](#api-connection)
- [Documentación](#documentación)

---

## Descripción

### ¿Qué es esta integración?

**Alsernet** (Laravel) es el sistema central de gestión de e-commerce.
**PrestaShop** es la tienda online pública/frontend que se sincroniza con Alsernet.

### Flujo de Datos

```
PrestaShop (Frontend)
        ↓ (API Calls)
    Alsernet (Central)
        ↓ (API Calls)
    Bases de Datos Externas
        ↓
    ERP, Warehouse, etc.
```

### Qué se sincroniza

| Entidad | Dirección | Descripción |
|---------|-----------|-------------|
| **Productos** | ↔ Ambas | Catálogo, precios, inventario |
| **Clientes** | ↔ Ambas | Datos de clientes, direcciones |
| **Órdenes** | ↔ Ambas | Pedidos, estados, historial |
| **Contenidos** | ← Alsernet | CMS, políticas, páginas estáticas |
| **Configuración** | ← Alsernet | Ajustes globales, impuestos |

---

## Estructura

```
integrations/prestashop/
├── content/                              # Código de PrestaShop
│   ├── app/                              # Configuración Symfony
│   ├── classes/                          # Clases de PrestaShop
│   ├── controllers/                      # Controladores
│   ├── modules/                          # Módulos (✨ personalizados)
│   │   ├── Alsernetauth/                 # Autenticación con Alsernet
│   │   ├── Alsernetcustomer/             # Sincronización clientes
│   │   ├── Alsernetproducts/             # Sincronización productos
│   │   ├── Alsernetshopping/             # Sincronización órdenes
│   │   ├── Alsernetcontents/             # Sincronización contenidos
│   │   └── Alsernetforms/                # Formularios personalizados
│   ├── override/                         # Overrides de clases
│   └── src/                              # Código Symfony personalizado
│
├── docs/                                 # Documentación
│   ├── api-connection.md                 # Configuración API
│   ├── modules-guide.md                  # Guía de módulos
│   ├── setup.md                          # Instalación
│   └── endpoints.md                      # Endpoints que consume
│
└── README.md                             # Este archivo
```

---

## Módulos Personalizados

### 🔐 Alsernetauth
**Autenticación y autorización con Alsernet**

```
Funcionalidades:
✅ Login/Logout con Alsernet
✅ SSO (Single Sign-On)
✅ Validación de tokens JWT
✅ Sincronización de sesiones
✅ Permisos basados en roles
```

**Endpoints que consume**:
- `POST /api/auth/login` - Autentica usuario
- `POST /api/auth/verify` - Valida token
- `POST /api/auth/logout` - Cierra sesión

---

### 👥 Alsernetcustomer
**Sincronización de clientes**

```
Funcionalidades:
✅ Crear cliente en PrestaShop → Alsernet
✅ Actualizar datos del cliente
✅ Sincronizar direcciones
✅ Sincronizar información de facturación
✅ Historial de cambios
```

**Flujo**:
```
PrestaShop (Registro)
    ↓
Alsernetcustomer (API Call)
    ↓
Alsernet API
    ↓
Base de datos Alsernet
```

**Eventos que dispara**:
- `customerCreate` - Cuando se crea cliente
- `customerUpdate` - Cuando se actualiza
- `addressCreate` - Nueva dirección
- `addressUpdate` - Dirección actualizada

---

### 📦 Alsernetproducts
**Sincronización de catálogo de productos**

```
Funcionalidades:
✅ Importar productos de Alsernet
✅ Actualizar precios y stock
✅ Sincronizar imágenes
✅ Categorías y atributos
✅ Variantes de productos
```

**Dirección de flujo**: Alsernet → PrestaShop (mayormente)

**Sincronización**:
- Horaria (cron job)
- Evento driven (webhook desde Alsernet)
- Manual (admin panel)

---

### 🛒 Alsernetshopping
**Sincronización de órdenes/pedidos**

```
Funcionalidades:
✅ Enviar orden de PrestaShop → Alsernet
✅ Recibir estado de envío
✅ Actualizar estado de pago
✅ Notificaciones al cliente
✅ Historial de cambios
```

**Flujo de orden**:
```
Cliente compra en PrestaShop
    ↓
Orden en PrestaShop
    ↓
Alsernetshopping API Call
    ↓
Alsernet procesa orden
    ↓
Warehouse, ERP, etc.
```

---

### 📄 Alsernetcontents
**Sincronización de contenidos CMS**

```
Funcionalidades:
✅ Importar páginas de Alsernet
✅ Importar políticas (privacidad, términos)
✅ Importar bloques de contenido
✅ Actualizar información estática
```

**Dirección de flujo**: Alsernet → PrestaShop

**Tipos de contenido**:
- Páginas estáticas
- Políticas legales
- Bloques de información
- FAQs

---

### 📋 Alsernetforms
**Formularios personalizados y validación**

```
Funcionalidades:
✅ Formularios de contacto
✅ Solicitudes de cotización
✅ Validación personalizada
✅ Integración con CRM
```

---

## Configuración

### 1. Instalación de módulos

```bash
cd integrations/prestashop/content

# PrestaShop cargará automáticamente los módulos desde:
modules/Alsernet*/

# Desde admin panel:
# 1. Ir a Admin > Módulos
# 2. Buscar "Alsernet"
# 3. Instalar cada módulo
```

### 2. Configuración de API

Ver [docs/api-connection.md](docs/api-connection.md)

```
Requerido:
✅ URL de Alsernet
✅ API Key
✅ API Secret
✅ Webhook Secret (si aplica)
```

### 3. Sincronización inicial

```bash
# Importar productos
php bin/console Alsernet:sync:products

# Importar clientes existentes
php bin/console Alsernet:sync:customers

# Importar órdenes anteriores
php bin/console Alsernet:sync:orders
```

---

## API Connection

### Autenticación

Los módulos usan **JWT (JSON Web Tokens)** para comunicarse con Alsernet.

```php
// Cada petición incluye:
Authorization: Bearer {JWT_TOKEN}
X-API-Key: {API_KEY}
X-API-Secret: {API_SECRET}
```

### Endpoints Principales

**Clientes**:
```
POST   /api/customers              # Crear cliente
PUT    /api/customers/{id}         # Actualizar cliente
GET    /api/customers/{id}         # Obtener cliente
DELETE /api/customers/{id}         # Eliminar cliente
```

**Productos**:
```
GET    /api/products               # Listar productos
GET    /api/products/{id}          # Obtener producto
POST   /api/products               # Crear producto
PUT    /api/products/{id}          # Actualizar producto
```

**Órdenes**:
```
POST   /api/orders                 # Crear orden
GET    /api/orders/{id}            # Obtener orden
PUT    /api/orders/{id}            # Actualizar estado
GET    /api/orders/{id}/history    # Historial
```

Ver documentación completa en [docs/endpoints.md](docs/endpoints.md)

---

## Documentación

### 📖 Guías disponibles

- **[API Connection](docs/api-connection.md)** - Configuración y autenticación
- **[Modules Guide](docs/modules-guide.md)** - Detalle de cada módulo
- **[Setup Instructions](docs/setup.md)** - Instalación paso a paso
- **[Endpoints Reference](docs/endpoints.md)** - Endpoints completos

### 🔗 Enlaces útiles

- [PrestaShop Oficial](https://www.prestashop.com)
- [Documentación PrestaShop API](https://devdocs.prestashop.com)
- [Alsernet Documentation](./../.claude/)

---

## ✅ Checklist de Setup

```
□ PrestaShop 1.6+ instalado
□ PHP CLI disponible
□ Conexión a base de datos configurada
□ URL de Alsernet configurada
□ API Key y Secret obtenidas
□ Módulos instalados desde admin panel
□ Sincronización inicial ejecutada
□ Webhooks configurados
□ Logs verificados (storage/logs/Alsernet/)
□ Pruebas de API completadas
```

---

## 🐛 Troubleshooting

### Módulo no se carga

```bash
# Verificar permisos
chmod -R 755 modules/

# Limpiar cache
rm -rf cache/*

# Recargar módulos
php bin/console cache:clear
```

### Error de conexión API

```bash
# Verificar configuración
tail -f storage/logs/Alsernet-api.log

# Probar conexión
curl -X GET http://Alsernet-url/api/health \
  -H "Authorization: Bearer {token}" \
  -H "X-API-Key: {key}"
```

### Sincronización lenta

```bash
# Aumentar timeout
php bin/console config:set Alsernet:api:timeout 60

# Ejecutar sincronización en background
php bin/console Alsernet:sync:products --background
```

---

## 📊 Estadísticas

| Aspecto | Valor |
|---------|-------|
| **Archivos PrestaShop** | 7,600+ |
| **Módulos Personalizados** | 6 |
| **Endpoints Integrados** | 50+ |
| **Versión PrestaShop** | 1.6+ |
| **Versión PHP** | 7.2+ |

---

## 📝 Notas Importantes

1. **Sincronización**: Es bidireccional pero con prioridades:
   - Clientes: Bidireccional
   - Productos: Desde Alsernet (principal)
   - Órdenes: Desde PrestaShop → Alsernet

2. **Datos sensibles**: API Keys se guardan en `config/parameters.php` (git-ignored)

3. **Logging**: Todos los eventos se registran en:
   - `storage/logs/Alsernet-*.log`
   - PrestaShop admin panel > Sistema > Registros

4. **Performance**: Use caché Redis para sincronización frecuente

---

**Última actualización**: Noviembre 30, 2025
**Versión**: 1.0 - Integración Completa
**Mantenimiento**: Equipo Alsernet
**Status**: Producción ✅
