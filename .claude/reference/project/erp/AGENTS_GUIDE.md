# Integración ERP - Guía de Uso de Agentes

**Cómo usar los agentes Plan, Backend y Frontend para construir características con la integración de Gestión ERP**

---

## 🎯 Descripción General Rápida

Ahora tienes tres agentes especializados que pueden trabajar juntos para construir características de integración ERP:

| Agente | Propósito | Mejor Para |
|--------|-----------|-----------|
| **Plan Agent** | Arquitectura y Diseño de Tareas | Planificar características, desglosar tareas complejas |
| **Backend Agent** | Implementación de APIs | Crear integraciones REST/XML-RPC, consultas a base de datos |
| **Frontend Agent** | Componentes de UI | Construir interfaces que consuman datos de ERP |

---

## 📋 Referencias de Documentación

Todos los agentes tienen acceso a estos archivos en `.claude/reference/project/erp/`:

- **README.md** - Índice de navegación y referencia rápida
- **erp-integration-overview.md** - Arquitectura del sistema, conceptos y flujos de trabajo
- **erp-api-endpoints.md** - Referencia completa de REST API (14 endpoints)
- **erp-sync-tables.md** - Definiciones de 50+ tablas de base de datos
- **erp-xmlrpc-services.md** - Servicios XML-RPC legacy con ejemplos de código

---

## 🔵 Uso del Plan Agent

### Propósito
El Plan Agent desglosa características complejas en pasos de implementación accionables. Entiende la arquitectura del sistema ERP y puede diseñar flujos de trabajo.

### Cuándo Usar
- Planificar una nueva característica que involucre integración ERP
- Desglosar tareas complejas de múltiples pasos
- Entender cómo funcionan juntos múltiples endpoints de ERP
- Diseñar flujos de trabajo de sincronización

### Cómo Solicitar

**Plantilla:**
```
"Necesito planificar una característica para [descripción de característica].
La característica involucra [qué endpoints/datos de ERP].
¿Puedes crear un plan detallado con pasos de implementación?"
```

### Ejemplo de Solicitud 1: Sistema de Gestión de Clientes

```
"Necesito planificar un sistema de gestión de clientes. Los usuarios deberían:
1. Crear nuevos clientes en Gestión mediante la API
2. Suscribir clientes a múltiples catálogos
3. Ver el estado de cumplimiento LOPD del cliente
4. Actualizar información de contacto del cliente

¿Puedes crear un plan detallado con:
- Lista de endpoints de API requeridos
- Tablas de base de datos requeridas y campos
- Requisitos de cumplimiento LOPD
- Secuencia de implementación"
```

**Qué hará el Plan Agent:**
- Referencia `erp-api-endpoints.md` secciones `/cliente/` y `/clientecatalogo/`
- Referencia `erp-sync-tables.md` para tablas relacionadas con clientes
- Referencia `erp-integration-overview.md` para requisitos LOPD
- Crear un plan paso a paso con:
  - Configuración de prerequisitos
  - Mapeo de endpoints de API
  - Validación de estructura de datos
  - Estrategia de manejo de errores
  - Enfoque de pruebas

### Ejemplo de Solicitud 2: Flujo de Procesamiento de Órdenes

```
"Planifica un flujo completo de procesamiento de órdenes que:
1. Cree una orden de cliente en Gestión
2. Verifique la disponibilidad de inventario
3. Aplique bonos promocionales
4. Envíe notificaciones de confirmación
5. Actualice el estado de la orden en Web Álvarez

Incluye todas las llamadas de API necesarias y manejo de errores"
```

**Qué hará el Plan Agent:**
- Mapear el flujo completo a través de endpoints:
  - POST `/pedido-cliente/` para crear orden
  - GET `/stock-central-web/` para inventario
  - GET/PUT `/bono/` para bonos
  - POST `/notificacion-central/` para notificaciones
  - XML-RPC `WebAlvarez.insertDatos` para actualizaciones
