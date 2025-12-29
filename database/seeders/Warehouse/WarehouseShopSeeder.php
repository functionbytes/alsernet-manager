<?php

namespace Database\Seeders\Warehouse;

use App\Models\Warehouse\WarehouseShop;
use Illuminate\Database\Seeder;

class WarehouseShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 10 available warehouse shops
        WarehouseShop::factory(10)->create();

        // Create 3 unavailable shops
        WarehouseShop::factory(3)->unavailable()->create();

        // Create 2 shops with low stock
        WarehouseShop::factory(2)->lowStock()->create();

        // Create 1 out of stock shop
        WarehouseShop::factory(1)->outOfStock()->create();
    }
}
