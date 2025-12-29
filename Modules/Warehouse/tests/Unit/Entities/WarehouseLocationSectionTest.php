<?php

namespace Modules\Warehouse\Tests\Unit\Entities;

use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseFloor;
use Modules\Warehouse\Entities\WarehouseLocation;
use Modules\Warehouse\Entities\WarehouseLocationSection;
use Modules\Warehouse\Entities\WarehouseLocationStyle;
use Modules\Warehouse\Entities\WarehouseInventorySlot;
use Tests\TestCase;
use Illuminate\Support\Str;

class WarehouseLocationSectionTest extends TestCase
{
    /**
     * Test: WarehouseLocationSection has correct fillable attributes
     */
    public function test_section_has_correct_fillable_attributes(): void
    {
        $section = new WarehouseLocationSection();

        $expectedFillable = [
            'uid',
            'location_id',
            'code',
            'barcode',
            'level',
            'face',
            'weight_max',
            'max_quantity',
            'available',
            'notes',
        ];

        $this->assertEquals($expectedFillable, $section->getFillable());
    }

    /**
     * Test: WarehouseLocationSection has correct casts
     */
    public function test_section_has_correct_casts(): void
    {
        $section = new WarehouseLocationSection();
        $casts = $section->getCasts();

        $this->assertEquals('boolean', $casts['available']);
        $this->assertEquals('decimal:2', $casts['weight_max']);
        $this->assertEquals('integer', $casts['max_quantity']);
        $this->assertEquals('integer', $casts['level']);
    }

    /**
     * Test: WarehouseLocationSection belongsTo location
     */
    public function test_section_belongs_to_location(): void
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

