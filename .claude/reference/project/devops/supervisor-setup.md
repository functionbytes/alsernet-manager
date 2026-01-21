# Supervisor Configuration for Laravel Scheduler (Production)

Este documento describe cómo configurar **Supervisor** en el servidor de producción para ejecutar el scheduler de Laravel automáticamente.

> **Nota**: Supervisor es para servidores **Linux**. En macOS (desarrollo), usa **Herd**.

## Requisitos

- Servidor Linux (Ubuntu, Debian, CentOS, etc.)
- PHP CLI
- Supervisor instalado

## Instalación de Supervisor

### En Ubuntu/Debian:
```bash
sudo apt-get update
sudo apt-get install supervisor
```

### En CentOS/RHEL:
```bash
sudo yum install supervisor
```

## Configuración

### 1. Crear archivo de configuración

Crea un nuevo archivo de configuración en `/etc/supervisor/conf.d/`:

```bash
sudo nano /etc/supervisor/conf.d/Alsernet-scheduler.conf
```

Pega el siguiente contenido (reemplaza `/path/to/Alsernet` con la ruta real):

```ini
[program:Alsernet-scheduler]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/Alsernet/artisan schedule:run
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/Alsernet/storage/logs/supervisor-schedule.log
stopwaitsecs=3600
user=www-data
environment=PATH="/usr/local/bin:/usr/bin:/bin",HOME="/home/www-data"
```

### Explicación de parámetros:

| Parámetro | Descripción |
|-----------|-------------|
| `command` | Comando a ejecutar (el scheduler) |
| `autostart` | Inicia automáticamente al arrancar Supervisor |
| `autorestart` | Reinicia automáticamente si falla |
| `numprocs` | Número de procesos (1 es suficiente para scheduler) |
| `stdout_logfile` | Archivo de log |
| `user` | Usuario que ejecuta el proceso (normalmente www-data) |
| `environment` | Variables de entorno necesarias |

### 2. Recargar configuración de Supervisor

```bash
sudo supervisorctl reread
sudo supervisorctl update
```

### 3. Verificar estado

```bash
sudo supervisorctl status

# Output esperado:
# Alsernet-scheduler:Alsernet-scheduler_00   RUNNING   pid 12345, uptime 0:05:23
```

## Comandos útiles de Supervisor

```bash
# Ver estado de todos los procesos
sudo supervisorctl status

# Ver solo nuestro proceso
sudo supervisorctl status Alsernet-scheduler

# Iniciar/Parar/Reiniciar el scheduler
sudo supervisorctl start Alsernet-scheduler
sudo supervisorctl stop Alsernet-scheduler
sudo supervisorctl restart Alsernet-scheduler

# Ver logs
tail -f /path/to/Alsernet/storage/logs/supervisor-schedule.log

# Recargar configuración (después de cambios)
sudo supervisorctl reread
sudo supervisorctl update
```

## Verificación

Para verificar que el scheduler está ejecutándose correctamente:

```bash
# Ver si los backups se ejecutan a tiempo
php artisan schedule:list

# Ver logs del sistema
tail -f storage/logs/laravel.log
tail -f /path/to/Alsernet/storage/logs/supervisor-schedule.log
```

## Solución de problemas

### El proceso no inicia

```bash
# Ver error detallado
sudo supervisorctl tail -f Alsernet-scheduler stderr

# Verificar permisos del directorio
ls -la /path/to/Alsernet/storage/logs/
# Debe ser propiedad de www-data
sudo chown -R www-data:www-data /path/to/Alsernet/storage
```

### El scheduler no ejecuta los comandos

1. Verifica que `isInitiated()` devuelve `TRUE`:
   ```bash
   php artisan tinker
   > isInitiated()
   ```

2. Verifica que `storage/app/installed` existe:
   ```bash
   ls -la /path/to/Alsernet/storage/app/installed
   ```

3. Si no existe, créalo:
   ```bash
   touch /path/to/Alsernet/storage/app/installed
   ```

### Permisos incorrectos

```bash
# Dar permisos correctos
sudo chown -R www-data:www-data /path/to/Alsernet
sudo chmod -R 755 /path/to/Alsernet
sudo chmod -R 775 /path/to/Alsernet/storage
sudo chmod -R 775 /path/to/Alsernet/bootstrap/cache
```

## Monitoreo

Recomendaciones para monitoreo en producción:

```bash
# Ver logs en tiempo real
sudo tail -f /path/to/Alsernet/storage/logs/supervisor-schedule.log

# Configurar rotación de logs (crear en cron)
0 0 * * * logrotate /etc/logrotate.d/Alsernet-scheduler
```

## Alternativa: Cron Job

Si prefieres usar cron en lugar de Supervisor (menos recomendado):

```bash
# Editar crontab
crontab -e

# Añadir esta línea para ejecutar cada minuto
* * * * * php /path/to/Alsernet/artisan schedule:run >> /dev/null 2>&1
```

## Referencias

- [Laravel Scheduler Documentation](https://laravel.com/docs/scheduling)
- [Supervisor Documentation](http://supervisord.org/)
