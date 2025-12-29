# Mail Module

El módulo Mail es responsable de la gestión completa del sistema de correos electrónicos de la aplicación Alsernet.

## Estructura del Módulo

```
Modules/Mail/
├── app/
│   ├── Providers/
│   │   ├── MailServiceProvider.php       # Service provider principal del módulo
│   │   └── RouteServiceProvider.php      # Registro de rutas
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Managers/Settings/Mails/
│   │   │   │   ├── MailTemplateController.php      # CRUD de plantillas de email
│   │   │   │   ├── MailComponentController.php     # CRUD de componentes reutilizables
│   │   │   │   ├── MailVariableController.php      # CRUD de variables disponibles
│   │   │   │   └── MailEndpointController.php      # CRUD de endpoints de API
│   │   │   └── Api/
│   │   │       └── EmailEndpointController.php     # API para envío de emails
│   │   └── Requests/
│   ├── Models/
│   │   ├── MailTemplate.php             # Plantilla de email principal
│   │   ├── MailTemplateLang.php         # Traducciones de plantillas
│   │   ├── MailLayout.php               # Layout (header/footer)
│   │   ├── MailLayoutLang.php           # Traducciones de layouts
│   │   ├── MailVariable.php             # Variables disponibles en plantillas
│   │   ├── MailVariableLang.php         # Traducciones de variables
│   │   ├── MailEndpoint.php             # Endpoint de API para envío
│   │   └── MailEndpointLog.php          # Log de envíos via API
│   ├── Services/
│   │   ├── MailVariableService.php             # Servicio para gestionar variables
│   │   ├── MailVariableValueService.php        # Servicio para obtener valores de variables
│   │   └── MailTemplateRendererService.php     # Servicio para renderizar plantillas
│   ├── Mail/                            # Clases de correos (Mailables)
│   │   ├── AppMailer.php
│   │   ├── DirectMail.php
│   │   ├── VerifyMail.php
│   │   ├── ContactMail.php
│   │   ├── Helpdesk/                   # Emails del módulo Helpdesk
│   │   ├── Subscribers/                # Emails para suscriptores
│   │   ├── Return/                     # Emails para devoluciones
│   │   ├── Managers/                   # Emails para administradores
│   │   ├── Supports/Tickets/          # Emails de soporte
│   │   └── Campaigns/Giftvoucher/     # Emails de campañas
│   ├── Jobs/
│   │   ├── Email/
│   │   │   └── SendEndpointEmailJob.php  # Job para enviar emails vía endpoint
│   │   ├── MailSend.php                 # Job genérico de envío
│   │   ├── SendEmails.php               # Job de envío en lote
│   │   ├── SendConfirmationEmailJob.php # Job de confirmación
│   │   ├── UpdateMailListJob.php        # Job para actualizar listas
│   │   └── VerifyMailListJob.php        # Job para verificar listas
│   ├── Events/
│   ├── Listeners/
│   ├── Notifications/
│   ├── Factories/
│   ├── Commands/
│   ├── Enums/
│   ├── Entities/
│   ├── Policies/
│   ├── Traits/
│   └── Helpers/
├── database/
│   ├── migrations/
│   │   ├── 2025_12_29_020501_create_mail_layouts_table.php
│   │   ├── 2025_12_29_020502_create_mail_templates_table.php
│   │   ├── 2025_12_29_020503_create_mail_variables_table.php
│   │   ├── 2025_12_29_020504_create_mail_endpoints_table.php
│   │   ├── 2025_12_29_020505_create_mail_template_langs_table.php
│   │   ├── 2025_12_29_020506_create_mail_layout_langs_table.php
│   │   ├── 2025_12_29_020507_create_mail_variable_translations_table.php
│   │   └── 2025_12_29_020508_create_mail_endpoint_logs_table.php
│   └── seeders/
├── routes/
│   ├── managers.php                     # Rutas del panel de administración
│   └── api.php                          # Rutas de API
├── resources/
│   └── views/mailers/
│       ├── auth/                        # Vistas de emails de autenticación
│       ├── distributors/               # Vistas para distribuidores
│       └── campaigns/                  # Vistas para campañas
├── tests/
│   ├── Feature/
│   └── Unit/
├── module.json                          # Configuración del módulo
└── README.md                            # Esta documentación

```

## Características Principales

### 1. **Gestión de Plantillas de Email**
- CRUD completo de plantillas de email
- Soporte multiidioma (traducciones)
- Variables dinámicas substituyibles
- Layout personalizable (header/footer)
- Previsualización en tiempo real
- Envío de emails de prueba

