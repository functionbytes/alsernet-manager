# Panel de Control de Supervisor - Guía de Configuración

## Overview

El nuevo panel de Supervisor para Alsernet proporciona una interfaz completa para gestionar procesos, crear backups, editar configuraciones y ver logs en tiempo real.

## Features Implementadas

### ✅ Diagnóstico y Estadísticas
- **Estado en tiempo real** de procesos (actualización cada 5 segundos)
- **Estadísticas visuales**: Total de procesos, activos, detenidos, Alsernet
- **Filtrado automático** de procesos Alsernet

### ✅ Gestión de Procesos
- Iniciar, detener y reiniciar procesos individuales
- Reiniciar servicio Supervisor completo
- Recargar configuración sin detener servicios
- Ver detalles y logs de cada proceso

### ✅ Sistema de Backups
- **Crear backups** manuales de configuraciones
- **Restaurar backups** con un clic
- **Descargar backups** como JSON
- **Eliminar backups** antiguos
- **Filtrar por ambiente** (dev, prod, staging)
- Auto-backup antes de editar configuraciones

### ✅ Gestión de Configuraciones
- **Editor inline** de archivos .conf
- **Selección visual** de archivos
- **Auto-backup** antes de cada cambio
- **Validación y seguridad** de rutas permitidas

### ✅ Visualización de Logs
- **Logs en tiempo real** de procesos
- **Selector de procesos** para cambiar dinámicamente
- **Interfaz dark** optimizada para logs

## Configuración Requerida

### 1. Configurar Permisos de Sudo

El sistema ejecuta comandos `supervisorctl` y `systemctl` con `sudo`. Para que funcione sin pedir contraseña:

```bash
# Editar sudoers (SIEMPRE usar visudo)
sudo visudo
```

Agregar estas líneas al final (reemplazar `www-data` si usas otro usuario web):

```sudoers
# Supervisor management for Alsernet
www-data ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl
www-data ALL=(ALL) NOPASSWD: /bin/systemctl restart supervisor
```

### 2. Ejecutar la Migración

```bash
php artisan migrate
```

Esto creará la tabla `supervisor_backups` para almacenar las configuraciones.

### 3. Configuración de Permisos de Archivos

Asegurar que el usuario web pueda leer/escribir en directorios de configuración:

```bash
# Para configuraciones del proyecto
sudo chown -R www-data:www-data /path/to/Alsernet/config/supervisor/
sudo chmod -R 755 /path/to/Alsernet/config/supervisor/

# Para directorios de supervisor (si es necesario)
sudo chown -R www-data:www-data /etc/supervisor/conf.d/
sudo chmod -R 755 /etc/supervisor/conf.d/
```

### 4. Crear Directorio de Backups (Opcional)

```bash
mkdir -p /path/to/Alsernet/storage/backups
chmod 755 /path/to/Alsernet/storage/backups
```

## Uso del Panel

### Panel Principal
Acceder a: `https://tu-app.local/manager/settings/supervisor`

**Botones principales:**
- **Reiniciar Supervisor**: Reinicia el servicio completo (⚠️ Cuidado)
- **Recargar Config**: Recarga configuración sin detener procesos (✅ Recomendado)
- **Actualizar**: Refresh manual del estado

### Tab: Procesos
- Ver todos los procesos de Alsernet
- Iniciar/detener/reiniciar procesos
- Ver detalles y logs de procesos individuales
- Tabla de todos los procesos del sistema

### Tab: Backups
- **Crear nuevo backup**: Nombre, descripción, ambiente
- **Filtrar**: Por ambiente (dev/prod/staging)
- **Acciones**: Restaurar, descargar, eliminar
- Los backups se crean automáticamente antes de editar configuraciones

### Tab: Configuración
- **Seleccionar archivo**: Lista de archivos .conf del sistema
- **Editor**: Ver y editar contenido
- **Auto-backup**: Se crea automáticamente antes de guardar
- **Permisos**: Solo permite editar archivos en rutas seguras

### Tab: Logs
- **Seleccionar proceso**: Dropdown de procesos Alsernet
- **Ver logs**: Últimas líneas en tiempo real
- **Interfaz dark**: Optimizada para lectura de logs

## Rutas API Disponibles

```
GET    /manager/settings/supervisor/                    # Panel principal
POST   /manager/settings/supervisor/reload              # Recargar config
POST   /manager/settings/supervisor/restart             # Reiniciar servicio
GET    /manager/settings/supervisor/status/ajax         # Estado en tiempo real

# Backups
GET    /manager/settings/supervisor/backups/list        # Listar backups
POST   /manager/settings/supervisor/backups/create      # Crear backup
POST   /manager/settings/supervisor/backups/{id}/restore # Restaurar
DELETE /manager/settings/supervisor/backups/{id}/delete  # Eliminar
GET    /manager/settings/supervisor/backups/{id}/download # Descargar

# Configuración
GET    /manager/settings/supervisor/config/files        # Listar archivos
GET    /manager/settings/supervisor/config/file         # Obtener archivo
POST   /manager/settings/supervisor/config/file/update  # Actualizar archivo

# Procesos
GET    /manager/settings/supervisor/{name}/show         # Detalles proceso
POST   /manager/settings/supervisor/{name}/start        # Iniciar
POST   /manager/settings/supervisor/{name}/stop         # Detener
POST   /manager/settings/supervisor/{name}/restart      # Reiniciar
GET    /manager/settings/supervisor/{name}/logs         # Logs
```

