# Supervisor Configuration for Document Module

Supervisor es un sistema de control de procesos que mantiene los procesos en ejecución de forma automática. Esta configuración ejecuta el worker de la cola de emails del módulo Document.

## 📋 Descripción

El worker procesa los jobs de email en la cola `emails`:
- Envío de notificaciones de documentos
- Envío de confirmaciones de carga
- Envío de emails de aprobación/rechazo
- Etc.

---

## 🍎 Instalación en macOS

### 1. Instalar Supervisor

```bash
# Usando Homebrew
brew install supervisor
```

### 2. Crear Directorio de Logs

```bash
mkdir -p ~/.supervisor/logs
```

### 3. Copiar Configuración

```bash
# Copiar el archivo de configuración de Mac
sudo cp modules/Document/supervisor/mac/document-queue.conf \
  /opt/homebrew/etc/supervisor.d/document-queue.conf
```

### 4. Iniciar Supervisord

```bash
# Iniciar el servicio supervisord
brew services start supervisor
```

O si prefieres ejecutarlo manualmente:

```bash
supervisord -c /opt/homebrew/etc/supervisord.conf
```

### 5. Verificar Estado

```bash
# Conectarte al cliente supervisorctl
supervisorctl

# Dentro del cliente, puedes usar:
status                              # Ver estado de todos los procesos
status document-queue-emails:*      # Ver estado del worker
tail document-queue-emails          # Ver últimos logs
tail document-queue-emails stderr   # Ver últimos errores
```

### 6. Comandos Útiles en supervisorctl

```bash
update                              # Recarga configuraciones nuevas
reread                              # Lee nuevos archivos de config
restart document-queue-emails:*     # Reinicia el worker
stop document-queue-emails:*        # Detiene el worker
start document-queue-emails:*       # Inicia el worker
exit                                # Sale de supervisorctl
```

### 7. Desactivar en el Inicio (Opcional)

Si solo quieres ejecutarlo manualmente:

```bash
brew services stop supervisor
```

---

## 🐧 Instalación en Linux (Ubuntu/Debian)

### 1. Instalar Supervisor

```bash
sudo apt-get update
sudo apt-get install supervisor
```

### 2. Crear Directorio de Logs

```bash
sudo mkdir -p /var/log/supervisor
sudo chown www-data:www-data /var/log/supervisor
```

### 3. Copiar Configuración

```bash
# Copiar el archivo de configuración de Linux
sudo cp modules/Document/supervisor/linux/document-queue.conf \
  /etc/supervisor/conf.d/document-queue.conf

# Verificar permisos
sudo chown root:root /etc/supervisor/conf.d/document-queue.conf
sudo chmod 644 /etc/supervisor/conf.d/document-queue.conf
```

### 4. Actualizar Supervisor

```bash
# Leer nuevas configuraciones
sudo supervisorctl reread

# Actualizar con nuevas configuraciones
sudo supervisorctl update

# Verificar estado
sudo supervisorctl status
```

### 5. Verificar Estado

```bash
# Ver estado del servicio
sudo systemctl status supervisor

# Ver estado detallado
sudo supervisorctl status document-queue-emails:*

# Ver logs
sudo tail -f /var/log/supervisor/document-queue-emails.log
```

### 6. Comandos Útiles

```bash
# Reiniciar el worker
sudo supervisorctl restart document-queue-emails:*

# Detener el worker
sudo supervisorctl stop document-queue-emails:*

# Iniciar el worker
sudo supervisorctl start document-queue-emails:*

# Recargar configuraciones
sudo supervisorctl reread && sudo supervisorctl update
```

### 7. Habilitar Autostart (Linux)

```bash
sudo systemctl enable supervisor
sudo systemctl start supervisor
```

---

## 🔧 Personalización de la Configuración

### Ajustes Importantes

En los archivos de configuración, puedes modificar:

```conf
; Número de procesos workers (2 es recomendado)
numprocs=2

; Intentos antes de marcar como fallido
--tries=3

; Tiempo máximo de ejecución de un job (segundos)
--timeout=60

; Número máximo de jobs antes de reiniciar
--max-jobs=1000

; Tiempo máximo antes de reiniciar el worker (segundos)
--max-time=3600

; Tiempo de espera antes de forzar kill
stopwaitsecs=10
```

### Ejemplo: Aumentar Procesos a 4

