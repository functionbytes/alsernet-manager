<?php

namespace Modules\Warehouse\Tests\Unit\Entities;

use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseFloor;
use Modules\Warehouse\Entities\WarehouseLocation;
use Modules\Warehouse\Entities\WarehouseLocationSection;
use Modules\Warehouse\Entities\WarehouseInventorySlot;
use Modules\Warehouse\Entities\WarehouseLocationStyle;
use Tests\TestCase;
use Illuminate\Support\Str;

class WarehouseFloorTest extends TestCase
{
    /**
     * Test: WarehouseFloor has correct fillable attributes
     */
    public function test_floor_has_correct_fillable_attributes(): void
    {
        $floor = new WarehouseFloor();

        $expectedFillable = [
            'uid',
            'warehouse_id',
            'code',
            'name',
            'description',
            'level',
            'available',
        ];

        $this->assertEquals($expectedFillable, $floor->getFillable());
    }

    /**
     * Test: WarehouseFloor has correct casts
     */
    public function test_floor_has_correct_casts(): void
    {
        $floor = new WarehouseFloor();

        $this->assertTrue(isset($floor->getCasts()['available']));
        $this->assertEquals('boolean', $floor->getCasts()['available']);
        $this->assertTrue(isset($floor->getCasts()['created_at']));
        $this->assertTrue(isset($floor->getCasts()['updated_at']));
    }

    /**
     * Test: WarehouseFloor belongsTo warehouse
     */
    public function test_floor_belongs_to_warehouse(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);

