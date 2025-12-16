# Setup Supervisor Script Guide

**Archivo**: `scripts/setup-supervisor.sh`
**Propósito**: Configurar automáticamente Supervisor para el Laravel Route Watcher daemon
**Ambiente**: Linux (Ubuntu, Debian, CentOS, macOS)

---

## 📋 Requisitos

- Linux/macOS con Supervisor instalado
- Permisos `sudo` (script requiere ejecución como root)
- PHP CLI configurado
- Laravel Route Watcher configurado en `config/supervisor/`

## ⚡ Uso Rápido

```bash
# Desarrollo
sudo ./scripts/setup-supervisor.sh dev

# Producción
sudo ./scripts/setup-supervisor.sh prod

# Ambos ambientes
sudo ./scripts/setup-supervisor.sh both
```

---

## 🔍 ¿Qué Hace el Script?

### 1. **Verificaciones Previas**
- ✅ Verifica que se ejecute como `root`
- ✅ Verifica que Supervisor esté instalado
- ✅ Proporciona instrucciones de instalación si falta

### 2. **Configuración (Dev/Prod)**

#### Desarrollo (`dev`)
```bash
# Copia config
config/supervisor/laravel-route-watcher-dev.conf
  → /etc/supervisor/conf.d/laravel-route-watcher-dev.conf

# Reemplaza variables
%(ENV_LARAVEL_ROOT)s  →  Ruta del proyecto
%(ENV_USER)s           →  Usuario actual
```

#### Producción (`prod`)
```bash
# Copia config
config/supervisor/laravel-route-watcher-prod.conf
  → /etc/supervisor/conf.d/laravel-route-watcher-prod.conf

# Reemplaza variables
%(ENV_LARAVEL_ROOT)s  →  Ruta del proyecto
www-data              →  Usuario web automático (_www en macOS)

# Configura permisos
storage/               →  Propiedad: www-data:www-data
storage/logs/supervisor/ →  Permisos: 755
```

### 3. **Directorios de Logs**
Crea automáticamente:
```
storage/logs/supervisor/
  ├── route-watcher-dev.log
  ├── route-watcher-dev-error.log
  ├── route-watcher-prod.log
  └── route-watcher-prod-error.log
```

### 4. **Activación**
```bash
# Recarga supervisor
sudo supervisorctl reread
sudo supervisorctl update

# Inicia el daemon
sudo supervisorctl start laravel-route-watcher-{dev|prod}

# Muestra status
sudo supervisorctl status laravel-route-watcher-{dev|prod}
```

---

## 📌 Comandos Útiles Después del Setup

```bash
# Ver todos los procesos de supervisor
sudo supervisorctl status

# Ver estado del route watcher
sudo supervisorctl status laravel-route-watcher-dev
sudo supervisorctl status laravel-route-watcher-prod

# Iniciar daemon
sudo supervisorctl start laravel-route-watcher-dev

# Detener daemon
sudo supervisorctl stop laravel-route-watcher-dev

# Reiniciar daemon
sudo supervisorctl restart laravel-route-watcher-dev

# Ver logs en tiempo real
tail -f storage/logs/supervisor/route-watcher-dev.log
tail -f storage/logs/supervisor/route-watcher-dev-error.log
```

---

## 🐛 Troubleshooting

### Error: "This script must be run as root"
```bash
# Solución: Usar sudo
sudo ./scripts/setup-supervisor.sh dev
```

### Error: "Supervisor is not installed"
```bash
# Ubuntu/Debian
sudo apt-get install supervisor

# CentOS/RHEL
sudo yum install supervisor

# macOS
brew install supervisor
```

### Daemon no inicia
```bash
# Revisar logs de error
tail -f storage/logs/supervisor/route-watcher-dev-error.log

# Revisar config
sudo supervisorctl config

# Reiniciar supervisor
sudo systemctl restart supervisor
```

### Permisos incorrectos en producción
```bash
# Reconstruir permisos
sudo chown -R www-data:www-data storage/
sudo chmod -R 755 storage/logs/supervisor/
```

---

## 📂 Archivos Relacionados

- **Script**: `scripts/setup-supervisor.sh`
- **Configuración Dev**: `config/supervisor/laravel-route-watcher-dev.conf`
- **Configuración Prod**: `config/supervisor/laravel-route-watcher-prod.conf`
- **Documentación**: `.claude/reference/project/devops/`

---

## ✅ Checklist de Setup

```
□ Supervisor instalado en el servidor
□ Ejecutar: sudo ./scripts/setup-supervisor.sh {dev|prod|both}
□ Verificar status: sudo supervisorctl status
□ Ver logs: tail -f storage/logs/supervisor/route-watcher-*.log
□ Confirmar que el daemon está ejecutándose (RUNNING)
□ Agregar a cron/systemd para auto-start en reboot
```

---

**Última actualización**: Noviembre 30, 2025
**Estado**: Producción Ready ✅
