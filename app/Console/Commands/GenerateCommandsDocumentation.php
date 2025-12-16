<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;
use ReflectionClass;

class GenerateCommandsDocumentation extends Command
{
    protected $signature = 'docs:generate-commands {--output=manual/ARTISAN_COMMANDS.md : Output file path}';

    protected $description = 'Generar y actualizar documentación de todos los comandos artisan disponibles';

    public function handle(): int
    {
        $this->info('📚 Generando documentación de comandos artisan...');

        $outputPath = base_path($this->option('output'));

        // Ensure directory exists
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $customCommands = $this->getCustomCommands();
        $laravelCommands = $this->getLaravelCommands();

        $markdown = $this->generateMarkdown($customCommands, $laravelCommands);

        file_put_contents($outputPath, $markdown);

        $this->info("✅ Documentación generada en: {$outputPath}");
        $this->info("📊 Total comandos documentados: " . (count($customCommands) + count($laravelCommands)));

        return Command::SUCCESS;
    }

    /**
     * Get all custom commands from app/Console/Commands
     */
    private function getCustomCommands(): array
    {
        $commands = [];
        $commandPath = app_path('Console/Commands');

        if (!is_dir($commandPath)) {
            return [];
        }

        $finder = new Finder();
        $finder->files()->in($commandPath)->name('*.php');

        foreach ($finder as $file) {
            try {
                $className = 'App\\Console\\Commands\\' . $file->getBasename('.php');

                if (class_exists($className) && is_subclass_of($className, Command::class)) {
                    $reflection = new ReflectionClass($className);

                    // Skip abstract classes
                    if ($reflection->isAbstract()) {
                        continue;
                    }

                    // Read the file to extract $signature and $description directly
                    $fileContents = file_get_contents($file->getRealPath());

                    $signature = $this->extractPropertyValue($fileContents, 'signature');
                    $description = $this->extractPropertyValue($fileContents, 'description');

                    if (!$signature) {
                        continue; // Skip if no signature found
                    }

                    // Extract command name from signature
                    $signatureParts = explode(' ', trim($signature));
                    $commandName = $signatureParts[0] ?? $className;

                    $commands[$commandName] = [
                        'class' => $className,
                        'signature' => $signature,
                        'description' => $description ?: 'No description available',
                        'file' => $file->getRelativePathname(),
                        'type' => 'custom'
                    ];
                }
            } catch (\Exception $e) {
                $this->warn("⚠️  Error procesando {$file->getFilename()}: {$e->getMessage()}");
            }
        }

        ksort($commands);
        return $commands;
    }

