# Laravel Scheduler Setup - Alsernet

Este documento describe la configuración del scheduler de Laravel utilizando **Supervisor** tanto en desarrollo como en producción.

## Configuración Actual (Desarrollo & Producción)

### Procesos en Supervisor

1. **Scheduler** (`Alsernet-scheduler`)
   - Ejecuta: `/Users/functionbytes/Function/Coding/Alsernet/scheduler-loop.sh`
   - Función: Ejecuta `php artisan schedule:run` cada minuto
   - Estado: `RUNNING`

2. **Queue Worker** (`Alsernet-queue`)
   - Ejecuta: `php artisan queue:work --queue=default --timeout=120 --tries=3`
   - Función: Procesa trabajos en la cola
   - Estado: `RUNNING`

## Arquitectura

```
Supervisor (Daemon Manager)
  ├── Scheduler Loop (Infinito)
  │   └── php artisan schedule:run (cada minuto)
  │       └── Ejecuta tareas programadas (backups, etc.)
  │
  └── Queue Worker (Infinito)
      └── Procesa jobs de la cola de trabajos
```

## Comandos Útiles

### Ver estado de procesos

```bash
# Ver todos los procesos de Alsernet
supervisorctl status | grep Alsernet

# Ver estado específico
supervisorctl status Alsernet-scheduler
supervisorctl status Alsernet-queue
```

### Controlar procesos

```bash
# Parar/Iniciar/Reiniciar scheduler
supervisorctl stop Alsernet-scheduler
supervisorctl start Alsernet-scheduler
supervisorctl restart Alsernet-scheduler

# Lo mismo para queue worker
supervisorctl stop Alsernet-queue
supervisorctl start Alsernet-queue
supervisorctl restart Alsernet-queue

# Parar todo
supervisorctl stop all
```

### Ver logs

```bash
# Logs del scheduler
tail -f /Users/functionbytes/Function/Coding/Alsernet/storage/logs/supervisor-schedule.log

# Logs del queue worker
tail -f /Users/functionbytes/Function/Coding/Alsernet/storage/logs/supervisor-queue.log

# Logs de la aplicación
tail -f /Users/functionbytes/Function/Coding/Alsernet/storage/logs/laravel.log
```

### Recargar configuración

```bash
# Si cambias los archivos de configuración
supervisorctl reread
supervisorctl update
```

## Archivos de Configuración

### Scheduler (`/opt/homebrew/etc/supervisor.d/Alsernet-scheduler.conf`)

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

### Queue Worker (`/opt/homebrew/etc/supervisor.d/Alsernet-queue.conf`)

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

### Script Scheduler Loop (`/Users/functionbytes/Function/Coding/Alsernet/scheduler-loop.sh`)

```bash
#!/bin/bash
cd /Users/functionbytes/Function/Coding/Alsernet

while true; do
    php artisan schedule:run >> /dev/null 2>&1
    sleep 60
done
```

## Tareas Programadas

Para ver todas las tareas programadas:

```bash
php artisan schedule:list
```

Salida esperada:
```
Timezone: Europe/Paris

┌──────────────────────────────────────────┬──────────┬──────────────────┐
│ Command                                  │ Interval │ Description      │
├──────────────────────────────────────────┼──────────┼──────────────────┤
│ app:run-scheduled-backups                │ * * * * │ Every minute     │
│ backup:run                               │ 0 3 * * │ Daily at 03:00   │
│ backup:clean                             │ 0 4 * * │ Daily at 04:00   │
│ backup:monitor                           │ 0 5 * * │ Daily at 05:00   │
│ ... (más tareas)                         │         │                  │
└──────────────────────────────────────────┴──────────┴──────────────────┘
```

## Monitoreo de Backups

### Ver últimos backups

```bash
ls -lht /Users/functionbytes/Function/Coding/Alsernet/storage/app/A-alvarez/*.zip | head -10
```

### Ver logs de backups

```bash
tail -50 storage/logs/laravel.log | grep -i backup
```

## Solución de Problemas

### Los procesos no están corriendo

```bash
# Verificar estado de Supervisor
brew services list | grep supervisor

# Reiniciar Supervisor
brew services restart supervisor

# Esperar 5 segundos y verificar estado
sleep 5
supervisorctl status | grep Alsernet
```

### El scheduler no ejecuta las tareas

1. Verifica que `isInitiated()` devuelve `TRUE`:
   ```bash
   php artisan tinker
   > isInitiated()
   # TRUE
   ```

2. Verifica que existe el archivo marcador:
   ```bash
   ls -la storage/app/installed
   ```

3. Si no existe, créalo:
   ```bash
   touch storage/app/installed
   ```

4. Limpia caches:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

### Script del scheduler tiene permisos incorrectos

```bash
# Hacer ejecutable
chmod +x /Users/functionbytes/Function/Coding/Alsernet/scheduler-loop.sh

# Verificar permisos
ls -la /Users/functionbytes/Function/Coding/Alsernet/scheduler-loop.sh
# Debe mostrar: -rwxr-xr-x
```

## Diferencias: Desarrollo vs Producción

| Aspecto | Desarrollo (macOS) | Producción (Linux) |
|---------|-------------------|-------------------|
| **Manager** | Supervisor (brew) | Supervisor (apt/yum) |
| **Configuración** | `/opt/homebrew/etc/supervisor.d/` | `/etc/supervisor/conf.d/` |
| **Usuario** | `functionbytes` | `www-data` |
| **PHP Path** | `/opt/homebrew/bin/php` | `/usr/bin/php` |
| **Logs** | `storage/logs/` | `storage/logs/` |

## Instalación en Producción (Linux)

Ver [SUPERVISOR_SETUP.md](SUPERVISOR_SETUP.md) para instrucciones detalladas de instalación en servidor Linux.

## Archivos Relacionados

- `scheduler-loop.sh` - Script que mantiene el scheduler ejecutándose
- `SUPERVISOR_SETUP.md` - Instrucciones para producción en Linux
- `app/Console/Kernel.php` - Definición de tareas programadas
- `supervisord-schedule.conf` - Template de configuración (referencia)

## Referencias

- [Laravel Scheduler Documentation](https://laravel.com/docs/scheduling)
- [Supervisor Documentation](http://supervisord.org/)
- [Homebrew Supervisor](https://formulae.brew.sh/formula/supervisor)
