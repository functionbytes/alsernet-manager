# Flujo de Validación de Documentos - case 'documents'

## 📊 Diagrama del Flujo Completo

```
┌─────────────────────────────────────────────────────────────────────────┐
│ USUARIO ACCEDE A PÁGINA DE DOCUMENTOS                                  │
│ GET /cms?id=136&token=abc123&document_type=corta                       │
└────────────────────┬────────────────────────────────────────────────────┘
                     │
                     v
┌─────────────────────────────────────────────────────────────────────────┐
│ 1️⃣ EXTRAER UID DEL TOKEN                                               │
│ ─────────────────────────────────────────────────────────────────────   │
│ Token: "abc123"                                                         │
│ UID: "abc123"                                                           │
│                                                                         │
│ Código:                                                                 │
│   $token = Tools::getValue('token');                                   │
│   $uid = strpos($token, '?token=') !== false                           │
│       ? trim(explode('?token=', $token)[1] ?? '')                      │
│       : trim($token);                                                  │
└────────────────────┬────────────────────────────────────────────────────┘
                     │
                     v
┌─────────────────────────────────────────────────────────────────────────┐
│ 2️⃣ INCLUIR CLASES REQUERIDAS                                            │
│ ─────────────────────────────────────────────────────────────────────   │
│ Carga en memoria:                                                       │
│   - DocumentValidator.php          (validador resiliente)             │
│   - EndpointAvailabilityChecker.php (verificador Circuit Breaker)     │
│                                                                         │
│ Código:                                                                 │
│   include_once dirname(__FILE__).'/classes/DocumentValidator.php';    │
│   include_once dirname(__FILE__).'/classes/EndpointAvailabilityChecker.php'; │
└────────────────────┬────────────────────────────────────────────────────┘
                     │
                     v
┌─────────────────────────────────────────────────────────────────────────┐
│ 3️⃣ VERIFICAR DISPONIBILIDAD DEL SERVIDOR                                │
│ ─────────────────────────────────────────────────────────────────────   │
│ ANTES de enviar la petición de validación:                             │
│   • Hace un HEAD request a /api/health/documents                       │
│   • Consulta tabla ps_alsernet_endpoint_health                         │
│   • Implementa Circuit Breaker (si 3 fallos → espera 5min)             │
│                                                                         │
│ Código:                                                                 │
│   $checker = new EndpointAvailabilityChecker;                         │
│   $serverAvailable = $checker->isEndpointAvailable(                    │
│       'https://webadminpruebas.a-alvarez.com/api/health/documents',   │
│       'documents'                                                       │
│   );                                                                    │
│                                                                         │
│ Resultado:                                                              │
│   [                                                                     │
│       'available' => true|false,                                       │
│       'reason' => 'Connection timeout|No se encontró|ok'              │
│       'next_retry_at' => '2025-01-06 10:45:00'                        │
│   ]                                                                     │
└────┬───────────────────────────────────────────────────────┬───────────┘
     │ ✅ DISPONIBLE                                         │ ❌ NO DISPONIBLE
     │                                                        │
     v                                                        v
  ┌────────────────────────────────────────┐    ┌──────────────────────────────────┐
  │ 3A: SERVIDOR DISPONIBLE                │    │ 3B: SERVIDOR NO DISPONIBLE       │
  │ ─────────────────────────────────────  │    │ ──────────────────────────────   │
  │ serverAvailable['available'] == true   │    │ serverAvailable['available'] == false
  │                                        │    │                                  │
  │ $serverStatus =                        │    │ $serverStatus =                  │
  │ "✅ Servidor disponible"              │    │ "⏳ Servidor no disponible:     │
  │                                        │    │  Connection timeout"             │
  └────────────────────┬───────────────────┘    └──────────────────┬──────────────┘
                       │                                           │
                       v                                           v
                ┌─────────────────────┐                    ┌──────────────────────┐
                │ 5️⃣ DETERMINAR TIPO  │                    │ 5️⃣ DETERMINAR TIPO   │
                │ DE DOCUMENTO        │                    │ DE DOCUMENTO         │
                │ (igual en ambos)    │                    │ (igual en ambos)     │
                │                     │                    │                      │
                │ $documentType =     │                    │ $documentType =      │
                │ Tools::getValue     │                    │ Tools::getValue      │
                │ ('document_type')   │                    │ ('document_type')    │
                │ ?? 'dni';           │                    │ ?? 'dni';            │
                │                     │                    │                      │
                │ Posibles valores:   │                    │ Posibles valores:    │
                │ - 'corta'           │                    │ - 'corta'            │
                │ - 'rifle'           │                    │ - 'rifle'            │
                │ - 'escopeta'        │                    │ - 'escopeta'         │
                │ - 'dni'             │                    │ - 'dni'              │
                └────────┬────────────┘                    └──────────┬───────────┘
                         │                                           │
                         v                                           v
            ┌────────────────────────────────┐        ┌──────────────────────────────┐
            │ 6️⃣ VALIDAR: SERVIDOR RESPONDE │        │ 6️⃣ VALIDAR: SERVIDOR CAÍDO   │
            │ ─────────────────────────────  │        │ ────────────────────────────  │
            │ ApiManager::sendRequest()      │        │ ApiManager::sendRequest()    │
            │                                │        │                              │
            │ 1. Verifica disponibilidad ✅  │        │ 1. Verifica disponibilidad ❌ │
            │ 2. ENVÍA petición POST         │        │ 2. NO envía petición         │
            │ 3. Recibe respuesta            │        │ 3. Guarda en BD con          │
            │ 4. Registra en BD              │        │    status='pending'          │
            │                                │        │ 4. Registra en BD            │
            │ Endpoint:                      │        │                              │
            │ POST /api/orders/validate-docs │        │ Guarda en tabla:             │
            │                                │        │ ps_alsernet_forms_requests   │
            │ Payload:                       │        │                              │
            │ {                              │        │ Columnas importantes:        │
            │   "uid": "abc123",             │        │ - endpoint_type: 'documents' │
            │   "document_type": "corta",    │        │ - status: 'pending'          │
            │   "customer_id": 123,          │        │ - payload: JSON completo     │
            │   "order_reference": "abc123", │        │ - created_at: ahora          │
            │   "ip_address": "192.168.1.1", │        │ - next_retry_at: NOW()+5min  │
            │   "user_agent": "Chrome/..."   │        │ - retry_count: 0             │
            │ }                              │        │ - max_retries: 3             │
            │                                │        │                              │
            │ Retorna: status='success|error'│        │ Retorna: status='pending'    │
            └────────┬──────────────────────┘        └──────────────┬──────────────┘
                     │                                              │
        ┌────────────┴───────────────┐                              │
        │                            │                              │
        v                            v                              v
   ┌─────────────┐           ┌──────────────┐          ┌──────────────────┐
   │ 6A: SUCCESS │           │ 6B: ERROR    │          │ 6C: PENDING      │
   │ ───────────│           │ ──────────── │          │ ──────────────   │
   │ status:    │           │ status:      │          │ status: pending  │
   │ 'success'  │           │ 'error'      │          │ (GUARDADO EN BD) │
   │            │           │              │          │                  │
   │ En BD:     │           │ En BD:       │          │ En BD:           │
   │ status=    │           │ status=      │          │ status=pending   │
   │ 'success'  │           │ 'failed'     │          │                  │
   │ synced_at= │           │ retry_count= │          │ Puede reintentarse│
   │ NOW()      │           │ max_retries  │          │ cuando servidor   │
   │            │           │ last_error=  │          │ vuelva a estar    │
   │            │           │ detalles     │          │ disponible        │
   └──────┬─────┘           └──────┬───────┘          └────────┬─────────┘
          │                        │                          │
          └────────────┬───────────┴──────────────────────────┘
                       │
                       v
         ┌─────────────────────────────────┐
         │ 7️⃣ GENERAR TRADUCCIONES         │
         │ ─────────────────────────────   │
         │ Obtener etiquetas traducidas    │
         │ según tipo de documento         │
         │                                 │
         │ [$trans_remember,               │
         │  $trans_list] =                 │
         │  $this->generateDocumentListOnly│
         │  ($uid, $validation['type'])    │
         └─────────────────────────────────┘
                       │
                       v
         ┌─────────────────────────────────┐
         │ 8️⃣ ASIGNAR A TEMPLATE SMARTY    │
         │ ─────────────────────────────   │
         │ Preparar variables para         │
         │ mostrar en HTML                 │
         │                                 │
         │ $this->context->smarty->assign([│
         │   'uid' => $uid,                │
         │   'status' => 'success|pending| │
         │               error',           │
         │   'label' => 'Permiso...',      │
         │   'upload' => true|false,       │
         │   'required_documents' => [...],│
         │   'uploaded_documents' => [...],│
         │   'missing_documents' => [...], │
         │ ]);                             │
         └─────────────────────────────────┘
                       │
                       v
         ┌─────────────────────────────────────────┐
         │ 9️⃣ MANEJO SEGÚN STATUS                  │
         │ ─────────────────────────────────────   │
         │                                         │
         │ IF status = 'pending'                   │
         │ └─ Mostrar: "Servidor no disponible"   │
         │            "Petición guardada"         │
         │            "Request ID: 12345"         │
         │            "Reintentaremos en 5 min"   │
         │            "upload" = false (bloqueado)│
         │                                         │
         │ ELSEIF status = 'error'                 │
         │ └─ Mostrar: "Error: {mensaje}"         │
         │            "upload" = false            │
         │                                         │
         │ ELSE (success)                          │
         │ └─ Mostrar: Documentos requeridos       │
         │            Botón de subida              │
         │            "upload" = true              │
         │                                         │
         │ Log: PrestaShopLogger::addLog(...)      │
         │ - Guardar evento en logs                │
         │ - Registrar request_id si es pending   │
         │ - Registrar error si es error          │
         └─────────────────────────────────────────┘
                       │
                       v
         ┌─────────────────────────────────┐
         │ 🔟 RENDERIZAR TEMPLATE          │
         │ ─────────────────────────────   │
         │ document.tpl                    │
         │                                 │
         │ return $this->fetch(            │
         │   'module:alsernetforms/views/  │
         │    templates/hook/forms/        │
         │    documents/document.tpl'      │
         │ );                              │
         └─────────────────────────────────┘
                       │
                       v
         ┌──────────────────────────────────┐
         │ HTML RETORNADO AL USUARIO        │
         │ ──────────────────────────────   │
         │                                  │
         │ CASO 1: SUCCESS (✅)             │
         │ ├─ Mensaje: Validación ok      │
         │ ├─ Formulario de subida activo │
         │ └─ Lista documentos faltantes   │
         │                                  │
         │ CASO 2: PENDING (⏳)             │
         │ ├─ Mensaje: Servidor caído     │
         │ ├─ ID de petición: 12345       │
         │ ├─ Request ID guardado en BD   │
         │ └─ Formulario BLOQUEADO        │
         │                                  │
         │ CASO 3: ERROR (❌)              │
         │ ├─ Mensaje: Error de validación│
         │ └─ Formulario BLOQUEADO        │
         │                                  │
         │ Luego: Cron cada 5min procesa │
         │ las peticiones 'pending'       │
         └──────────────────────────────────┘
```

