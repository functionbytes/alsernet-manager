# Sistema de Logging Avanzado - Alsernet

## 📋 Descripción General

El nuevo sistema de logging implementado en Alsernet utiliza un **Stack Completo** que combina:

- 📁 **Rotación diaria de archivos** (almacenados por mes/día)
- 🗄️ **Base de datos** para búsqueda y análisis rápido
- 🔔 **Syslog** para eventos críticos
- 🧹 **Limpieza automática** de logs antiguos

## 🏗️ Arquitectura

### Flujo de Logging

```
Aplicación (Laravel)
    ↓
Monolog Logger
    ├── Daily Handler → storage/logs/Y/m/d/laravel.log
    ├── Database Handler → application_logs (tabla)
    └── Syslog Handler → Sistema operativo
```

### Estructura de Carpetas

Los archivos de log se organizan automáticamente:

```
storage/logs/
├── 2025/
│   ├── 11/
│   │   ├── 28/
│   │   │   └── laravel.log
│   │   ├── 29/
│   │   │   └── laravel.log
│   │   └── 30/
│   │       └── laravel.log
│   └── 12/
│       └── 01/
│           └── laravel.log
└── queue-worker.log
```

## 📊 Tabla de Base de Datos

La tabla `application_logs` almacena registros críticos (WARNING, ERROR, etc.):

```sql
CREATE TABLE application_logs (
    id BIGINT PRIMARY KEY,
    level VARCHAR(255) -- ERROR, WARNING, INFO, DEBUG
    channel VARCHAR(255), -- 'stack', 'single', etc.
    message TEXT,
    context JSON, -- Datos adicionales
    extra JSON, -- Información extra
    stack_trace LONGTEXT, -- Stack trace de excepciones
    user_id VARCHAR(255), -- Usuario asociado
    ip_address VARCHAR(45), -- IP de la request
    url VARCHAR(255), -- URL de la request
    method VARCHAR(10), -- HTTP method (GET, POST, etc.)
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP -- Soft deletes
);
```

## 🔧 Configuración

### Variables de Entorno (.env)

```env
# Canal de logging principal
LOG_CHANNEL=stack

# Stack de canales (daily, database, syslog)
LOG_STACK=daily,database,syslog

# Nivel de logging (debug, info, notice, warning, error, critical, alert, emergency)
LOG_LEVEL=debug

# Días de retención para archivos de log
LOG_DAILY_DAYS=30

# Formato de carpetas (Y/m/d = año/mes/día)
LOG_PATH_FORMAT=Y/m/d
```

### Configuración en config/logging.php

**Daily Channel** (Archivos rotados diariamente):
- Ruta: `storage/logs/YYYY/MM/DD/laravel.log`
- Retención: 30 días (configurable)
- Nivel: debug (todos los logs)

**Database Channel** (Base de datos):
- Tabla: `application_logs`
- Nivel: warning (solo WARNING, ERROR, CRITICAL, etc.)
- Formato: JSON

**Syslog Channel** (Sistema operativo):
- Eventos críticos
- Facilidad: LOG_USER
- Integración: Sistema de logging del servidor

## 🚀 Primeros Pasos

### 1. Ejecutar Migraciones

```bash
php artisan migrate
```

Esto creará la tabla `application_logs`.

### 2. Registrar el Service Provider (Opcional)

Si deseas escuchar eventos de logging:

```bash
# En config/app.php, agregar a 'providers':
App\Providers\LoggingServiceProvider::class,
```

### 3. Verificar el Almacenamiento

```bash
# Ver estructura de carpetas
ls -R storage/logs/

# Ver logs de hoy
cat storage/logs/2025/11/29/laravel.log

# Ver logs en BD
php artisan tinker
>>> App\Models\ApplicationLog::count()
```

## 📱 Panel de Control

### Acceso a la Interfaz

Navega a: **Manager → Configuración → Acceso**

### Opciones Disponibles

#### 1. **Selector de Fuente**
- **Base de Datos (Recomendado)**: Búsqueda rápida, filtros avanzados
- **Archivos**: Ver logs históricos, auditoría

#### 2. **Filtros Avanzados** (Solo BD)
- Por Nivel: ERROR, WARNING, INFO, DEBUG
- Por rango de fechas
- Por usuario
- Por IP

#### 3. **Acciones**
- 🧹 **Limpiar registros**: Elimina logs viejos
- 📊 **Estadísticas**: Ver información del servidor
- ⬇️ **Descargar**: Exportar logs

#### 4. **Ver Detalles**
- Haz clic en cualquier registro para ver:
  - Timestamp completo
  - Nivel del log
  - IP Address (desde BD)
  - User ID (desde BD)
  - URL de la request
  - Context JSON
  - Mensaje completo

## 🧹 Limpieza de Logs

### Comando Automático

```bash
# Limpiar logs más antiguos a 30 días
php artisan logs:cleanup

# Limpiar logs más antiguos a 7 días
php artisan logs:cleanup --days=7

# Limpiar logs más antiguos a 1 día
php artisan logs:cleanup --days=1
```

### Agendar Limpieza Automática

