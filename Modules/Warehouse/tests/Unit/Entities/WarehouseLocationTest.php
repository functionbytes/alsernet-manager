<?php

namespace Modules\Warehouse\Tests\Unit\Entities;

use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseFloor;
use Modules\Warehouse\Entities\WarehouseLocation;
use Modules\Warehouse\Entities\WarehouseLocationSection;
use Modules\Warehouse\Entities\WarehouseLocationStyle;
use Modules\Warehouse\Entities\WarehouseInventorySlot;
use Tests\TestCase;

class WarehouseLocationTest extends TestCase
{
    /**
     * Test: WarehouseLocation has correct fillable attributes
     */
    public function test_location_has_correct_fillable_attributes(): void
    {
        $location = new WarehouseLocation();

        $expectedFillable = [
            'uid',
            'warehouse_id',
            'floor_id',
            'code',
            'style_id',
            'position_x',
            'position_y',
            'total_levels',
            'available',
            'notes',
            'visual_width_m',
            'visual_height_m',
            'visual_position_x',
            'visual_position_y',
            'use_custom_visual',
            'visual_rotation',
        ];

        $this->assertEquals($expectedFillable, $location->getFillable());
    }

    /**
     * Test: WarehouseLocation has correct casts
     */
    public function test_location_has_correct_casts(): void
    {
        $location = new WarehouseLocation();
        $casts = $location->getCasts();

        $this->assertEquals('boolean', $casts['available']);
        $this->assertEquals('boolean', $casts['use_custom_visual']);
        $this->assertEquals('float', $casts['visual_width_m']);
        $this->assertEquals('float', $casts['visual_height_m']);
    }