        $this->assertInstanceOf(WarehouseLocation::class, $section->location);
        $this->assertEquals($location->id, $section->location->id);
    }

    /**
     * Test: WarehouseLocationSection hasMany slots
     */
    public function test_section_has_many_slots(): void
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

        $slots = $section->slots()->get();

        $this->assertCount(2, $slots);
        $this->assertTrue($slots->pluck('id')->contains($slot1->id));
        $this->assertTrue($slots->pluck('id')->contains($slot2->id));
    }

    /**
     * Test: getTotalSlots returns correct count
     */
    public function test_get_total_slots_returns_correct_count(): void
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

        WarehouseInventorySlot::factory(5)->create(['section_id' => $section->id]);

        $this->assertEquals(5, $section->getTotalSlots());
    }

    /**
     * Test: getTotalSlots returns zero when no slots
     */
    public function test_get_total_slots_returns_zero_when_no_slots(): void
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

        $this->assertEquals(0, $section->getTotalSlots());
    }

    /**
     * Test: getOccupiedSlots returns count of occupied slots
     */
    public function test_get_occupied_slots_returns_occupied_count(): void
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

        WarehouseInventorySlot::factory(3)->create(['section_id' => $section->id, 'quantity' => 10]);
        WarehouseInventorySlot::factory(2)->create(['section_id' => $section->id, 'quantity' => 0]);

        $this->assertEquals(3, $section->getOccupiedSlots());
    }

    /**
     * Test: getAvailableSlots returns count of available slots
     */
    public function test_get_available_slots_returns_available_count(): void
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

        WarehouseInventorySlot::factory(3)->create(['section_id' => $section->id, 'quantity' => 10]);
        WarehouseInventorySlot::factory(2)->create(['section_id' => $section->id, 'quantity' => 0]);

        $this->assertEquals(2, $section->getAvailableSlots());
    }

    /**
     * Test: getOccupancyPercentage calculation
     */
    public function test_get_occupancy_percentage(): void
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

        WarehouseInventorySlot::factory(5)->create(['section_id' => $section->id, 'quantity' => 10]);
        WarehouseInventorySlot::factory(5)->create(['section_id' => $section->id, 'quantity' => 0]);

        // 5 occupied out of 10 = 50%
        $this->assertEquals(50.0, $section->getOccupancyPercentage());
    }

    /**
     * Test: getOccupancyPercentage returns zero when section has no slots
     */
    public function test_get_occupancy_percentage_returns_zero_when_no_slots(): void
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

        $this->assertEquals(0, $section->getOccupancyPercentage());
    }

    /**
     * Test: getTotalQuantity returns sum of quantities
     */
    public function test_get_total_quantity(): void
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

        WarehouseInventorySlot::factory()->create(['section_id' => $section->id, 'quantity' => 10]);
        WarehouseInventorySlot::factory()->create(['section_id' => $section->id, 'quantity' => 20]);
        WarehouseInventorySlot::factory()->create(['section_id' => $section->id, 'quantity' => 15]);

        $this->assertEquals(45, $section->getTotalQuantity());
    }

    /**
     * Test: getTotalQuantity returns zero when no slots
     */
    public function test_get_total_quantity_returns_zero_when_no_slots(): void
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

        $this->assertEquals(0, $section->getTotalQuantity());
    }

    /**
     * Test: isNearCapacity returns true when near max_quantity
     */
    public function test_is_near_capacity_returns_true_when_near_max(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create();
        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
        ]);
        $section = WarehouseLocationSection::factory()->create([
            'location_id' => $location->id,
            'max_quantity' => 100,
        ]);

        WarehouseInventorySlot::factory()->create(['section_id' => $section->id, 'quantity' => 86]);

        // 86 >= (100 * 0.85) = true
        $this->assertTrue($section->isNearCapacity());
    }

    /**
     * Test: isNearCapacity returns false when under capacity
     */
    public function test_is_near_capacity_returns_false_when_under_capacity(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create();
        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
        ]);
        $section = WarehouseLocationSection::factory()->create([
            'location_id' => $location->id,
            'max_quantity' => 100,
        ]);

        WarehouseInventorySlot::factory()->create(['section_id' => $section->id, 'quantity' => 50]);

        $this->assertFalse($section->isNearCapacity());
    }

    /**
     * Test: isNearCapacity returns false when max_quantity not set
     */
    public function test_is_near_capacity_returns_false_when_no_max_quantity(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create();
        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
        ]);
        $section = WarehouseLocationSection::factory()->create([
            'location_id' => $location->id,
            'max_quantity' => null,
        ]);

        $this->assertFalse($section->isNearCapacity());
    }

    /**
     * Test: uid scope returns single section by UUID
     */
    public function test_uid_scope_returns_single_section(): void
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

        $result = WarehouseLocationSection::uid($section->uid);

        $this->assertInstanceOf(WarehouseLocationSection::class, $result);
        $this->assertEquals($section->uid, $result->uid);
    }

    /**
     * Test: barcode scope returns section by barcode
     */
    public function test_barcode_scope_returns_section(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create();
        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
        ]);
        $barcode = '123456789';
        $section = WarehouseLocationSection::factory()->create([
            'location_id' => $location->id,
            'barcode' => $barcode,
        ]);

        $result = WarehouseLocationSection::barcode($barcode);

        $this->assertInstanceOf(WarehouseLocationSection::class, $result);
        $this->assertEquals($barcode, $result->barcode);
    }

    /**
     * Test: available scope returns only available sections
     */
    public function test_available_scope_returns_available_sections(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);
        $style = WarehouseLocationStyle::factory()->create();
        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
        ]);

        WarehouseLocationSection::factory(3)->create(['location_id' => $location->id, 'available' => true]);
        WarehouseLocationSection::factory(2)->create(['location_id' => $location->id, 'available' => false]);

        $available = WarehouseLocationSection::available()->get();

        $this->assertCount(3, $available);
        $available->each(fn ($section) => $this->assertTrue($section->available));
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
        $section = WarehouseLocationSection::factory()->create(['location_id' => $location->id]);

        $summary = $section->getSummary();

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('id', $summary);
        $this->assertArrayHasKey('uid', $summary);
        $this->assertArrayHasKey('code', $summary);
        $this->assertArrayHasKey('level', $summary);
        $this->assertArrayHasKey('total_slots', $summary);
        $this->assertArrayHasKey('occupied_slots', $summary);
        $this->assertArrayHasKey('available_slots', $summary);
        $this->assertArrayHasKey('occupancy_percentage', $summary);
        $this->assertArrayHasKey('total_quantity', $summary);
    }

    /**
     * Test: getFullInfo returns complete information
     */
    public function test_get_full_info_returns_complete_information(): void
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

        $fullInfo = $section->getFullInfo();

        $this->assertIsArray($fullInfo);
        $this->assertArrayHasKey('location_id', $fullInfo);
        $this->assertArrayHasKey('location_code', $fullInfo);
        $this->assertArrayHasKey('notes', $fullInfo);
        $this->assertArrayHasKey('created_at', $fullInfo);
        $this->assertArrayHasKey('updated_at', $fullInfo);
    }

    /**
     * Test: WarehouseLocationSection auto-generates UUID on creation
     */
    public function test_section_auto_generates_uuid(): void
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

        $this->assertNotNull($section->uid);
        $this->assertTrue(Str::isUuid($section->uid));
    }

    /**
     * Test: WarehouseLocationSection table configuration
     */
    public function test_section_table_configuration(): void
    {
        $section = new WarehouseLocationSection();

        $this->assertEquals('warehouse_location_sections', $section->getTable());
    }
}