En `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Ejecutar limpieza de logs diariamente a las 2 AM
    $schedule->command('logs:cleanup --days=30')
        ->daily()
        ->at('02:00')
        ->onFailure(function () {
            // Log fallo si es necesario
        });
}
```

### Limpieza Manual en UI

En el panel de Acceso, hacer clic en **"Limpiar registros"**.

## 📝 Ejemplos de Uso

### Registrar Logs en tu Código

```php
<?php

use Illuminate\Support\Facades\Log;

// Información
Log::info('Usuario creado', ['user_id' => 1]);

// Advertencia
Log::warning('Memoria baja en el servidor', ['memory' => 85]);

// Error
Log::error('Error al procesar archivo', ['file' => 'users.csv']);

// Debug
Log::debug('Consulta a BD ejecutada', ['query' => 'SELECT * FROM users']);

// Excepciones
try {
    // código
} catch (\Exception $e) {
    Log::error('Error en procesamiento', [
        'exception' => $e,
        'user_id' => auth()->id(),
    ]);
}
```

### Consultar Logs en BD

```php
<?php

use App\Models\ApplicationLog;

// Últimos 10 errores
ApplicationLog::errors()->latest()->limit(10)->get();

// Logs del últimos 7 días
ApplicationLog::recent(7)->get();

// Por nivel específico
ApplicationLog::byLevel('WARNING')->get();

// Por canal
ApplicationLog::byChannel('stack')->get();

// Logs de un usuario
ApplicationLog::where('user_id', 123)->get();

// Logs de una IP
ApplicationLog::where('ip_address', '192.168.1.1')->get();
```

## ⚙️ Tuning y Optimización

### Optimizar Consultas de BD

Agregar índices personalizados:

```php
// En una nueva migración
Schema::table('application_logs', function (Blueprint $table) {
    $table->index(['user_id', 'created_at']);
    $table->index(['ip_address', 'created_at']);
    $table->fulltext(['message']); // MySQL only
});
```

### Monitoreo de Espacio en Disco

```bash
# Ver tamaño de logs
du -sh storage/logs/

# Ver número de registros en BD
php artisan tinker
>>> App\Models\ApplicationLog::count()

# Ver registros por nivel
>>> App\Models\ApplicationLog::selectRaw('level, count(*) as total')
>>>     ->groupBy('level')
>>>     ->get()
```

### Archivado de Logs Antiguos

```bash
# Comprimir logs de más de 30 días
find storage/logs -mtime +30 -name "*.log" -exec gzip {} \;

# Mover a almacenamiento externo
find storage/logs -mtime +30 -name "*.log" -exec mv {} /backup/logs/ \;
```

## 🐛 Troubleshooting

### Los logs no se guardan en BD

1. Verificar que la tabla existe:
   ```bash
   php artisan migrate:status
   ```

2. Verificar que LOG_LEVEL permite WARNING+:
   ```env
   LOG_LEVEL=debug # o warning, error, etc.
   ```

3. Revisar error_log:
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Archivo de log muy grande

```bash
# Comprimir logs antiguos
gzip storage/logs/2025/10/*.log

# Limpiar logs con comando
php artisan logs:cleanup --days=7
```

### Base de datos muy grande

```php
// Limpiar logs soft-deleted
php artisan tinker
>>> App\Models\ApplicationLog::onlyTrashed()->forceDelete();

// O en comando
php artisan logs:cleanup --days=7
```

## 📊 Reportes y Análisis

### Estadísticas de Errores

```php
<?php

use App\Models\ApplicationLog;

// Errores más frecuentes
ApplicationLog::errors()
    ->groupBy('message')
    ->selectRaw('message, count(*) as total')
    ->orderBy('total', 'DESC')
    ->limit(10)
    ->get();

// Errores por hora
ApplicationLog::errors()
    ->selectRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00") as hour, count(*) as total')
    ->groupBy('hour')
    ->orderBy('hour', 'DESC')
    ->get();

// IPs con más errores
ApplicationLog::errors()
    ->groupBy('ip_address')
    ->selectRaw('ip_address, count(*) as total')
    ->orderBy('total', 'DESC')
    ->limit(10)
    ->get();
```

## 📚 Referencias

- [Laravel Logging Documentation](https://laravel.com/docs/11/logging)
- [Monolog Documentation](https://github.com/Seldaek/monolog)
- [PostgreSQL JSON Functions](https://www.postgresql.org/docs/current/functions-json.html)

## 🔐 Seguridad

- ✅ Los logs se guardan con soft deletes (recuperables)
- ✅ IP y User ID se registran automáticamente
- ✅ Stack traces se almacenan de forma segura en BD
- ⚠️ Limpia logs regularmente (30 días por defecto)
- ⚠️ Protege acceso a `/manager/settings/system/access` con permisos

## 📈 Próximas Mejoras

- [ ] Exportar logs a formatos (CSV, Excel)
- [ ] Gráficos de errores en tiempo real
- [ ] Alertas automáticas por email
- [ ] Integración con Slack/Discord
- [ ] Dashboard de análisis avanzado