- Crear secuencia de implementación
- Identificar transformaciones de datos requeridas
- Definir pasos de recuperación de errores

### Ejemplo de Solicitud 3: Sincronización de Inventario

```
"Planifica un sistema de sincronización de inventario en tiempo real que:
1. Consulte Gestión para cambios de stock pendientes
2. Actualice la visibilidad de productos en Web Álvarez
3. Prevenga la venta excesiva (overselling)
4. Registre todas las transacciones de sincronización

Muéstrame el flujo completo con estrategia de reintentos"
```

**Qué hará el Plan Agent:**
- Referencia el flujo de sincronización basado en transacciones:
  - GET `/CambiosPendientes/` para listar cambios pendientes
  - GET `/TransaccionPendiente/` para obtener detalles
  - GET `/ConfirmarTransaccion/` para marcar como sincronizado
- Crear estrategia de bucle de sondeo
- Diseñar reintentos y manejo de errores
- Planificar seguimiento de base de datos para auditoría

---

## 🟠 Uso del Backend Agent

### Propósito
El Backend Agent implementa APIs, consultas de base de datos e integraciones de servicios. Escribe código listo para producción usando la documentación de ERP.

### Cuándo Usar
- Implementar endpoints de API que llamen a servicios Gestión
- Escribir consultas de base de datos para sincronización
- Crear trabajos/workers para procesamiento en segundo plano
- Integrar servicios XML-RPC

### Cómo Solicitar

**Plantilla:**
```
"Implementa [característica específica] usando:
- Endpoint de API Gestión: [endpoint de docs]
- Datos requeridos: [nombres de tablas de docs]
- Integración con: [qué sistema]"
```

### Ejemplo de Solicitud 1: Endpoint de Creación de Clientes

```
"Crea un endpoint de API Laravel que:
1. Acepte datos de cliente (nombre, email, teléfono, CIF)
2. Valide la aceptación de LOPD
3. Llame al endpoint POST /cliente/ de Gestión
4. Maneje conflictos de email/CIF duplicado (error 20401, 20402)
5. Retorne el ID del cliente

Referencia: erp-api-endpoints.md Sección 4 (endpoint cliente)"
```

**Qué hará el Backend Agent:**
- Crear un método de controlador Laravel
- Implementar validación de solicitud
- Mapear campos de entrada a parámetros de API desde `erp-api-endpoints.md`
- Referencia requisitos LOPD desde `erp-integration-overview.md`
- Manejar códigos de error (20401, 20402, 20404)
- Agregar logging y respuestas de error apropiadas
- Escribir código comprehensivo con ejemplos

**Estructura de Código Generado:**
```php
// app/Http/Controllers/CustomerController.php
public function createCustomer(Request $request) {
    // Validar entrada contra requisitos de erp-api-endpoints.md
    $validated = $request->validate([
        'cliente_nombre' => 'required|string',
        'cliente_email' => 'required|email|unique:customers',
        'cliente_cif' => 'required|string',
        'cliente_faceptacion_lopd' => 'required|date', // Requisito LOPD
    ]);

    // Llamar API de Gestión
    $response = $this->callGestionAPI(
        'POST',
        '/cliente/',
        $this->mapToApiFormat($validated)
    );

    // Manejar errores 20401, 20402, 20404
    if ($response->hasError()) {
        return $this->handleApiError($response->errorCode);
    }

    return response()->json(['idcliente' => $response->idcliente]);
}
```

### Ejemplo de Solicitud 2: Job de Sincronización de Inventario

```
"Crea un job de cola Laravel que:
1. Llame GET /CambiosPendientes/ para obtener transacciones pendientes
2. Para cada transacción, llame GET /TransaccionPendiente/
3. Actualice inventario local en la base de datos
4. Llame GET /ConfirmarTransaccion/ para marcar como sincronizado
5. Reintente sincronizaciones fallidas con backoff exponencial

Usa el flujo de sincronización basado en transacciones de erp-integration-overview.md"
```

