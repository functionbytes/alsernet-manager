<?php

namespace Modules\Warehouse\Tests\Unit\Entities;

use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseFloor;
use Modules\Warehouse\Entities\WarehouseInventorySlot;
use Modules\Warehouse\Entities\WarehouseLocation;
use Modules\Warehouse\Entities\WarehouseLocationSection;
use Modules\Warehouse\Entities\WarehouseLocationStyle;
use Modules\Warehouse\Models\Product\Product;
use Tests\TestCase;

class WarehouseInventorySlotTest extends TestCase
{
    /**
     * Test: WarehouseInventorySlot has correct fillable attributes
     */
    public function test_slot_has_correct_fillable_attributes(): void
    {
        $slot = new WarehouseInventorySlot();

        $expectedFillable = [
            'uid',
            'section_id',
            'product_id',
            'quantity',
            'kardex',
            'is_occupied',
            'last_movement',
            'last_section_id',
        ];

        $this->assertEquals($expectedFillable, $slot->getFillable());
    }

    /**
     * Test: WarehouseInventorySlot has correct casts
     */
    public function test_slot_has_correct_casts(): void
    {
        $slot = new WarehouseInventorySlot();
        $casts = $slot->getCasts();

        $this->assertEquals('integer', $casts['quantity']);
        $this->assertEquals('integer', $casts['kardex']);
        $this->assertEquals('boolean', $casts['is_occupied']);
        $this->assertEquals('datetime', $casts['last_movement']);
    }

    /**
     * Test: WarehouseInventorySlot belongsTo section
     */
    public function test_slot_belongs_to_section(): void
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
        $slot = WarehouseInventorySlot::factory()->create(['section_id' => $section->id]);

