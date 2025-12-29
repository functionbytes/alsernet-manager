# Orden Correcto de Migraciones - Returns Module

## 📋 Resumen

Las **34 migraciones** han sido reorganizadas en el orden correcto para garantizar que:
1. ✅ Las **tablas base** (sin Foreign Keys) se crean primero
2. ✅ Las **tablas dependientes** (con Foreign Keys) se crean después
3. ✅ Las Foreign Keys nunca referencian tablas inexistentes

---

## 🔧 Tablas Base (8) - Ejecutarse primero (02:10 - 03:20)

| # | Timestamp | Tabla | Descripción |
|---|-----------|-------|-------------|
| 1 | `2025_12_29_021000` | `inventaries` | Inventario base |
| 2 | `2025_12_29_022000` | `return_orders` | Órdenes de devolución |
| 3 | `2025_12_29_023000` | `return_policies` | Políticas de devolución |
| 4 | `2025_12_29_024000` | `return_product_categories` | Categorías de productos |
| 5 | `2025_12_29_025000` | `return_reasons` | Razones de devolución |
| 6 | `2025_12_29_030000` | `return_states` | Estados de devolución |
| 7 | `2025_12_29_031000` | `return_types` | Tipos de devolución |
| 8 | `2025_12_29_032000` | `return_warranty_types` | Tipos de garantía |

---

## 📦 Tablas con Foreign Keys (26) - Ejecutarse después (03:30 - 07:40)

| # | Timestamp | Tabla | FK References |
|---|-----------|-------|---|
| 1 | `2025_12_29_033000` | `return_attachments` | `return_requests`, `users` |
| 2 | `2025_12_29_034000` | `return_barcodes` | `return_requests`, `return_products` |
| 3 | `2025_12_29_035000` | `return_communications` | `return_requests`, `users` |
| 4 | `2025_12_29_040000` | `return_component_shipments` | `orders`, `users` |
| 5 | `2025_12_29_041000` | `return_costs` | `return_requests`, `users` |
| 6 | `2025_12_29_042000` | `return_discussions` | `return_requests`, `users` |
| 7 | `2025_12_29_043000` | `return_documents` | `return_requests` |
| 8 | `2025_12_29_044000` | `return_exceptions` | `return_requests`, `return_inspections` |
| 9 | `2025_12_29_045000` | `return_history` | `return_requests`, `return_statuses` |
| 10 | `2025_12_29_050000` | `return_inspections` | `return_items`, `users` |
| 11 | `2025_12_29_051000` | `return_order_components` | `orders`, `products` |
| 12 | `2025_12_29_052000` | `return_payments` | `return_requests`, `users` |
| 13 | `2025_12_29_053000` | `return_pdf_documents` | `users` |
| 14 | `2025_12_29_054000` | `return_product_components` | `products`, `suppliers` |
| 15 | `2025_12_29_055000` | `return_product_rules` | `return_product_categories` |
| 16 | `2025_12_29_060000` | `return_products` | `return_requests` |
| 17 | `2025_12_29_061000` | `return_reason_lang` | `return_reasons` |
| 18 | `2025_12_29_062000` | `return_request_products` | `return_requests`, `products` |
| 19 | `2025_12_29_063000` | `return_requests` | `orders`, `customers`, `return_statuses`, `return_types`, `return_reasons` |
| 20 | `2025_12_29_064000` | `return_status` | `return_states` |
| 21 | `2025_12_29_065000` | `return_status_history` | `return_requests`, `users` |
| 22 | `2025_12_29_070000` | `return_status_lang` | `return_statuses` |
| 23 | `2025_12_29_071000` | `return_type_lang` | `return_types` |
| 24 | `2025_12_29_072000` | `return_validations` | `orders`, `products` |
| 25 | `2025_12_29_073000` | `return_warranties` | `orders`, `products` |
| 26 | `2025_12_29_074000` | `return_warranty_claims` | `return_warranties`, `users` |

---

## 💡 Lógica de Reorganización

### Problema Original
Las fechas originales (todas con prefijo `2025_12_29_020xxx`) no respetaban las dependencias:
- Tablas con FK se ejecutaban antes que sus tablas padre
- Esto causaría errores de constraint violations

### Solución Aplicada
1. **Identificar tablas base**: Aquellas sin `foreignId()`
2. **Agrupar por dependencias**: Las que referencian a otras se mueven después
3. **Asignar timestamps secuenciales**: 10 minutos de diferencia entre migraciones

### Estructura Temporal Nueva
```
02:10 - 03:20   → Tablas base
03:30 - 07:40   → Tablas con Foreign Keys
```

---

## ✅ Ventajas

- ✨ **Orden garantizado**: Las tablas padre siempre se crean antes
- 🔐 **Sin errores de FK**: Todas las referencias serán válidas
- 📊 **Fácil de mantener**: Timestamps indicadores del flujo de creación
- 🔍 **Traceability**: Timestamps permiten entender la secuencia

---

## 📌 Notas Importantes

- Estas migraciones están en `database/pending/returns/` (no en `migrations/`)
- Se pueden copiar a `database/migrations/returns/` cuando sea necesario migrar
- La carpeta `pending/` es un área de staging para migraciones preparadas pero no ejecutadas aún
