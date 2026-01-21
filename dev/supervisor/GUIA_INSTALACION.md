# 📋 GUÍA DE INSTALACIÓN - SUPERVISORES CONSOLIDADOS

## 🎯 OBJETIVO
Consolidar los 3 archivos de supervisor en 1 solo, eliminando duplicados y unificando la configuración.

---

## 📊 CAMBIOS A REALIZAR

### ❌ ARCHIVOS A ELIMINAR (Pasos 1-3)

```bash
# Paso 1: Eliminar queue worker general
sudo rm /etc/supervisor/conf.d/alsernet-queue-all.conf

# Paso 2: Eliminar queue worker ERP
sudo rm /etc/supervisor/conf.d/alsernet-queue-erp.conf

# Paso 3: Eliminar queue worker antiguo (apunta a webadminpruebas)
sudo rm /etc/supervisor/conf.d/laravel-queue-worker.conf
```

### ✅ ARCHIVO A INSTALAR (Paso 4)

```bash
# Paso 4: Copiar nuevo archivo consolidado
sudo cp /home/webadmin/web/dev/supervisor/laravel-queue-worker.conf /etc/supervisor/conf.d/laravel-queue-worker.conf

# Verificar que se copió correctamente
sudo cat /etc/supervisor/conf.d/laravel-queue-worker.conf
```

---

## 🔄 APLICAR CAMBIOS (Pasos 5-7)

### Paso 5: Recargar configuración
```bash
sudo supervisorctl reread
```

Salida esperada:
```
laravel-queue-default: added process group
laravel-queue-erp: added process group
```

### Paso 6: Actualizar supervisor
```bash
supervisorctl update
```

Salida esperada:
```
laravel-queue-default: spawned
laravel-queue-erp: spawned
```

### Paso 7: Verificar estado
```bash
sudo supervisorctl status
```

Salida esperada:
```
laravel-queue-default   RUNNING   pid 12345, uptime 0:00:05
laravel-queue-erp       RUNNING   pid 12346, uptime 0:00:05
laravel-reverb          RUNNING   pid 12347, uptime 0:00:05
laravel-scheduler       RUNNING   pid 12348, uptime 0:00:05
```

---

## 🐛 TROUBLESHOOTING

### Problema: "ERROR: Already in use"
```bash
# Reinicia completamente supervisor
sudo systemctl restart supervisor
```

### Problema: Logs no aparecen
```bash
# Verificar que la carpeta existe
ls -la /home2/webadmin/web/storage/logs/

# Si no existe, crearla
mkdir -p /home2/webadmin/web/storage/logs
chmod 777 /home2/webadmin/web/storage/logs
```

### Problema: Queue workers no procesan
```bash
# Ver logs
tail -f /home2/webadmin/web/storage/logs/laravel-queue-default.log
tail -f /home2/webadmin/web/storage/logs/laravel-queue-erp.log
```

---

## ✅ VERIFICACIÓN FINAL

Después de aplicar todos los pasos:

```bash
# 1. Verificar procesos activos
ps aux | grep queue:work

# Deberías ver algo así:
# php /home2/webadmin/web/artisan queue:work --queue=default,emails,notifications
# php /home2/webadmin/web/artisan queue:work --queue=erp

# 2. Verificar estado en supervisor
sudo supervisorctl status

# 3. Contar jobs en cola
php artisan queue:failed
```

---

## 📝 RESUMEN DE CAMBIOS

| Antes | Después |
|-------|---------|
| 3 workers diferentes | 2 workers consolidados |
| alsernet-queue-all.conf | laravel-queue-worker.conf |
| alsernet-queue-erp.conf | (incluido en laravel-queue-worker.conf) |
| laravel-queue-worker.conf (viejo) | (eliminado, datos migrados) |

---

**¿Necesitas ayuda? Contacta al equipo de DevOps**
