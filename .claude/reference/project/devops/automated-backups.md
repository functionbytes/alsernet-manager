# Guía: Backups Automáticos Programados

## Descripción General

El sistema de backups programados permite automatizar la creación de copias de seguridad en horarios específicos, días determinados o intervalos personalizados.

## Componentes del Sistema

### 1. **Tabla `backup_schedules`**
Almacena la configuración de cada schedule:
- Nombre y descripción
- Frecuencia (diario, semanal, mensual, personalizado)
- Hora de ejecución
- Días específicos (para semanal/mensual)
- Tipos de backup a incluir
- Timestamps del último y próximo backup

### 2. **Modelo `BackupSchedule`**
- `shouldRunNow()`: Determina si el schedule debe ejecutarse ahora
- `calculateNextRun()`: Calcula la próxima ejecución
- `markAsRun()`: Registra que se ejecutó

### 3. **Comando `app:run-scheduled-backups`**
- Se ejecuta automáticamente cada minuto vía Laravel Scheduler
- Verifica qué schedules están pendientes
- Dispara los BackupJobs correspondientes

### 4. **Controlador `BackupScheduleController`**
Provee endpoints para:
- Listar schedules
- Crear nuevos schedules
- Editar schedules existentes
- Activar/desactivar schedules
- Eliminar schedules

## Tipos de Frecuencia

### Diario
Se ejecuta todos los días a la hora especificada.

**Ejemplo:**
```
Nombre: Backup Diario
Frecuencia: Diario
Hora: 02:00
Tipos: App Code, Config, Database
```

Se ejecutará:
- Mañana a las 02:00
- Pasado mañana a las 02:00
- Todos los días a esa hora

### Semanal
Se ejecuta en días específicos de la semana.

**Ejemplo:**
```
Nombre: Backup Tri-semanal
Frecuencia: Semanal
Hora: 03:00
Días: Lunes, Miércoles, Viernes
Tipos: Database
```

Se ejecutará:
- Próximo lunes a las 03:00
- Próximo miércoles a las 03:00
- Próximo viernes a las 03:00
- Y se repite cada semana

### Mensual
Se ejecuta en días específicos del mes.

**Ejemplo:**
```
Nombre: Backup de Fin de Mes
Frecuencia: Mensual
Hora: 04:00
Días: 1, 15, 28 (primer, mitad y casi fin de mes)
Tipos: App Code, Config, Database
```

Se ejecutará:
- Día 1 de cada mes a las 04:00
- Día 15 de cada mes a las 04:00
- Día 28 de cada mes a las 04:00

### Personalizado
Se ejecuta cada X horas desde el último backup.

**Ejemplo:**
```
Nombre: Backup Cada 6 Horas
Frecuencia: Personalizado
Hora: 02:00 (se ignora)
Intervalo: 6 horas
Tipos: Database
```

Se ejecutará:
- Inmediatamente (primera vez)
- 6 horas después del primero
- 6 horas después del segundo
- Y así sucesivamente

## Cómo Funciona

### Flujo de Ejecución

```
1. Laravel Scheduler ejecuta cada minuto
        ↓
2. Ejecuta: php artisan app:run-scheduled-backups
        ↓
3. El comando verifica la tabla backup_schedules
        ↓
4. Para cada schedule habilitado:
   - ¿Debe ejecutarse ahora?
   - ¿Es la hora correcta?
   - ¿Coincide el día/frecuencia?
        ↓
5. Si SÍ: Dispara CreateBackupJob
        ↓
6. El job se procesa en queue
        ↓
7. Actualiza last_run_at y next_run_at
```

### Requisito: Crontab

Para que esto funcione, necesitas un cron job que ejecute el scheduler cada minuto:

```bash
* * * * * cd /ruta/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

**Nota:** Si usas Herd, el scheduler corre automáticamente.

## Administración desde la UI

### URL
```
/manager/settings/backups/schedules
```

### Acciones

#### 1. Crear Schedule
- Haz clic en "Crear Schedule"
- Completa el formulario con:
  - Nombre descriptivo
  - Frecuencia deseada
  - Hora de ejecución
  - Días (si aplica)
  - Tipos de backup
- Haz clic en "Crear Schedule"

#### 2. Ver Lista
La tabla muestra:
- Nombre del schedule
- Frecuencia
- Hora configurada
- Tipos de backup (badges)
- Último backup ejecutado
- Próximo backup programado
- Estado (Activo/Inactivo)

#### 3. Editar Schedule
- Haz clic en el ícono de editar (lápiz)
- Modifica los campos necesarios
- Haz clic en "Guardar Cambios"

#### 4. Activar/Desactivar
- Haz clic en el ícono de pausa/play
- El schedule se activa o desactiva sin eliminarlo

#### 5. Eliminar Schedule
- Haz clic en el ícono de basurero
- Confirma en el modal
- El schedule se elimina permanentemente

## Ejemplos de Configuración

### Configuración Básica

**Backup Diario Nocturno**
```
Nombre: Backup Nightly
Frecuencia: Diario
Hora: 03:00
Tipos: App Code, Config, Routes, Resources, Database
Cada noche a las 3 AM se hace una copia completa
```

### Configuración Intermedia

**Backup Selectivo Por Día**
```
Nombre: DB Lunes y Viernes
Frecuencia: Semanal
Hora: 02:00
Días: Lunes, Viernes
Tipos: Database
Backup de base de datos 2 veces por semana

