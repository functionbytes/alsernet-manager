# Sistema de Envío de Emails para Documentos

## 📋 Descripción General

Sistema completo y automático para gestionar el envío de emails relacionados con documentos. Los emails se envían de forma **síncrona (directa)** utilizando el servidor SMTP/Sendmail configurado, sin necesidad de depender de servicios externos.

---

## 🏗️ Arquitectura

### Capas del Sistema

```
API/Controller
    ↓
Events (DocumentCreated, DocumentUploaded, DocumentReminderRequested)
    ↓
Listeners (Escuchan eventos y disparan acciones)
    ↓
Services (DocumentMailService - Lógica de negocio de emails)
    ↓
Mailables (Plantillas de email)
    ↓
SMTP/Sendmail (Envío directo)
```

---

## 📧 Tipos de Emails

### 1. **Email Inicial - Notificación de Carga** (Síncrono)
- **Cuándo:** Cuando se registra un documento vía API
- **Qué:** Pide al cliente que cargue la documentación
- **Método:** Envío directo e inmediato
- **Archivo:** `DocumentUploadNotificationMail.php`
- **Evento:** `DocumentCreated`

**Ejemplo de flujo:**
```php
// En DocumentsController::syncByOrderId()
$document = new Document();
$document->save();

event(new DocumentCreated($document));
// ↓ Listener: SendDocumentUploadNotification
// ↓ Envía email síncrono
```

### 2. **Email de Recordatorio** (Asíncrono)
- **Cuándo:** 1 día después de crear el documento
- **Qué:** Recordatorio para cargar documentación (solo si no se cargó)
- **Método:** Ejecuta en la cola con delay de +1 día
- **Archivo:** `DocumentReminderMail.php`
- **Job:** `SendDocumentReminderJob`

**Timing:**
```
Documento creado
    ↓
+24 horas
    ↓
Se ejecuta SendDocumentReminderJob
    ↓
Si el documento tiene media → Cancela
Si NO tiene media → Envía email
```

### 3. **Email de Confirmación** (Síncrono)
- **Cuándo:** Cuando el cliente carga la documentación
- **Qué:** Confirma recepción de documentos
- **Método:** Envío directo e inmediato
- **Archivo:** `DocumentUploadedMail.php`
- **Evento:** `DocumentUploaded`

**Ejemplo de flujo:**
```php
// En DocumentsController::upload()
$media = $document->addMediaFromRequest('file');

event(new DocumentUploaded($document));
// ↓ Listener: SendDocumentUploadConfirmation
// ↓ Envía email síncrono
```

---

## 🚀 Uso en Código

### Uso Directo desde el Modelo

```php
$document = Document::find(1);

// Enviar notificación inicial
$document->sendUploadNotification();

// Enviar recordatorio
$document->sendReminder();

// Enviar confirmación
$document->sendUploadedConfirmation();
```

### Uso desde el Servicio

```php
use App\Services\Documents\DocumentMailService;

$document = Document::find(1);

// Método individual
DocumentMailService::sendUploadNotification($document);
DocumentMailService::sendReminder($document);
DocumentMailService::sendUploadedConfirmation($document);

// Enviar múltiples emails
$results = DocumentMailService::sendAll($document, ['notification', 'confirmation']);
```

### Uso desde el Controlador (Automático)

```php
// En DocumentsController::syncByOrderId()
$document = new Document();
$document->order_id = $orderId;
$document->save();

// Dispara evento automáticamente
event(new DocumentCreated($document));
// ↓ Se envía email inicial de forma síncrona
// ↓ Se programa recordatorio para +1 día
```

---

## ⚙️ Configuración SMTP/Sendmail

### Archivo: `.env`

```env
# Usar sendmail (Recomendado - No requiere credenciales externas)
MAIL_MAILER=sendmail
MAIL_SENDMAIL_PATH=/usr/sbin/sendmail -bs -i

# Dirección FROM
MAIL_FROM_ADDRESS=mail@a-alvarez.com
MAIL_FROM_NAME="A-Alvarez"

# Alternativa: SMTP local
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=25
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

### Archivo: `config/mail.php`

```php
'default' => env('MAIL_MAILER', 'sendmail'),

'mailers' => [
    'sendmail' => [
        'transport' => 'sendmail',
        'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
    ],
    // ... otras configuraciones
]
```

---

## 📊 Flujo Completo de Ejemplo

### Escenario: Nuevo Documento vía API

**1. Crear Documento (API)**
```
POST /api/documents/sync-by-order-id
Body: { "order_id": 123 }

↓ DocumentsController::syncByOrderId()
↓ $document = new Document()
↓ $document->save()
↓ event(new DocumentCreated($document))
```

**2. Evento DocumentCreated se Dispara**
```
DocumentCreated event
  ↓
Listener: SendDocumentUploadNotification::handle()
  ├─ ✅ Envía email SÍNCRONO (directo vía SMTP)
  │  └─ Log: "Document notification sent"
  │
  └─ Programa email ASÍNCRONO para +1 día
     └─ Job: SendDocumentReminderJob (con delay)
```

**3. +24 Horas: Recordatorio se Ejecuta**
```
SendDocumentReminderJob ejecuta
  ↓
Verifica si documento tiene media
  ├─ SÍ tiene media → Cancela
  └─ NO tiene media → Envía email de recordatorio
     └─ Log: "Document reminder sent"
```

**4. Cliente Carga Documento**
```
POST /documents/upload
Body: { "uid": "xxx", "file": ... }

