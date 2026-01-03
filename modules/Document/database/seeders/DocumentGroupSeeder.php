<?php

namespace Modules\Document\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Document\Entities\DocumentGroup;

class DocumentGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            [
                'uid' => Str::ulid(),
                'name' => 'Documentación Regulatoria',
                'key' => 'regulatory_documentation',
                'description' => 'Licencias, permisos, certificaciones y autorizaciones',
                'icon' => 'fa-certificate',
                'color' => '#0d6efd',
                'is_required' => true,
                'is_active' => true,
                'position' => 1,
            ],
            [
                'uid' => Str::ulid(),
                'name' => 'Documentos Financieros',
                'key' => 'financial_documents',
                'description' => 'Facturas, recibos, contratos y acuerdos de pago',
                'icon' => 'fa-file-invoice-dollar',
                'color' => '#198754',
                'is_required' => true,
                'is_active' => true,
                'position' => 2,
            ],
            [
                'uid' => Str::ulid(),
                'name' => 'Identidad y Personales',
                'key' => 'identity_personal',
                'description' => 'Documentos de identidad, pasaporte, datos personales',
                'icon' => 'fa-id-card',
                'color' => '#0dcaf0',
                'is_required' => true,
                'is_active' => true,
                'position' => 3,
            ],
            [
                'uid' => Str::ulid(),
                'name' => 'Cumplimiento y Auditoría',
                'key' => 'compliance_audit',
                'description' => 'Reportes de auditoría, certificados de cumplimiento, inspecciones',
                'icon' => 'fa-clipboard-check',
                'color' => '#ffc107',
                'is_required' => false,
                'is_active' => true,
                'position' => 4,
            ],
            [
                'uid' => Str::ulid(),
                'name' => 'Documentos Operacionales',
                'key' => 'operational_documents',
                'description' => 'Órdenes de compra, documentos de envío, recibos de mercancía',
                'icon' => 'fa-boxes',
                'color' => '#6c757d',
                'is_required' => false,
                'is_active' => true,
                'position' => 5,
            ],
            [
                'uid' => Str::ulid(),
                'name' => 'Documentación de Productos',
                'key' => 'product_documentation',
                'description' => 'Especificaciones, manuales, garantías y documentación técnica',
                'icon' => 'fa-book',
                'color' => '#0d6efd',
                'is_required' => false,
                'is_active' => true,
                'position' => 6,
            ],
        ];

        foreach ($groups as $group) {
            DocumentGroup::firstOrCreate(
                ['key' => $group['key']],
                $group
            );
        }

        $this->command->info('✅ Document groups seeded successfully');
    }
}
