# CreateBlockedProductDocuments - Flujo Detallado Explicado

## 📋 Resumen del Flujo General

El comando sigue este flujo lógico:

```
1️⃣ OBTENER ÚLTIMO ORDER_ID
   ↓
2️⃣ TRAER ÓRDENES NUEVAS DE PRESTASHOP
   ↓
3️⃣ PARA CADA ORDEN - VALIDAR
   ├─ ¿Documento ya existe?
   ├─ ¿Tiene productos?
   ├─ ¿Los productos tienen bloqueo?
   └─ ✅ SÍ → Crear documento
       ❌ NO → Saltar
   ↓
4️⃣ MOSTRAR RESUMEN FINAL
```

---

## 🔍 EJEMPLO CONCRETO CON TUS NÚMEROS

### Escenario:
- **Último order_id en documents:** `809422`
- **Última orden en Prestashop:** `809500`

### Resultado esperado:
```
El comando traería las órdenes: 809423, 809424, ... 809500
Total de nuevas órdenes: 78 órdenes
```

---

## 📍 PASO 1: OBTENER ÚLTIMO ORDER_ID

### Código:
```php
private function getLastOrderId(): int
{
    $lastDocument = Document::whereNotNull('order_id')
        ->orderBy('order_id', 'desc')
        ->first();

    return $lastDocument?->order_id ?? 0;
}
```

### ¿Qué hace?
1. Busca en la tabla `documents`
2. Filtra solo documentos que TIENEN `order_id` (no nulos)
3. Los ordena de mayor a menor (`desc`)
4. Toma el PRIMERO (el más grande)
5. Retorna ese `order_id`

### Ejemplo:
```
SELECT * FROM documents
WHERE order_id IS NOT NULL
ORDER BY order_id DESC
LIMIT 1;

Resultado: order_id = 809422 ✅
```

---

## 📍 PASO 2: TRAER ÓRDENES DESDE PRESTASHOP

### En el método `processOrdersWithBlockedProducts()`:

```php
$prestashopOrders = $this->fetchPrestashopOrdersAfterOrderId($lastOrderId);
```

### Código que ejecuta:
```php
private function fetchPrestashopOrdersAfterOrderId(int $lastOrderId): array
{
    $config = config('prestashop');

    $query = "SELECT id_order, id_customer, id_lang, reference, date_add
              FROM aalv_orders
              WHERE id_order > {$lastOrderId}          // ← CLAVE: > (mayor que)
              ORDER BY id_order ASC";                  // ← Ordenar de menor a mayor

    try {
        $output = shell_exec("mysql -h {$config['host']} -u {$config['username']} ...");
        // ... procesar resultados ...
    }
}
```

### ¿Qué hace exactamente?

**Con tu ejemplo (lastOrderId = 809422):**

```sql
SELECT id_order, id_customer, id_lang, reference, date_add
FROM aalv_orders
WHERE id_order > 809422        -- Trae DESDE 809423 en adelante
ORDER BY id_order ASC;
```

### Retorna un array así:
```php
[
    ['id_order' => 809423, 'id_customer' => 5000, 'id_lang' => 1, 'reference' => 'PRES-809423', ...],
    ['id_order' => 809424, 'id_customer' => 5001, 'id_lang' => 1, 'reference' => 'PRES-809424', ...],
    ['id_order' => 809425, 'id_customer' => 5002, 'id_lang' => 1, 'reference' => 'PRES-809425', ...],
    ...
    ['id_order' => 809500, 'id_customer' => 5099, 'id_lang' => 1, 'reference' => 'PRES-809500', ...],
]
```

**Total: 78 órdenes nuevas** (809423 hasta 809500)

---

## 📍 PASO 3: PROCESAR CADA ORDEN (EL BUCLE)

Este es el corazón del comando:

```php
foreach ($prestashopOrders as $order) {
    try {
        // 3.1 - Verificar si documento ya existe
        $existingDocument = Document::where('order_id', $order['id_order'])->first();

        if ($existingDocument) {
            $skippedExists++;
            continue;  // ← Saltamos a siguiente orden
        }

        // 3.2 - Obtener productos de la orden
        $orderProducts = $this->fetchPrestashopOrderProducts($order['id_order']);

        if (empty($orderProducts)) {
            $skippedNoBlockade++;
            continue;  // ← Saltamos a siguiente orden
        }

        // 3.3 - Verificar si algún producto tiene bloqueo
        $blockadeInfo = $this->getProductBlockadeInfo($orderProducts);

        if (! $blockadeInfo) {
            $skippedNoBlockade++;
            continue;  // ← Saltamos a siguiente orden
        }

        // 3.4 - Crear documento
        if ($this->createDocument($order, $blockadeInfo, $orderProducts)) {
            $created++;
        } else {
            $errorCount++;
        }
    }
}
```

### Explicación detallada de cada paso del bucle:

---

### ✅ **3.1 - Verificar si documento ya existe**

```php
$existingDocument = Document::where('order_id', $order['id_order'])->first();

if ($existingDocument) {
    $skippedExists++;  // Contador +1
    $bar->advance();   // Actualizar barra de progreso
    continue;          // Ir a siguiente orden
}
```

**¿Por qué?** Si el documento ya existe, no queremos crear duplicados.

**Ejemplo:**
```
Orden 809423 → ¿Existe documento? NO → Continuar
Orden 809424 → ¿Existe documento? SÍ → SALTAR (skippedExists++)
Orden 809425 → ¿Existe documento? NO → Continuar
```

---

### ✅ **3.2 - Obtener productos de la orden**

```php
$orderProducts = $this->fetchPrestashopOrderProducts($order['id_order']);
```

Ejecuta en Prestashop:
```sql
SELECT product_id, product_name, product_reference, product_quantity, product_price
FROM aalv_order_detail
WHERE id_order = 809423;
```

**Retorna un array con los productos:**
```php
[
    [
        'id_product' => 101,
        'product_name' => 'FUSIL M4',
        'product_reference' => 'CORTA-001',
        'product_quantity' => 1,
        'product_price' => 1500.00
    ],
    [
        'id_product' => 102,
        'product_name' => 'MUNICIÓN 9MM',
        'product_reference' => 'AMMO-001',
        'product_quantity' => 100,
        'product_price' => 50.00
    ]
]
```

**Si está vacío (0 productos):**
```php
if (empty($orderProducts)) {
    $skippedNoBlockade++;  // Contador +1
    continue;              // Ir a siguiente orden
}
```

---

### ✅ **3.3 - Verificar si algún producto tiene BLOQUEO**

Este es el validador crítico:

```php
private function getProductBlockadeInfo(array $products): ?array
{
    $typeMap = DocumentType::pluck('id', 'slug')->toArray();

    foreach ($products as $product) {
        // Buscar en tabla document_product_blockades
        $blockades = DocumentProductBlockade::where('product_id', $product['id_product'])
            ->with('documentType')
            ->get();

        foreach ($blockades as $blockade) {
            // ✅ CLAVE: Tiene document_type_id + documentType
            if ($blockade->document_type_id && $blockade->documentType) {
                return [
                    'product_id' => $product['id_product'],
                    'product_name' => $product['product_name'],
                    'blockade_type_slug' => $blockade->documentType->slug,
                    'blockade_type_id' => $blockade->document_type_id,
                ];
            }
        }
    }

    return null;  // Ningún producto tiene bloqueo
}
```

**¿Qué busca en `document_product_blockades`?**

Para cada producto en la orden:
1. ¿Existe un bloqueo para este `product_id`?
2. ¿El bloqueo tiene un `document_type_id` asignado?
3. ¿El tipo de documento existe?

**Ejemplo con orden 809423:**

