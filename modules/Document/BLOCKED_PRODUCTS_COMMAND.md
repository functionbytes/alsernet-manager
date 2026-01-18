# Create Blocked Product Documents Command

## Overview
Este comando crea automáticamente documentos para órdenes de Prestashop que tienen productos con bloqueos de documentación registrados.

## Lógica del Comando

### Flujo de Funcionamiento
1. **Obtiene el último `order_id` registrado** en la tabla `documents`
2. **Busca en Prestashop** todas las órdenes con `id_order > último_order_id`
3. **Para cada orden**:
   - Obtiene los productos de la orden
   - Verifica si algún producto tiene un bloqueo (`DocumentProductBlockade`) con `type_id`
   - Si encuentra bloqueo con tipo: **crea un documento**
   - Si no encuentra bloqueo: **salta la orden**
4. **Para cada documento creado**:
   - Asocia automáticamente todos los productos de la orden
   - Obtiene datos del cliente desde Prestashop
   - Establece estado inicial como "Awaiting Documents"

## Uso del Comando

### Básico (con confirmación)
```bash
php artisan app:create-blocked-product-documents
```

### Forzar ejecución sin confirmación
```bash
php artisan app:create-blocked-product-documents --force
```

### Limitar cantidad de órdenes a procesar
```bash
php artisan app:create-blocked-product-documents --force --limit=100
```

### Comenzar desde un order_id específico
```bash
php artisan app:create-blocked-product-documents --force --start-after=5000
```

### Combinar opciones
```bash
php artisan app:create-blocked-product-documents --force --limit=50 --start-after=5000
```

## Opciones del Comando

| Opción | Descripción |
|--------|-------------|
| `--force` | Salta la confirmación interactiva |
| `--limit=N` | Procesa máximo N órdenes |
| `--start-after=ID` | Comienza procesando desde order_id > ID |

## Resultados Esperados

El comando mostrará un resumen al finalizar:

```
📊 Results:
  ✓ Documents Created: 15
  ⊘ Skipped (no blockade): 45
  ⊘ Skipped (already exists): 2
  ⊘ Skipped (errors): 1
  Total Processed: 63
```

### Explicación de Resultados

- **Documents Created**: Documentos generados exitosamente
- **Skipped (no blockade)**: Órdenes sin productos con bloqueos
- **Skipped (already exists)**: Órdenes que ya tenían documento asociado
- **Skipped (errors)**: Errores durante el procesamiento

## Datos Asociados al Documento

Cuando se crea un documento, se incluye:

```php
- uid: Identificador único auto-generado (DOC-XXXXX...)
- type_id: Tipo de documento (del bloqueo del producto)
- order_id: ID de la orden en Prestashop
- order_reference: Referencia de la orden
- order_date: Fecha de la orden
- customer_id: ID del cliente en Prestashop
- customer_firstname: Nombre del cliente
- customer_lastname: Apellido del cliente
- customer_email: Email del cliente
- customer_dni: DNI/NIF del cliente (si está disponible)
- customer_company: Empresa del cliente (si está disponible)
- status_id: Awaiting Documents (pendiente de envío)
- validation_status: pending
- source_id: prestashop (fuente del documento)
- Todos los productos de la orden
```

## Relación con Bloqueos de Productos

El comando verifica la tabla `document_product_blockades`:

```
document_product_blockades
├── product_id: ID del producto en Prestashop
├── product_attribute_id: (opcional) Atributo específico del producto
├── document_type_id: ID del tipo de documento requerido
└── source_id: Fuente del bloqueo
```

Si un producto está bloqueado para un tipo de documento, se crea un documento de ese tipo.

## Ejemplo de Ejecución

```bash
$ php artisan app:create-blocked-product-documents --force --limit=20

🔄 Starting blocked product document creation...

Last registered order ID: 12500
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📍 Processing Prestashop orders for blocked products
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Found 20 new orders to process

[████████████████████] 20/20

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ PROCESSING COMPLETE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📊 Results:
  ✓ Documents Created: 12
  ⊘ Skipped (no blockade): 7
  ⊘ Skipped (already exists): 0
  ⊘ Skipped (errors): 1
  Total Processed: 20
```

## Ejecución Programada (Scheduled)

Si deseas ejecutar este comando automáticamente, agrega a `bootstrap/app.php` o en el provider correspondiente:

```php
// En tu service provider o bootstrap/app.php
$schedule->command('app:create-blocked-product-documents --force')
    ->daily()
    ->at('02:00')
    ->onSuccess(function () {
        // Notificar éxito
    })
    ->onFailure(function () {
        // Notificar error
    });
```

## Notas Importantes

1. **No elimina documentos**: Si un documento ya existe para una orden, lo salta
2. **Datos de Prestashop**: Conecta directamente a la BD de Prestashop via MySQL CLI
3. **Credenciales**: Las credenciales están hardcodeadas (considera usar variables de entorno)
4. **Idempotente**: Es seguro ejecutar varias veces - no crea duplicados
5. **Progreso en vivo**: Muestra una barra de progreso durante el procesamiento
6. **Manejo de errores**: Los errores se registran pero no detienen el procesamiento