**Qué hará el Backend Agent:**
- Crear un job queueable
- Implementar el bucle de sincronización de tres pasos
- Agregar lógica de reintentos con backoff exponencial
- Actualizar tablas de inventario desde `erp-sync-tables.md`
- Agregar logging de transacciones para auditoría
- Manejar fallos de conexión y timeouts

### Ejemplo de Solicitud 3: Creación de Orden con Múltiples Endpoints

```
"Crea una característica compleja que:
1. Cree una orden de cliente mediante POST /pedido-cliente/
2. Verifique stock mediante GET /stock-central-web/
3. Aplique bonos mediante PUT /bono/
4. Cree notificación mediante POST /notificacion-central/
5. Actualice Web Álvarez mediante XML-RPC WebAlvarez.insertDatos
6. Envíe SMS mediante SMSServer.sendSMS

Todo en una transacción coordinada única. Maneja todos los códigos de error."
```

**Qué hará el Backend Agent:**
- Crear una clase de servicio que orqueste todas las llamadas
- Implementar transformación de datos entre sistemas
- Manejar fallos parciales (algunos endpoints tienen éxito, otros fallan)
- Implementar lógica de rollback/compensación
- Agregar manejo de errores comprehensivo
- Referencia todas las tablas desde `erp-sync-tables.md`
- Referencia todos los endpoints desde `erp-api-endpoints.md`

---

## 🟢 Uso del Frontend Agent

### Propósito
El Frontend Agent construye componentes de UI que consumen datos de ERP. Usa Tailwind/Bootstrap y referencia estructuras de datos de la documentación.

### Cuándo Usar
- Crear interfaces de gestión de clientes
- Construir formularios de órdenes que validen contra ERP
- Diseñar visualizaciones de inventario/stock
- Construir dashboards administrativos con datos ERP

### Cómo Solicitar

**Plantilla:**
```
"Crea un componente de UI que:
1. Muestre [datos de ERP]
2. Permita [acción del usuario]
3. Valide contra [restricciones de ERP]
4. Siga [estilo de plantilla Modernize]"
```

### Ejemplo de Solicitud 1: Formulario de Registro de Cliente

```
"Crea un formulario de registro de cliente que:
1. Recopile: nombre, email, teléfono, CIF, preferencia de idioma
2. Valide que email/CIF sean únicos (manejar errores 20401, 20402)
3. Requiera aceptación de LOPD (checkbox para cliente_faceptacion_lopd)
4. Tenga selector de idioma para campo cliente_idioma
5. Use estilos de plantilla Bootstrap Modernize
6. Muestre descripciones de campos desde tabla cliente de erp-sync-tables.md

Hazlo responsivo y amigable para dispositivos móviles"
```

**Qué hará el Frontend Agent:**
- Crear un componente Vue 3 o plantilla Blade
- Mapear campos del formulario a tabla de clientes desde `erp-sync-tables.md`
- Implementar validación del lado del cliente
- Mostrar mensajes de error amigables para cada código de error
- Incluir checkbox de consentimiento LOPD con contexto requerido
- Referencia sistema de diseño Modernize
- Agregar atributos de accesibilidad
- Crear layout responsivo

**Estructura de Componente Generado:**
```vue
<template>
  <div class="card">
    <div class="card-body">
      <h4 class="card-title">{{ __('Crear Cliente') }}</h4>

      <!-- Consentimiento LOPD - Requerido por ley -->
      <div class="form-check mb-3">
        <input
          v-model="form.lopd_accepted"
          type="checkbox"
          class="form-check-input"
          id="lopd"
        >
        <label class="form-check-label">
          {{ __('Acepto los requisitos LOPD') }}
          <!-- Referencia: erp-integration-overview.md LOPD Compliance -->
        </label>
      </div>

      <!-- Nombre del Cliente -->
      <div class="mb-3">
        <label class="form-label">{{ __('Nombre Completo') }}</label>
        <input
          v-model="form.nombre"
          type="text"
          class="form-control"
        >
        <!-- De erp-sync-tables.md: campo cliente_nombre -->
      </div>

      <!-- Selección de Idioma -->
      <div class="mb-3">
        <label class="form-label">{{ __('Idioma') }}</label>
        <select v-model="form.idioma" class="form-select">
          <!-- De erp-sync-tables.md: campo cliente_idioma -->
          <option value="es">Español</option>
          <option value="en">English</option>
          <option value="fr">Français</option>
        </select>
      </div>
    </div>
  </div>
</template>
```