### 2. **Componentes Reutilizables**
- Bloques de contenido que se pueden reutilizar
- Duplicación de componentes
- Soporte multiidioma
- Previsualización
- Variables específicas por componente

### 3. **Sistema de Variables**
- Variables del sistema (obligatorias)
- Variables personalizadas
- Traducción de descripciones
- Categorización por módulo
- Valores dinámicos basados en contexto

### 4. **API de Endpoints**
- Envío de emails vía API REST
- Autenticación por token
- Validación de variables
- Logging de envíos
- Límites de velocidad (throttling)

### 5. **Jobs en Queue**
- `SendEndpointEmailJob` - Envío asincrónico vía endpoint
- `MailSend` - Job genérico de envío
- `SendEmails` - Envío en lote
- `SendConfirmationEmailJob` - Emails de confirmación
- `UpdateMailListJob` - Actualización de listas
- `VerifyMailListJob` - Verificación de listas

## Rutas del Módulo

### Rutas de Administración (Manager)
```
/manager/settings/mailers/
├── templates/              # Gestión de plantillas
├── components/             # Gestión de componentes
├── variables/              # Gestión de variables
└── endpoints/              # Gestión de endpoints API
```

### Rutas de API
```
/api/email-endpoints/
├── {slug}/send             # POST - Enviar email
├── {slug}/info             # GET - Información del endpoint
└── {slug}/status           # GET - Estado del endpoint
```

## Servicios Clave

### `MailVariableService`
- Obtiene variables disponibles
- Agrupa por módulo y categoría
- Obtiene valores traducidos

### `MailVariableValueService`
- Obtiene valores específicos de variables
- Maneja fallback de idiomas
- Resuelve variables dinámicas

### `MailTemplateRendererService`
- Renderiza plantillas con variables
- Aplica layouts
- Maneja sustitución de variables

## Integración con Otros Módulos

### Documents Module
- Envía emails de notificación de documentos
- Archivos relacionados:
  - `Modules/Documents/app/Mail/DocumentCustomMail.php`
  - `Modules/Documents/app/Services/DocumentMailService.php`

### Helpdesk Module
- Notificaciones de tickets
- Respuestas y comentarios
- Cambios de estado

## Migraciones de Base de Datos

Todas las migraciones están en `database/migrations/`. Las tablas principales son:

- `mail_templates` - Plantillas de email
- `mail_template_langs` - Traducciones de plantillas
- `mail_layouts` - Layouts (header/footer)
- `mail_layout_langs` - Traducciones de layouts
- `mail_variables` - Variables disponibles
- `mail_variable_translations` - Traducciones de variables
- `mail_endpoints` - Endpoints de API
- `mail_endpoint_logs` - Logs de envíos

## Configuración

El módulo se registra automáticamente en `bootstrap/providers.php`:
```php
Modules\Mail\Providers\MailServiceProvider::class,
```

### Servicios Singleton
El MailServiceProvider registra estos servicios como singletons:
- `MailVariableService`
- `MailTemplateRendererService`
- `MailVariableValueService`

## Convenciones de Código

### Modelos
- Todos usan trait `HasUid` para identificadores únicos
- Soportan relaciones multiidioma
- Utilizan fillable y casts apropiados

### Controladores
- Heredan de `App\Http\Controllers\Controller`
- Utilizan Form Requests para validación
- Retornan vistas o JSON según contexto

### Vistas
- Ubicadas en `resources/views/mailers/`
- Organizadas por contexto (auth, distributors, campaigns)
- Utilizan componentes Blade reutilizables

## Testing

Tests del módulo se encuentran en:
- `tests/Feature/` - Tests de integración
- `tests/Unit/` - Tests unitarios

Ejecutar tests del módulo:
```bash
php artisan test Modules/Mail
```

## Problemas Comunes

### Las vistas no se cargan
Asegúrate de que las rutas de vistas están registradas en `MailServiceProvider::registerViews()`

### Las migraciones no funcionan
Verifica que las migraciones estén en `database/migrations/` del módulo

### Las referencias de controladores fallan
Asegúrate de usar `Modules\Mail\Http\Controllers` en lugar de `App\Http\Controllers`

## Más Información

- [Documentación de Nwidart Modules](https://nwidart.com/laravel-modules/)
- Estructura similar en `Modules/Documents/`
- Ver `bootstrap/providers.php` para el registro del módulo
