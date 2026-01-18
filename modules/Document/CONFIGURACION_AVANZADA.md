# Configuración Avanzada - Create Blocked Product Documents

## 1️⃣ Mejora de Seguridad: Trasladar Credenciales a Variables de Entorno

### Problema Actual
Las credenciales de Prestashop están hardcodeadas en el comando:
```php
$host = '213.134.40.101';
$user = 'alvarez_dbu';
$password = 'X908#AU90#104';
$database = 'alvarez_db';
```

### Solución: Usar Variables de Entorno

#### Step 1: Agregar a `.env`
```env
# Prestashop Database Connection
PRESTASHOP_HOST=213.134.40.101
PRESTASHOP_USER=alvarez_dbu
PRESTASHOP_PASSWORD=X908#AU90#104
PRESTASHOP_DATABASE=alvarez_db
```

#### Step 2: Agregar a `config/prestashop.php`
```php
<?php

return [
    'host' => env('PRESTASHOP_HOST', '127.0.0.1'),
    'user' => env('PRESTASHOP_USER', 'root'),
    'password' => env('PRESTASHOP_PASSWORD', ''),
    'database' => env('PRESTASHOP_DATABASE', 'prestashop'),
];
```

#### Step 3: Actualizar el Comando
Reemplaza los métodos `fetchPrestashop...()` con:

```php
private function getPrestashopConfig(): array
{
    return [
        'host' => config('prestashop.host'),
        'user' => config('prestashop.user'),
        'password' => config('prestashop.password'),
        'database' => config('prestashop.database'),
    ];
}

private function fetchPrestashopOrdersAfterOrderId(int $lastOrderId): array
{
    $config = $this->getPrestashopConfig();

    $query = "SELECT id_order, id_customer, id_lang, reference, date_add
              FROM aalv_orders
              WHERE id_order > {$lastOrderId}
              ORDER BY id_order ASC";

    try {
        $output = shell_exec("mysql -h {$config['host']} -u {$config['user']} -p'{$config['password']}' {$config['database']} -sN -e \"".addslashes($query).'" 2>/dev/null');
        // ... resto del código
    }
    // ...
}
```

---

## 2️⃣ Ejecución Programada (Scheduler)

### Opción A: Ejecución Diaria

```php
// En bootstrap/app.php o en tu EventServiceProvider

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;

return Application::configure(basePath: dirname(__DIR__))
    ->withSchedule(function (Schedule $schedule) {
        // Ejecutar diariamente a las 3:00 AM
        $schedule->command('app:create-blocked-product-documents --force')
            ->dailyAt('03:00')
            ->onSuccess(function () {
                // Notificar éxito
                \Log::info('✅ Blocked product documents created successfully');
            })
            ->onFailure(function () {
                // Notificar error
                \Log::error('❌ Failed to create blocked product documents');
            });
    });
```

### Opción B: Ejecución Cada Hora

```php
$schedule->command('app:create-blocked-product-documents --force --limit=50')
    ->hourly()
    ->at(30); // Ejecutar a los 30 minutos de cada hora
```

### Opción C: Ejecución Cada 5 Minutos (Para Sincronización Rápida)

```php
$schedule->command('app:create-blocked-product-documents --force --limit=10')
    ->everyFiveMinutes();
```

### Opción D: Ejecución Personalizada con Condiciones

```php
$schedule->command('app:create-blocked-product-documents --force --limit=100')
    ->dailyAt('02:00')
    ->weekdays() // Solo de lunes a viernes
    ->when(function () {
        // Solo ejecutar si no hay otra sincronización en progreso
        return ! \DB::table('document_sync_status')
            ->where('status', 'processing')
            ->exists();
    })
    ->then(function () {
        \Log::info('Blocked products sync started');
    });
```

### Verificar que Scheduler Está Corriendo

```bash
# Iniciar el scheduler (Laravel)
php artisan schedule:work

# O en producción, agregar al cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 3️⃣ Notificaciones por Email (Opcional)

### Crear Notificación

```bash
php artisan make:notification BlockedProductsDocumentsCreated
```

### Contenido de `app/Notifications/BlockedProductsDocumentsCreated.php`

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BlockedProductsDocumentsCreated extends Notification
{
    use Queueable;

    public function __construct(
        public int $created,
        public int $skipped,
        public int $errors,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Blocked Product Documents Created')
            ->line("✅ {$this->created} documents were created")
            ->line("⊘ {$this->skipped} orders were skipped")
            ->line("❌ {$this->errors} errors occurred")
            ->action('View Documents', url('/manager/helpdesk/documents'))
            ->line('Thank you for using our application!');
    }
}
```

### Usar en el Comando

```php
// En el método handle()

try {
    // ... procesamiento ...

    // Notificar después del procesamiento
    Notification::route('mail', 'admin@example.com')
        ->notify(new \App\Notifications\BlockedProductsDocumentsCreated(
            $stats['created'],
            $stats['skipped_no_blockade'],
            $stats['errors']
        ));

    return 0;
} catch (\Exception $e) {
    // ... manejo de errores ...
}
```