### Ejemplo de Solicitud 2: Wizard de Creación de Orden

```
"Crea un wizard de creación de orden con múltiples pasos:

Paso 1: Selección de Cliente
  - Buscar/seleccionar cliente existente
  - Mostrar estado de cumplimiento LOPD del cliente

Paso 2: Selección de Productos
  - Buscar productos por código desde v_sinc_w_producto
  - Mostrar disponibilidad de stock desde v_sinc_stock_central_web
  - Mostrar precios de tarifa desde v_sinc_tarifa_linea
  - Prevenir órdenes si stock < umbral

Paso 3: Detalles de Envío
  - Campo de dirección
  - Selector de método de envío
  - Entrega estimada

Paso 4: Pago
  - Selector de método de pago (debe existir en sistema)
  - Cálculo de total
  - Aplicación de descuento/bono

Paso 5: Revisar y Confirmar
  - Mostrar todos los detalles
  - Botón de confirmación que envía al backend

Usa plantilla Modernize, incluye validación de formulario,
muestra verificaciones de disponibilidad en tiempo real"
```

**Qué hará el Frontend Agent:**
- Crear un formulario de múltiples pasos con Vue
- Implementar búsqueda de productos con autocompletar
- Obtener y mostrar stock en tiempo real
- Mostrar precios basados en tablas de tarifa
- Validar que método de envío existe
- Calcular totales de orden con descuentos
- Mostrar advertencias de cumplimiento de LOPD/cliente
- Manejar todas las respuestas de error del backend
- Usar estilos Bootstrap Modernize
- Agregar persistencia de estado del formulario

### Ejemplo de Solicitud 3: Dashboard de Inventario

```
"Crea un dashboard de inventario que:
1. Muestre niveles de stock actual desde v_sinc_stock_central_web
2. Muestre advertencias de stock bajo (< umbral)
3. Liste cambios de inventario recientes desde CambiosPendientes
4. Tenga indicador de estado de sincronización (última hora de sincronización)
5. Muestre fallos de sincronización con botón de reintentar
6. Muestre productos agrupados por categoría desde v_sinc_w_producto

Actualizaciones en tiempo real mediante WebSocket cuando cambia el stock.
Usa plantilla Bootstrap Modernize."
```

**Qué hará el Frontend Agent:**
- Crear un componente de dashboard
- Obtener datos de stock del endpoint `/stock-central-web/`
- Mostrar productos con categorización
- Mostrar alertas de stock bajo (umbral configurable)
- Mostrar transacciones pendientes con estado
- Crear mecanismo de reintentos para sincronizaciones fallidas
- Implementar suscripción a WebSocket para actualizaciones en tiempo real
- Usar tarjetas y tablas Bootstrap
- Agregar funcionalidad de búsqueda/filtrado
- Incluir opción de exportar a CSV

---

## 🔄 Ejemplo de Flujo de Trabajo Completo: Sistema de Gestión de Órdenes

Este ejemplo muestra los tres agentes trabajando juntos:

### Paso 1: Planificación (Plan Agent)

**Tu Solicitud:**
```
"Planifica un sistema completo de gestión de órdenes que:
1. Cree órdenes con datos de cliente y producto
2. Rastree el estado de la orden desde envío hasta entrega
3. Maneje bonos promocionales
4. Envíe notificaciones
5. Se sincronice con Web Álvarez

Desglosalo en componentes backend y frontend"
```