---

## 🔄 Flujo de Reintentos (Cron)

```
CADA 5 MINUTOS (configurado en crontab):
│
v
┌──────────────────────────────────────────┐
│ Cron ejecuta:                            │
│ process-pending-requests.php             │
└──────────────────┬───────────────────────┘
                   │
                   v
    ┌──────────────────────────────┐
    │ PendingRequestsProcessor     │
    │ - Obtiene 50 pending         │
    │ - Agrupa por endpoint_type   │
    └──────────────────┬───────────┘
                       │
                       v
    ┌──────────────────────────────────────┐
    │ PARA CADA PETICIÓN PENDIENTE:        │
    │                                      │
    │ 1. Verificar servidor disponible     │
    │    ├─ SI NO → incrementar retry_count│
    │    │         → programar próximo     │
    │    └─ SI SÍ → continuar              │
    │                                      │
    │ 2. Reintentar petición original      │
    │    ├─ Enviar mismo payload guardado  │
    │    ├─ Recibir respuesta              │
    │    └─ Actualizar BD                  │
    │                                      │
    │ 3. Si éxito:                         │
    │    └─ status='success', synced_at=NOW()
    │                                      │
    │ 4. Si fallo pero servidor ok:        │
    │    ├─ retry_count++                  │
    │    ├─ next_retry_at = exponential    │
    │    └─ Si retry_count >= max_retries: │
    │       status='failed'                │
    └──────────────────────────────────────┘
```

