# ERP Integration Documentation Index

**Complete knowledge base for Gestión ERP and Web Álvarez integration**

---

## 📚 Documentation Files

### 1. **erp-integration-overview.md** ⭐ START HERE
   - System architecture and components
   - Integration flow diagrams
   - Data flow processes
   - Configuration requirements
   - Implementation checklist
   - **Best for**: Understanding the system, architecture decisions, planning

### 2. **erp-api-endpoints.md** 🔌 CORE API REFERENCE
   - Complete REST API endpoint documentation
   - 14 main endpoints with detailed parameters
   - Request/response examples for each endpoint
   - Error codes and error handling
   - **Best for**: Implementing API calls, debugging integration issues, parameter validation

### 3. **erp-sync-tables.md** 📊 DATA STRUCTURES
   - 50+ synchronized table definitions
   - Field names, types, and descriptions
   - Table categories (business, pricing, products, configuration)
   - Data relationships
   - **Best for**: Understanding data structures, database schema, table relationships

### 4. **erp-xmlrpc-services.md** 📨 LEGACY SERVICES
   - XML-RPC service documentation
   - WebAlvarez.insertDatos (order updates)
   - SMSServer.sendSMS (notifications)
   - Python and PHP implementation examples
   - XML message formatting and Base64 encoding
   - **Best for**: Legacy integrations, order updates, SMS notifications

---

## 🎯 Quick Navigation by Task

### "I need to create a customer order"
→ Read: **erp-api-endpoints.md** - Section 6 (pedido-cliente)
→ Understand: **erp-integration-overview.md** - Data Flow section
→ Reference: **erp-sync-tables.md** - v_sinc_w_producto

### "I need to sync inventory from Gestión"
→ Read: **erp-integration-overview.md** - Table Synchronization Flow
→ Implement: **erp-api-endpoints.md** - Sections 1-3 (CambiosPendientes, TransaccionPendiente, ConfirmarTransaccion)
→ Reference: **erp-sync-tables.md** - v_sinc_stock_central_web

### "I need to query a customer's information"
→ Read: **erp-api-endpoints.md** - Section 4 (cliente)
→ Reference: **erp-integration-overview.md** - LOPD Compliance section
→ Example: **erp-xmlrpc-services.md** - Python examples

### "I need to send an order update to Web Álvarez"
→ Read: **erp-xmlrpc-services.md** - Section 1 (WebAlvarez.insertDatos)
→ Understand: **erp-sync-tables.md** - Core Business Tables
→ Implement: Code examples in **erp-xmlrpc-services.md**

### "I need to send SMS notifications"
→ Read: **erp-xmlrpc-services.md** - Section 2 (SMSServer.sendSMS)
→ Implement: Python/PHP code examples provided
→ Best practices: **erp-integration-overview.md** - Implementation Checklist

### "I need to understand the promotional bonus system"
→ Read: **erp-api-endpoints.md** - Section 8 (bono)
→ Reference: **erp-sync-tables.md** - v_sinc_tbono_promocion, v_sinc_w_modelo
→ Implementation: **erp-xmlrpc-services.md** - Workflow section

### "I need to manage gift cards/vouchers"
→ Read: **erp-api-endpoints.md** - Section 14 (vale)
→ Understand states: **erp-api-endpoints.md** - Error codes section
→ Reference: **erp-sync-tables.md** - Gift card related tables

---

## 📋 API Endpoints Summary

### Table Synchronization (3 endpoints)
| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/CambiosPendientes/` | GET | List pending transactions |
| `/TransaccionPendiente/` | GET | Get transaction details |
| `/ConfirmarTransaccion/` | GET | Mark as synchronized |

### Customer & Order Management (3 endpoints)
| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/cliente/` | GET, POST, PUT | Customer CRUD + LOPD |
| `/clientecatalogo/` | GET, POST, DELETE | Catalog subscriptions |
| `/pedido-cliente/` | GET, POST | Order CRUD |

### Inventory & Articles (2 endpoints)
| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/stock-central-web/` | GET | Query free stock |
| `/articulo/` | GET | Article lookup |

### Promotional System (3 endpoints)
| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/bono/` | GET, PUT | Bonus query/update |
| `/generacion-bono/` | POST | Bonus generation |
| `/vale/` | GET, PUT, POST | Gift card management |