**Salida del Plan Agent:**
```
COMPONENTES BACKEND:
1. Servicio de Gestión de Clientes
   - POST /cliente/ (crear cliente)
   - PUT /cliente/ (actualizar cliente)
   - Referencia: erp-api-endpoints.md Sección 4

2. Servicio de Procesamiento de Órdenes
   - POST /pedido-cliente/ (crear orden)
   - GET /pedido-cliente/ (obtener órdenes)
   - Validar: LOPD, CIF, suscripción a catálogo
   - Referencia: erp-api-endpoints.md Sección 6

3. Verificador de Inventario
   - GET /stock-central-web/ (verificar disponibilidad)
   - Referencia: erp-api-endpoints.md Sección 5

4. Motor de Promociones
   - GET /bono/ (verificar bonos)
   - PUT /bono/ (aplicar bono)
   - Referencia: erp-api-endpoints.md Sección 8

5. Servicio de Notificación
   - POST /notificacion-central/ (email)
   - XML-RPC SMSServer.sendSMS (SMS)
   - Referencia: erp-xmlrpc-services.md

6. Sincronización con Web Álvarez
   - XML-RPC WebAlvarez.insertDatos (actualizar estado)
   - Referencia: erp-xmlrpc-services.md Sección 1

COMPONENTES FRONTEND:
1. Formulario de Registro de Cliente
   - Capturar: nombre, email, CIF, consentimiento LOPD
   - Referencia: tabla cliente de erp-sync-tables.md

2. Wizard de Creación de Orden
   - Paso 1: Selección de cliente
   - Paso 2: Selección de producto con verificación de stock
   - Paso 3: Información de envío
   - Paso 4: Método de pago
   - Paso 5: Confirmación

3. Dashboard de Estado de Orden
   - Listar órdenes de cliente
   - Mostrar línea de tiempo de orden
   - Mostrar detalles de orden

PUNTOS DE INTEGRACIÓN:
- Tabla cliente: v_sinc_w_cliente
- Tabla producto: v_sinc_w_producto
- Tabla stock: v_sinc_stock_central_web
- Sincronización de orden: v_sinc_pedido_cliente

MANEJO DE ERRORES:
- 20401: Email duplicado → Mostrar "Email ya existe"
- 20402: CIF duplicado → Mostrar "CIF ya existe"
- 20404: LOPD faltante → Requerir checkbox
- 20430: Producto no encontrado → Mostrar error de validación
- 20420: LOPD no aceptado → Bloquear creación de orden
```

### Paso 2: Implementación Backend (Backend Agent)

**Tu Solicitud:**
```
"Implementa el Servicio de Procesamiento de Órdenes basado en el plan.
Crea:
1. Clase OrderService que llame POST /pedido-cliente/
2. Maneja todos los errores de validación de erp-api-endpoints.md
3. Mapea correctamente datos de cliente + producto
4. Llama WebAlvarez.insertDatos para sincronizar estado
5. Envía notificación SMS

Referencia erp-api-endpoints.md Sección 6 y erp-xmlrpc-services.md"
```

**El Backend Agent Crea:**
- `OrderService.php` - Orquesta todas las operaciones de orden
- `Validations/OrderValidator.php` - Valida contra restricciones de ERP
- `Jobs/SyncOrderToWebAlvarez.php` - Job en segundo plano para actualizaciones
- `Jobs/SendOrderNotification.php` - Envía SMS/email
- Migración de base de datos para seguimiento local de órdenes
- Pruebas unitarias con respuestas de API simuladas

### Paso 3: Implementación Frontend (Frontend Agent)

**Tu Solicitud:**
```
"Crea un formulario de creación de orden basado en el plan.
Usa la implementación de OrderService para llamadas API.
Muestra:
1. Selector de cliente
2. Búsqueda de producto con verificación de stock en tiempo real
3. Selector de método de envío
4. Selector de método de pago
5. Resumen de orden con totales
6. Botón de envío que llama al backend

Usa plantilla Bootstrap Modernize,
valida contra campos de erp-sync-tables.md"
```