```
Orden 809423 tiene 2 productos:

┌─ Producto 101 (FUSIL M4)
│  ├─ Búsqueda: SELECT * FROM document_product_blockades WHERE product_id = 101
│  ├─ Resultado:
│  │  ├─ document_type_id: 3 (tipo "corta")
│  │  ├─ documentType existe: SÍ ✅
│  │  └─ ¡BLOQUEO ENCONTRADO!
│  └─ Retorna:
│     {
│       'product_id': 101,
│       'blockade_type_id': 3,
│       'blockade_type_slug': 'corta'
│     }
│
└─ Nunca llegamos a producto 102 (porque ya encontramos bloqueo)
```

**Si NO hay bloqueo:**
```php
if (! $blockadeInfo) {
    $skippedNoBlockade++;  // Contador +1
    continue;              // Ir a siguiente orden
}
```

---

### ✅ **3.4 - CREAR DOCUMENTO**

Si pasó todas las validaciones anteriores, crear el documento:

```php
if ($this->createDocument($order, $blockadeInfo, $orderProducts)) {
    $created++;  // Documento creado exitosamente
} else {
    $errorCount++;  // Error al crear
}
```

**El método `createDocument()` hace:**

```php
private function createDocument(array $order, array $blockadeInfo, array $orderProducts): bool
{
    try {
        $config = config('prestashop');

        // Datos del cliente (si existe)
        $customerData = [];
        if ($order['id_customer']) {
            $customerData = $this->fetchPrestashopCustomer($order['id_customer']);
        }

        // Generar UID único
        $uid = $this->generateDocumentUid();

        // Preparar datos del documento
        $documentData = [
            'uid' => $uid,
            'type_id' => $blockadeInfo['blockade_type_id'],  // ← Del bloqueo
            'source_id' => 3,  // Prestashop
            'order_id' => $order['id_order'],
            'order_reference' => $order['reference'] ?? null,
            'order_date' => $order['date_add'] ?? null,
            'customer_id' => $order['id_customer'] ?? null,
            'customer_firstname' => $customerData['firstname'] ?? null,
            'customer_lastname' => $customerData['lastname'] ?? null,
            'customer_email' => $customerData['email'] ?? null,
            'customer_dni' => $customerData['vat_number'] ?? null,
            'customer_company' => $customerData['company'] ?? null,
            'status_id' => 2,  // Awaiting Documents
            'validation_status' => 'pending',
            'current_stage' => 1,
            'total_stages' => 1,
        ];

        // ✅ CREAR DOCUMENTO
        $document = Document::create($documentData);

        // Asociar TODOS los productos de la orden
        foreach ($orderProducts as $product) {
            $document->products()->create([
                'product_id' => $product['id_product'],
                'product_name' => strtoupper($product['product_name'] ?? ''),
                'product_reference' => strtoupper($product['product_reference'] ?? ''),
                'quantity' => $product['product_quantity'],
                'price' => $product['product_price'],
            ]);
        }

        return true;  // ✅ Éxito

    } catch (\Exception $e) {
        \Log::error("Failed to create document: {$e->getMessage()}");
        return false;  // ❌ Error
    }
}
```

**¿Qué crea exactamente?**

Para la orden 809423:
```
Documento:
├─ UID: DOC-ABCDEF123456
├─ type_id: 3 (corta)
├─ order_id: 809423
├─ order_reference: PRES-809423
├─ customer_firstname: JUAN
├─ customer_lastname: PEREZ
├─ customer_email: juan@example.com
├─ status_id: 2 (Awaiting Documents)
└─ Productos Asociados:
   ├─ Producto 101 (FUSIL M4, cantidad: 1, precio: 1500.00)
   └─ Producto 102 (MUNICIÓN 9MM, cantidad: 100, precio: 50.00)
```

---

## 📍 PASO 4: MOSTRAR RESUMEN FINAL

Después de procesar todas las órdenes, muestra estadísticas:

```php
$this->info('📊 Results:');
$this->info("  ✓ Documents Created: {$stats['created']}");
$this->warn("  ⊘ Skipped (no blockade): {$stats['skipped_no_blockade']}");
$this->warn("  ⊘ Skipped (already exists): {$stats['skipped_exists']}");
$this->warn("  ⊘ Skipped (errors): {$stats['errors']}");
$this->info("  Total Processed: {$stats['total']}");
```