---

## 📋 Estados de Una Petición

```
┌─────────────────────────────────────────────────────────────┐
│ TABLA: ps_alsernet_forms_requests                           │
├─────────────────────────────────────────────────────────────┤
│ status = ?                                                  │
│                                                             │
│ 'pending'            → Esperando reintento (normal)       │
│ 'processing'         → Actualmente procesando por cron    │
│ 'success'            → Completada exitosamente ✅          │
│ 'failed'             → Falló tras max_retries ❌          │
│ 'server_unavailable' → Servidor sigue caído               │
└─────────────────────────────────────────────────────────────┘

TRANSICIONES DE ESTADO:

pending
  ↓ (si reintento exitoso)
  → success (FINAL ✅)

pending
  ↓ (si reintento falla pero retry_count < max_retries)
  → pending (recalcular next_retry_at)

pending
  ↓ (si retry_count >= max_retries)
  → failed (FINAL ❌)

pending
  ↓ (si servidor sigue caído)
  → server_unavailable (esperar 5 min)
  → pending (próximo reintento)
```

---

## 🎯 Resumen: Qué Pasa EN CADA CASO

### CASO 1: Servidor ✅ DISPONIBLE

```
Usuario accede → Verifica servidor (disponible) →
  → ENVÍA petición a Laravel →
  → Recibe respuesta →
  → Retorna success/error

⏱️ Tiempo: ~1 segundo (síncronamente)
📊 Base de datos: Registra con status='success'
```

