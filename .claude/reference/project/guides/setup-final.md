# Configuración Final - Alsernet

## Estado General

✅ **LISTO PARA USAR**

### Componentes Configurados

1. **Herd** (Web Server)
   - Sirve la aplicación en: `https://Alsernet.test`
   - Maneja: PHP, Nginx, MySQL, Redis
   - Status: Enlazado y activo

2. **Supervisor** (Proceso Manager)
   - **Scheduler**: Ejecuta tareas programadas cada minuto
   - **Queue Worker**: Procesa jobs en background
   - Status: 2/2 procesos RUNNING

## Acceso a la Aplicación

```
URL: https://Alsernet.test
Acceso: Con Herd activado
```

## Procesos en Ejecución

### Ver Estado

```bash
# Verificar Supervisor
supervisorctl status | grep Alsernet

# Salida esperada:
# Alsernet-queue:Alsernet-queue_00     RUNNING   pid XXXXX, uptime X:XX:XX
# Alsernet-scheduler:Alsernet-scheduler_00 RUNNING pid XXXXX, uptime X:XX:XX
```

### Controlar Procesos

```bash
# Parar todos
supervisorctl stop Alsernet-*

# Iniciar todos
supervisorctl start Alsernet-*

# Reiniciar Supervisor completo
brew services restart supervisor
```

## Tareas Programadas

Ver todas las tareas:

```bash
php artisan schedule:list
```

### Tareas Activas

| Tarea | Horario | Descripción |
|-------|---------|-------------|
| `app:run-scheduled-backups` | Cada minuto | Ejecuta backups programados |
| `backup:run` | 03:00 diario | Backup automático |
| `backup:clean` | 04:00 diario | Limpiar backups antiguos |
| `backup:monitor` | 05:00 diario | Monitorear salud de backups |

## Monitoreo de Backups Automáticos

### Ver últimos backups creados

```bash
ls -lht storage/app/A-alvarez/*.zip | head -5
```

### Ver logs de ejecución

```bash
# Logs del scheduler
tail -f storage/logs/supervisor-schedule.log

# Logs del queue worker
tail -f storage/logs/supervisor-queue.log

# Logs de la aplicación
tail -f storage/logs/laravel.log
```

## Archivos Importantes

```
/Users/functionbytes/Function/Coding/Alsernet/
├── scheduler-loop.sh                    # Script que ejecuta schedule:run
├── app/Console/Kernel.php              # Definición de tareas programadas
├── SCHEDULER_SETUP.md                  # Documentación de scheduler
├── SUPERVISOR_SETUP.md                 # Documentación para producción (Linux)
└── supervisord-schedule.conf           # Template de configuración
```

## Configuración de Supervisor

### Archivos de configuración

```
/opt/homebrew/etc/supervisor.d/
├── Alsernet-scheduler.conf             # Configuración del scheduler
└── Alsernet-queue.conf                 # Configuración del queue worker
```

## Solución Rápida de Problemas

### Los procesos no están corriendo

```bash
# Reiniciar Supervisor
brew services restart supervisor

# Esperar 5 segundos
sleep 5

# Verificar
supervisorctl status | grep Alsernet
```

### No se ejecutan los backups

```bash
# 1. Verifica isInitiated
php artisan tinker
> isInitiated()
# TRUE

# 2. Verifica que existe el archivo marcador
ls -la storage/app/installed
# Si no existe, crea:
touch storage/app/installed

# 3. Limpia caches
php artisan cache:clear
php artisan config:clear

# 4. Prueba manual
php artisan schedule:run --verbose
```

### Permisos incorrectos en el script

```bash
# Hacer ejecutable
chmod +x scheduler-loop.sh

# Verificar
ls -la scheduler-loop.sh
# Debe mostrar: -rwxr-xr-x
```

## Comparativa de Configuración

### Antes (launchd)
- ❌ Procesos de launchd (poco escalable)
- ❌ Sin interfaz de gestión
- ❌ Configuración manual en .plist

### Ahora (Supervisor + Herd)
- ✅ Supervisor para procesos
- ✅ Herd para web server
- ✅ Mismo setup en dev y producción
- ✅ Fácil gestión y monitoreo
- ✅ Logs centralizados

## Próximos Pasos en Producción

Cuando despliegues a producción (Linux):

1. Instala Supervisor en el servidor:
   ```bash
   sudo apt-get install supervisor
   ```

2. Copia la configuración:
   ```bash
   sudo cp supervisord-schedule.conf /etc/supervisor/conf.d/Alsernet-scheduler.conf
   ```

3. Actualiza rutas y usuario en la configuración

4. Recarga Supervisor:
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   ```

Ver [SUPERVISOR_SETUP.md](SUPERVISOR_SETUP.md) para instrucciones detalladas.

## Checklist de Verificación

- ✅ Herd enlazado al proyecto
- ✅ Acceso a `https://Alsernet.test` funciona
- ✅ Supervisor scheduler RUNNING
- ✅ Supervisor queue worker RUNNING
- ✅ `php artisan schedule:list` muestra tareas
- ✅ `isInitiated()` retorna TRUE
- ✅ `storage/app/installed` existe
- ✅ Logs se crean en `storage/logs/`

## Documentación Completa

- [SCHEDULER_SETUP.md](SCHEDULER_SETUP.md) - Detalles de scheduler
- [SUPERVISOR_SETUP.md](SUPERVISOR_SETUP.md) - Instalación en Linux
- [HERD_SETUP.md](HERD_SETUP.md) - (Anterior, ya no se usa)

## Contacto & Soporte

Para cualquier problema:

1. Verifica [SCHEDULER_SETUP.md](SCHEDULER_SETUP.md) - Solución de Problemas
2. Revisa los logs en `storage/logs/`
3. Ejecuta `supervisorctl status` para ver procesos
4. Ejecuta `php artisan schedule:list` para ver tareas

---

**Última actualización**: 2025-11-29 12:30
**Status**: ✅ Completamente funcional
