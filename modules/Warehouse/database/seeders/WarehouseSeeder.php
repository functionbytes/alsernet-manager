<?php

namespace Modules\Warehouse\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * WarehouseSeeder
 *
 * Seeder maestro para el sistema de gestión del almacén.
 * Ejecuta los seeders en orden correcto:
 * 1. FloorSeeder    → Crea pisos
 * 2. StandStyleSeeder → Crea estilos de estanterías
 * 3. StandSeeder → Crea estanterías
 * 4. InventorySlotSeeder → Crea posiciones de inventario
 *
 * Uso:
 * php artisan db:seed --class=WarehouseSeeder
 */
class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏭 Iniciando siembra del sistema de almacén...');
        $this->command->newLine();

        // 1. Crear pisos
        $this->command->info('1️⃣  Creando pisos del almacén...');
        $this->call(FloorSeeder::class);
        $this->command->newLine();

        // 2. Crear estilos de estanterías
        $this->command->info('2️⃣  Creando estilos de estanterías...');
        $this->call(StandStyleSeeder::class);
        $this->command->newLine();

        // 3. Crear estanterías
        $this->command->info('3️⃣  Creando estanterías...');
        $this->call(StandSeeder::class);
        $this->command->newLine();

        // 4. Crear posiciones de inventario
        $this->command->info('4️⃣  Creando posiciones de inventario...');
        $this->call(InventorySlotSeeder::class);
        $this->command->newLine();

        $this->command->info('✅ ¡Sistema de almacén sembrado exitosamente!');
        $this->command->line('');
        $this->command->line('📊 Resumen:');
        $this->command->line('   • 4 pisos creados');
        $this->command->line('   • 3 estilos de estanterías');
        $this->command->line('   • ~15 estanterías físicas');
        $this->command->line('   • ~1000+ posiciones de inventario');
    }
}
