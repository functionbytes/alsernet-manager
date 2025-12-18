<?php

namespace App\Console\Commands;

use App\Events\Document\DocumentCreated;
use App\Models\Document\Document;
use App\Services\Documents\DocumentEmailService;
use Illuminate\Console\Command;

class CreateSampleDocumentsFromPrestashop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-sample-documents {--count=3 : Number of sample documents to create} {--send-emails : Send initial request emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sample documents from PrestaShop with test data and optionally send initial request emails';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = (int) $this->option('count');
        $sendEmails = $this->option('send-emails');

        $this->info("Creating {$count} sample documents from PrestaShop...");

        $sampleOrders = $this->generateSampleOrders($count);

        $createdCount = 0;
        $emailCount = 0;

        foreach ($sampleOrders as $orderData) {
            try {
                $document = $this->createDocumentFromOrder($orderData);

                if ($document) {
                    $createdCount++;
                    $this->line("✓ Created document: {$document->uid} (Order #{$document->order_id})", 'comment');

                    // Enviar correo de solicitud inicial si se solicita
                    if ($sendEmails) {
                        $this->sendInitialRequestEmail($document);
                        $emailCount++;
                        $this->line("  ✉ Sent initial request email to {$document->customer_email}", 'info');
                    }
                }
            } catch (\Exception $e) {
                $this->error("Error creating document: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info('✓ Complete!');
        $this->line("  Documents created: {$createdCount}");
        if ($sendEmails) {
            $this->line("  Emails sent: {$emailCount}");
        }

        return 0;
    }

    /**
     * Generate sample PrestaShop order data
     */
    private function generateSampleOrders(int $count): array
    {
        $documentTypes = ['corta', 'rifle', 'escopeta', 'dni'];
        $firstNames = ['Juan', 'Maria', 'Carlos', 'Ana', 'Miguel', 'Isabel'];
        $lastNames = ['García', 'López', 'Martínez', 'Rodríguez', 'Pérez', 'González'];
        $products = [
            ['name' => 'Pistola 9mm', 'reference' => 'PST-001', 'type' => 'corta'],
            ['name' => 'Rifle 308', 'reference' => 'RIF-001', 'type' => 'rifle'],
            ['name' => 'Escopeta 12', 'reference' => 'ESC-001', 'type' => 'escopeta'],
            ['name' => 'Aire comprimido', 'reference' => 'AIR-001', 'type' => 'dni'],
        ];

        $orders = [];

        for ($i = 0; $i < $count; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $product = $products[array_rand($products)];

            $orders[] = [
                'order_id' => 1000000 + $i,
                'reference' => 'PS-'.str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'date_add' => now()->subDays(rand(1, 30))->format('Y-m-d H:i:s'),
                'type' => $product['type'],
                'customer' => [
                    'id_customer' => 500000 + $i,
                    'firstname' => $firstName,
                    'lastname' => $lastName,
                    'email' => strtolower($firstName.'.'.$lastName.'@example.com'),
                    'siret' => 'DNI-'.str_pad(random_int(1000000, 9999999), 8, '0', STR_PAD_LEFT),
                    'company' => 'Test Company '.($i + 1),
                    'phone_mobile' => '6'.str_pad(random_int(1000000, 9999999), 8, '0', STR_PAD_LEFT),
                ],
                'products' => [
                    [
                        'product_id' => 1000 + $i,
                        'product_name' => $product['name'],
                        'product_reference' => $product['reference'],
                        'product_quantity' => 1,
                        'unit_price_tax_incl' => rand(100, 1000),
                    ],
                ],
                'iso_code' => 'es',
            ];
        }

        return $orders;
    }

    /**
     * Create a document from PrestaShop order data
     */
    private function createDocumentFromOrder(array $orderData): ?Document
    {
        // Verificar si ya existe el documento
        $existing = Document::where('order_id', $orderData['order_id'])->first();
        if ($existing) {
            $this->warn("Document already exists for order {$orderData['order_id']}");

            return null;
        }

        // Crear documento
        $document = new Document;
        $document->uid = \Illuminate\Support\Str::uuid(); // Generar UID único
        $document->order_id = $orderData['order_id'];
        $document->order_reference = $orderData['reference'];
        $document->order_date = $orderData['date_add'];
        $document->type = $orderData['type'];
        $document->source = 'api'; // Simular origen desde API PrestaShop
        $document->proccess = 0;
        $document->lang_id = \App\Models\Lang::iso('es')?->id;

        $document->customer_id = $orderData['customer']['id_customer'];
        $document->customer_firstname = $orderData['customer']['firstname'];
        $document->customer_lastname = $orderData['customer']['lastname'];
        $document->customer_email = $orderData['customer']['email'];
        $document->customer_dni = $orderData['customer']['siret'];
        $document->customer_company = $orderData['customer']['company'];
        $document->customer_cellphone = $orderData['customer']['phone_mobile'];

        $document->save();

        // Crear productos
        foreach ($orderData['products'] as $product) {
            $document->products()->create([
                'product_id' => $product['product_id'],
                'product_name' => $product['product_name'],
                'product_reference' => $product['product_reference'],
                'quantity' => $product['product_quantity'],
                'price' => $product['unit_price_tax_incl'],
            ]);
        }

        // Disparar evento de creación
        DocumentCreated::dispatch($document);

        return $document;
    }

    /**
     * Send initial request email to document customer
     */
    private function sendInitialRequestEmail(Document $document): void
    {
        try {
            app(DocumentEmailService::class)->sendInitialRequest($document);
        } catch (\Exception $e) {
            $this->warn("Failed to send email to {$document->customer_email}: {$e->getMessage()}");
        }
    }
}