---

## 4️⃣ Monitoreo y Logging Avanzado

### Crear Tabla de Auditoría

```php
// migration: create_blocked_products_sync_logs_table.php

Schema::create('blocked_products_sync_logs', function (Blueprint $table) {
    $table->id();
    $table->integer('last_order_id');
    $table->integer('orders_processed');
    $table->integer('documents_created');
    $table->integer('documents_skipped');
    $table->integer('errors_count');
    $table->json('errors')->nullable();
    $table->timestamp('started_at');
    $table->timestamp('completed_at');
    $table->timestamps();
});
```

### Registrar Cada Ejecución

```php
// En el comando, agregar esto al final

BlockedProductsSyncLog::create([
    'last_order_id' => $lastOrderId,
    'orders_processed' => $stats['total'],
    'documents_created' => $stats['created'],
    'documents_skipped' => $stats['skipped_no_blockade'] + $stats['skipped_exists'],
    'errors_count' => $stats['errors'],
    'started_at' => now()->subMinutes(5),
    'completed_at' => now(),
]);
```

### Visualizar Histórico

```php
// En un controller o en Tinker

BlockedProductsSyncLog::latest()
    ->take(10)
    ->get()
    ->each(function ($log) {
        echo "Created: {$log->documents_created} | Errors: {$log->errors_count}\n";
    });
```

---

## 5️⃣ Optimizaciones de Rendimiento

### Opción A: Procesar en Background con Queue

```php
// Crear job
php artisan make:job ProcessBlockedProductDocuments
```

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBlockedProductDocuments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Ejecutar comando en background
        \Artisan::call('app:create-blocked-product-documents', [
            '--force' => true,
            '--limit' => 100,
        ]);
    }
}
```

### Usar en Scheduler

```php
$schedule->call(function () {
    ProcessBlockedProductDocuments::dispatch();
})->dailyAt('03:00');
```

### Opción B: Batch Processing

```php
// Procesar órdenes en lotes para evitar timeouts

private function processOrdersWithBlockedProducts(int $lastOrderId): array
{
    $batchSize = 50;
    $offset = 0;
    $totalCreated = 0;
    $totalSkipped = 0;
    $totalErrors = 0;

    while (true) {
        $orders = $this->fetchPrestashopOrdersAfterOrderId($lastOrderId, $batchSize, $offset);

        if (empty($orders)) {
            break;
        }

        foreach ($orders as $order) {
            // Procesar cada orden
        }

        $offset += $batchSize;

        // Liberar memoria
        gc_collect_cycles();
    }

    return [/* ... */];
}
```

---

## 6️⃣ Validación y Testing

### Test Unitario

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateBlockedProductDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_documents_for_blocked_products(): void
    {
        // Crear datos de prueba
        $blockade = DocumentProductBlockade::factory()
            ->create(['document_type_id' => 3]);

        // Ejecutar comando
        $this->artisan('app:create-blocked-product-documents', ['--force' => true])
            ->assertExitCode(0);

        // Verificar que se creó documento
        $this->assertDatabaseHas('documents', [
            'type_id' => 3,
        ]);
    }

    public function test_command_skips_orders_without_blockades(): void
    {
        // Ejecutar comando sin bloqueos
        $this->artisan('app:create-blocked-product-documents', ['--force' => true])
            ->assertExitCode(0);

        // Verificar que no se crearon documentos
        $this->assertDatabaseMissing('documents', [
            'uid' => 'DOC-%',
        ]);
    }
}
```

---

## 7️⃣ Troubleshooting

### Problema: Comando tarda demasiado

**Solución**: Reducir límite y aumentar frecuencia
```bash
# En lugar de procesar 1000 a la vez, procesar 50 cada 1 hora
php artisan app:create-blocked-product-documents --force --limit=50
```

### Problema: Conexión MySQL rechazada

**Solución**: Verificar credenciales y firewall
```bash
mysql -h 213.134.40.101 -u alvarez_dbu -p'X908#AU90#104' -e "SELECT 1"
```

### Problema: Documentos duplicados

**Solución**: El comando es idempotente, no debería pasar
```bash
# Si ocurre, verificar:
SELECT COUNT(*), order_id FROM documents GROUP BY order_id HAVING COUNT(*) > 1;
```

---

## 📚 Archivos Relacionados

- Comando: `modules/Document/app/Console/Commands/CreateBlockedProductDocuments.php`
- Service Provider: `modules/Document/app/Providers/DocumentsServiceProvider.php`
- Documentación: `modules/Document/BLOCKED_PRODUCTS_COMMAND.md`
- Resumen: `modules/Document/COMANDO_RESUMEN.md`

---

**Última actualización**: 18 de Enero, 2025
**Versión**: 1.0
**Autor**: Claude Code
