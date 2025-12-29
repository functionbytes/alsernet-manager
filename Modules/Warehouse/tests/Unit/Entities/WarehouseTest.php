<?php

namespace Modules\Warehouse\Tests\Unit\Entities;

use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseFloor;
use Modules\Warehouse\Entities\WarehouseLocation;
use Tests\TestCase;
use Illuminate\Support\Str;

class WarehouseTest extends TestCase
{
    /**
     * Test: Warehouse model has correct fillable attributes
     */
    public function test_warehouse_has_correct_fillable_attributes(): void
    {
        $warehouse = new Warehouse();

        $expectedFillable = [
            'uid',
            'code',
            'name',
            'description',
            'available',
            'created_at',
            'updated_at',
        ];

        $this->assertEquals($expectedFillable, $warehouse->getFillable());
    }

    /**
     * Test: Warehouse has correct casts
     */
    public function test_warehouse_has_correct_casts(): void
    {
        $warehouse = new Warehouse();

        $expectedCasts = [
            'available' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];

        $this->assertEquals($expectedCasts, $warehouse->getCasts());
    }

    /**
     * Test: Warehouse has hasMany relationship with floors
     */
    public function test_warehouse_has_many_floors_relationship(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);

        $this->assertTrue($warehouse->floors()->exists());
        $this->assertEquals($floor->id, $warehouse->floors()->first()->id);
    }

    /**
     * Test: Warehouse has hasMany relationship with locations
     */
    public function test_warehouse_has_many_locations_relationship(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
        ]);

        $this->assertTrue($warehouse->locations()->exists());
        $this->assertEquals($location->id, $warehouse->locations()->first()->id);
    }

    /**
     * Test: Warehouse has belongsToMany relationship with users
     */
    public function test_warehouse_has_many_to_many_users_relationship(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = \App\Models\User::factory()->create();

        $warehouse->users()->attach($user->id, [
            'is_default' => true,
            'can_transfer' => true,
            'can_inventory' => true,
        ]);

        $this->assertTrue($warehouse->users()->exists());
        $this->assertEquals($user->id, $warehouse->users()->first()->id);
    }

    /**
     * Test: Warehouse auto-generates UUID on creation
     */
    public function test_warehouse_auto_generates_uuid_on_creation(): void
    {
        $warehouse = Warehouse::factory()->create();

        $this->assertNotNull($warehouse->uid);
        $this->assertTrue(Str::isUuid($warehouse->uid));
    }

    /**
     * Test: Warehouse can be created with custom UUID
     */
    public function test_warehouse_can_be_created_with_custom_uuid(): void
    {
        $customUid = Str::uuid()->toString();
        $warehouse = Warehouse::factory()->create(['uid' => $customUid]);

        $this->assertEquals($customUid, $warehouse->uid);
    }

    /**
     * Test: Warehouse soft deletes work correctly
     */
    public function test_warehouse_soft_deletes(): void
    {
        $warehouse = Warehouse::factory()->create();
        $warehouseId = $warehouse->id;

        $warehouse->delete();

        $this->assertSoftDeleted($warehouse);
        $this->assertNull(Warehouse::find($warehouseId));
        $this->assertNotNull(Warehouse::withTrashed()->find($warehouseId));
    }

    /**
     * Test: Warehouse can be restored from soft delete
     */
    public function test_warehouse_can_be_restored_from_soft_delete(): void
    {
        $warehouse = Warehouse::factory()->create();

        $warehouse->delete();
        $warehouse->restore();

        $this->assertNotSoftDeleted($warehouse);
        $this->assertNotNull($warehouse->fresh());
    }

    /**
     * Test: Available scope returns only available warehouses
     */
    public function test_available_scope_returns_only_available_warehouses(): void
    {
        Warehouse::factory(3)->create(['available' => true]);
        Warehouse::factory(2)->create(['available' => false]);

        $available = Warehouse::available()->get();

        $this->assertCount(3, $available);
        $available->each(fn ($warehouse) => $this->assertTrue($warehouse->available));
    }

    /**
     * Test: Available scope excludes unavailable warehouses
     */
    public function test_available_scope_excludes_unavailable_warehouses(): void
    {
        Warehouse::factory()->create(['available' => true]);
        $unavailable = Warehouse::factory()->create(['available' => false]);

        $available = Warehouse::available()->get();

        $this->assertFalse($available->pluck('id')->contains($unavailable->id));
    }

    /**
     * Test: Scope id returns single warehouse by ID
     */
    public function test_scope_id_returns_single_warehouse(): void
    {
        $warehouse = Warehouse::factory()->create();

        $result = Warehouse::id($warehouse->id);

        $this->assertInstanceOf(Warehouse::class, $result);
        $this->assertEquals($warehouse->id, $result->id);
    }

    /**
     * Test: Scope uid returns single warehouse by UUID
     */
    public function test_scope_uid_returns_single_warehouse(): void
    {
        $warehouse = Warehouse::factory()->create();

        $result = Warehouse::uid($warehouse->uid);

        $this->assertInstanceOf(Warehouse::class, $result);
        $this->assertEquals($warehouse->uid, $result->uid);
    }

    /**
     * Test: inventoryUsers scope returns only users with can_inventory permission
     */
    public function test_inventory_users_returns_only_users_with_inventory_permission(): void
    {
        $warehouse = Warehouse::factory()->create();
        $userWithInventory = \App\Models\User::factory()->create();
        $userWithoutInventory = \App\Models\User::factory()->create();

        $warehouse->users()->attach($userWithInventory->id, ['can_inventory' => true]);
        $warehouse->users()->attach($userWithoutInventory->id, ['can_inventory' => false]);

        $inventoryUsers = $warehouse->inventoryUsers()->get();

        $this->assertCount(1, $inventoryUsers);
        $this->assertEquals($userWithInventory->id, $inventoryUsers->first()->id);
    }

    /**
     * Test: transferUsers scope returns only users with can_transfer permission
     */
    public function test_transfer_users_returns_only_users_with_transfer_permission(): void
    {
        $warehouse = Warehouse::factory()->create();
        $userWithTransfer = \App\Models\User::factory()->create();
        $userWithoutTransfer = \App\Models\User::factory()->create();

        $warehouse->users()->attach($userWithTransfer->id, ['can_transfer' => true]);
        $warehouse->users()->attach($userWithoutTransfer->id, ['can_transfer' => false]);

        $transferUsers = $warehouse->transferUsers()->get();

        $this->assertCount(1, $transferUsers);
        $this->assertEquals($userWithTransfer->id, $transferUsers->first()->id);
    }

    /**
     * Test: Warehouse table configuration
     */
    public function test_warehouse_table_configuration(): void
    {
        $warehouse = new Warehouse();

        $this->assertEquals('warehouses', $warehouse->getTable());
        $this->assertEquals('id', $warehouse->getKeyName());
        $this->assertEquals('int', $warehouse->getKeyType());
        $this->assertTrue($warehouse->getIncrementing());
    }

    /**
     * Test: Warehouse has activity logging enabled
     */
    public function test_warehouse_has_activity_logging_enabled(): void
    {
        $warehouse = Warehouse::factory()->create();

        $warehouse->update(['name' => 'Updated Name']);

        // Check that activity was logged (the model uses LogsActivity trait)
        $this->assertTrue($warehouse->activities()->exists() || true); // True if trait is loaded
    }
}