### Notifications & Config (3 endpoints)
| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/notificacion-central/` | POST | Email notifications |
| `/cambio_publicidad_email/` | GET | Email preference changes |
| `/albaran-cliente/` | GET | Invoice/shipment records |

### Other (2 endpoints)
| Endpoint | Method | Purpose |
|----------|--------|---------|
| **WebAlvarez.insertDatos** | XML-RPC | Order updates |
| **SMSServer.sendSMS** | XML-RPC | SMS notifications |

---

## 🔑 Key Concepts

### LOPD (Ley Orgánica de Protección de Datos)
- Spanish data protection law compliance requirement
- **All customers must accept LOPD** to create orders
- Three consent levels:
  - `cliente_faceptacion_lopd` - General acceptance (required)
  - `cliente_no_info_comercial` - Opt out of marketing
  - `cliente_no_datos_a_terceros` - Opt out of data sharing
- See: **erp-api-endpoints.md** - Section 4.2

### CIF/DNI
- Tax ID (CIF = Spanish company ID)
- DNI = Spanish personal ID
- **Required for invoice generation** (`cliente_cif`)
- Must be unique or force creation

### Transaction-Based Sync
1. Changes tracked in `CambiosPendientes`
2. Fetched via `TransaccionPendiente`
3. Applied to Web Álvarez
4. Confirmed via `ConfirmarTransaccion`
- See: **erp-integration-overview.md** - Data Flow section

### Base64 Encoding
- **All string values in XML-RPC must be Base64-encoded**
- Handles special characters, non-ASCII text
- PHP: `base64_encode($value)`
- Python: `base64.b64encode(value.encode()).decode()`

---

## 🔧 Configuration

### Base API URL
```
http://interges:8080/api-gestion/
```

### XML-RPC Services
```
WebAlvarez: 192.168.1.6:8081 (WebAlvarez.insertDatos)
SMSServer: 213.134.40.126:8080 (SMSServer.sendSMS)
```

### Response Format
- **REST API**: XML
- **XML-RPC**: XML
- **DateTime**: ISO 8601 (e.g., 2024-01-15T14:30:00)

### Rate Limiting
- No explicit rate limiting mentioned
- Best practice: Implement exponential backoff for retries
- Batch operations when possible

---

## ⚠️ Important Constraints

1. **LOPD Acceptance Required**
   - Cannot create orders without LOPD acceptance
   - Cannot update customer without LOPD date

2. **Catalog Requirement**
   - Customer must belong to at least 1 catalog
   - Cannot subscribe to non-existent catalogs

3. **Payment Method Validation**
   - Payment method must exist in system
   - Amount must match order total (or be a component)

4. **Article Existence**
   - All product codes must exist in Gestión
   - Price validation checks product tariff

5. **Stock Checking**
   - Products hidden if stock < threshold
   - Stock is non-reserved inventory only

6. **Duplicate Prevention**
   - Email uniqueness enforced (can force)
   - CIF uniqueness enforced (can force)
   - Order identifiers must be unique per origin

---

## 📊 Data Model Overview

### Customer
- Identification: idcliente, idcliente_gestion, codigo_internet
- Personal: nombre, apellidos, cif/dni
- Contact: email, telefonos (primary + secondary)
- Preferences: idioma, zona_fiscal, genero
- Privacy: LOPD consent, marketing flags
- Catalogs: Multiple catalog subscriptions
- Quotas: Service subscriptions (optional)

### Order
- Identification: idpedidocli, npedidocli, identificador_origen
- Header: fecha_pedido, estado, total_con_impuestos
- Lines: Multiple products with quantities, prices, discounts
- Shipping: Destinatario, direccion, transportista, coste
- Payment: Multiple payment methods with amounts
- Customer: Inline customer data or reference

### Product
- Identification: idarticulo, codigo, referencia
- Presentation: id_modelo, imagen, descripcion
- Pricing: precio, tarifa (quantity-based)
- Availability: stock, vendible
- Classification: categoria, familia, subfamilia
- Extras: ean13, upc, peso, dimensiones

---

## 🚀 Getting Started

1. **Read** `erp-integration-overview.md` (15 min)
   - Understand architecture and concepts

2. **Review** `erp-api-endpoints.md` (30 min)
   - Familiarize with all endpoints

3. **Study** relevant tables in `erp-sync-tables.md` (20 min)
   - Focus on tables you'll use

4. **Implement** REST endpoints (your task)
   - Start with customer query (GET /cliente/)
   - Then order creation (POST /pedido-cliente/)

5. **Test** with provided examples
   - Use test data provided

6. **Deploy** to production
   - Follow checklist in overview

---

## 📞 Support Resources

- **Architecture Questions**: See `erp-integration-overview.md`
- **API Implementation**: See `erp-api-endpoints.md`
- **Data Structure Questions**: See `erp-sync-tables.md`
- **Legacy Integration**: See `erp-xmlrpc-services.md`
- **Error Codes**: All documents have error code sections

---

## 📝 Document Statistics

| File | Size | Sections | Topics |
|------|------|----------|--------|
| erp-integration-overview.md | ~8KB | 8 | Architecture, flows, checklists |
| erp-api-endpoints.md | ~25KB | 14 | All REST endpoints with examples |
| erp-sync-tables.md | ~20KB | 50+ | Complete table definitions |
| erp-xmlrpc-services.md | ~15KB | 2 | XML-RPC services + code examples |
| **TOTAL** | **~68KB** | **70+** | **Complete ERP integration** |

---

## 🎓 Learning Path

**Beginner** (Getting Started)
1. erp-integration-overview.md (all sections)
2. erp-api-endpoints.md (sections 1-3, 4.1)
3. Basic customer query implementation

**Intermediate** (Full Implementation)
1. Complete erp-api-endpoints.md
2. erp-sync-tables.md (focus sections)
3. Full order creation flow

**Advanced** (Optimization & Legacy)
1. erp-xmlrpc-services.md (both services)
2. Error handling strategies
3. Performance optimization

---

**Last Updated**: November 30, 2025
**Version**: 1.0
**Status**: Production Ready ✅

---

## Quick Links
- [Overview](./erp-integration-overview.md)
- [API Endpoints](./erp-api-endpoints.md)
- [Sync Tables](./erp-sync-tables.md)
- [XML-RPC Services](./erp-xmlrpc-services.md)