    /**
     * Test: WarehouseLocation belongsTo warehouse
     */
    public function test_location_belongs_to_warehouse(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create();

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
        ]);

        $this->assertInstanceOf(Warehouse::class, $location->warehouse);
        $this->assertEquals($warehouse->id, $location->warehouse->id);
    }

    /**
     * Test: WarehouseLocation belongsTo floor
     */
    public function test_location_belongs_to_floor(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create();

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
        ]);

        $this->assertInstanceOf(WarehouseFloor::class, $location->floor);
        $this->assertEquals($floor->id, $location->floor->id);
    }

    /**
     * Test: WarehouseLocation belongsTo style
     */
    public function test_location_belongs_to_style(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create();

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
        ]);

        $this->assertInstanceOf(WarehouseLocationStyle::class, $location->style);
        $this->assertEquals($style->id, $location->style->id);
    }

    /**
     * Test: WarehouseLocation hasMany sections
     */
    public function test_location_has_many_sections(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create();

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
        ]);

        $section1 = WarehouseLocationSection::factory()->create(['location_id' => $location->id]);
        $section2 = WarehouseLocationSection::factory()->create(['location_id' => $location->id]);

        $sections = $location->sections()->get();

        $this->assertCount(2, $sections);
        $this->assertTrue($sections->pluck('id')->contains($section1->id));
    }

    /**
     * Test: WarehouseLocation hasManyThrough slots
     */
    public function test_location_has_many_through_slots(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create();

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
        ]);

        $section = WarehouseLocationSection::factory()->create(['location_id' => $location->id]);
        $slot1 = WarehouseInventorySlot::factory()->create(['section_id' => $section->id]);
        $slot2 = WarehouseInventorySlot::factory()->create(['section_id' => $section->id]);

        $slots = $location->slots()->get();

        $this->assertCount(2, $slots);
        $this->assertTrue($slots->pluck('id')->contains($slot1->id));
    }

    /**
     * Test: getTotalSlots computed property calculation
     */
    public function test_get_total_slots_calculation(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create(['faces' => ['front', 'back']]);

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
            'total_levels' => 3,
            'total_sections' => 4,
        ]);

        // Total = faces (2) × levels (3) × sections (4) = 24
        $this->assertEquals(24, $location->getTotalSlots());
    }

    /**
     * Test: getTotalSlots with single face
     */
    public function test_get_total_slots_with_single_face(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create(['faces' => ['front']]);

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
            'total_levels' => 2,
            'total_sections' => 5,
        ]);

        // Total = faces (1) × levels (2) × sections (5) = 10
        $this->assertEquals(10, $location->getTotalSlots());
    }

    /**
     * Test: getOccupiedSlots returns count of occupied slots
     */
    public function test_get_occupied_slots(): void
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

        $this->assertEquals(3, $location->getOccupiedSlots());
    }

    /**
     * Test: getAvailableSlots returns count of available slots
     */
    public function test_get_available_slots(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create(['faces' => ['front']]);

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
            'total_levels' => 1,
            'total_sections' => 5,
        ]);

        $section = WarehouseLocationSection::factory()->create(['location_id' => $location->id]);
        WarehouseInventorySlot::factory(3)->create(['section_id' => $section->id, 'quantity' => 10]);
        WarehouseInventorySlot::factory(2)->create(['section_id' => $section->id, 'quantity' => 0]);

        // Total: 5, Occupied: 3, Available: 2
        $this->assertEquals(2, $location->getAvailableSlots());
    }

    /**
     * Test: getOccupancyPercentage calculation
     */
    public function test_get_occupancy_percentage(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create(['faces' => ['front']]);

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
            'total_levels' => 1,
            'total_sections' => 10,
        ]);

        $section = WarehouseLocationSection::factory()->create(['location_id' => $location->id]);
        WarehouseInventorySlot::factory(5)->create(['section_id' => $section->id, 'quantity' => 10]);
        WarehouseInventorySlot::factory(5)->create(['section_id' => $section->id, 'quantity' => 0]);

        // 5 occupied out of 10 = 50%
        $this->assertEquals(50.0, $location->getOccupancyPercentage());
    }

    /**
     * Test: getOccupancyPercentage returns zero when location has zero slots
     */
    public function test_get_occupancy_percentage_returns_zero_when_no_slots(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create(['faces' => []]);

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
            'total_levels' => 2,
            'total_sections' => 2,
        ]);

        $this->assertEquals(0, $location->getOccupancyPercentage());
    }

    /**
     * Test: getVisualWidth returns custom visual width when set
     */
    public function test_get_visual_width_returns_custom_when_set(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create(['width' => 2.5]);

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
            'use_custom_visual' => true,
            'visual_width_m' => 3.5,
        ]);

        $this->assertEquals(3.5, $location->getVisualWidth());
    }

    /**
     * Test: getVisualWidth returns style width when custom not set
     */
    public function test_get_visual_width_returns_style_width_when_not_custom(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create(['width' => 2.5]);

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
            'use_custom_visual' => false,
        ]);

        $this->assertEquals(2.5, $location->getVisualWidth());
    }

    /**
     * Test: getVisualHeight returns custom visual height when set
     */
    public function test_get_visual_height_returns_custom_when_set(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create(['height' => 1.8]);

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
            'use_custom_visual' => true,
            'visual_height_m' => 2.2,
        ]);

        $this->assertEquals(2.2, $location->getVisualHeight());
    }

    /**
     * Test: getVisualPositionX returns custom position when set
     */
    public function test_get_visual_position_x_returns_custom_when_set(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create();

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
            'position_x' => 10.0,
            'use_custom_visual' => true,
            'visual_position_x' => 15.5,
        ]);

        $this->assertEquals(15.5, $location->getVisualPositionX());
    }

    /**
     * Test: getVisualPositionY returns custom position when set
     */
    public function test_get_visual_position_y_returns_custom_when_set(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create();

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
            'position_y' => 5.0,
            'use_custom_visual' => true,
            'visual_position_y' => 8.75,
        ]);

        $this->assertEquals(8.75, $location->getVisualPositionY());
    }

    /**
     * Test: getFullName returns composed name
     */
    public function test_get_full_name(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id, 'name' => 'Floor 1']);
        $style = WarehouseLocationStyle::factory()->create();

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
            'code' => 'LOC-001',
        ]);

        $this->assertEquals('LOC-001 (Floor 1)', $location->getFullName());
    }

    /**
     * Test: available scope returns only available locations
     */
    public function test_available_scope_returns_only_available(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create();

        WarehouseLocation::factory(3)->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
            'available' => true,
        ]);
        WarehouseLocation::factory(2)->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
            'available' => false,
        ]);

        $available = WarehouseLocation::available()->get();

        $this->assertCount(3, $available);
    }

    /**
     * Test: byFloor scope filters by floor
     */
    public function test_by_floor_scope_filters_correctly(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor1 = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $floor2 = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create();

        WarehouseLocation::factory(3)->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor1->id,
            'style_id' => $style->id,
        ]);
        WarehouseLocation::factory(2)->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor2->id,
            'style_id' => $style->id,
        ]);

        $locations = WarehouseLocation::byFloor($floor1->id)->get();

        $this->assertCount(3, $locations);
        $locations->each(fn ($loc) => $this->assertEquals($floor1->id, $loc->floor_id));
    }

    /**
     * Test: byStyle scope filters by style
     */
    public function test_by_style_scope_filters_correctly(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style1 = WarehouseLocationStyle::factory()->create();
        $style2 = WarehouseLocationStyle::factory()->create();

        WarehouseLocation::factory(3)->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style1->id,
        ]);
        WarehouseLocation::factory(2)->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style2->id,
        ]);

        $locations = WarehouseLocation::byStyle($style1->id)->get();

        $this->assertCount(3, $locations);
        $locations->each(fn ($loc) => $this->assertEquals($style1->id, $loc->style_id));
    }

    /**
     * Test: search scope searches by uid
     */
    public function test_search_scope_searches_by_uid(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create();

        $location1 = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
            'uid' => 'search-uid-12345',
        ]);
        WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
        ]);

        $results = WarehouseLocation::search('search-uid')->get();

        $this->assertCount(1, $results);
        $this->assertEquals($location1->id, $results->first()->id);
    }

    /**
     * Test: getSummary returns correct array structure
     */
    public function test_get_summary_returns_correct_structure(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create();

        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
        ]);

        $summary = $location->getSummary();

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('id', $summary);
        $this->assertArrayHasKey('uid', $summary);
        $this->assertArrayHasKey('code', $summary);
        $this->assertArrayHasKey('full_name', $summary);
        $this->assertArrayHasKey('total_slots', $summary);
        $this->assertArrayHasKey('occupied_slots', $summary);
        $this->assertArrayHasKey('available_slots', $summary);
        $this->assertArrayHasKey('occupancy_percentage', $summary);
    }

    /**
     * Test: WarehouseLocation table configuration
     */
    public function test_location_table_configuration(): void
    {
        $location = new WarehouseLocation();

        $this->assertEquals('warehouse_locations', $location->getTable());
        $this->assertEquals('id', $location->getKeyName());
        $this->assertEquals('int', $location->getKeyType());
        $this->assertTrue($location->getIncrementing());
    }
}
