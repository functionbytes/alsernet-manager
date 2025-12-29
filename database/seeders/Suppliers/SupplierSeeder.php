<?php

namespace Database\Seeders\Suppliers;

use App\Models\Supplier\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'label' => 'Nike',
                'code' => 'NIKE',
                'erp_id' => 1001,
                'supplier_id' => null,
                'website_url' => 'https://www.nike.com',
                'is_active' => true,
            ],
            [
                'label' => 'Adidas',
                'code' => 'ADIDAS',
                'erp_id' => 1002,
                'supplier_id' => null,
                'website_url' => 'https://www.adidas.com',
                'is_active' => true,
            ],
            [
                'label' => 'Puma',
                'code' => 'PUMA',
                'erp_id' => 1003,
                'supplier_id' => null,
                'website_url' => 'https://www.puma.com',
                'is_active' => true,
            ],
            [
                'label' => 'Asics',
                'code' => 'ASICS',
                'erp_id' => 1004,
                'supplier_id' => null,
                'website_url' => 'https://www.asics.com',
                'is_active' => true,
            ],
            [
                'label' => 'New Balance',
                'code' => 'NB',
                'erp_id' => 1005,
                'supplier_id' => null,
                'website_url' => 'https://www.newbalance.com',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::updateOrCreate(
                ['code' => $supplierData['code']],
                $supplierData
            );
        }

        $this->command->info('Created 5 suppliers: Nike, Adidas, Puma, Asics, New Balance');
    }
}
