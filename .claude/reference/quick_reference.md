# 🚀 Referencia Rápida - Documentación de Comandos

## Comandos Útiles

### Generar/Actualizar Documentación

```bash
# Opción 1: Comando artisan directo
php artisan docs:generate-commands

# Opción 2: Script composer
composer docs

# Opción 3: Especificar archivo de salida
php artisan docs:generate-commands --output=manual/ARTISAN_COMMANDS.md
```

### Configuración de Git Hooks

```bash
# Ejecutar una sola vez para configurar los hooks
composer setup:hooks

# O manualmente
bash manual/setup-hooks.sh
```

## Crear un Nuevo Comando

### 1. Usar el comando de Laravel

```bash
php artisan make:command MyNamespace/MyCommand
```

### 2. Estructura Básica

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MyCommand extends Command
{
    protected $signature = 'namespace:command-name';
    protected $description = 'Descripción clara de qué hace';

    public function handle(): int
    {
        $this->info('Ejecutando comando...');

        // Tu código aquí

        return Command::SUCCESS;
    }
}
```

### 3. Actualizar Documentación

```bash
composer docs
```

## Convenciones de Naming

| Elemento | Formato | Ejemplo |
|----------|---------|---------|
| Clase | PascalCase + Command | `SyncRoutesCommand` |
| Signature | namespace:comando | `routes:sync` |
| Descripción | Texto claro español | `Sincronizar rutas con BD` |

## Comandos Disponibles

Ver `/manual/ARTISAN_COMMANDS.md` para la lista completa de 22+ comandos disponibles en este proyecto.

### Por Categoría

**Devoluciones** (`returns:*`)
- `returns:cleanup-communications` - Limpiar comunicaciones antiguas

**Rutas** (`routes:*`)
- `routes:sync` - Sincronizar rutas con base de datos
- `routes:watcher:start` - Iniciar observador de rutas

**Documentos** (`documents:*`)
- `documents:send-reminders` - Enviar recordatorios de documentos

**Componentes** (`components:*`)
- `components:process` - Procesar componentes

**Sistema** (`system:*`)
- `system:cleanup` - Limpiar sistema

Y muchos más...

## Mejores Prácticas

### ✅ Hacer

- Incluir descripción clara en cada comando
- Usar mensajes coloridos para feedback: `info()`, `warn()`, `error()`
- Validar argumentos y opciones
- Retornar `SUCCESS` o `FAILURE`
- Agrupar bajo namespace lógico
- Actualizar documentación tras cambios

### ❌ No Hacer

- Comandos sin descripción
- Signatures muy largas o confusas
- Comandos huérfanos sin namespace
- Olvidar actualizar documentación
- Usar comandos para lógica de negocio compleja

## Troubleshooting

### La documentación no se actualiza

```bash
# Verificar que artisan funciona
php artisan list

# Ejecutar generación con verbosidad
php artisan docs:generate-commands -vv

# Verificar permisos de carpeta
chmod -R 755 manual/
```

### Git hooks no se ejecutan

```bash
# Verificar instalación
ls -la .git/hooks/pre-commit

# Reinstalar
composer setup:hooks

# Verificar permisos
chmod +x .git/hooks/pre-commit
```

## Recursos

- 📚 [Documentación Completa](ARTISAN_COMMANDS.md)
- 📖 [Manual Detallado](README.md)
- 🔗 [Laravel Docs - Artisan](https://laravel.com/docs/artisan)
- 💻 [Código Fuente del Generador](../app/Console/Commands/GenerateCommandsDocumentation.php)

---

**Creado:** 2025-11-29
**Actualización:** Generada automáticamente