    /**
     * Extract property value from class file contents
     */
    private function extractPropertyValue(string $fileContents, string $property): ?string
    {
        // Match pattern: protected $property = 'value'; or protected $property = "value";
        $pattern = "/protected\s+\\\$" . preg_quote($property) . "\s*=\s*['\"]([^'\"]*?)['\"]/s";

        if (preg_match($pattern, $fileContents, $matches)) {
            return trim($matches[1]);
        }

        // Also try multiline strings with regex
        $pattern = "/protected\s+\\\$" . preg_quote($property) . "\s*=\s*['\"]([^'\"]*(?:['\"][^'\"]*)*)['\"];/s";
        if (preg_match($pattern, $fileContents, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Get all default Laravel commands
     */
    private function getLaravelCommands(): array
    {
        $commands = [];

        try {
            // Get all registered artisan commands using the application
            $allCommands = $this->getApplication()->all()['commands'] ?? [];

            $laravelNamespaces = ['Illuminate\\', 'Laravel\\'];

            foreach ($allCommands as $command) {
                if (!is_object($command)) {
                    continue;
                }

                $commandClass = get_class($command);

                // Filter only Laravel framework commands
                $isLaravel = false;
                foreach ($laravelNamespaces as $namespace) {
                    if (strpos($commandClass, $namespace) === 0) {
                        $isLaravel = true;
                        break;
                    }
                }

                // Skip custom commands and migration commands
                if (!$isLaravel || strpos($commandClass, 'Illuminate\\Database\\Console\\Migrations') === 0) {
                    continue;
                }

                try {
                    $name = method_exists($command, 'getName') ? $command->getName() : 'unknown';
                    $description = method_exists($command, 'getDescription') ? $command->getDescription() : 'No description available';

                    // Exclude some internal commands
                    if (in_array($name, ['completion', 'help', 'list'])) {
                        continue;
                    }

                    $commands[$name] = [
                        'class' => $commandClass,
                        'signature' => $name,
                        'description' => $description,
                        'type' => 'laravel'
                    ];
                } catch (\Exception $e) {
                    continue;
                }
            }
        } catch (\Exception $e) {
            $this->warn("⚠️  No se pudieron cargar los comandos de Laravel: {$e->getMessage()}");
        }

        ksort($commands);
        return $commands;
    }

    /**
     * Generate markdown documentation
     */
    private function generateMarkdown(array $customCommands, array $laravelCommands): string
    {
        $now = now()->format('Y-m-d H:i:s');

        $markdown = "# Documentación de Comandos Artisan\n\n";
        $markdown .= "> 📝 Generado automáticamente el $now\n\n";
        $markdown .= "> Este documento se actualiza automáticamente ejecutando: `php artisan docs:generate-commands`\n\n";

        // Table of contents
        $markdown .= "## 📑 Tabla de Contenidos\n\n";
        $markdown .= "- [Comandos Personalizados](#comandos-personalizados) (" . count($customCommands) . ")\n";
        $markdown .= "- [Comandos de Laravel](#comandos-de-laravel) (" . count($laravelCommands) . ")\n";
        $markdown .= "- [Guía de Uso](#guía-de-uso)\n\n";

        // Custom Commands
        $markdown .= "## Comandos Personalizados\n\n";
        $markdown .= "Estos son los comandos específicos desarrollados para este proyecto.\n\n";

        foreach ($customCommands as $name => $data) {
            $markdown .= $this->formatCommandBlock($name, $data);
        }

        // Laravel Commands
        $markdown .= "## Comandos de Laravel\n\n";
        $markdown .= "Estos son los comandos predeterminados de Laravel disponibles en este proyecto.\n\n";

        foreach ($laravelCommands as $name => $data) {
            $markdown .= $this->formatCommandBlock($name, $data);
        }

        // Usage Guide
        $markdown .= "## Guía de Uso\n\n";
        $markdown .= "### Regenerar Documentación\n\n";
        $markdown .= "Para actualizar este documento después de crear o modificar comandos:\n\n";
        $markdown .= "```bash\nphp artisan docs:generate-commands\n```\n\n";

        $markdown .= "### Convenciones de Naming\n\n";
        $markdown .= "Al crear nuevos comandos, sigue estas convenciones:\n\n";
        $markdown .= "- **Nombre de clase**: PascalCase, ej: `SyncRoutesCommand`\n";
        $markdown .= "- **Signature**: snake-case con namespace, ej: `routes:sync`\n";
        $markdown .= "- **Descripción**: Texto claro en español explicando qué hace\n\n";

        $markdown .= "### Estructura de un Comando Personalizado\n\n";
        $markdown .= "```php\n";
        $markdown .= "<?php\n\n";
        $markdown .= "namespace App\\Console\\Commands;\n\n";
        $markdown .= "use Illuminate\\Console\\Command;\n\n";
        $markdown .= "class MyCommand extends Command\n";
        $markdown .= "{\n";
        $markdown .= "    protected \$signature = 'namespace:command-name';\n";
        $markdown .= "    protected \$description = 'Descripción clara del comando';\n\n";
        $markdown .= "    public function handle(): int\n";
        $markdown .= "    {\n";
        $markdown .= "        // Lógica del comando aquí\n";
        $markdown .= "        return Command::SUCCESS;\n";
        $markdown .= "    }\n";
        $markdown .= "}\n";
        $markdown .= "```\n\n";

        $markdown .= "### Mejores Prácticas\n\n";
        $markdown .= "1. **Descripción clara**: Siempre incluye una descripción `protected \$description`\n";
        $markdown .= "2. **Namespace lógico**: Agrupa comandos relacionados bajo el mismo namespace\n";
        $markdown .= "3. **Validación**: Valida los argumentos y opciones en el comando\n";
        $markdown .= "4. **Feedback al usuario**: Usa `info()`, `warn()`, `error()` para comunicar progreso\n";
        $markdown .= "5. **Return codes**: Retorna `SUCCESS` o `FAILURE` apropiadamente\n";
        $markdown .= "6. **Documentación automática**: Actualiza la documentación después de cambios\n\n";

        return $markdown;
    }

    /**
     * Format a single command block
     */
    private function formatCommandBlock(string $name, array $data): string
    {
        $block = "### `{$name}`\n\n";
        $block .= "**Descripción:** {$data['description']}\n\n";

        if ($data['type'] === 'custom' && isset($data['file'])) {
            $block .= "**Archivo:** `{$data['file']}`\n\n";
        }

        $block .= "**Comando completo:** `php artisan {$data['signature']}`\n\n";

        if ($data['type'] === 'custom' && isset($data['class'])) {
            $block .= "**Clase:** `{$data['class']}`\n\n";
        }

        $block .= "---\n\n";

        return $block;
    }
}