**Ejemplo de salida:**
```
📊 Results:
  ✓ Documents Created: 23
  ⊘ Skipped (no blockade): 42
  ⊘ Skipped (already exists): 12
  ⊘ Skipped (errors): 1
  Total Processed: 78
```

---

## 🎯 DIAGRAMA DE DECISIÓN COMPLETO

```
┌─────────────────────────────────────────┐
│ INICIO: orden 809423                    │
└──────────────────┬──────────────────────┘
                   │
        ┌──────────▼──────────┐
        │ ¿Documento existe?  │
        └─────────┬──────────┘
          SÍ ────►├─► SKIPEAR (skippedExists++)
                  │
          NO ◄────┘
                   │
        ┌──────────▼────────────────────┐
        │ Obtener productos de orden    │
        │ (FUSIL M4, MUNICIÓN 9MM)      │
        └─────────┬──────────────────────┘
                   │
        ┌──────────▼──────────────┐
        │ ¿Tiene productos?       │
        └─────────┬──────────────┘
          NO ────►├─► SKIPEAR (skippedNoBlockade++)
                  │
          SÍ ◄────┘
                   │
        ┌──────────▼────────────────────────────┐
        │ Verificar cada producto               │
        │ - FUSIL M4 (id:101)                   │
        │   └─ ¿Tiene bloqueo? SÍ (tipo: corta)│
        │     └─► ENCONTRADO → Salir del loop  │
        └──────────┬─────────────────────────────┘
                   │
        ┌──────────▼──────────────┐
        │ ¿Tiene bloqueo?         │
        └─────────┬──────────────┘
          NO ────►├─► SKIPEAR (skippedNoBlockade++)
                  │
          SÍ ◄────┘
                   │
        ┌──────────▼─────────────────────┐
        │ Crear Documento                 │
        │ - UID: DOC-ABC123              │
        │ - type_id: 3 (corta)           │
        │ - order_id: 809423             │
        │ - Productos asociados: 2       │
        └──────────┬─────────────────────┘
                   │
        ┌──────────▼──────────────────┐
        │ ¿Creación exitosa?          │
        └─────────┬──────────────────┘
          SÍ ────►├─► created++
          NO ────►├─► errorCount++
                   │
        ┌──────────▼──────────────────┐
        │ Siguiente orden (809424)     │
        │ (Repetir proceso)            │
        └──────────────────────────────┘
```

---

## 💡 RESUMEN EN PALABRAS SIMPLES

**¿Qué hace el comando?**

1. **Encuentra el último number:** Mira en `documents` cuál es el `order_id` más grande (809422)

2. **Trae órdenes nuevas:** Va a Prestashop y dice "dame todas las órdenes mayores a 809422" → Obtiene 809423, 809424, ... 809500

3. **Valida cada orden:**
   - ¿Ya existe documento? → Saltar
   - ¿Tiene productos? → Saltar si no
   - ¿Algún producto tiene bloqueo? → Saltar si no
   - ✅ Si todo OK → Crear documento

4. **Crea el documento** con:
   - El tipo del bloqueo encontrado
   - Todos los datos de la orden (cliente, referencia, fecha)
   - Todos los productos asociados

5. **Muestra resumen:** Cuántos se crearon, cuántos se saltaron y por qué

---

## 🔑 PUNTOS CLAVE A RECORDAR

| Concepto | Explicación |
|----------|------------|
| **lastOrderId** | El order_id MÁS GRANDE registrado actualmente |
| **> $lastOrderId** | Búsqueda comienza DESDE el siguiente número |
| **getProductBlockadeInfo()** | Función que valida si hay bloqueo |
| **document_product_blockades** | Tabla que define qué productos requieren documentos |
| **type_id** | El ID del tipo de documento requerido por el bloqueo |
| **createDocument()** | Crea el documento CON todos los productos de la orden |

---

**Versión:** 1.0
**Fecha:** 18 de Enero, 2025
**Autor:** Claude Code