### CASO 2: Servidor ❌ NO DISPONIBLE

```
Usuario accede → Verifica servidor (timeout) →
  → NO ENVÍA petición →
  → GUARDA en BD con status='pending' →
  → Retorna pending + request_id

⏱️ Tiempo: ~1 segundo (inmediatamente)
📊 Base de datos: Registra con status='pending'
🔄 Luego: Cron procesará cada 5 min
```

### CASO 3: Servidor ❌ CAÍDO, Petición PENDING

```
Cron ejecuta cada 5 min → Verifica servidor →
  → AÚN CAÍDO →
  → Incrementa retry_count →
  → Reprograma next_retry_at

⏱️ Próximo reintento: En 5, 15, 30 o 60 min (backoff exponencial)
📊 Base de datos: Actualiza retry_count y next_retry_at
🔄 Se mantiene 'pending' hasta max_retries
```

---

## 🛡️ Circuit Breaker Protection

```
THRESHOLD = 3 fallos consecutivos

Estado: CERRADO ✅ (servidor ok)
└─ Las peticiones se envían normalmente

Estado: ABIERTO ❌ (servidor caído)
├─ Último fallo: 10:00:00
├─ Próximo check: 10:05:00 (esperar 5 min)
├─ Ninguna petición se envía durante estos 5 min
└─ Protege al servidor caído de bombardeo

Estado: HALF-OPEN (recuperación)
├─ Pasados 5 min, hace 1 HEAD request
├─ Si OK → cierra circuito (normalidad)
└─ Si FALLA → reabre (esperar otros 5 min)
```

---

## 📝 Ejemplo Completo: Usuario Intenta Validar Cuando Servidor Caído