        $this->assertInstanceOf(Warehouse::class, $floor->warehouse);
        $this->assertEquals($warehouse->id, $floor->warehouse->id);
    }

    /**
     * Test: WarehouseFloor hasMany locations
     */
    public function test_floor_has_many_locations(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create();

        $location1 = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
        ]);
        $location2 = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
        ]);

        $locations = $floor->locations()->get();

        $this->assertCount(2, $locations);
        $this->assertTrue($locations->pluck('id')->contains($location1->id));
        $this->assertTrue($locations->pluck('id')->contains($location2->id));
    }

    /**
     * Test: getLocationCount returns correct count
     */
    public function test_get_location_count_returns_correct_count(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create();

        WarehouseLocation::factory(3)->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
        ]);

        $this->assertEquals(3, $floor->getlocationCount());
    }

    /**
     * Test: getLocationCount returns zero when no locations
     */
    public function test_get_location_count_returns_zero_when_no_locations(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);

        $this->assertEquals(0, $floor->getlocationCount());
    }

    /**
     * Test: getTotalSlotsCount returns correct count
     */
    public function test_get_total_slots_count_returns_correct_count(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create(['faces' => ['front', 'back']]);

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
            'total_levels' => 2,
            'total_sections' => 3,
        ]);

        $section = WarehouseLocationSection::factory()->create(['location_id' => $location->id]);
        WarehouseInventorySlot::factory(5)->create(['section_id' => $section->id]);

        $this->assertEquals(5, $floor->getTotalSlotsCount());
    }

    /**
     * Test: getTotalSlotsCount returns zero when no slots
     */
    public function test_get_total_slots_count_returns_zero_when_no_slots(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);

        $this->assertEquals(0, $floor->getTotalSlotsCount());
    }

    /**
     * Test: getOccupiedSlotsCount returns count of occupied slots
     */
    public function test_get_occupied_slots_count_returns_occupied_slots(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create(['faces' => ['front']]);

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
        ]);

        $section = WarehouseLocationSection::factory()->create(['location_id' => $location->id]);
        WarehouseInventorySlot::factory(3)->create(['section_id' => $section->id, 'quantity' => 10]);
        WarehouseInventorySlot::factory(2)->create(['section_id' => $section->id, 'quantity' => 0]);

        $this->assertEquals(3, $floor->getOccupiedSlotsCount());
    }

    /**
     * Test: getOccupancyPercentage calculation
     */
    public function test_get_occupancy_percentage_calculation(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create(['faces' => ['front']]);

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
        ]);

        $section = WarehouseLocationSection::factory()->create(['location_id' => $location->id]);
        WarehouseInventorySlot::factory(5)->create(['section_id' => $section->id, 'quantity' => 10]);
        WarehouseInventorySlot::factory(5)->create(['section_id' => $section->id, 'quantity' => 0]);

        // 5 occupied out of 10 total = 50%
        $this->assertEquals(50.0, $floor->getOccupancyPercentage());
    }

    /**
     * Test: getOccupancyPercentage returns zero when floor has zero slots (no division by zero)
     */
    public function test_get_occupancy_percentage_returns_zero_when_no_slots(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);

        $this->assertEquals(0, $floor->getOccupancyPercentage());
    }

    /**
     * Test: getOccupancyPercentage returns 100 when all slots occupied
     */
    public function test_get_occupancy_percentage_returns_100_when_all_occupied(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create(['faces' => ['front']]);

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
        ]);

        $section = WarehouseLocationSection::factory()->create(['location_id' => $location->id]);
        WarehouseInventorySlot::factory(10)->create(['section_id' => $section->id, 'quantity' => 5]);

        $this->assertEquals(100.0, $floor->getOccupancyPercentage());
    }

    /**
     * Test: byWarehouse scope filters by warehouse ID
     */
    public function test_by_warehouse_scope_filters_correctly(): void
    {
        $warehouse1 = Warehouse::factory()->create();
        $warehouse2 = Warehouse::factory()->create();

        WarehouseFloor::factory(3)->create(['warehouse_id' => $warehouse1->id]);
        WarehouseFloor::factory(2)->create(['warehouse_id' => $warehouse2->id]);

        $floors = WarehouseFloor::byWarehouse($warehouse1->id)->get();

        $this->assertCount(3, $floors);
        $floors->each(fn ($floor) => $this->assertEquals($warehouse1->id, $floor->warehouse_id));
    }

    /**
     * Test: available scope returns only available floors
     */
    public function test_available_scope_returns_only_available_floors(): void
    {
        $warehouse = Warehouse::factory()->create();

        WarehouseFloor::factory(3)->create(['warehouse_id' => $warehouse->id, 'available' => true]);
        WarehouseFloor::factory(2)->create(['warehouse_id' => $warehouse->id, 'available' => false]);

        $available = WarehouseFloor::available()->get();

        $this->assertCount(3, $available);
        $available->each(fn ($floor) => $this->assertTrue($floor->available));
    }

    /**
     * Test: uid scope returns single floor by UUID
     */
    public function test_uid_scope_returns_single_floor(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);

        $result = WarehouseFloor::uid($floor->uid);

        $this->assertInstanceOf(WarehouseFloor::class, $result);
        $this->assertEquals($floor->uid, $result->uid);
    }

    /**
     * Test: ordered scope orders by level and name
     */
    public function test_ordered_scope_orders_by_level_and_name(): void
    {
        $warehouse = Warehouse::factory()->create();

        WarehouseFloor::factory()->create([
            'warehouse_id' => $warehouse->id,
            'level' => 2,
            'name' => 'Floor B',
        ]);
        WarehouseFloor::factory()->create([
            'warehouse_id' => $warehouse->id,
            'level' => 1,
            'name' => 'Floor A',
        ]);
        WarehouseFloor::factory()->create([
            'warehouse_id' => $warehouse->id,
            'level' => 1,
            'name' => 'Floor Z',
        ]);

        $ordered = WarehouseFloor::ordered()->get();

        $this->assertEquals(1, $ordered->first()->level);
        $this->assertEquals('Floor A', $ordered->first()->name);
        $this->assertEquals(2, $ordered->last()->level);
    }

    /**
     * Test: search scope searches by name
     */
    public function test_search_scope_searches_by_name(): void
    {
        $warehouse = Warehouse::factory()->create();

        WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id, 'name' => 'Ground Floor']);
        WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id, 'name' => 'First Floor']);
        WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id, 'name' => 'Basement']);

        $results = WarehouseFloor::search('Ground')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Ground Floor', $results->first()->name);
    }

    /**
     * Test: getSummary returns correct array structure
     */
    public function test_get_summary_returns_correct_structure(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);

        $summary = $floor->getSummary();

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('id', $summary);
        $this->assertArrayHasKey('uid', $summary);
        $this->assertArrayHasKey('warehouse_id', $summary);
        $this->assertArrayHasKey('name', $summary);
        $this->assertArrayHasKey('level', $summary);
        $this->assertArrayHasKey('available', $summary);
        $this->assertArrayHasKey('locations_count', $summary);
        $this->assertArrayHasKey('total_slots', $summary);
        $this->assertArrayHasKey('occupancy_percentage', $summary);
    }

    /**
     * Test: WarehouseFloor table configuration
     */
    public function test_floor_table_configuration(): void
    {
        $floor = new WarehouseFloor();

        $this->assertEquals('warehouse_floors', $floor->getTable());
        $this->assertEquals('id', $floor->getKeyName());
        $this->assertEquals('int', $floor->getKeyType());
        $this->assertTrue($floor->getIncrementing());
    }
}