**El Frontend Agent Crea:**
- `OrderForm.vue` - Componente de formulario de múltiples pasos
- `ProductSearch.vue` - Autocompletar de producto con stock
- `CustomerSelector.vue` - Búsqueda/creación de cliente
- `OrderSummary.vue` - Revisión final antes de envío
- Validación de formulario y manejo de errores
- Diseño responsivo con estilos Modernize

---

## 📚 Cómo los Agentes Referencian la Documentación

### Referencias del Backend Agent:
1. **Parámetros de API** → `erp-api-endpoints.md`
   - Rutas de endpoint, métodos, campos requeridos
   - Formatos de solicitud/respuesta
   - Códigos de error y soluciones

2. **Estructuras de Datos** → `erp-sync-tables.md`
   - Nombres de campos y tipos de datos
   - Relaciones entre tablas
   - Restricciones y validaciones

3. **Flujo de Integración** → `erp-integration-overview.md`
   - Arquitectura del sistema
   - Procesos de flujo de datos
   - Estrategias de manejo de errores

4. **Servicios Legacy** → `erp-xmlrpc-services.md`
   - Firmas de métodos XML-RPC
   - Codificación de parámetros (Base64)
   - Ejemplos de código real

### Referencias del Frontend Agent:
1. **Campos de Datos** → `erp-sync-tables.md`
   - Qué tablas proporcionan qué datos
   - Descripciones de campos para etiquetas/placeholders
   - Información de restricciones (requerido, único, etc.)

2. **Manejo de Errores** → `erp-api-endpoints.md`
   - Códigos de error y mensajes amigables
   - Restricciones de validación
   - Resolución de conflictos

3. **Restricciones de API** → `erp-integration-overview.md`
   - Requisitos LOPD
   - Reglas de negocio
   - Requisitos de validación

### Referencias del Plan Agent:
1. **Arquitectura** → `erp-integration-overview.md`
   - Componentes del sistema y flujo
   - Mejores prácticas
   - Lista de verificación de implementación

2. **Mapa de API Completo** → `erp-api-endpoints.md`
   - Todas las operaciones disponibles
   - Dependencias entre endpoints
   - Rutas de recuperación de errores

3. **Diseño de Datos** → `erp-sync-tables.md`
   - Esquema de base de datos
   - Relaciones de campo
   - Requisitos de sincronización

---

## 🎯 Plantillas de Solicitud Rápida

### Para Plan Agent:
```
"Planifica [característica] que se integre con:
- Endpoints Gestión: [nombres]
- Tablas de datos: [nombres]
- Requisitos: [lista]

Muéstrame:
1. Secuencia de implementación
2. Llamadas API requeridas con parámetros
3. Estrategia de manejo de errores
4. Enfoque de pruebas"
```

### Para Backend Agent:
```
"Implementa [característica] que:
1. Llame a Gestión [endpoint]
2. Valide contra [restricciones de docs]
3. Actualice [tablas de base de datos]
4. Maneje errores [códigos específicos]

Referencia: [sección en documentación]"
```

### Para Frontend Agent:
```
"Crea una interfaz para [característica] que:
1. Muestre [datos de tablas]
2. Acepte [entrada del usuario para campos]
3. Valide [restricciones de docs]
4. Muestre [errores/advertencias específicas]

Estilo: Plantilla Bootstrap Modernize"
```

---

## 📊 Enlaces Rápidos de Documentación por Caso de Uso

