# PrestaShop Setup Guide

**Guía paso a paso para instalar y configurar PrestaShop con integración a Alsernet**

---

## 📋 Requisitos Previos

### Hardware Mínimo
```
✅ CPU: 2 cores
✅ RAM: 2 GB
✅ Disco: 20 GB
✅ Conexión: 1 Mbps
```

### Software Requerido

| Componente | Versión | Estado |
|-----------|---------|--------|
| **PHP** | 7.2+ | Obligatorio |
| **MySQL** | 5.7+ | Obligatorio |
| **PostgreSQL** | 12+ | Opcional |
| **Redis** | 6+ | Recomendado |
| **Nginx/Apache** | Latest | Obligatorio |
| **cURL** | Latest | Obligatorio |
| **Git** | Latest | Recomendado |

### Permisos

```bash
# El usuario web (www-data) debe tener permisos de escritura en:
- integrations/prestashop/content/app/
- integrations/prestashop/content/modules/
- integrations/prestashop/content/override/
- storage/
- cache/
```

---

## 🚀 Instalación Rápida

### Opción 1: Docker (Recomendado)

```bash
# Clonar repositorio
git clone https://github.com/yourcompany/Alsernet.git
cd Alsernet/integrations/prestashop

# Copiar env
cp .env.example .env

# Editar .env con configuración de Alsernet
nano .env

# Levantar containers
docker-compose up -d

# Ejecutar setup
docker-compose exec prestashop php bin/console Alsernet:setup

# Abrir navegador
http://localhost:8000
```

### Opción 2: Manual en Linux

#### 1. Instalar dependencias

```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install -y php7.4-cli php7.4-fpm php7.4-mysql php7.4-curl php7.4-xml

# Habilitar extensiones
sudo phpenmod curl xml mysql

# Reiniciar PHP-FPM
sudo systemctl restart php7.4-fpm
```

#### 2. Clonar repositorio

```bash
# Clonar Alsernet
git clone https://github.com/yourcompany/Alsernet.git /var/www/Alsernet
cd /var/www/Alsernet/integrations/prestashop

# Asignar permisos
sudo chown -R www-data:www-data content/
sudo chmod -R 755 content/
```

#### 3. Configurar servidor web

**Nginx** (`/etc/nginx/sites-available/prestashop`):

```nginx
server {
    listen 80;
    server_name prestashop.local;
    root /var/www/Alsernet/integrations/prestashop;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

**Habilitar sitio**:

```bash
sudo ln -s /etc/nginx/sites-available/prestashop /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### 4. Crear base de datos

```bash
# MySQL
mysql -u root -p
CREATE DATABASE prestashop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'prestashop'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON prestashop.* TO 'prestashop'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 5. Instalar PrestaShop

```bash
# Ir al directorio
cd /var/www/Alsernet/integrations/prestashop/content

# Instalar dependencias
composer install

# Crear archivo de configuración
cp app/config/parameters.php.sample app/config/parameters.php

# Editar parameters.php
nano app/config/parameters.php

# Ejecutar instalador
php bin/console prestashop:install

# Instalar datos de demostración (opcional)
php bin/console prestashop:sample-data:create
```

#### 6. Configurar módulos Alsernet

```bash
# Ir a admin panel
http://prestashop.local/admin

# Login con credenciales creadas
user: admin@company.com
pass: ****

# Ir a Módulos > Alsernet
# Instalar cada módulo:
# 1. Alsernetauth
# 2. Alsernetcustomer
# 3. Alsernetproducts
# 4. Alsernetshopping
# 5. Alsernetcontents
# 6. Alsernetforms
```

---

## ⚙️ Configuración de Alsernet

### 1. Obtener credenciales

En Alsernet admin panel:

```
Settings > API > Generate Token

Guardar:
- API URL: https://Alsernet.local/api
- API Key: key_xxxxx
- API Secret: secret_yyyyy
- Webhook Secret: webhook_zzzzz
```

### 2. Configurar en PrestaShop

**Vía Admin Panel**:

```
Admin > Modules > Alsernet Configuration

Completar:
- API URL: https://Alsernet.local/api
- API Key: key_xxxxx
- API Secret: secret_yyyyy
- Webhook Secret: webhook_zzzzz

Habilitar:
☑ SSL Verification
☑ Debug Mode (en desarrollo)

Guardar
```

**Vía Archivo**:

Editar `app/config/parameters.php`:

```php
'Alsernet' => [
    'api_url' => 'https://Alsernet.local/api',
    'api_key' => 'key_xxxxx',
    'api_secret' => 'secret_yyyyy',
    'webhook_secret' => 'webhook_zzzzz',
    'timeout' => 30,
    'verify_ssl' => true,
    'debug' => false,
]
```

### 3. Verificar conexión

```bash
cd /var/www/Alsernet/integrations/prestashop

php bin/console Alsernet:verify-connection

# Output esperado:
# ✅ API Connection: OK
# ✅ Authentication: Success
# ✅ API Version: 3.0.1
```

---

## 🔄 Sincronización Inicial

### 1. Sincronizar productos

```bash
# Importar todos los productos de Alsernet
php bin/console Alsernet:sync:products --full --batch=50

# Esto descargará:
# - 50+ productos
# - Imágenes
# - Categorías
# - Atributos
```

### 2. Sincronizar clientes existentes (opcional)

```bash
# Si tienes clientes existentes en PrestaShop
php bin/console Alsernet:sync:customers --direction=push

# Si quieres importar clientes de Alsernet
php bin/console Alsernet:sync:customers --direction=pull
```

### 3. Verificar sincronización

```bash
# Ver logs
tail -f storage/logs/Alsernet-sync.log

# Entrar a admin y verificar:
Admin > Catálogo > Productos
# Deberían ver los productos importados

