# Comando: Create Blocked Product Documents - RESUMEN EJECUTIVO

## 🎯 Objetivo
Crear automáticamente documentos para órdenes de Prestashop que tienen productos bloqueados que requieren documentación.

## 📊 Flujo de Funcionamiento

```
┌──────────────────────────────────────────────────────────────┐
│ 1. OBTENER ÚLTIMO ORDER_ID REGISTRADO                         │
│    SELECT MAX(order_id) FROM documents                        │
└────────────────────┬─────────────────────────────────────────┘
                     │
                     ▼
┌──────────────────────────────────────────────────────────────┐
│ 2. BUSCAR ÓRDENES EN PRESTASHOP                              │
│    WHERE id_order > último_order_id                          │
│    ORDER BY id_order ASC                                      │
└────────────────────┬─────────────────────────────────────────┘
                     │
                     ▼
        ┌────────────────────────────────┐
        │ PARA CADA ORDEN               │
        └────────────┬───────────────────┘
                     │
        ┌────────────▼───────────────────┐
        │ 3. OBTENER PRODUCTOS           │
        │    SELECT * FROM aalv_order_detail
        │    WHERE id_order = X           │
        └────────────┬───────────────────┘
                     │
        ┌────────────▼────────────────────┐
        │ 4. VERIFICAR BLOQUEOS           │
        │    ¿Producto tiene blockade +   │
        │     type_id?                    │
        │                                 │
        │    SI ──────────────┐           │
        │    NO ───────────┐  │           │
        └────────────┬────┼──┘           │
                     │    │              │
            ┌────────┘    │              │
            │             │              │
     SALTATE ORDEN    ┌────▼──────────────┐
                     │ 5. CREAR DOCUMENTO │
                     │    - UID único     │
                     │    - type_id       │
                     │    - Datos cliente │
                     │    - Productos    │
                     │    - Status=await │
                     └────────────────────┘
                             │
                     ┌───────▼────────────┐
                     │ DOCUMENTO CREADO   │
                     │ EXITOSAMENTE ✓     │
                     └────────────────────┘
```

## 🔧 Instalación

Ya está instalado en: `modules/Document/app/Console/Commands/CreateBlockedProductDocuments.php`

Registro: `modules/Document/app/Providers/DocumentsServiceProvider.php`

## 🚀 Uso Rápido

### Ejecución Simple (con confirmación)
```bash
php artisan app:create-blocked-product-documents
```

### Forzar Ejecución
```bash
php artisan app:create-blocked-product-documents --force
```

### Con Límite de Órdenes
```bash
php artisan app:create-blocked-product-documents --force --limit=50
```

### Comenzar Desde Order ID Específico
```bash
php artisan app:create-blocked-product-documents --force --start-after=10000
```

### Combinado (Más Usado)
```bash
php artisan app:create-blocked-product-documents --force --limit=100 --start-after=12500
```

## 📋 Datos del Documento Creado

```php
Document {
    uid: "DOC-ABCDEF123456"           // Único, auto-generado
    type_id: 3                         // Del bloqueo del producto
    order_id: 12501                    // De Prestashop
    order_reference: "PRES-001-2025"  // De Prestashop
    order_date: "2025-01-18 10:30:00" // De Prestashop

    customer_id: 5000                  // De Prestashop
    customer_firstname: "JUAN"         // De Prestashop
    customer_lastname: "PEREZ"         // De Prestashop
    customer_email: "juan@example.com" // De Prestashop
    customer_dni: "12345678A"          // De Prestashop (si existe)
    customer_company: "EMPRESA XYZ"    // De Prestashop (si existe)

    status_id: 2                       // Awaiting Documents
    validation_status: "pending"       // Pendiente de envío
    source_id: 3                       // Prestashop

    current_stage: 1                   // Etapa inicial
    total_stages: 1                    // Solo 1 etapa

    products: [                        // Todos los productos de la orden
        {
            product_id: 101,
            product_name: "FUSIL M4",
            product_reference: "CORTA-001",
            quantity: 1,
            price: 1500.00
        }
    ]
}
```

## ✅ Verificación de Bloqueos

El comando verifica esta tabla:

```
document_product_blockades
│
├── product_id: 101 ─────────┐
├── product_attribute_id: NULL
├── document_type_id: 3 ──────┼─► Requiere tipo "corta"
├── source_id: 1             │
└── created_at: 2025-01-18   │
                             │
                    ✓ BLOQUEO ACTIVO
                    → Crear documento de tipo "corta"
```

Si producto NO tiene blockade o blockade SIN type_id:
```
✗ NO CREA DOCUMENTO
→ Salta a siguiente orden
```

## 📊 Resultado de Ejecución

```
🔄 Starting blocked product document creation...

Last registered order ID: 12500
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📍 Processing Prestashop orders for blocked products
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Found 150 new orders to process
Limiting to 100 orders

[████████████████████████████] 100/100

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ PROCESSING COMPLETE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📊 Results:
  ✓ Documents Created: 42
  ⊘ Skipped (no blockade): 55
  ⊘ Skipped (already exists): 2
  ⊘ Skipped (errors): 1
  Total Processed: 100

⚠️  Errors encountered:
  • Order 12645: Failed to fetch customer data
```

## 🔑 Características Clave

✅ **Idempotente**: No crea duplicados si ejecutas varias veces
✅ **Progresivo**: Comienza desde el último order_id registrado
✅ **Configurable**: Límites, punto de inicio personalizables
✅ **Tolerante**: Los errores no detienen el procesamiento
✅ **Informativo**: Muestra barra de progreso y resumen detallado
✅ **Seguro**: Confirmación antes de ejecutar (puede forzarse con --force)
✅ **Integrado**: Datos desde Prestashop automáticamente
✅ **Documentado**: Incluye todos los datos del cliente y productos

## ⚠️ Notas Importantes

1. **Credenciales MySQL**: Están hardcodeadas en el comando
   - Host: `213.134.40.101`
   - Base: `alvarez_db`
   - Considera mover a variables de entorno

2. **Datos Prestashop**: Se obtienen directamente via MySQL CLI
   - Requiere acceso remoto a la BD
   - No usa API de Prestashop

3. **No elimina documentos**: Si existen, solo los salta
   - Es seguro ejecutar varias veces

4. **Ejecución programada**: Puede ejecutarse con scheduler
   ```php
   $schedule->command('app:create-blocked-product-documents --force')
       ->daily()
       ->at('03:00');
   ```

## 📝 Próximos Pasos

1. ✅ Comando creado y registrado
2. ✅ Documentación completa
3. ⏳ Ejecutar en ambiente de pruebas
4. ⏳ Verificar documentos creados en GUI
5. ⏳ Configurar ejecución programada (opcional)

## 🧪 Test de Prueba

```bash
# Procesar solo 5 órdenes para prueba
php artisan app:create-blocked-product-documents --force --limit=5

# Si deseas rollback: elimina documentos creados
# DELETE FROM documents WHERE created_at > NOW() - INTERVAL 1 HOUR;
```

---

**Creado**: 18 de Enero, 2025
**Versión**: 1.0
**Estado**: ✅ Listo para usar
