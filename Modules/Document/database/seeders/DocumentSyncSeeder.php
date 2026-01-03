<?php

namespace Modules\Document\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Document\Entities\DocumentSync;

class DocumentSyncSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds the document_syncs table with all available document sync types.
     * Syncs represent the synchronization method used for documents.
     */
    public function run(): void
    {
        $syncs = [
            [
                'key' => 'none',
                'label' => 'Sin Sincronización',
                'description' => 'No se sincroniza con sistemas externos',
                'icon' => 'slash',
                'color' => '#6c757d',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'key' => 'prestashop',
                'label' => 'PrestaShop',
                'description' => 'Sincronización automática con PrestaShop',
                'icon' => 'globe',
                'color' => '#24b9a6',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'key' => 'erp',
                'label' => 'ERP',
                'description' => 'Sincronización con sistema ERP',
                'icon' => 'database',
                'color' => '#0d6efd',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'key' => 'api',
                'label' => 'API',
                'description' => 'Sincronización a través de API externa',
                'icon' => 'code',
                'color' => '#6f42c1',
                'is_active' => true,
                'order' => 4,
            ],
            [
                'key' => 'email_imap',
                'label' => 'Email (IMAP)',
                'description' => 'Sincronización de documentos desde email',
                'icon' => 'envelope',
                'color' => '#0dcaf0',
                'is_active' => true,
                'order' => 5,
            ],
        ];

        foreach ($syncs as $sync) {
            DocumentSync::firstOrCreate(
                ['key' => $sync['key']],
                $sync
            );
        }
    }
}
