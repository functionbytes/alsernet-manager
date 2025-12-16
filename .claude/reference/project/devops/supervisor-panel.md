# Panel de Control - Supervisor

## Acceso al Panel

**URL**: `https://Alsernet.test/manager/settings/supervisor`

El panel de control de Supervisor está disponible en la sección de **Configuración > Sistema > Supervisor**.

## Funcionalidades

### 1. Dashboard Principal

La página principal del panel muestra:

- **Total de Procesos**: Cantidad de procesos registrados en Supervisor
- **Procesos Activos (RUNNING)**: Procesos que se están ejecutando correctamente
- **Procesos Detenidos**: Procesos parados o en estado de espera
- **Procesos Alsernet**: Procesos específicos de la aplicación

### 2. Procesos de Alsernet

Tabla dedicada a los procesos principales de la aplicación:

- **Alsernet-scheduler**: Ejecuta tareas programadas cada minuto
- **Alsernet-queue**: Procesa trabajos de la cola en background

Acciones disponibles para cada proceso:

- **Si está RUNNING**:
  - 🛑 **Detener**: Pausa la ejecución del proceso
  - 🔄 **Reiniciar**: Reinicia el proceso sin perder datos
  - 👁️ **Detalles**: Ver logs y información completa

- **Si está STOPPED**:
  - ▶️ **Iniciar**: Reinicia la ejecución del proceso
  - 👁️ **Detalles**: Ver logs y última información

### 3. Vista de Detalles del Proceso

Al hacer clic en **Detalles** de un proceso, verás:

#### Información del Proceso
- **Estado Actual**: Estado en tiempo real
- **PID**: Identificador único del proceso en el sistema
- **Uptime**: Tiempo que lleva ejecutándose ininterrumpidamente
- **Detalles Completos**: Información adicional como memoria, tiempo de ejecución, etc.

#### Logs en Tiempo Real
- Los logs se actualizan automáticamente cada 10 segundos
- Muestra las últimas 100 líneas de salida
- Scroll automático al final de los logs
- Botón manual para actualizar logs

### 4. Controles Principales

#### Recargar Configuración
Botón **Recargar Configuración** en la parte superior:
- Ejecuta `supervisorctl reread` y `supervisorctl update`
- Útil después de cambiar archivos de configuración
- Los procesos se reinician brevemente durante la recarga

#### Actualizar Estado
Botón **Actualizar Estado**:
- Recarga la información de procesos en tiempo real
- Se actualiza automáticamente cada 5 segundos

### 5. Tareas Programadas y Comandos Artisan

El panel incluye funcionalidades adicionales (inspiradas en Mercosan CronjobController) para gestionar tareas programadas:

#### Scheduled Jobs
- Ver todos los jobs programados en Laravel
- Mostrar próxima ejecución de cada tarea
- Información detallada de expresiones cron

#### Ejecutar Scheduler Manualmente
- Ejecutar `schedule:run` bajo demanda
- Útil para testing de tareas
- Ver salida del comando

#### Ejecutar Comandos Artisan
- Ejecutar comandos Artisan directamente desde el panel
- Ejemplos: `cache:clear`, `config:cache`, etc.
- Ver salida del comando ejecutado

#### Listar Comandos Disponibles
- Ver todos los comandos Artisan disponibles
- Incluye descripción de cada comando
- Fallback con comandos comunes si hay error

## Procesos Alsernet

### Scheduler (`Alsernet-scheduler`)

```
Comando: /Users/functionbytes/Function/Coding/Alsernet/scheduler-loop.sh
Función: Ejecuta php artisan schedule:run cada minuto
Estado esperado: RUNNING
```

**Propósito**: Ejecutar tareas programadas como:
- Backups automáticos
- Limpieza de backups antiguos
- Monitoreo de salud de backups

### Queue Worker (`Alsernet-queue`)

```
Comando: php artisan queue:work --queue=default --timeout=120 --tries=3
Función: Procesa trabajos asincronos de la cola
Estado esperado: RUNNING
```

**Propósito**: Ejecutar tareas en background como:
- Creación de backups
- Envío de correos electrónicos
- Procesamiento de datos pesados

## Solución de Problemas

### Los procesos no están RUNNING

1. Ve al panel: `https://Alsernet.test/manager/settings/supervisor`
2. Verifica el estado de cada proceso
3. Si están STOPPED, haz clic en **Iniciar**
4. Si el estado no cambia, revisa los logs

### Logs vacíos o sin información

1. Haz clic en el proceso que deseas investigar
2. Ve a la sección de **Logs del Proceso**
3. Espera a que se actualicen (cada 10 segundos)
4. Haz clic en **Actualizar** manualmente si es necesario

### Proceso se detiene constantemente

1. Ver logs del proceso en el panel
2. Buscar mensajes de error
3. Verificar que los permisos de archivos sean correctos:
   ```bash
   ls -la /Users/functionbytes/Function/Coding/Alsernet/scheduler-loop.sh
   chmod +x /Users/functionbytes/Function/Coding/Alsernet/scheduler-loop.sh
   ```

### Cambios en configuración no se aplican

1. Edita el archivo de configuración apropiado:
   ```
   /opt/homebrew/etc/supervisor.d/Alsernet-scheduler.conf
   /opt/homebrew/etc/supervisor.d/Alsernet-queue.conf
   ```
2. Haz clic en **Recargar Configuración** en el panel
3. Espera a que se reinicien los procesos

## Archivos de Configuración

### Configuración del Scheduler
**Ruta**: `/opt/homebrew/etc/supervisor.d/Alsernet-scheduler.conf`