## Estructura de SupervisorBackup

```json
{
  "id": 1,
  "name": "Backup Producción 2024",
  "description": "Backup antes de cambios importantes",
  "environment": "prod",
  "config_files": {
    "/etc/supervisor/conf.d/laravel-queue-worker.conf": "...",
    "/etc/supervisor/conf.d/laravel-scheduler.conf": "..."
  },
  "supervisor_status": [...],
  "backup_size": 5120,
  "backed_up_at": "2024-11-29 15:30:00",
  "restored_at": "2024-11-29 16:45:00",
  "restored_by": "1",
  "is_auto": false,
  "created_at": "2024-11-29 15:30:00",
  "updated_at": "2024-11-29 16:45:00"
}
```

## Troubleshooting

### "Failed to get status" o "Failed to execute supervisorctl"

**Causa**: Permisos insuficientes

**Solución**:
```bash
# Verificar que sudoers está configurado correctamente
sudo visudo -c

# Probar ejecución manual
sudo supervisorctl status

# Si el usuario web no puede ejecutar, añadir a sudoers:
sudo visudo
# Agregar: www-data ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl
```

### Los archivos de configuración no se pueden editar

**Causa**: Permisos de directorio

**Solución**:
```bash
# Verificar permisos
ls -la /etc/supervisor/conf.d/
ls -la config/supervisor/

# Asegurar que www-data tiene permisos
sudo chown www-data:www-data /etc/supervisor/conf.d/
sudo chmod 755 /etc/supervisor/conf.d/
```

### Los backups no se guardan

**Causa**: Tabla no existe

**Solución**:
```bash
php artisan migrate
php artisan migrate:fresh # si es necesario resetear
```

### El auto-backup antes de editar falla

**Causa**: Permisos de escritura en base de datos

**Solución**:
```bash
# Verificar conexión a base de datos
php artisan tinker
# Ejecutar: \App\Models\SupervisorBackup::count()
```

## Recomendaciones de Seguridad

1. **Restringir acceso al panel**: Agregar autenticación/autorización
   ```php
   // En routes/theme.php
   Route::middleware(['auth', 'admin'])->group(function () {
       // Rutas de supervisor
   });
   ```

2. **Auditar cambios**: Los backups incluyen quién hizo los cambios
   ```php
   // Ver logs en storage/logs/
   ```

3. **Hacer backups regulares** antes de cambios importantes
   ```bash
   # Comando para crear backup automático
   php artisan supervisor:backup "Pre-update backup"
   ```

4. **Validar cambios en configuración** antes de guardar
   - Revisar sintaxis de archivos .conf
   - Usar comentarios descriptivos
   - Hacer backup antes de cambios grandes

## Ejemplos de Uso

### Crear un backup de producción
1. Ir a Tab "Backups"
2. Nombre: "Pre-upgrade production"
3. Ambiente: "Producción"
4. Click "Crear Backup"

### Editar archivo de configuración
1. Ir a Tab "Configuración"
2. Seleccionar archivo en la lista
3. Editar contenido
4. Click "Guardar Cambios"
5. Un backup automático se crea antes de guardar

### Restaurar un backup
1. Ir a Tab "Backups"
2. Encontrar el backup
3. Click en botón "Restaurar" (icono de refresh)
4. Confirmar en el diálogo
5. ⚠️ Esto sobrescribirá la configuración actual

### Ver logs en tiempo real
1. Ir a Tab "Logs"
2. Seleccionar proceso en el dropdown
3. Los logs aparecen automáticamente
4. Refrescar manualmente o esperar la actualización automática

## Notas Importantes

- ⚠️ **Reiniciar Supervisor** detiene TODOS los procesos temporalmente
- ✅ **Recargar Config** es más seguro, solo recarga la configuración
- 🔒 **Solo se pueden editar archivos en rutas seguras** (/etc/supervisor/conf.d, config/supervisor)
- 💾 **Los backups se crean automáticamente** antes de cada cambio
- 📊 **Las estadísticas se actualizan cada 5 segundos**

## Próximas Mejoras Sugeridas

- [ ] Validador de sintaxis .conf
- [ ] Historial de cambios
- [ ] Notificaciones en tiempo real
- [ ] Gráficos de uso de recursos
- [ ] Alertas automáticas si un proceso falla
- [ ] CLI para crear backups desde terminal
- [ ] Exportar/importar configuraciones

---

**Versión**: 1.0
**Última actualización**: 2024-11-29
**Mantenimiento**: Alsernet Supervisor Control Panel