| Necesidad | Documento | Sección |
|-----------|-----------|---------|
| ¿Qué endpoints de API existen? | erp-api-endpoints.md | Descripción General |
| ¿Cómo creo un cliente? | erp-api-endpoints.md | Sección 4 |
| ¿Cómo creo una orden? | erp-api-endpoints.md | Sección 6 |
| ¿Qué campos tiene cliente? | erp-sync-tables.md | Tablas de cliente |
| ¿Cómo verifico stock? | erp-api-endpoints.md | Sección 5 |
| ¿Cómo sincronizo con Web Álvarez? | erp-xmlrpc-services.md | WebAlvarez.insertDatos |
| ¿Cuál es el requisito LOPD? | erp-integration-overview.md | Cumplimiento LOPD |
| ¿Qué códigos de error existen? | erp-api-endpoints.md | Manejo de Errores |
| ¿Cómo manejo errores de API? | erp-integration-overview.md | Manejo de Errores |
| ¿Cuál es el flujo de sincronización de transacciones? | erp-integration-overview.md | Flujo de Datos |

---

## ✨ Mejores Prácticas

### 1. Siempre Cita la Documentación
Cuando solicites trabajo de un agente, referencia qué documento tiene la información:
```
"Crea un cliente usando endpoint POST /cliente/
(ver erp-api-endpoints.md Sección 4)"
```

### 2. Agrupa Tareas Relacionadas
En lugar de múltiples solicitudes separadas, combina trabajo relacionado:
```
✗ Mal: "Crea controlador de cliente"
✗ Mal: "Crea controlador de orden"
✓ Bien: "Crea OrderManagementService que:
  - Gestiona clientes mediante /cliente/
  - Crea órdenes mediante /pedido-cliente/
  - Se sincroniza con Web Álvarez"
```

### 3. Especifica Manejo de Errores
Dile al agente qué códigos de error manejar:
```
"Maneja estos códigos de error de erp-api-endpoints.md:
- 20401: Email duplicado
- 20402: CIF duplicado
- 20404: LOPD faltante
Muestra mensajes amigables para cada uno"
```

### 4. Referencia Estructuras de Datos
Dile al agente qué tablas usar:
```
"Usa estas tablas de erp-sync-tables.md:
- v_sinc_w_cliente (datos del cliente)
- v_sinc_w_producto (datos del producto)
- v_sinc_stock_central_web (disponibilidad de stock)"
```

### 5. Encadena Solicitudes de Agentes
Usa salida de Plan para guiar Backend, usa Backend para guiar Frontend:
```
1. Plan Agent: "Diseña el sistema de órdenes"
2. Backend Agent: "Implementa basado en este plan: [pega plan]"
3. Frontend Agent: "Construye UI usando este backend: [pega visión general backend]"
```

---

## 🚀 Primeros Pasos

### Ejemplo Mínimo: Crear un Cliente

**Solicitud al Backend Agent:**
```
"Crea un método de controlador Laravel que:
1. Valide datos de cliente (nombre, email, CIF)
2. Llame al endpoint POST /cliente/ de Gestión
3. Maneje errores de email/CIF duplicado (20401, 20402)
4. Retorne el ID del nuevo cliente

Usa erp-api-endpoints.md Sección 4 para parámetros"
```

**El agente hará:**
- Referencia POST /cliente/ de la documentación
- Implementar validación apropiada
- Mapear Laravel Request a parámetros de API
- Manejar errores con respuestas HTTP apropiadas
- Retornar el ID del cliente creado

### Ejemplo Completo: Construir Gestión de Órdenes

1. **Día 1 - Planificación:**
   ```
   Plan Agent: "Diseña un sistema completo de gestión de órdenes"
   ```
   → Obtén plan detallado con todos los componentes

2. **Día 2 - Backend:**
   ```
   Backend Agent: "Implementa basado en este plan: [pega]"
   ```
   → Obtén capa de API y servicio completa

3. **Día 3 - Frontend:**
   ```
   Frontend Agent: "Construye UI para este backend: [pega]"
   ```
   → Obtén interfaz completa orientada al cliente

---

**Última Actualización**: 30 de Noviembre de 2025
**Versión**: 1.0
**Estado**: Listo para Usar ✅