Admin > Clientes
# Deberían ver los clientes sincronizados
```

---

## 📅 Configurar Cron Jobs

### Opción 1: cPanel

```
1. Login en cPanel
2. Ir a "Cron Jobs"
3. Agregar nuevos trabajos:

# Sincronizar precios cada 5 minutos
*/5 * * * * /usr/bin/php /home/user/public_html/bin/console Alsernet:sync:prices

# Sincronizar stock cada 15 minutos
*/15 * * * * /usr/bin/php /home/user/public_html/bin/console Alsernet:sync:stock

# Sincronizar productos cada hora
0 * * * * /usr/bin/php /home/user/public_html/bin/console Alsernet:sync:products:incremental
```

### Opción 2: Linux/Server

```bash
# Editar crontab
crontab -e

# Agregar líneas:
*/5 * * * * /usr/bin/php /var/www/Alsernet/integrations/prestashop/bin/console Alsernet:sync:prices
*/15 * * * * /usr/bin/php /var/www/Alsernet/integrations/prestashop/bin/console Alsernet:sync:stock
0 * * * * /usr/bin/php /var/www/Alsernet/integrations/prestashop/bin/console Alsernet:sync:products:incremental

# Guardar (Ctrl+O, Enter, Ctrl+X)
```

---

## 🧪 Testing & Validación

### 1. Verificar instalación

```bash
# Revisar requirements de PHP
php bin/console system:check

# Revisar módulos
php bin/console modules:list

# Revisar base de datos
php bin/console database:check
```

### 2. Probar API Connection

```bash
# Test básico
php bin/console Alsernet:test:connection

# Test de autenticación
php bin/console Alsernet:test:auth

# Test de endpoints
php bin/console Alsernet:test:customers
php bin/console Alsernet:test:products
php bin/console Alsernet:test:orders
```

### 3. Probar módulos

```bash
# Habilitar modo debug
php bin/console config:set debug=1

# Crear cliente de prueba
# Admin > Clientes > Agregar

# Ver si se sincroniza
tail -f storage/logs/Alsernet-customers.log

# Verificar en Alsernet
curl -H "Authorization: Bearer {token}" \
     https://Alsernet.local/api/customers/1
```

---

## 🔒 Seguridad

### 1. SSL Certificate

```bash
# Generar certificado Let's Encrypt
sudo certbot certonly --webroot -w /var/www/Alsernet -d prestashop.com

# Renovar automático
sudo certbot renew --quiet --no-eff-email
```

### 2. Proteger archivos sensibles

```bash
# No exponer config
<FilesMatch "\.php$|\.json$">
    <IfModule mod_authz_core.c>
        Require all denied
    </IfModule>
</FilesMatch>

# Excepto index.php
<Files "index.php">
    <IfModule mod_authz_core.c>
        Require all granted
    </IfModule>
</Files>
```

### 3. Configurar headers de seguridad

```nginx
# Agregar headers
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "no-referrer-when-downgrade" always;
add_header Content-Security-Policy "default-src 'self';" always;
```

### 4. Rotar API Keys

```bash
# Cada 90 días, generar nuevo en Alsernet
Admin > API Settings > Rotate Keys

# Actualizar en PrestaShop
app/config/parameters.php
# O Admin panel
```

---

## 📊 Monitoreo

### Ver estado de módulos

```
Admin > Modules > Alsernet

Muestra:
- ✅ Alsernetauth - OK
- ✅ Alsernetcustomer - OK
- ✅ Alsernetproducts - OK
- ⚠️  Alsernetshopping - Warning
- ✅ Alsernetcontents - OK
- ✅ Alsernetforms - OK
```

### Ver logs

```bash
# Todos los logs
tail -f storage/logs/Alsernet-*.log

# Errores
grep ERROR storage/logs/Alsernet-*.log

# Sincronizaciones
grep SYNC storage/logs/Alsernet-sync.log
```

### Estadísticas

```bash
# Productos
php bin/console stats:products

# Clientes
php bin/console stats:customers

# Órdenes
php bin/console stats:orders
```

---

## 🐛 Troubleshooting

### "Connection refused"

```bash
# Verificar que Alsernet está accesible
curl -I https://Alsernet.local/api

# Verificar firewall
sudo ufw status

# Permitir puerto 443
sudo ufw allow 443/tcp
```

### "Invalid API Key"

```bash
# Regenerar en Alsernet
Admin > API > Generate New Key

# Actualizar en PrestaShop
app/config/parameters.php
'api_key' => 'new_key_xyz'
```

### "Database connection error"

```bash
# Verificar credenciales
app/config/parameters.php

# Reconectar a BD
php bin/console database:reconnect

# Crear nueva conexión
php bin/console database:create
```

---

## ✅ Checklist Final

```
Instalación
□ PHP 7.2+ instalado
□ MySQL/PostgreSQL disponible
□ cURL habilitado
□ Permisos correctos en carpetas

Configuración
□ Database creada y conectada
□ Servidor web configurado (Nginx/Apache)
□ SSL Certificate instalado
□ Módulos PrestaShop instalados

Integración Alsernet
□ API URL configurada
□ API Key y Secret configurados
□ Conexión verificada
□ Webhook Secret configurado

Sincronización
□ Productos sincronizados
□ Clientes sincronizados
□ Órdenes probadas
□ Cron jobs configurados

Seguridad
□ HTTPS funcionando
□ Headers de seguridad configurados
□ Permisos de archivos correctos
□ Logs rotando correctamente

Monitoreo
□ Logs accesibles
□ Dashboard de módulos visible
□ Alertas de errores configuradas
```

---

**Última actualización**: Noviembre 30, 2025
**Status**: Production Ready ✅