---

Nombre: Código Diario
Frecuencia: Diario
Hora: 01:00
Tipos: App Code, Config, Routes
Backup de código todos los días
```

### Configuración Avanzada

**Múltiples Estrategias**
```
Nombre: Full Backup Semanal
Frecuencia: Semanal
Hora: 04:00
Días: Domingo
Tipos: App Code, Config, Routes, Resources, Database
Backup completo cada domingo

---

Nombre: DB Diario
Frecuencia: Diario
Hora: 02:00
Tipos: Database
Backup incremental de BD todos los días

---

Nombre: Code Horario
Frecuencia: Personalizado
Intervalo: 1 hora
Tipos: App Code
Backup de código cada hora para máxima seguridad
```

## Logs

### Ubicación
```
/storage/logs/scheduled-backups.log
```

### Contenido
```
[2025-11-29 02:00:15] Starting backup execution
✓ Backup 'Backup Diario Automático' executed successfully
Total backups executed: 1
```

## Troubleshooting

### Los backups no se ejecutan

**Problema:** Los schedules están creados pero nunca se ejecutan.

**Solución:**
1. Verifica que el cron job esté activo:
   ```bash
   crontab -l
   ```

2. Verifica que el queue worker esté corriendo:
   ```bash
   php artisan queue:work --stop-when-empty
   ```

3. Revisa los logs:
   ```bash
   tail -f /storage/logs/scheduled-backups.log
   tail -f /storage/logs/laravel.log
   ```

### Schedule no se ejecuta a la hora correcta

**Problema:** El schedule está configurado para las 02:00 pero se ejecuta a otra hora.

**Solución:**
1. Verifica la zona horaria en `config/app.php`:
   ```php
   'timezone' => 'America/Mexico_City', // Ajusta según tu zona
   ```

2. Verifica el `scheduled_time` en la BD:
   ```bash
   SELECT name, scheduled_time FROM backup_schedules;
   ```

### Tipo "Personalizado" no respeta la hora

**Problema:** Configuré un backup personalizado pero ignora la hora.

**Solución:** En los backups personalizados, la hora se ignora. Se ejecutan basándose en el intervalo de horas desde el último backup. Esto es normal.

## API JSON

### Obtener detalles de un schedule

```bash
GET /manager/settings/backup-schedules/{id}/details
```

**Response:**
```json
{
  "success": true,
  "schedule": {
    "id": 1,
    "name": "Backup Diario Automático",
    "enabled": true,
    "frequency": "daily",
    "scheduled_time": "02:00:00",
    "backup_types": ["app_code", "config", "database"],
    "last_run_at": "2025-11-29 02:00:41",
    "next_run_at": "2025-11-30 02:00:00"
  }
}
```

### Activar/Desactivar un schedule

```bash
POST /manager/settings/backup-schedules/{id}/toggle
```

**Response:**
```json
{
  "success": true,
  "enabled": true,
  "message": "Schedule activado"
}
```

## Mejores Prácticas

### 1. **Hora de Ejecución**
- Programa backups grandes entre 2 AM y 4 AM
- Evita horas de pico de usuarios
- Considera la zona horaria del servidor

### 2. **Frecuencia**
- Backups críticos (DB): Diarios
- Código/Config: Puede ser semanal
- Storage: Depende del volumen de cambios

### 3. **Retención**
- Los backups automáticos se almacenan igual que los manuales
- Implementa una política de limpieza automática
- Considera el espacio en disco disponible

### 4. **Monitoreo**
- Revisa regularmente los logs de scheduled-backups.log
- Monitorea el espacio disponible
- Verifica que los backups realmente se creen

### 5. **Respaldo**
- No confíes solo en backups locales
- Copia los backups a otra ubicación (cloud, servidor remoto)
- Realiza pruebas de restauración periódicamente

## Información Técnica

### Campos de la Tabla

```sql
- id: bigint (PK)
- name: string (191) - Nombre del schedule
- enabled: boolean - Activado/desactivado
- frequency: enum (daily, weekly, monthly, custom)
- scheduled_time: time - Hora HH:MM:SS
- days_of_week: json - Array [0-6] para semanal
- days_of_month: json - Array [1-31] para mensual
- custom_interval_hours: int - Intervalo en horas
- backup_types: json - Array de tipos de backup
- last_run_at: timestamp - Última ejecución
- next_run_at: timestamp - Próxima ejecución
- created_at, updated_at: timestamps
```

### Archivos Involucrados

```
Database:
  database/migrations/2025_11_28_230312_create_backup_schedules_table.php

Models:
  app/Models/BackupSchedule.php

Controllers:
  app/Http/Controllers/Managers/Settings/BackupScheduleController.php

Commands:
  app/Console/Commands/RunScheduledBackups.php

Views:
  resources/views/managers/views/settings/backups/schedules/index.blade.php
  resources/views/managers/views/settings/backups/schedules/create.blade.php
  resources/views/managers/views/settings/backups/schedules/edit.blade.php

Routes:
  routes/managers.php (Backup Schedules routes)
```

## Soporte

Para reportar problemas o sugerencias:
1. Revisa los logs primero
2. Verifica la configuración de timezone
3. Asegúrate de que el queue worker esté corriendo
4. Contacta al administrador del sistema