```bash
# macOS - Editar el archivo
sudo nano /opt/homebrew/etc/supervisor.d/document-queue.conf

# Linux - Editar el archivo
sudo nano /etc/supervisor/conf.d/document-queue.conf

# Cambiar:
numprocs=4

# Luego recargar:
# macOS:  supervisorctl reread && supervisorctl update
# Linux: sudo supervisorctl reread && sudo supervisorctl update
```

---

## 🐛 Troubleshooting

### Los procesos no inician

```bash
# Verificar logs de supervisord
# macOS
tail -f ~/.supervisor/logs/supervisord.log

# Linux
sudo tail -f /var/log/supervisor/supervisord.log
```

### Error: "Can't connect to /tmp/supervisor.sock"

```bash
# macOS - Reinicia supervisord
brew services restart supervisor

# Linux - Reinicia el servicio
sudo systemctl restart supervisor
```

### Verificar que PHP es accesible

```bash
# macOS
/opt/homebrew/opt/php@8.4/bin/php --version

# Linux
/usr/bin/php --version
```

### Ver estado detallado del worker

```bash
# macOS
supervisorctl tail -f document-queue-emails

# Linux
sudo supervisorctl tail -f document-queue-emails
```

---

## 📊 Monitoreo

### Ver logs en tiempo real

```bash
# macOS
tail -f ~/.supervisor/logs/document-queue-emails.log

# Linux
sudo tail -f /var/log/supervisor/document-queue-emails.log
```

### Verificar procesos en ejecución

```bash
# macOS
ps aux | grep "queue:work"

# Linux
ps aux | grep "queue:work"
```

### Contar jobs procesados

```bash
# Ver últimas líneas de los logs para obtener estadísticas
# macOS
tail -100 ~/.supervisor/logs/document-queue-emails.log

# Linux
sudo tail -100 /var/log/supervisor/document-queue-emails.log
```

---

## 🔄 Ciclo de Vida

### Cuando el worker recibe SIGTERM (shutdown graceful)

1. Completa el job actual en ejecución
2. No acepta nuevos jobs
3. Se detiene de forma ordenada
4. Supervisor lo reinicia automáticamente si `autorestart=true`

### Reinicio automático

El worker se reiniciará automáticamente si:
- Falla con error
- Alcanza el límite de jobs (`max-jobs`)
- Alcanza el límite de tiempo (`max-time`)

---

## 🔐 Seguridad

### Permisos de Logs

```bash
# macOS - Asegurar que el usuario tiene permisos
mkdir -p ~/.supervisor/logs
chmod 755 ~/.supervisor/logs

# Linux - Configurar permisos correctamente
sudo mkdir -p /var/log/supervisor
sudo chown www-data:www-data /var/log/supervisor
sudo chmod 755 /var/log/supervisor
```

### Usuario Correcto

- **macOS:** `user=functionbytes` (tu usuario)
- **Linux:** `user=www-data` `group=www-data`

---

## 📝 Notas Importantes

1. **Rutas ajustables:** Cambia las rutas según tu instalación
2. **PHP Version:** Verifica que la versión de PHP en el `command` coincida con tu instalación
3. **Logs:** Mantén monitorizado el tamaño de los logs (rotación automática configurada)
4. **Performance:** Ajusta `numprocs` según el volumen de emails (2-4 es típico)
5. **Timeout:** Si los emails tardan más, aumenta `--timeout`

---

## ✅ Verificación Rápida

Después de instalar, verifica que todo funciona:

```bash
# 1. Supervisorctl muestra el estado
supervisorctl status

# 2. Los procesos están en RUNNING
# Debe mostrar: document-queue-emails:0  RUNNING  pid xxxx, uptime x:xx:xx
#             document-queue-emails:1  RUNNING  pid xxxx, uptime x:xx:xx

# 3. Verificar que procesa jobs
# Envía un email de prueba desde la API
# curl -X POST https://manager.test/api/documents/test-uid/send-notification

# 4. Verifica logs
tail ~/.supervisor/logs/document-queue-emails.log  # macOS
sudo tail /var/log/supervisor/document-queue-emails.log  # Linux
```

---

## 🚀 Próximos Pasos

1. **Configurar alertas:** Usar herramientas como Monit o Netdata
2. **Escalar workers:** Aumentar `numprocs` si hay mucho volumen
3. **Dashboard:** Acceder a `http://localhost:9001` si configuraste el inet socket
4. **Backup:** Guardar los archivos de configuración en control de versiones

---

**Para más información sobre Supervisor:**
- https://supervisord.readthedocs.io/