```
10:00:00 - Usuario accede a /cms?id=136&token=ORDER-123&document_type=corta

10:00:01 - Verificar disponibilidad
           └─ HEAD a /api/health/documents
           └─ TIMEOUT ⏱️ (servidor caído)

10:00:02 - No enviar a Laravel
           - NO hace POST /api/orders/validate-documents
           - GUARDA en BD:
             {
               id: 9876,
               endpoint_type: 'documents',
               status: 'pending',
               payload: '{"uid":"ORDER-123","document_type":"corta",...}',
               retry_count: 0,
               max_retries: 3,
               created_at: '2025-01-06 10:00:02',
               next_retry_at: '2025-01-06 10:05:02'
             }

10:00:03 - Usuario ve:
           ⏳ "El servidor está temporalmente no disponible"
           "Tu petición ha sido guardada (ID: 9876)"
           "Lo intentaremos de nuevo en unos minutos"
           "Upload BLOQUEADO"

10:05:05 - Cron ejecuta proceso-pending
           - Lee petición 9876
           - Verifica servidor (aún caído)
           - retry_count++ (ahora 1)
           - next_retry_at = NOW() + 5min

10:10:05 - Cron ejecuta proceso-pending (2º intento)
           - Lee petición 9876 (retry_count=1)
           - Verifica servidor (aún caído)
           - retry_count++ (ahora 2)
           - next_retry_at = NOW() + 15min

10:15:05 - Laravel vuelve ONLINE 🟢

10:25:05 - Cron ejecuta proceso-pending (3º intento)
           - Lee petición 9876 (retry_count=2)
           - Verifica servidor (⟶ DISPONIBLE ✅)
           - ENVÍA POST /api/orders/validate-documents
           - Recibe respuesta: 200 OK
           - Actualiza en BD:
             {
               id: 9876,
               status: 'success',
               synced_at: '2025-01-06 10:25:05',
               response: '{...datos de validación...}'
             }

→ LA PETICIÓN SE PROCESÓ EXITOSAMENTE AUNQUE EL SERVIDOR ESTUVO 25 MINUTOS CAÍDO
```

---

## 🚀 Ventajas de Este Flujo

| Ventaja | Descripción |
|---------|-------------|
| ✅ **Cero Pérdida** | Ninguna petición se pierde si servidor cae |
| ✅ **Automático** | Cron procesa automáticamente sin intervención |
| ✅ **No Bombarda** | Circuit Breaker evita sobrecargar servidor |
| ✅ **Rastreable** | Cada petición tiene request_id para tracking |
| ✅ **Visible** | Usuario ve qué pasó (success/pending/error) |
| ✅ **Auditable** | Todo registrado en BD y logs |
| ✅ **Escalable** | Maneja miles de peticiones pendientes |

---

## ⚠️ Casos Especiales

### ¿Qué pasa si usuario recarga página con pending?

```
Petición original → status='pending' (ID: 9876)
Usuario recarga → Nueva petición → status='pending' (ID: 9877)

RESULTADO: 2 peticiones pendientes para mismo UID
→ Se procesan AMBAS cuando servidor vuelve
→ Usar getPendingRequestsForUid($uid) para ver todas
→ Deduplicar manualmente si es necesario
```

### ¿Qué pasa si retry_count llega a max_retries?

```
retry_count=0 → pending
retry_count=1 → pending (próximo en 5 min)
retry_count=2 → pending (próximo en 15 min)
retry_count=3 → FAILED ❌

→ Se marca como 'failed' permanentemente
→ Nunca se reintentará más
→ Requiere intervención manual si se quiere reintentar
```

### ¿Qué pasa si servidor vuelve a caer mientras procesa?

```
Cron lee petición (pending)
Servidor responde 200 OK
Intenta procesar...
PERO: Servidor cae a mitad

→ La respuesta HTTP se trunca o falla
→ La petición se marca como 'failed'
→ next_retry_at se recalcula
→ Se volverá a intentar en próximo ciclo cron
```

---

## 📌 Checklist Antes de Usar

- [ ] ✅ Tabla `ps_alsernet_forms_requests` existe en BD
- [ ] ✅ Tabla `ps_alsernet_endpoint_health` existe en BD
- [ ] ✅ Cron configurado: `*/5 * * * * php cron/process-pending-requests.php`
- [ ] ✅ Laravel tiene endpoint: `GET /api/health/documents`
- [ ] ✅ Laravel tiene endpoint: `POST /api/orders/validate-documents`
- [ ] ✅ Clases incluidas: `DocumentValidator.php`, `EndpointAvailabilityChecker.php`
- [ ] ✅ Template `document.tpl` actualizado para mostrar 'pending' state

---

**Versión:** 1.0.0
**Fecha:** 2025-01-06
**Autor:** Alsernet Development Team
