# ERP Integration Guide - Gestión & Web Álvarez

**Complete integration documentation for the Gestión ERP system and Web Álvarez platform**

---

## 📋 Table of Contents

- [System Overview](#system-overview)
- [Architecture](#architecture)
- [Integration Services](#integration-services)
- [REST API Base Configuration](#rest-api-base-configuration)
- [Data Flow](#data-flow)
- [Error Handling](#error-handling)
- [Implementation Checklist](#implementation-checklist)

---

## System Overview

The integration between **Gestión** (internal ERP system by Microserver) and **Web Álvarez** (e-commerce platform) enables:

### Key Capabilities

✅ **Order Management** - Update order status, payment info, shipments
✅ **Customer Synchronization** - Create/update customer data
✅ **Inventory Sync** - Real-time stock synchronization
✅ **Promotional System** - Bonus/gift card management
✅ **Notifications** - Email and SMS notifications
✅ **Catalog Sync** - Product catalogs, tariffs, availability
✅ **Auditing** - Complete transaction logging

### Core Systems

| System | Type | Purpose |
|--------|------|---------|
| **Gestión** | ERP (Internal) | Central business system, inventory, clients, orders |
| **Web Álvarez** | E-commerce | Web platform, customer portal, order placement |
| **WebAlvarez Service** | XML-RPC | Legacy service for order updates and SMS |
| **Gestión API** | REST | Modern API for data synchronization |

---

## Architecture

### Integration Flow

```
Web Álvarez (Customer Actions)
        ↓
Gestión API (REST)
        ├─ Data Synchronization
        ├─ Customer Management
        ├─ Order Processing
        └─ Inventory Updates
        ↓
WebAlvarez Service (XML-RPC)
        ├─ Order Status Updates
        └─ SMS Notifications
        ↓
Database (PostgreSQL/MySQL)
```

### Technology Stack

**Backend Infrastructure**
- Gestión REST API: `http://interges:8080/api-gestion/`
- WebAlvarez XML-RPC: `192.168.1.6:8081` & `213.134.40.126:8080`
- Database: PostgreSQL (primary) or MySQL
- Response Format: JSON (REST) / XML (XML-RPC)

**Data Encoding**
- String/Text: Base64 encoding (especially for XML-RPC)
- DateTime: ISO 8601 format (e.g., `2020-01-31T17:38:06`)
- Numeric: Double/Int types

---

## Integration Services

### 1. WebAlvarez Services (Legacy XML-RPC)

#### Purpose
Push order data and send SMS notifications from Gestión to Web Álvarez

#### Services

##### WebAlvarez.insertDatos - Order Updates
- **Host**: 192.168.1.6
- **Port**: 8081
- **Method**: WebAlvarez.insertDatos
- **Purpose**: Update order status, customer info, payments, shipments

**Parameters** (30+ fields)
- Order: ID_Pedido, id_cliente, Estado, F_Cambio_Estado, fecha_Ped, total_pedido
- Customer: NombreCli, Apellidos, Telefonos, Telefono1
- Order Lines: Referencia, Descripcion, Unidades, SubTotal, Stock
- Payment: FormaPago, FPago, Importe
- Incidents: Tipo, Solucionado
- Shipment: Transportista, Ref_Envio, Porte, Telefono, FEnvio, Lineas_Envio

**Data Format**: XML with Base64-encoded values

##### SMSServer.sendSMS - SMS Notification
- **Host**: 213.134.40.126
- **Port**: 8080
- **Method**: SMSServer.sendSMS

**Parameters**
- cliente (string): "ALVAREZ"
- password (string): SMS service password
- numero (string): Phone number (e.g., "666555444")
- sms (base64): SMS text content
- offline (boolean): Queue if offline (0=false, 1=true)

---

### 2. Gestión REST API

#### Base Configuration

```
Base URL: http://interges:8080/api-gestion/
Content-Type: application/x-www-form-urlencoded (POST/PUT)
Response Format: XML
DateTime Format: ISO 8601 (e.g., 2020-01-31T17:38:06)
Encoding: Base64 for sensitive data (passwords, card numbers)
```

#### API Endpoints Summary

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/CambiosPendientes/` | GET | List pending transactions |
| `/TransaccionPendiente/` | GET | Get transaction details |
| `/ConfirmarTransaccion/` | GET | Mark transaction as synced |
| `/bono/` | GET, PUT | Promotional bonus queries/updates |
| `/stock-central-web/` | GET | Free stock (not reserved) queries |
| `/articulo/` | GET | Article lookup by code |
| `/cliente/` | GET, POST, PUT | Customer management |
| `/clientecatalogo/` | GET, POST, DELETE | Customer catalog subscriptions |
| `/pedido-cliente/` | GET, POST | Order management |
| `/albaran-cliente/` | GET | Invoice/shipment queries |
| `/notificacion-central/` | POST | Create email notifications |
| `/generacion-bono/` | POST | Generate promotional bonuses |
| `/cambio_publicidad_email/` | GET | Email preference changes |
| `/vale/` | GET, PUT, POST | Gift card/voucher management |

---

## Data Flow

### Table Synchronization Flow

```
1. Query CambiosPendientes
   ↓ (Get list of pending transactions)
2. For each transaction:
   - Fetch TransaccionPendiente details
   - Process changes (INSERT/UPDATE/DELETE)
   - Apply to Web Álvarez tables
   ↓
3. Call ConfirmarTransaccion
   ↓ (Mark as synced)
4. Move to next transaction
```

### Order Processing Flow

```
1. Customer places order in Web Álvarez
   ↓
2. Send to Gestión via POST /pedido-cliente/
   ↓ (Validates: LOPD, CIF, email, catalog)
3. Create order + customer if needed
   ↓
4. Gestión processes internally
   ↓
5. Call WebAlvarez.insertDatos
   ↓ (Update order status in Web Álvarez)
6. Send SMS notification via SMSServer.sendSMS
   ↓
7. Web Álvarez updates UI + notifies customer
```

### Inventory Sync Flow

```
Gestión Central Stock Changes
        ↓
Create v_sinc_stock_central_web entry
        ↓
Add to CambiosPendientes transaction
        ↓
Web Álvarez polls via TransaccionPendiente
        ↓
Update product visibility
        ↓
Web Álvarez confirms via ConfirmarTransaccion
```

---

## Error Handling

### HTTP Status Codes

| Code | Meaning | Action |
|------|---------|--------|
| **200** | OK | Success, process response |
| **201** | Created | Resource created |
| **204** | No Content | Success, no response body |
| **400** | Bad Request | Validate input, check logs |
| **404** | Not Found | Resource doesn't exist |
| **408** | Request Timeout | Server busy, retry later |
| **409** | Conflict | Duplicate/constraint violation |
| **500** | Server Error | Contact support |

### Common Error Messages

```
ERROR 20401: Duplicate email (cliente_email = XXXXXX)
ERROR 20402: Duplicate CIF (cliente_cif = XXXXXX)
ERROR 20403: Customer must have at least 1 catalog
ERROR 20404: Customer must accept LOPD
ERROR 20410: Phone prefix not found (prefijo_telefono = XXXXXX)
ERROR 20420: Cannot create order - customer hasn't accepted LOPD
ERROR 20421: Cannot invoice - customer missing CIF
ERROR 20430: Article doesn't exist (referencia = XXXXXX)
ERROR 20431: Unexpected price for article
ERROR 20440: Payment method doesn't exist (pago_forma_pago = XXXXXX)
ERROR 20450: Order cannot have both item and lottery lines
ERROR 20451: Duplicate order identifier
```

### Retry Strategy

**Exponential Backoff**
- Max attempts: 3
- Delays: 1s, 2s, 4s
- Retry on: 429, 500, 502, 503, 504

---

## Implementation Checklist

### Prerequisites
- [ ] Access to Gestión instance (http://interges:8080)
- [ ] WebAlvarez XML-RPC credentials (host/port, cliente, password)
- [ ] Database connection details
- [ ] SSL certificates (for production HTTPS)
- [ ] Phone prefix configuration (e.g., "0034" for Spain)

### Configuration
- [ ] Set base API URL
- [ ] Configure WebAlvarez service endpoints
- [ ] Set timezone/locale (Spanish language support)
- [ ] Configure LOPD/privacy settings
- [ ] Setup error logging and monitoring
- [ ] Configure retry policies

### Integration Points
- [ ] Implement table sync (CambiosPendientes cycle)
- [ ] Setup customer management (POST/PUT /cliente/)
- [ ] Implement order creation (POST /pedido-cliente/)
- [ ] Configure order status updates (WebAlvarez.insertDatos)
- [ ] Setup SMS notifications (SMSServer.sendSMS)
- [ ] Implement inventory sync (stock-central)
- [ ] Configure promotional bonuses (bono, generacion-bono)
- [ ] Setup email notifications (notificacion-central)

### Testing
- [ ] Test with sample customer data
- [ ] Verify order creation flow
- [ ] Test payment processing
- [ ] Verify SMS sending
- [ ] Test inventory updates
- [ ] Validate error handling
- [ ] Load test (concurrent orders)
- [ ] Test all error codes

### Deployment
- [ ] Environment-specific configuration
- [ ] Database backups configured
- [ ] Monitoring/alerting setup
- [ ] Rate limiting configured
- [ ] Security review complete
- [ ] Documentation updated
- [ ] Team training completed

---

## Key Integration Considerations

### LOPD Compliance
- All customers must accept LOPD (Ley Orgánica de Protección de Datos)
- Field: `cliente_faceptacion_lopd` (required for order creation)
- Cannot insert customer without LOPD acceptance
- Options: Informational marketing, Data to third parties, Legitimate interest

### Identification
- CIF (Código de Identificación Fiscal) or DNI required for invoicing
- Email uniqueness within system
- Phone prefix configuration (international dialing codes)

### Catalog Subscriptions
- Customers must belong to at least 1 catalog
- Multiple catalog support (comma-separated IDs)
- Track subscription dates (fsuscripcion)
- Support for catalog-specific content/offerings

### Stock Management
- Two types: Central stock (shared) vs. Location-specific
- Stock synchronization prevents overselling
- Support for reserved quantities
- Real-time visibility in Web Álvarez

### Order Processing
- Support for item orders and lottery orders
- Multiple shipping options
- Payment method validation
- Gift wrapping and personalized messages
- Priority/urgency flags

---

**Last Updated**: November 30, 2025
**Version**: 1.0
**Status**: Production Ready ✅
