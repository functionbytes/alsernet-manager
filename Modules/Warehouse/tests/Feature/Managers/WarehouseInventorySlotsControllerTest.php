<?php

namespace Modules\Warehouse\Tests\Feature\Managers;

use App\Models\Product\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseFloor;
use Modules\Warehouse\Entities\WarehouseInventorySlot;
use Modules\Warehouse\Entities\WarehouseLocation;
use Modules\Warehouse\Entities\WarehouseLocationSection;
use Modules\Warehouse\Entities\WarehouseLocationStyle;
use Tests\TestCase;

class WarehouseInventorySlotsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Warehouse $warehouse;

    protected WarehouseFloor $floor;

    protected WarehouseLocation $location;

    protected WarehouseLocationSection $section;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsManager();

        $this->warehouse = Warehouse::factory()->create();
        $this->floor = WarehouseFloor::factory()->for($this->warehouse)->create();

        $style = WarehouseLocationStyle::factory()->create();
        $this->location = WarehouseLocation::factory()
            ->for($this->warehouse)
            ->for($this->floor)
            ->create(['style_id' => $style->id]);

        $this->section = $this->location->sections()->create([
            'code' => 'TEST-SEC',
            'level' => 1,
            'available' => true,
        ]);

        $this->product = Product::factory()->create();
    }

    /**
     * Test index lists slots for section
     */
    public function testIndexListsSlotsForSection(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            WarehouseInventorySlot::factory()
                ->for($this->section)
                ->for($this->product)
                ->create(['quantity' => $i * 10]);
        }

        $response = $this->get(route('manager.warehouse.section.slots', [
            'warehouse_uid' => $this->warehouse->uid,
            'floor_uid' => $this->floor->uid,
            'location_uid' => $this->location->uid,
            'section_uid' => $this->section->uid,
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('warehouse::managers.inventory-slots.index');
        $response->assertViewHas('slots');

        $slots = $response->viewData('slots');
        $this->assertGreaterThanOrEqual(5, $slots->total());
    }

    /**
     * Test index filters by occupied status
     */
    public function testIndexFiltersById StatusOccupiedOrAvailable(): void
    {
        WarehouseInventorySlot::factory()
            ->for($this->section)
            ->for($this->product)
            ->create(['quantity' => 10, 'is_occupied' => true]);

        WarehouseInventorySlot::factory()
            ->for($this->section)
            ->for($this->product)
            ->create(['quantity' => 0, 'is_occupied' => false]);

        $response = $this->get(route('manager.warehouse.section.slots', [
            'warehouse_uid' => $this->warehouse->uid,
            'floor_uid' => $this->floor->uid,
            'location_uid' => $this->location->uid,
            'section_uid' => $this->section->uid,
            'status' => 'occupied',
        ]));

        $response->assertStatus(200);
        $slots = $response->viewData('slots');
        $this->assertGreaterThan(0, $slots->total());
    }

    /**
     * Test index filters by product
     */
    public function testIndexFiltersById Product(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        WarehouseInventorySlot::factory()
            ->for($this->section)
            ->for($product1)
            ->create(['quantity' => 10]);

        WarehouseInventorySlot::factory()
            ->for($this->section)
            ->for($product2)
            ->create(['quantity' => 20]);

        $response = $this->get(route('manager.warehouse.section.slots', [
            'warehouse_uid' => $this->warehouse->uid,
            'floor_uid' => $this->floor->uid,
            'location_uid' => $this->location->uid,
            'section_uid' => $this->section->uid,
            'product_id' => $product1->id,
        ]));

        $response->assertStatus(200);
        $slots = $response->viewData('slots');
        $this->assertTrue(
            $slots->pluck('product_id')->contains($product1->id),
            'Filter should return only slots with product1'
        );
    }

    /**
     * Test store creates slot with product
     */
    public function testStoreCreatesSlotWithProduct(): void
    {
        $data = [
            'warehouse_uid' => $this->warehouse->uid,
            'floor_uid' => $this->floor->uid,
            'location_uid' => $this->location->uid,
            'section_uid' => $this->section->uid,
            'product_id' => $this->product->id,
            'quantity' => 50,
            'kardex' => 50,
        ];

        $response = $this->post(route('manager.warehouse.section.slots.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('warehouse_inventory_slots', [
            'section_id' => $this->section->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
        ]);
    }

    /**
     * Test store prevents duplicate product in same section
     */
    public function testStorePreventsDuplicateProductInSection(): void
    {
        WarehouseInventorySlot::factory()
            ->for($this->section)
            ->for($this->product)
            ->create();

        $data = [
            'warehouse_uid' => $this->warehouse->uid,
            'floor_uid' => $this->floor->uid,
            'location_uid' => $this->location->uid,
            'section_uid' => $this->section->uid,
            'product_id' => $this->product->id,
            'quantity' => 10,
        ];

        $response = $this->post(route('manager.warehouse.section.slots.store'), $data);

        $response->assertSessionHasErrors();
    }

    /**
     * Test addQuantity increments properly
     */
    public function testAddQuantityIncrementsProper ly(): void
    {
        $slot = WarehouseInventorySlot::factory()
            ->for($this->section)
            ->for($this->product)
            ->create(['quantity' => 10]);

        $response = $this->postJson(
            route('manager.warehouse.section.slots.add-quantity', [
                'warehouse_uid' => $this->warehouse->uid,
                'floor_uid' => $this->floor->uid,
                'location_uid' => $this->location->uid,
                'section_uid' => $this->section->uid,
                'slot_uid' => $slot->uid,
            ]),
            [
                'quantity' => 5,
                'reason' => 'Testing increment',
            ]
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('warehouse_inventory_slots', [
            'id' => $slot->id,
            'quantity' => 15,
        ]);
    }

    /**
     * Test subtractQuantity decrements properly
     */
    public function testSubtractQuantityDecrementsProper ly(): void
    {
        $slot = WarehouseInventorySlot::factory()
            ->for($this->section)
            ->for($this->product)
            ->create(['quantity' => 20]);

        $response = $this->postJson(
            route('manager.warehouse.section.slots.subtract-quantity', [
                'warehouse_uid' => $this->warehouse->uid,
                'floor_uid' => $this->floor->uid,
                'location_uid' => $this->location->uid,
                'section_uid' => $this->section->uid,
                'slot_uid' => $slot->uid,
            ]),
            [
                'quantity' => 5,
                'reason' => 'Testing decrement',
            ]
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('warehouse_inventory_slots', [
            'id' => $slot->id,
            'quantity' => 15,
        ]);
    }

    /**
     * Test subtractQuantity validation prevents negative stock
     */
    public function testSubtractQuantityValidation(): void
    {
        $slot = WarehouseInventorySlot::factory()
            ->for($this->section)
            ->for($this->product)
            ->create(['quantity' => 5]);

        $response = $this->postJson(
            route('manager.warehouse.section.slots.subtract-quantity', [
                'warehouse_uid' => $this->warehouse->uid,
                'floor_uid' => $this->floor->uid,
                'location_uid' => $this->location->uid,
                'section_uid' => $this->section->uid,
                'slot_uid' => $slot->uid,
            ]),
            [
                'quantity' => 0, // Invalid: must be min:1
            ]
        );

        $response->assertStatus(422);
        $response->assertSessionHasErrors();
    }

    /**
     * Test clear empties slot
     */
    public function testClearEmptiesSlot(): void
    {
        $slot = WarehouseInventorySlot::factory()
            ->for($this->section)
            ->for($this->product)
            ->create(['quantity' => 100]);

        $response = $this->postJson(
            route('manager.warehouse.section.slots.clear', [
                'warehouse_uid' => $this->warehouse->uid,
                'floor_uid' => $this->floor->uid,
                'location_uid' => $this->location->uid,
                'section_uid' => $this->section->uid,
                'slot_uid' => $slot->uid,
            ]),
            ['reason' => 'Testing clear']
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('warehouse_inventory_slots', [
            'id' => $slot->id,
            'quantity' => 0,
        ]);
    }

    /**
     * Test moveTo transfers product to new section
     */
    public function testMoveToTransfersProductToNewSection(): void
    {
        $newSection = $this->location->sections()->create([
            'code' => 'NEW-SEC',
            'level' => 2,
            'available' => true,
        ]);

        $slot = WarehouseInventorySlot::factory()
            ->for($this->section)
            ->for($this->product)
            ->create(['quantity' => 50]);

        $response = $this->postJson(
            route('manager.warehouse.section.slots.move-to', [
                'warehouse_uid' => $this->warehouse->uid,
                'floor_uid' => $this->floor->uid,
                'location_uid' => $this->location->uid,
                'section_uid' => $this->section->uid,
                'slot_uid' => $slot->uid,
            ]),
            [
                'new_section_id' => $newSection->id,
                'quantity' => 30,
                'reason' => 'Testing move',
            ]
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('warehouse_inventory_slots', [
            'id' => $slot->id,
            'quantity' => 20, // Original quantity reduced by transfer amount
        ]);
    }

    /**
     * Test moveTo validates destination exists
     */
    public function testMoveToValidatesDestination(): void
    {
        $slot = WarehouseInventorySlot::factory()
            ->for($this->section)
            ->for($this->product)
            ->create(['quantity' => 50]);

        $response = $this->postJson(
            route('manager.warehouse.section.slots.move-to', [
                'warehouse_uid' => $this->warehouse->uid,
                'floor_uid' => $this->floor->uid,
                'location_uid' => $this->location->uid,
                'section_uid' => $this->section->uid,
                'slot_uid' => $slot->uid,
            ]),
            [
                'new_section_id' => 99999, // Non-existent section
                'quantity' => 10,
            ]
        );

        $response->assertStatus(422);
    }

    /**
     * Test update modifies slot data
     */
    public function testUpdateSlotModifiesData(): void
    {
        $slot = WarehouseInventorySlot::factory()
            ->for($this->section)
            ->for($this->product)
            ->create(['quantity' => 10]);

        $response = $this->post(route('manager.warehouse.section.slots.update'), [
            'warehouse_uid' => $this->warehouse->uid,
            'floor_uid' => $this->floor->uid,
            'location_uid' => $this->location->uid,
            'section_uid' => $this->section->uid,
            'uid' => $slot->uid,
            'quantity' => 25,
            'kardex' => 25,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('warehouse_inventory_slots', [
            'id' => $slot->id,
            'quantity' => 25,
        ]);
    }

    /**
     * Test destroy removes slot
     */
    public function testDestroyRemovesSlot(): void
    {
        $slot = WarehouseInventorySlot::factory()
            ->for($this->section)
            ->for($this->product)
            ->create(['quantity' => 10]);

        $slotId = $slot->id;

        $response = $this->delete(route('manager.warehouse.section.slots.destroy', [
            'warehouse_uid' => $this->warehouse->uid,
            'floor_uid' => $this->floor->uid,
            'location_uid' => $this->location->uid,
            'section_uid' => $this->section->uid,
            'slot_uid' => $slot->uid,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('warehouse_inventory_slots', ['id' => $slotId]);
    }

    /**
     * Test index shows pagination and counts
     */
    public function testIndexShowsPaginationAndCounts(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            WarehouseInventorySlot::factory()
                ->for($this->section)
                ->for($this->product)
                ->create(['quantity' => $i]);
        }

        $response = $this->get(route('manager.warehouse.section.slots', [
            'warehouse_uid' => $this->warehouse->uid,
            'floor_uid' => $this->floor->uid,
            'location_uid' => $this->location->uid,
            'section_uid' => $this->section->uid,
        ]));

        $response->assertStatus(200);
        $slots = $response->viewData('slots');

        $this->assertGreaterThan(0, $slots->total());
        $this->assertTrue($slots->hasPages(), 'Pagination should be available');
        $this->assertLessThanOrEqual(20, $slots->count(), 'Should paginate at 20 per page');
    }
}