```ini
[program:Alsernet-scheduler]
process_name=%(program_name)s_%(process_num)02d
command=/Users/functionbytes/Function/Coding/Alsernet/scheduler-loop.sh
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/Users/functionbytes/Function/Coding/Alsernet/storage/logs/supervisor-schedule.log
stopwaitsecs=60
user=functionbytes
environment=PATH="/opt/homebrew/bin:/usr/local/bin:/usr/bin:/bin",HOME="/Users/functionbytes"
```

### Configuración del Queue Worker
**Ruta**: `/opt/homebrew/etc/supervisor.d/Alsernet-queue.conf`

```ini
[program:Alsernet-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /Users/functionbytes/Function/Coding/Alsernet/artisan queue:work --queue=default --timeout=120 --tries=3
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/Users/functionbytes/Function/Coding/Alsernet/storage/logs/supervisor-queue.log
stopwaitsecs=60
user=functionbytes
environment=PATH="/opt/homebrew/bin:/usr/local/bin:/usr/bin:/bin",HOME="/Users/functionbytes"
```

## API Endpoints

El panel expone varios endpoints JSON para integración programática:

### Procesos Supervisor
- `GET /manager/settings/supervisor/status/ajax` - Estado de todos los procesos
- `POST /manager/settings/supervisor/{processName}/start` - Iniciar proceso
- `POST /manager/settings/supervisor/{processName}/stop` - Detener proceso
- `POST /manager/settings/supervisor/{processName}/restart` - Reiniciar proceso
- `GET /manager/settings/supervisor/{processName}/logs` - Obtener logs
- `POST /manager/settings/supervisor/reload` - Recargar configuración

### Tareas Programadas y Comandos
- `GET /manager/settings/supervisor/api/scheduled-jobs` - Listar tareas programadas
- `POST /manager/settings/supervisor/api/run-scheduler` - Ejecutar scheduler
- `POST /manager/settings/supervisor/api/run-command` - Ejecutar comando Artisan
- `GET /manager/settings/supervisor/api/list-commands` - Listar comandos disponibles

**Ejemplo de uso:**
```bash
# Obtener scheduled jobs
curl -H "Authorization: Bearer TOKEN" \
  https://Alsernet.test/manager/settings/supervisor/api/scheduled-jobs

# Ejecutar scheduler
curl -X POST -H "Authorization: Bearer TOKEN" \
  https://Alsernet.test/manager/settings/supervisor/api/run-scheduler

# Ejecutar comando
curl -X POST -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"command": "cache:clear"}' \
  https://Alsernet.test/manager/settings/supervisor/api/run-command
```

## Monitoreo desde la Línea de Comandos

Aunque ahora tienes el panel gráfico, también puedes usar estos comandos:

### Ver estado de procesos
```bash
supervisorctl status | grep Alsernet
```

### Ver logs específicos
```bash
tail -f storage/logs/supervisor-schedule.log
tail -f storage/logs/supervisor-queue.log
```

### Controlar procesos directamente
```bash
supervisorctl start Alsernet-scheduler
supervisorctl stop Alsernet-scheduler
supervisorctl restart Alsernet-scheduler
supervisorctl reread
supervisorctl update
```

## Flujo de Trabajo Típico

1. **Verificar Estado**: Abre el panel y verifica que ambos procesos estén RUNNING
2. **Crear Backup**: Ve a Configuración > Backups > Crear Copia
3. **Programar Backup**: Ve a Configuración > Backups > Backups Programados
4. **Monitorear**: El scheduler ejecutará automáticamente tareas a las horas programadas
5. **Revisar Logs**: Si hay problemas, ve al panel y revisa los logs de cada proceso

## Seguridad

El panel está protegido por:
- Autenticación de usuario (requiere login)
- Middleware de autenticación de Laravel
- CSRF tokens en todas las peticiones POST

Solo usuarios autenticados con acceso al panel de administración pueden:
- Ver estado de procesos
- Ver logs
- Iniciar/detener/reiniciar procesos
- Recargar configuración

## Contacto & Soporte

Para problemas:
1. Revisa la sección de Solución de Problemas en este documento
2. Consulta los logs en `storage/logs/`
3. Verifica que Supervisor esté corriendo: `brew services list | grep supervisor`
4. Reinicia Supervisor si es necesario: `brew services restart supervisor`

## Inspiración: Mercosan CronjobController

Este panel fue mejorado incorporando ideas del proyecto Mercosan, específicamente del `CronjobController.php`.

### Comparativa de Enfoque

**Mercosan CronjobController** se enfoca en:
- ✅ Tareas programadas (scheduled jobs)
- ✅ Ejecución de comandos Artisan
- ✅ Listar comandos disponibles
- ❌ No gestiona procesos Supervisor

**Alsernet SupervisorController** (completo) se enfoca en:
- ✅ Gestión de procesos Supervisor (start/stop/restart)
- ✅ Logs en tiempo real
- ✅ Estado de procesos
- ✅ Tareas programadas (scheduled jobs)
- ✅ Ejecución de comandos Artisan
- ✅ Listar comandos disponibles

### Ventajas de la Implementación Alsernet

1. **Panel Unificado**: Gestiona tanto Supervisor como tareas programadas
2. **Real-time Monitoring**: Logs y estado actualizado cada 5 segundos
3. **Interfaz Visual**: Dashboard intuitivo con gráficos y estadísticas
4. **API Completa**: Endpoints para integración programática
5. **Proceso Management**: Control total sobre procesos daemon

---

**Última actualización**: 2025-11-29
**Status**: ✅ Panel completamente funcional con features inspiradas en Mercosan
