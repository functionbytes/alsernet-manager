# 📦 SISTEMA DE INVENTORY SLOTS - DOCUMENTACIÓN COMPLETA

**Fecha:** 17 de Noviembre de 2025
**Versión:** 1.0
**Autor:** Análisis Automático - Claude Code

---

## 📑 ÍNDICE

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Estructura Jerárquica](#estructura-jerárquica)
3. [Modelos y Características](#modelos-y-características)
4. [Controladores Principales](#controladores-principales)
5. [Rutas y Endpoints](#rutas-y-endpoints)
6. [Migraciones de Base de Datos](#migraciones-de-base-de-datos)
7. [Flujos de Uso Típicos](#flujos-de-uso-típicos)
8. [Relaciones y Constraints](#relaciones-y-constraints)
9. [Patrones y Lógica Importante](#patrones-y-lógica-importante)
10. [Vistas Asociadas](#vistas-asociadas)
11. [Métodos por Funcionalidad](#métodos-por-funcionalidad)
12. [Integración con Productos](#integración-con-productos)
13. [Flujo Completo de Ejemplo](#flujo-completo-de-ejemplo)
14. [Conclusión](#conclusión)

---

## 1. RESUMEN EJECUTIVO

El sistema de **Inventory Slots** es un subsistema del módulo Warehouse que gestiona las posiciones físicas dentro de las estanterías del almacén. Implementa una arquitectura jerárquica de almacenamiento: **Pisos (Floors) → Estanterías (Stands) → Posiciones (Inventory Slots)**, donde cada posición es un contenedor específico para almacenar productos con control de cantidad y peso.

### Características Principales
- ✅ Gestión granular de posiciones individuales en almacén
- ✅ Control dual de capacidad (cantidad y peso)
- ✅ Validaciones automáticas antes de operaciones
- ✅ Rastro de movimientos para auditoría
- ✅ Visualización interactiva del almacén
- ✅ API REST para operaciones de inventario
- ✅ Búsquedas complejas con scopes reutilizables
- ✅ Integridad referencial con constraints

---

## 2. ESTRUCTURA JERÁRQUICA

```
WAREHOUSE (Almacén Completo)
    │
    ├── FLOOR (Piso/Planta)
    │   └── STAND (Estantería)
    │       ├── STYLE (Estilo/Tipo de Estantería)
    │       └── INVENTORY_SLOT (Posición Individual)
    │           └── PRODUCT (Producto Almacenado)
```

### Niveles de Granularidad

| Nivel | Entidad | Cantidad Típica | Función |
|-------|---------|-----------------|---------|
| 1 | Warehouse | 1 | Contenedor principal |
| 2 | Floor | 2-3 | Pisos del almacén (P1, P2, Sótano) |
| 3 | Stand | 15-30 | Estanterías por piso |
| 4 | Inventory Slot | 30-100+ | Posiciones por estantería |
| 5 | Product | Múltiples | Productos en posiciones |

---

## 3. MODELOS Y CARACTERÍSTICAS

### 3.1 MODEL: InventorySlot

**Ubicación:** `app/Models/Warehouse/InventorySlot.php`

**Propósito:** Representa una posición concreta dentro de una estantería. Es la unidad más pequeña del almacén donde se almacenan productos.

#### Estructura de Base de Datos

```sql
Tabla: warehouse_inventory_slots

Campos principales:
├── id (bigint)                    Identificador único
├── uid (uuid)                     Identificador universal para URLs/APIs
├── stand_id (FK)                  Referencia a la estantería
├── product_id (FK, nullable)      Producto almacenado (puede estar vacía)
├── face (enum)                    left | right | front | back
├── level (int)                    Nivel vertical (1=arriba, aumenta hacia abajo)
├── section (int)                  Sección horizontal (1=izquierda, aumenta hacia derecha)
├── barcode (string, nullable)     Código de barras único
├── quantity (int)                 Cantidad actual del producto
├── max_quantity (int, nullable)   Máximo permitido
├── weight_current (decimal)       Peso actual en kg
├── weight_max (decimal, nullable) Peso máximo permitido
├── is_occupied (boolean)          Cache para búsquedas rápidas
└── last_movement (timestamp)      Última operación
```

#### Relaciones

```php
belongsTo(Stand)      // Una posición pertenece a una estantería
belongsTo(Product)    // Puede contener un producto (nullable)
```

#### Scopes (Consultas Reutilizables)

```php
->occupied()                        // Solo posiciones ocupadas
->available()                       // Solo posiciones libres
->byStand($standId)                // Por estantería
->byProduct($productId)            // Por producto
->byFace($face)                    // Por cara (left, right, front, back)
->byLevel($level)                  // Por nivel
->byBarcode($barcode)              // Por código de barras
->search($search)                  // Búsqueda por barcode/uid
->nearWeightCapacity($threshold)   // Cerca del límite de peso (default 90%)
->overCapacity()                   // Excede capacidad de peso
->overQuantity()                   // Excede cantidad máxima
```

#### Métodos de Información

```php
getAddress()                    // "PASILLO13A / Izquierda / Nivel 2 / Sección 3"
getFaceLabel()                  // Convierte "left" → "Izquierda"
isOccupied()                    // ¿Está ocupada?
isAvailable()                   // ¿Está libre?
getAvailableQuantity()          // Cantidad que falta para llenar
getAvailableWeight()            // Peso que falta para llenar
getWeightPercentage()           // % ocupación por peso
getQuantityPercentage()         // % ocupación por cantidad
getFullInfo()                   // Retorna array con toda la información
getSummary()                    // Retorna array resumido
```

#### Métodos de Validación

```php
canAddQuantity(int $amount)         // ¿Puede agregarse esta cantidad?
canAddWeight(float $weight)         // ¿Puede agregarse este peso?
isNearQuantityCapacity($threshold)  // ¿Cerca del límite de cantidad?
isNearWeightCapacity($threshold)    // ¿Cerca del límite de peso?
isOverQuantity()                    // ¿Excede cantidad máxima?
isOverWeight()                      // ¿Excede peso máximo?
```

#### Métodos de Operación

```php
addQuantity(int $amount)      // Suma cantidad (con validación)
subtractQuantity(int $amount) // Resta cantidad (con validación)
addWeight(float $weight)      // Suma peso (con validación)
subtractWeight(float $weight) // Resta peso (con validación)
clear()                        // Vacía completamente la posición
```

---

### 3.2 MODEL: Stand

**Ubicación:** `app/Models/Warehouse/Stand.php`

**Propósito:** Representa una estantería física concreta dentro del almacén.

#### Estructura de Base de Datos

```sql
Tabla: warehouse_stands

Campos principales:
├── id (bigint)               Identificador único
├── uid (uuid)                Identificador universal
├── floor_id (FK)             Piso donde se encuentra
├── stand_style_id (FK)       Tipo/estilo de estantería
├── code (string, unique)     PASILLO13A, ISLA02, etc
├── barcode (string, nullable, unique)  Código de barras físico
├── position_x, position_y    Coordenadas para visualización
├── position_z                Coordenada Z
├── total_levels (int)        Niveles totales (profundidad vertical)
├── total_sections (int)      Secciones totales (divisiones horizontales)
├── capacity (decimal)        Peso máximo permitido en toda la estantería
├── available (boolean)       ¿Está operativa?
└── notes (text)              Mantenimiento, daños, etc
```

#### Relaciones

```php
belongsTo(Floor)           // Pertenece a un piso
belongsTo(StandStyle)      // Tiene un estilo/tipo
hasMany(InventorySlot)     // Contiene muchas posiciones
```

#### Scopes

```php
->available()              // Solo estanterías activas
->byFloor($floorId)       // Por piso
->byCode($code)           // Por código
->byBarcode($barcode)     // Por código de barras
->byStyle($styleId)       // Por estilo
->search($search)         // Búsqueda general
->ordered()               // Ordenado por posición X, Y
```

#### Métodos Principales

```php
getFullName()                    // "PASILLO13A (Planta 1)"
getTotalSlots()                  // Número total de posiciones
getOccupiedSlots()              // Número de posiciones ocupadas
getAvailableSlots()             // Número de posiciones libres
getOccupancyPercentage()        // % de ocupación total
getTotalCapacity()              // Peso máximo total
getCurrentWeight()              // Peso actual sumado
getSlot(face, level, section)   // Obtiene una posición específica
getSlotsByFace(face)            // Todas las posiciones de una cara
getSlotsByLevel(level)          // Todas las posiciones de un nivel
getSummary()                    // Información resumida
isNearCapacity($threshold)      // ¿Cerca del límite de peso?
createSlots()                   // Crea todas las posiciones automáticamente
```

---

### 3.3 MODEL: Floor

**Ubicación:** `app/Models/Warehouse/Floor.php`

**Propósito:** Representa un piso/planta del almacén (ej: Planta 1, Sótano, etc).

#### Estructura de Base de Datos

```sql
Tabla: warehouse_floors

Campos principales:
├── id (bigint)               Identificador único
├── uid (uuid)                Identificador universal
├── code (string, unique)     P1, P2, S0, etc
├── name (string)             "Planta 1", "Sótano", etc
├── description (text)        Descripción
├── available (boolean)       ¿Está disponible?
└── order (int)               Orden visual
```

#### Relaciones

```php
hasMany(Stand)     // Contiene muchas estanterías
```

#### Scopes

```php
->available()      // Solo pisos disponibles
->ordered()        // Ordenado por orden y nombre
->byCode($code)    // Por código
->search($search)  // Búsqueda general
```

#### Métodos Principales

```php
getStandCount()               // Número total de estanterías
getAvailableStandCount()      // Número de estanterías activas
getTotalSlotsCount()          // Número total de posiciones en el piso
getOccupiedSlotsCount()       // Número de posiciones ocupadas
getOccupancyPercentage()      // % de ocupación del piso
getSummary()                  // Información resumida
```

---

### 3.4 MODEL: StandStyle

**Ubicación:** `app/Models/Warehouse/StandStyle.php`

**Propósito:** Define los tipos/estilos de estanterías disponibles.

#### Estructura de Base de Datos

```sql
Tabla: warehouse_stand_styles

Campos principales:
├── id (bigint)                    Identificador único
├── uid (uuid)                     Identificador universal
├── code (string, unique)          ROW, ISLAND, WALL
├── name (string)                  Nombre legible
├── description (text)             Descripción
├── faces (json array)             ["left", "right", "front", "back"]
├── default_levels (int)           Niveles por defecto
├── default_sections (int)         Secciones por defecto
└── available (boolean)            ¿Está disponible?
```

#### Relaciones

```php
hasMany(Stand)     // Muchas estanterías pueden tener este estilo
```

#### Constantes

```php
const TYPE_ROW = 'ROW'        // Pasillo lineal (frente y fondo)
const TYPE_ISLAND = 'ISLAND'  // Isla (360°, todas las caras)
const TYPE_WALL = 'WALL'      // Pared (solo una cara)

const FACE_LEFT = 'left'
const FACE_RIGHT = 'right'
const FACE_FRONT = 'front'
const FACE_BACK = 'back'
```

#### Métodos Principales

```php
getTypeName()              // "Pasillo Lineal", "Isla (360°)", etc
getFacesLabel()            // "Izquierda, Derecha"
hasValidFaces()            // ¿Todas las caras son válidas?
getStandCount()            // Número de estanterías de este estilo
getActiveStandCount()      // Número de estanterías activas
getSummary()               // Información resumida
```

---

## 4. CONTROLADORES PRINCIPALES

### 4.1 InventorySlotsController

**Ubicación:** `app/Http/Controllers/Managers/Warehouse/InventorySlotsController.php`

#### Métodos CRUD Estándar

##### index(Request $request) - Listar Posiciones

```
Endpoints:
GET /warehouse/slots/
GET /managers/warehouse/slots/

Parámetros de Consulta:
- stand_id (optional)        Filtrar por estantería
- status (optional)          'occupied' | 'available'
- face (optional)            'left' | 'right' | 'front' | 'back'
- search (optional)          Buscar por barcode/uid

Retorna: Vista con tabla paginada (20 items por página)
         Carga: stands, faces, slots con relaciones
```

##### create() - Formulario de Creación

```
Endpoints:
GET /warehouse/slots/create/
GET /managers/warehouse/slots/create/

Retorna: Vista con formulario vacío
         Carga: stands disponibles, productos disponibles
```

##### store(Request $request) - Guardar Nueva Posición

```
Endpoints:
POST /warehouse/slots/store/
POST /managers/warehouse/slots/store/

Validaciones Requeridas:
├── stand_id              required | exists:warehouse_stands
├── product_id            nullable | exists:products
├── face                  required | in:left,right,front,back
├── level                 required | integer | min:1
├── section               required | integer | min:1
├── quantity              nullable | integer | min:0
├── max_quantity          nullable | integer | min:1
├── weight_current        nullable | numeric | min:0
└── weight_max            nullable | numeric | min:0

Asignaciones Automáticas:
├── uid                   UUID generado
├── barcode               'SLOT-' + 8 caracteres aleatorios
└── is_occupied           basado en product_id
```

##### view($uid) - Ver Detalles

```
Endpoints:
GET /warehouse/slots/view/{uid}
GET /managers/warehouse/slots/view/{uid}

Retorna: Vista detallada con información completa
         Incluye: stand, floor, style, product
```

##### edit($uid) - Formulario de Edición

```
Endpoints:
GET /warehouse/slots/edit/{uid}
GET /managers/warehouse/slots/edit/{uid}

Retorna: Vista con formulario pre-llenado
```

##### update(Request $request) - Actualizar Posición

```
Endpoints:
POST /warehouse/slots/update/
POST /managers/warehouse/slots/update/

Campos Editables:
├── uid                  required | exists:warehouse_inventory_slots
├── product_id          nullable | exists:products
├── quantity            nullable | integer | min:0
├── max_quantity        nullable | integer | min:1
├── weight_current      nullable | numeric | min:0
└── weight_max          nullable | numeric | min:0

Actualización Automática:
└── is_occupied         basado en product_id
```

##### destroy($uid) - Eliminar Posición

```
Endpoints:
GET /warehouse/slots/destroy/{uid}
GET /managers/warehouse/slots/destroy/{uid}

Acción: Elimina registro completo
```

#### Métodos de Operación JSON (API REST)

##### addQuantity(Request $request, $uid) - Agregar Cantidad

```
Endpoints:
POST /warehouse/slots/{uid}/add-quantity/
POST /managers/warehouse/slots/{uid}/add-quantity/

Entrada:
{
    "quantity": 5
}

Validaciones:
- quantity              required | integer | min:1

Respuesta Éxito (200):
{
    "success": true,
    "message": "Cantidad agregada exitosamente",
    "data": {
        ...getSummary()
    }
}

Respuesta Error - Sin espacio (422):
{
    "success": false,
    "message": "No hay suficiente espacio para esta cantidad"
}
```

##### subtractQuantity(Request $request, $uid) - Restar Cantidad

```
Endpoints:
POST /warehouse/slots/{uid}/subtract-quantity/
POST /managers/warehouse/slots/{uid}/subtract-quantity/

Entrada:
{
    "quantity": 3
}

Validaciones:
- quantity              required | integer | min:1

Respuesta: Estructura similar a addQuantity
```

##### addWeight(Request $request, $uid) - Agregar Peso

```
Endpoints:
POST /warehouse/slots/{uid}/add-weight/
POST /managers/warehouse/slots/{uid}/add-weight/

Entrada:
{
    "weight": 2.5
}

Validaciones:
- weight               required | numeric | min:0

Respuesta: Estructura similar a addQuantity
```

##### clear(Request $request, $uid) - Vaciar Completamente

```
Endpoints:
POST /warehouse/slots/{uid}/clear/
POST /managers/warehouse/slots/{uid}/clear/

Sin parámetros requeridos

Limpia:
- product_id          → null
- quantity            → 0
- weight_current      → 0
- is_occupied         → false
```

---

### 4.2 WarehouseMapController

**Ubicación:** `app/Http/Controllers/Managers/Warehouse/WarehouseMapController.php`

**Nota:** Este controlador NO crea/edita inventory slots, pero SÍ los consulta para visualización.

#### map() - Página Interactiva del Almacén

```
Endpoints:
GET /warehouse/map/

Carga:
- Todos los pisos con estanterías
- Estilos de estanterías

Renderiza: Vista con canvas SVG para visualización 3D
```

#### getLayoutSpec(Request $request) - Especificación de Diseño (JSON)

```
Endpoints:
GET /warehouse/api/layout-spec?floor_id=1

Parámetros:
- floor_id (optional)   Filtrar por piso

Retorna:
{
    "success": true,
    "layoutSpec": [
        {
            "id": "PASILLO13A",
            "floors": [1],
            "kind": "row",
            "itemLocationsByIndex": {
                "1": {
                    "left": [
                        {
                            "uid": "...",
                            "barcode": "SLOT-...",
                            "face": "left",
                            "level": 1,
                            "section": 1,
                            "is_occupied": true,
                            "product_id": 42
                        },
                        ...más slots
                    ],
                    "right": [...slots],
                    "front": [...slots],
                    "back": [...slots]
                }
            }
        }
    ],
    "metadata": {
        "totalStands": 15,
        "totalFloors": 2
    }
}
```

#### getWarehouseConfig() - Configuración del Almacén (JSON)

```
Endpoints:
GET /warehouse/api/config/

Retorna:
{
    "warehouse": {
        "width_m": 42.23,
        "height_m": 30.26
    },
    "scale": 30,
    "floors": [
        {
            "id": 1,
            "code": "P1",
            "name": "Planta 1",
            "number": 1
        },
        ...más pisos
    ]
}
```

#### getSlotDetails($uid) - Detalles de una Posición (JSON)

```
Endpoints:
GET /warehouse/api/slot/{uid}/

Retorna:
{
    "success": true,
    "slot": {
        "uid": "550e8400-e29b-41d4-a716-446655440000",
        "barcode": "SLOT-A1B2C3D4",
        "address": "PASILLO13A / Izquierda / Nivel 2 / Sección 3",
        "is_occupied": true,
        "product": {
            "id": 1,
            "title": "Producto X",
            "barcode": "PROD-123456"
        },
        "quantity": {
            "current": 10,
            "max": 20,
            "available": 10,
            "percentage": 50
        },
        "weight": {
            "current": 5.5,
            "max": 10,
            "available": 4.5,
            "percentage": 55
        },
        "last_movement": "2025-11-17 10:30:45"
    }
}
```

#### Métodos Privados de Colorización

```php
getStandColorClass($stand): string
// Retorna clase CSS basada en % ocupación:
// <25%  → 'shelf--azul'   (vacío)
// 25-50% → 'shelf--verde'  (bajo)
// 50-75% → 'shelf--ambar'  (medio)
// >75%   → 'shelf--rojo'   (lleno)

getSlotColorByOccupancy($slot): string
// Basado en % peso y cantidad:
// No ocupado → 'shelf--gris'
// >=90% peso → 'shelf--rojo'
// >=70% peso → 'shelf--ambar'
// Similar para cantidad
// Defecto → 'shelf--verde'
```

---

## 5. RUTAS Y ENDPOINTS

**Ubicación:** `routes/managers.php`

### Rutas de Manager (Autenticadas)

```php
Route::group(['prefix' => 'theme/warehouse'], ...) {
    Route::group(['prefix' => 'slots'], ...) {
        GET    /                               → index()
        GET    /create                         → create()
        POST   /store                          → store()
        POST   /update                         → update()
        GET    /edit/{uid}                     → edit()
        GET    /view/{uid}                     → view()
        GET    /destroy/{uid}                  → destroy()
        POST   /{uid}/add-quantity             → addQuantity()
        POST   /{uid}/subtract-quantity        → subtractQuantity()
        POST   /{uid}/add-weight               → addWeight()
        POST   /{uid}/clear                    → clear()
    }
}
```

**Nombres de Rutas Manager:**
- `manager.warehouse.slots`
- `manager.warehouse.slots.create`
- `manager.warehouse.slots.store`
- `manager.warehouse.slots.edit`
- `manager.warehouse.slots.view`
- `manager.warehouse.slots.destroy`
- `manager.warehouse.slots.add-quantity`
- `manager.warehouse.slots.subtract-quantity`
- `manager.warehouse.slots.add-weight`
- `manager.warehouse.slots.clear`

### Rutas Públicas (Sin Autenticación)

```php
Route::group(['prefix' => 'warehouse'], ...) {
    // Mapa interactivo
    GET    /map                                → map()
    GET    /api/layout-spec                    → getLayoutSpec()
    GET    /api/config                         → getWarehouseConfig()
    GET    /api/slot/{uid}                     → getSlotDetails()

    // CRUD de Slots
    Route::group(['prefix' => 'slots'], ...) {
        GET    /                               → index()
        GET    /create                         → create()
        POST   /store                          → store()
        POST   /update                         → update()
        GET    /edit/{uid}                     → edit()
        GET    /view/{uid}                     → view()
        GET    /destroy/{uid}                  → destroy()
        POST   /{uid}/add-quantity             → addQuantity()
        POST   /{uid}/subtract-quantity        → subtractQuantity()
        POST   /{uid}/add-weight               → addWeight()
        POST   /{uid}/clear                    → clear()
    }
}
```

**Nombres de Rutas Públicas:**
- `slots`
- `warehouse.slots.create`
- `warehouse.slots.store`
- `warehouse.slots.edit`
- `warehouse.slots.view`
- `warehouse.slots.destroy`
- `warehouse.slots.add-quantity`
- `warehouse.slots.subtract-quantity`
- `warehouse.slots.add-weight`
- `warehouse.slots.clear`

---

## 6. MIGRACIONES DE BASE DE DATOS

### 6.1 Flujo de Creación

```
2025_11_17_000001_create_floors_table.php
            ↓
2025_11_17_000002_create_stand_styles_table.php
            ↓
2025_11_17_000003_create_stands_table.php
            ↓
2025_11_17_000004_create_inventory_slots_table.php
            ↓
2025_11_17_000005_add_product_fk_to_inventory_slots.php
```

### 6.2 Características de Índices

**Tabla inventory_slots:**

```sql
-- Búsquedas por campo individual
INDEX `idx_stand_id` (stand_id)
INDEX `idx_product_id` (product_id)
INDEX `idx_barcode` (barcode)
INDEX `idx_is_occupied` (is_occupied)
INDEX `idx_last_movement` (last_movement)

-- Restricción de unicidad (posición única por stand)
UNIQUE INDEX `uq_stand_position` (stand_id, face, level, section)

-- Búsquedas combinadas (optimización)
INDEX `idx_stand_occupied` (stand_id, is_occupied)
INDEX `idx_stand_face_level` (stand_id, face, level)
```

---

## 7. FLUJOS DE USO TÍPICOS

### Flujo 1: Crear una Estantería Nueva (Precursor de Inventory Slots)

```
1. Crear StandStyle (ROW, ISLAND, WALL)
   → Define caras disponibles
   → Define niveles y secciones por defecto

2. Crear Floor (Piso)
   → Define dónde está la estantería
   → Ej: "Planta 1", "Sótano"

3. Crear Stand (Estantería)
   → Vincula Floor + StandStyle
   → Especifica coordenadas X, Y, Z
   → Define total_levels y total_sections

4. Crear InventorySlots automáticamente
   → Ejecutar: $stand->createSlots()
   → Crea: caras × niveles × secciones posiciones
   → Ej: 2 caras × 3 niveles × 5 secciones = 30 posiciones
```

### Flujo 2: Asignar Producto a una Posición

```
1. GET /warehouse/slots/edit/{uid}
   → Cargar formulario con lista de productos

2. Seleccionar producto y cantidades/pesos

3. POST /warehouse/slots/update/
   → Valida datos
   → Actualiza product_id, quantity, weight
   → Establece is_occupied = true

4. RESULTADO: Posición ocupada con producto
```

### Flujo 3: Gestionar Inventario (Agregar/Restar)

```
AGREGAR CANTIDAD:
POST /warehouse/slots/{uid}/add-quantity/
    ↓
validar canAddQuantity(5)?
    ↓
    ✓ Sí  → addQuantity(5)
           → quantity = quantity + 5
           → is_occupied = true
           → last_movement = now()
    ✗ No  → Error: "No hay suficiente espacio"

RESTAR CANTIDAD:
POST /warehouse/slots/{uid}/subtract-quantity/
    ↓
validar quantity >= 5?
    ↓
    ✓ Sí  → subtractQuantity(5)
           → quantity = quantity - 5
           → is_occupied = (quantity > 0)
           → last_movement = now()
    ✗ No  → Error: "No se puede restar más de lo que existe"
```

### Flujo 4: Visualizar Mapa del Almacén

```
1. GET /warehouse/map/
   → Carga datos de pisos y estanterías

2. JavaScript solicita: GET /warehouse/api/layout-spec?floor_id=1
   → Retorna especificación de layout en JSON

3. JavaScript solicita: GET /warehouse/api/config/
   → Retorna dimensiones y configuración del almacén

4. Renderiza SVG interactivo con:
   → Estanterías coloreadas por ocupación
   → Click en estantería → solicita getSlotDetails()
   → Muestra información en modal

COLOR SCHEMA:
├── Azul       <25% ocupado (vacío)
├── Verde      25-75% ocupado (normal)
├── Ámbar      70-90% ocupado (casi lleno)
├── Rojo       >90% ocupado (muy lleno)
└── Gris       No ocupado o deshabilitado
```

---

## 8. RELACIONES Y CONSTRAINTS

### 8.1 Jerarquía Completa

```
Floor (1)
    │
    └─ many: Stand
        │
        ├─ 1: StandStyle (FK con RESTRICT)
        │
        └─ many: InventorySlot
                 │
                 └─ 1: Product (FK nullable con SET NULL)
```

### 8.2 Integridad Referencial

| Relación | ON DELETE | Descripción |
|----------|-----------|-------------|
| Stand → Floor | CASCADE | Si se elimina piso → Se eliminan estanterías |
| Stand → StandStyle | RESTRICT | No se puede eliminar estilo si hay estanterías |
| InventorySlot → Stand | CASCADE | Si se elimina estantería → Se eliminan todas sus posiciones |
| InventorySlot → Product | SET NULL | Si se elimina producto → Se limpia product_id de posiciones |

### 8.3 Validaciones Especiales

**Posiciones Múltiples por Stand:**
```sql
UNIQUE (stand_id, face, level, section)
-- Previene duplicados: la misma posición no puede existir 2 veces
```

---

## 9. PATRONES Y LÓGICA IMPORTANTE

### 9.1 UUID + ID Pattern

Todos los modelos Warehouse usan:
```php
- id:      Para PKs internas (FK entre tablas, índices)
- uid:     Para URLs/APIs públicas (buscar por URL: /slot/{uid})
- barcode: Para identificación física (código QR, etiquetas)
```

**Ventajas:**
- UUIDs no exponen IDs internos
- Barcodes se usan en operaciones manuales
- IDs internos para relaciones de BD

### 9.2 is_occupied Cache

```php
// Campo boolean en lugar de contar siempre
is_occupied = (product_id !== null && quantity > 0)

// Mejora rendimiento de búsquedas:
->occupied()  // WHERE is_occupied = true
->available() // WHERE is_occupied = false

// Debe mantenerse sincronizado en operaciones
addQuantity()     → is_occupied = true
subtractQuantity() → is_occupied = (quantity > 0)
clear()           → is_occupied = false
```

### 9.3 Dos Límites de Capacidad

**Por Cantidad:**
```php
if ($slot->quantity >= $slot->max_quantity)
    // No puede agregarse más unidades
    throw new Exception("Límite de cantidad alcanzado");
```

**Por Peso:**
```php
if ($slot->weight_current >= $slot->weight_max)
    // No puede agregarse más peso
    throw new Exception("Límite de peso alcanzado");
```

**Ambos deben validarse independientemente.**

### 9.4 Last Movement Tracking

Cada operación actualiza:
```php
'last_movement' => now()
```

**Útil para:**
- Auditoría de cambios
- Análisis de rotación de inventario
- Identificar posiciones obsoletas
- Rastrear patrones de uso

### 9.5 Búsqueda Jerárquica

Ejemplos de consultas complejas:

```php
// Todas las posiciones vacías de un piso en la cara izquierda
InventorySlot::whereHas('stand', function ($q) {
    $q->where('floor_id', $floorId);
})
->where('face', 'left')
->available()
->get();

// Posiciones cerca del límite de peso en un stand
$stand->slots()
    ->nearWeightCapacity(80)
    ->get();

// Posiciones en exceso de capacidad
InventorySlot::overCapacity()
            ->orWhere(function ($q) {
                $q->overQuantity();
            })
            ->get();

// Búsqueda combinada: Producto específico en piso específico
InventorySlot::whereHas('stand', function ($q) {
    $q->where('floor_id', $floorId);
})
->where('product_id', $productId)
->get();
```

---

## 10. VISTAS ASOCIADAS

### 10.1 Estructura de Carpetas

```
resources/views/managers/views/warehouse/inventory-slots/
├── index.blade.php       (Listado con filtros)
├── create.blade.php      (Formulario creación)
├── edit.blade.php        (Formulario edición)
└── view.blade.php        (Detalles completos)
```

### 10.2 Características de Vistas

| Vista | Función | Datos Cargados |
|-------|---------|----------------|
| `index.blade.php` | Listar todas las posiciones con filtros | Stands, faces, slots paginados |
| `create.blade.php` | Formulario vacío para nueva posición | Stands, productos disponibles |
| `edit.blade.php` | Editar posición existente | Slot actual, stands, productos |
| `view.blade.php` | Ver detalles completos de posición | Slot, stand, floor, product, historial |

---

## 11. MÉTODOS POR FUNCIONALIDAD

### 11.1 Crear

```
InventorySlotsController::create()    → GET /slots/create/
InventorySlotsController::store()     → POST /slots/store/
Stand::createSlots()                  → Crea todas las posiciones de una estantería
```

### 11.2 Leer / Consultar

```
InventorySlotsController::index()          → GET /slots/ (con filtros)
InventorySlotsController::view()           → GET /slots/view/{uid}
WarehouseMapController::getSlotDetails()   → GET /api/slot/{uid}
InventorySlot::getFullInfo()               → Array completo
InventorySlot::getSummary()                → Array resumido
InventorySlot::occupied()                  → Scope: posiciones ocupadas
InventorySlot::available()                 → Scope: posiciones vacías
```

### 11.3 Actualizar

```
InventorySlotsController::edit()                    → GET /slots/edit/{uid}
InventorySlotsController::update()                  → POST /slots/update/
InventorySlotsController::addQuantity()             → POST /slots/{uid}/add-quantity
InventorySlotsController::subtractQuantity()        → POST /slots/{uid}/subtract-quantity
InventorySlotsController::addWeight()               → POST /slots/{uid}/add-weight
InventorySlot::addQuantity()                        → Método del modelo
InventorySlot::subtractQuantity()                   → Método del modelo
InventorySlot::addWeight()                          → Método del modelo
InventorySlot::subtractWeight()                     → Método del modelo
```

### 11.4 Eliminar

```
InventorySlotsController::destroy()   → GET /slots/destroy/{uid}
InventorySlotsController::clear()     → POST /slots/{uid}/clear/
InventorySlot::clear()                → Vacía posición sin eliminarla
```

### 11.5 Consultar / Analizar

```
InventorySlot::occupied()                  → Posiciones ocupadas
InventorySlot::available()                 → Posiciones vacías
Stand::getOccupancyPercentage()            → % ocupación estantería
Floor::getOccupancyPercentage()            → % ocupación piso
InventorySlot::nearWeightCapacity()        → Cerca del límite
InventorySlot::overCapacity()              → En exceso
WarehouseMapController::getLayoutSpec()    → Especificación para renderizado
WarehouseMapController::getWarehouseConfig() → Configuración del almacén
```

---

## 12. INTEGRACIÓN CON PRODUCTOS

**Tabla relacionada:** `products`

### 12.1 Relación en el Modelo

```php
InventorySlot::product()  // BelongsTo relación
    ↓
Carga nombre, barcode, y otros atributos del producto

// En controlador:
$slot->product->name        // Nombre del producto
$slot->product->barcode     // Código de barras del producto
$slot->product->title       // Título del producto
```

### 12.2 Características Importantes

- Un **producto** puede estar en **múltiples posiciones** (distribuido)
- Una **posición** solo contiene un **tipo de producto**
- Al eliminar producto → las posiciones quedan con `product_id = null`
- Validación: `product_id` debe existir en tabla `products`

### 12.3 Casos de Uso Complejos

```php
// Encontrar todas las ubicaciones de un producto
$locations = InventorySlot::where('product_id', $productId)
    ->with(['stand.floor'])
    ->get();

// Calcular inventario total de un producto
$totalQuantity = InventorySlot::where('product_id', $productId)
    ->sum('quantity');

// Encontrar producto con mayor dispersión
$mostDispersed = Product::with('slots')
    ->withCount('slots')
    ->orderByDesc('slots_count')
    ->first();
```

---

## 13. FLUJO COMPLETO DE EJEMPLO

### Escenario: Añadir 10 unidades de "Laptop HP" a posición PASILLO1A-L-2-3

```
PASO 1: Usuario accede a listado
────────────────────────────────
GET /warehouse/slots/
Parámetros: stand_id=PASILLO1A, status=available
Resultado: Se muestra lista de posiciones vacías del PASILLO1A

PASO 2: Usuario selecciona una posición vacía
──────────────────────────────────────────────
GET /warehouse/slots/view/{uid}
Se carga: Detalles de posición "L-2-3"
├── Vacía, capacidad 30 unidades
├── Límite peso 20kg
└── Sin producto asignado

PASO 3: Usuario accede a edición
────────────────────────────────
GET /warehouse/slots/edit/{uid}
Se carga: Formulario con campos editables
├── Selector de productos (dropdown)
├── Campo de cantidad inicial
└── Campos de peso máximo, cantidad máxima

PASO 4: Usuario asigna el producto
─────────────────────────────────
POST /warehouse/slots/update/
Parámetros:
├── uid: (uuid de la posición)
├── product_id: 42 (Laptop HP)
├── quantity: 0 (cantidad inicial)
└── max_quantity: 30

Validaciones ejecutadas:
├── product_id existe en productos ✓
├── quantity = 0 ✓
├── max_quantity = 30 ✓

Resultado: Se actualiza
├── product_id = 42
├── is_occupied = true (tiene producto)
└── last_movement = 2025-11-17 14:30:00

PASO 5: Usuario agrega cantidad
──────────────────────────────
POST /warehouse/slots/{uid}/add-quantity/
Parámetros:
└── quantity: 10

Validaciones:
├── quantity = 10 (min: 1) ✓
└── canAddQuantity(10)? true ✓

Operación:
├── quantity: 0 + 10 = 10
├── is_occupied = true (sigue ocupada)
├── last_movement = 2025-11-17 14:30:15
└── Retorna: { success: true, message: "...", data: {...} }

RESULTADO FINAL
──────────────
Posición: "PASILLO1A / Izquierda / Nivel 2 / Sección 3"
├── Producto: "Laptop HP"
├── Cantidad: 10 / 30
├── Estado: Ocupada
├── Última operación: 2025-11-17 14:30:15
└── Disponible para agregar: 20 unidades más

VISUALIZACIÓN EN MAPA
────────────────────
- Estantería PASILLO1A cambia color según % ocupación
- Slot en cara "left" se colorea según ocupación
- Información visible al hacer hover/click en posición
```

---

## 14. CONCLUSIÓN

El sistema de **Inventory Slots** es una implementación jerárquica y bien estructurada de gestión de almacén que:

### Características Clave
✅ Proporciona control granular a nivel de posición individual
✅ Valida capacidades tanto de cantidad como de peso
✅ Rastrea movimientos para auditoría
✅ Soporta búsquedas complejas con scopes
✅ Ofrece visualización interactiva del almacén
✅ Mantiene integridad referencial con constraints adecuados
✅ Usa UUIDs para APIs públicas e IDs para relaciones internas
✅ Implementa caché con `is_occupied` para rendimiento

### Integración Completa
Está completamente integrado con:
- Sistema de Pisos (Floors)
- Sistema de Estanterías (Stands)
- Sistema de Estilos (StandStyles)
- Sistema de Productos (Products)
- Visualización interactiva (WarehouseMapController)

### Recomendaciones
1. Mantener sincronizado el campo `is_occupied` en todas las operaciones
2. Usar scopes predefinidos para consultas comunes
3. Validar capacidades (cantidad y peso) siempre antes de agregar
4. Registrar `last_movement` en operaciones de inventario
5. Usar UUIDs en URLs públicas, IDs internos en relaciones
6. Monitorear posiciones en rojo (>90% capacidad)
7. Realizar auditorías periódicas de posiciones inconsistentes

---

**Documento generado:** 17 de Noviembre de 2025
**Información completa:** Sistema de Inventory Slots
**Versión:** 1.0