↓ DocumentsController::upload()
↓ $document->addMediaFromRequest('file')
↓ event(new DocumentUploaded($document))
```

**5. Evento DocumentUploaded se Dispara**
```
DocumentUploaded event
  ↓
Listener: SendDocumentUploadConfirmation::handle()
  ↓
✅ Envía email SÍNCRONO (confirmación de recepción)
  ↓
Log: "Document confirmation sent"
```

---

## 📁 Archivos del Sistema

### Eventos
- `app/Events/Documents/DocumentCreated.php` - Nuevo documento registrado
- `app/Events/Documents/DocumentUploaded.php` - Documento cargado
- `app/Events/Documents/DocumentReminderRequested.php` - Recordatorio solicitado

### Listeners
- `app/Listeners/Documents/SendDocumentUploadNotification.php` - Escucha DocumentCreated
- `app/Listeners/Documents/SendDocumentUploadConfirmation.php` - Escucha DocumentUploaded
- `app/Listeners/Documents/SendDocumentUploadReminder.php` - Escucha DocumentReminderRequested

### Servicios
- `app/Services/Documents/DocumentMailService.php` - Lógica de envío de emails

### Mailables (Plantillas)
- `app/Mail/Documents/DocumentUploadNotificationMail.php`
- `app/Mail/Documents/DocumentReminderMail.php`
- `app/Mail/Documents/DocumentUploadedMail.php`

### Vistas Blade
- `resources/views/mailers/documents/notification.blade.php`
- `resources/views/mailers/documents/reminder.blade.php`
- `resources/views/mailers/documents/uploaded.blade.php`

### Jobs
- `app/Jobs/Documents/SendDocumentUploadNotificationJob.php`
- `app/Jobs/Documents/SendDocumentReminderJob.php`
- `app/Jobs/Documents/SendDocumentUploadedConfirmationJob.php`
- `app/Jobs/Documents/SendDocumentMailDirectlyJob.php`

### Controlador
- `app/Http/Controllers/Administratives/Orders/DocumentsController.php`

### Modelo
- `app/Models/Order/Document.php` - Métodos helper para envío de emails

---

## 🔍 Monitoreo y Logs

Los emails se registran en los logs para auditoría:

```
storage/logs/laravel.log
```

### Logs Exitosos
```
[2024-01-15 10:30:45] local.INFO: Document notification sent successfully {
  "document_uid": "abc123",
  "recipient": "customer@example.com",
  "order_id": 456,
  "sent_method": "sync"
}
```

### Logs de Error
```
[2024-01-15 10:30:46] local.ERROR: Unable to send document notifications {
  "document_uid": "abc123",
  "order_id": 456,
  "recipient": "invalid@",
  "exception": "SMTP connection failed"
}
```

---

## ⚠️ Manejo de Errores

### Si Falla el Envío SMTP

1. **Email Inicial (Síncrono):**
   - Intenta enviar 3 veces automáticamente
   - Si falla, se registra en logs
   - El usuario recibe respuesta de error en la API

2. **Email de Recordatorio (Asíncrono):**
   - Se reintenta según configuración de cola
   - Si falla, se mueve a `failed_jobs`
   - Se puede reintentar manualmente

3. **Email de Confirmación (Síncrono):**
   - Intenta enviar inmediatamente
   - Si falla, se registra pero no bloquea la carga

---

## 🛠️ Configuración Avanzada

### Cambiar Cola para Recordatorios

En `SendDocumentUploadNotification::handle()`:
```php
// Por defecto: cola 'emails'
dispatch(new SendDocumentReminderJob($document))
    ->onQueue('emails');

// Cambiar a otra cola:
dispatch(new SendDocumentReminderJob($document))
    ->onQueue('default');
```

### Ajustar Delay del Recordatorio

En `SendDocumentUploadNotification::handle()`:
```php
// Recordatorio en 1 día (default)
->delay(now()->addDay())

// Recordatorio en 2 horas
->delay(now()->addHours(2))

// Recordatorio en 3 días
->delay(now()->addDays(3))
```

### Personalizar Plantillas de Email

Editar archivos en:
```
resources/views/mailers/documents/
├── notification.blade.php
├── reminder.blade.php
└── uploaded.blade.php
```

---

## 📞 Troubleshooting

### "Email no se envía"

1. Verificar SMTP configurado en `.env`
2. Verificar que `customer_email` esté presente en documento
3. Revisar logs: `tail -f storage/logs/laravel.log`
4. Verificar sendmail disponible: `which sendmail`

### "SMTP Connection Timeout"

1. Verificar servidor SMTP local: `telnet localhost 25`
2. Configurar timeout mayor: En `config/mail.php`
3. Usar alternativa: cambiar a `log` mailer para debug

### "Email va a SPAM"

1. Configurar SPF/DKIM en el dominio
2. Usar `MAIL_FROM_NAME` que coincida con dominio
3. Incluir unsubscribe link en plantillas

---

## 🚀 Resumen Rápido

**Para enviar emails de documentos:**

```php
// Automático (recomendado)
event(new DocumentCreated($document));

// Manual directo
$document->sendUploadNotification();
$document->sendReminder();
$document->sendUploadedConfirmation();

// Servicio
DocumentMailService::sendUploadNotification($document);
```

**El sistema es:**
- ✅ Automático (eventos y listeners)
- ✅ Síncrono (envío directo, sin esperar cola)
- ✅ Confiable (manejo de errores)
- ✅ Auditable (logs completos)
- ✅ Sin dependencias externas (SMTP local)