        $this->assertInstanceOf(WarehouseLocationSection::class, $slot->section);
        $this->assertEquals($section->id, $slot->section->id);
    }

    /**
     * Test: WarehouseInventorySlot hasOneThrough location
     */
    public function test_slot_has_one_through_location(): void
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
        $slot = WarehouseInventorySlot::factory()->create(['section_id' => $section->id]);

        $this->assertInstanceOf(WarehouseLocation::class, $slot->location);
        $this->assertEquals($location->id, $slot->location->id);
    }

    /**
     * Test: WarehouseInventorySlot belongsTo product
     */
    public function test_slot_belongs_to_product(): void
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
        $product = Product::factory()->create();
        $slot = WarehouseInventorySlot::factory()->create([
            'section_id' => $section->id,
            'product_id' => $product->id,
        ]);

        $this->assertInstanceOf(Product::class, $slot->product);
        $this->assertEquals($product->id, $slot->product->id);
    }

    /**
     * Test: WarehouseInventorySlot belongsTo lastSection
     */
    public function test_slot_belongs_to_last_section(): void
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
        $lastSection = WarehouseLocationSection::factory()->create(['location_id' => $location->id]);
        $slot = WarehouseInventorySlot::factory()->create([
            'section_id' => $section->id,
            'last_section_id' => $lastSection->id,
        ]);

        $this->assertInstanceOf(WarehouseLocationSection::class, $slot->lastSection);
        $this->assertEquals($lastSection->id, $slot->lastSection->id);
    }

    /**
     * Test: occupied scope returns slots with quantity > 0
     */
    public function test_occupied_scope_returns_occupied_slots(): void
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
        WarehouseInventorySlot::factory(3)->create(['section_id' => $section->id, 'quantity' => 0]);

        $occupied = WarehouseInventorySlot::occupied()->get();

        $this->assertCount(5, $occupied);
        $occupied->each(fn ($slot) => $this->assertGreaterThan(0, $slot->quantity));
    }

    /**
     * Test: available scope returns slots with quantity = 0
     */
    public function test_available_scope_returns_empty_slots(): void
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
        WarehouseInventorySlot::factory(3)->create(['section_id' => $section->id, 'quantity' => 0]);

        $available = WarehouseInventorySlot::available()->get();

        $this->assertCount(3, $available);
        $available->each(fn ($slot) => $this->assertEquals(0, $slot->quantity));
    }

    /**
     * Test: byProduct scope filters by product ID
     */
    public function test_by_product_scope_filters_correctly(): void
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
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        WarehouseInventorySlot::factory(3)->create(['section_id' => $section->id, 'product_id' => $product1->id]);
        WarehouseInventorySlot::factory(2)->create(['section_id' => $section->id, 'product_id' => $product2->id]);

        $slots = WarehouseInventorySlot::byProduct($product1->id)->get();

        $this->assertCount(3, $slots);
        $slots->each(fn ($slot) => $this->assertEquals($product1->id, $slot->product_id));
    }

    /**
     * Test: search scope searches by uid
     */
    public function test_search_scope_searches_by_uid(): void
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
        $searchUid = 'search-uid-test-123';
        $slot = WarehouseInventorySlot::factory()->create([
            'section_id' => $section->id,
            'uid' => $searchUid,
        ]);

        $results = WarehouseInventorySlot::search('search-uid')->get();

        $this->assertCount(1, $results);
        $this->assertEquals($slot->id, $results->first()->id);
    }

    /**
     * Test: search scope searches by product name
     */
    public function test_search_scope_searches_by_product_name(): void
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
        $product = Product::factory()->create(['name' => 'Test Product Name']);
        $slot = WarehouseInventorySlot::factory()->create([
            'section_id' => $section->id,
            'product_id' => $product->id,
        ]);

        $results = WarehouseInventorySlot::search('Test Product')->get();

        $this->assertCount(1, $results);
        $this->assertEquals($slot->id, $results->first()->id);
    }

    /**
     * Test: lowStock scope returns slots below threshold
     */
    public function test_low_stock_scope_returns_slots_below_threshold(): void
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

        WarehouseInventorySlot::factory(3)->create(['section_id' => $section->id, 'quantity' => 5]);
        WarehouseInventorySlot::factory(2)->create(['section_id' => $section->id, 'quantity' => 15]);
        WarehouseInventorySlot::factory(1)->create(['section_id' => $section->id, 'quantity' => 0]);

        $lowStock = WarehouseInventorySlot::lowStock(10)->get();

        $this->assertCount(3, $lowStock);
    }

    /**
     * Test: isOccupied returns true when quantity > 0
     */
    public function test_is_occupied_returns_true_when_quantity_greater_than_zero(): void
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
        $slot = WarehouseInventorySlot::factory()->create(['section_id' => $section->id, 'quantity' => 10]);

        $this->assertTrue($slot->isOccupied());
    }

    /**
     * Test: isOccupied returns false when quantity = 0
     */
    public function test_is_occupied_returns_false_when_quantity_is_zero(): void
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
        $slot = WarehouseInventorySlot::factory()->create(['section_id' => $section->id, 'quantity' => 0]);

        $this->assertFalse($slot->isOccupied());
    }

    /**
     * Test: isAvailable returns true when quantity = 0
     */
    public function test_is_available_returns_true_when_quantity_is_zero(): void
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
        $slot = WarehouseInventorySlot::factory()->create(['section_id' => $section->id, 'quantity' => 0]);

        $this->assertTrue($slot->isAvailable());
    }

    /**
     * Test: isAvailable returns false when quantity > 0
     */
    public function test_is_available_returns_false_when_quantity_greater_than_zero(): void
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
        $slot = WarehouseInventorySlot::factory()->create(['section_id' => $section->id, 'quantity' => 10]);

        $this->assertFalse($slot->isAvailable());
    }

    /**
     * Test: Empty slot with quantity 0 is valid state
     */
    public function test_empty_slot_with_zero_quantity_is_valid(): void
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
        $slot = WarehouseInventorySlot::factory()->create([
            'section_id' => $section->id,
            'quantity' => 0,
            'product_id' => null,
        ]);

        $this->assertEquals(0, $slot->quantity);
        $this->assertNull($slot->product_id);
        $this->assertTrue($slot->isAvailable());
    }

    /**
     * Test: getAddress returns formatted address string
     */
    public function test_get_address_returns_formatted_string(): void
    {
        $warehouse = Warehouse::factory()->create(['name' => 'Main Warehouse']);
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id, 'name' => 'Floor 1']);
        $style = WarehouseLocationStyle::factory()->create();
        $location = WarehouseLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
            'code' => 'LOC-001',
        ]);
        $section = WarehouseLocationSection::factory()->create([
            'location_id' => $location->id,
            'code' => 'SEC-001',
            'level' => 2,
        ]);
        $slot = WarehouseInventorySlot::factory()->create(['section_id' => $section->id]);

        $address = $slot->getAddress();

        $this->assertStringContainsString('Main Warehouse', $address);
        $this->assertStringContainsString('Floor 1', $address);
        $this->assertStringContainsString('LOC-001', $address);
        $this->assertStringContainsString('SEC-001', $address);
    }

    /**
     * Test: getLocation returns location via section
     */
    public function test_get_location_returns_location(): void
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
        $slot = WarehouseInventorySlot::factory()->create(['section_id' => $section->id]);

        $slotLocation = $slot->getLocation();

        $this->assertInstanceOf(WarehouseLocation::class, $slotLocation);
        $this->assertEquals($location->id, $slotLocation->id);
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
        $product = Product::factory()->create();
        $slot = WarehouseInventorySlot::factory()->create([
            'section_id' => $section->id,
            'product_id' => $product->id,
            'quantity' => 50,
        ]);

        $summary = $slot->getSummary();

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('id', $summary);
        $this->assertArrayHasKey('uid', $summary);
        $this->assertArrayHasKey('section', $summary);
        $this->assertArrayHasKey('product', $summary);
        $this->assertArrayHasKey('quantity', $summary);
        $this->assertArrayHasKey('is_occupied', $summary);
        $this->assertArrayHasKey('address', $summary);
    }

    /**
     * Test: WarehouseInventorySlot table configuration
     */
    public function test_slot_table_configuration(): void
    {
        $slot = new WarehouseInventorySlot();

        $this->assertEquals('warehouse_inventory_slots', $slot->getTable());
        $this->assertEquals('id', $slot->getKeyName());
        $this->assertEquals('int', $slot->getKeyType());
        $this->assertTrue($slot->getIncrementing());
    }
}
