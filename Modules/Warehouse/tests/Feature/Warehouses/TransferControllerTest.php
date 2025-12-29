<?php

namespace Modules\Warehouse\Tests\Feature\Warehouses;

use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseFloor;
use Modules\Warehouse\Entities\WarehouseInventoryMovement;
use Modules\Warehouse\Entities\WarehouseInventorySlot;
use Modules\Warehouse\Entities\WarehouseLocation;
use Modules\Warehouse\Entities\WarehouseLocationSection;
use Tests\TestCase;

class TransferControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Warehouse $warehouse;

    private WarehouseLocation $location;

    private WarehouseLocationSection $fromSection;

    private WarehouseLocationSection $toSection;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user with warehouse.transfer and warehouse.inventory permissions
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['warehouse.transfer', 'warehouse.inventory']);

        // Create warehouse structure
        $this->warehouse = Warehouse::factory()->create(['name' => 'Main Warehouse']);
        $floor = WarehouseFloor::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'name' => 'Ground Floor',
        ]);

        $this->location = WarehouseLocation::factory()->create([
            'floor_id' => $floor->id,
            'code' => 'LOC-001',
        ]);

        $this->fromSection = WarehouseLocationSection::factory()->create([
            'location_id' => $this->location->id,
            'code' => 'SEC-A01',
            'level' => 1,
            'face' => 'North',
            'max_quantity' => 100,
            'available' => true,
        ]);

        $this->toSection = WarehouseLocationSection::factory()->create([
            'location_id' => $this->location->id,
            'code' => 'SEC-A02',
            'level' => 1,
            'face' => 'South',
            'max_quantity' => 100,
            'available' => true,
        ]);

        // Create product with stock
        $this->product = Product::factory()->create([
            'barcode' => 'PROD-12345',
            'reference' => 'REF-001',
            'title' => 'Test Product',
        ]);

        // Create inventory slot with product in from section
        WarehouseInventorySlot::factory()->create([
            'section_id' => $this->fromSection->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
            'is_occupied' => true,
        ]);
    }

    /**
     * Test that the transfer index page displays correctly
     */
    public function test_index_returns_transfer_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('warehouse.inventories.transfer.index'));

        $response->assertStatus(200);
        $response->assertViewIs('warehouse::warehouse.transfers.index');
        $response->assertViewHas('warehouses');

        $warehouses = $response->viewData('warehouses');
        $this->assertTrue($warehouses->count() > 0);
    }

    /**
     * Test searching for product by barcode
     */
    public function test_search_product_finds_by_barcode(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.inventories.transfer.search'), [
                'search' => 'PROD-12345',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('product.barcode', 'PROD-12345');
        $response->assertJsonPath('product.id', $this->product->id);
        $response->assertJsonStructure([
            'success',
            'product' => ['id', 'uid', 'title', 'reference', 'barcode'],
            'locations' => [
                '*' => [
                    'location_id',
                    'location_code',
                    'warehouse_id',
                    'warehouse_name',
                    'sections' => [
                        '*' => [
                            'section_id',
                            'section_code',
                            'section_level',
                            'section_face',
                            'quantity',
                            'uid',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Test searching for product by reference
     */
    public function test_search_product_finds_by_reference(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.inventories.transfer.search'), [
                'search' => 'REF-001',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('product.reference', 'REF-001');
    }

    /**
     * Test searching returns 404 when product not found
     */
    public function test_search_product_returns_not_found_for_missing(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.inventories.transfer.search'), [
                'search' => 'NON-EXISTENT-PRODUCT',
            ]);

        $response->assertStatus(404);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'Producto no encontrado');
    }

    /**
     * Test searching returns error when product has no stock
     */
    public function test_search_product_no_stock(): void
    {
        // Create product without stock
        $product = Product::factory()->create([
            'barcode' => 'EMPTY-PROD',
            'reference' => 'EMPTY-REF',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.inventories.transfer.search'), [
                'search' => 'EMPTY-PROD',
            ]);

        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'El producto no tiene stock en ninguna sección');
    }

    /**
     * Test get available sections returns sections with stock info
     */
    public function test_get_available_sections_returns_with_stock(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.inventories.transfer.available-sections'), [
                'location_id' => $this->location->id,
                'exclude_section_id' => $this->fromSection->id,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'success',
            'sections' => [
                '*' => [
                    'id',
                    'code',
                    'level',
                    'face',
                    'total_quantity',
                    'max_quantity',
                    'available_slots',
                ],
            ],
        ]);

        $sections = $response->json('sections');
        $this->assertTrue(count($sections) >= 1);
    }

    /**
     * Test transfer moves product between locations
     */
    public function test_transfer_moves_product_between_locations(): void
    {
        $fromSlot = WarehouseInventorySlot::where('section_id', $this->fromSection->id)
            ->where('product_id', $this->product->id)
            ->first();

        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.inventories.transfer.process'), [
                'product_id' => $this->product->id,
                'from_section_id' => $this->fromSection->id,
                'to_section_id' => $this->toSection->id,
                'quantity' => 20,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('transfer_info.quantity', 20);

        // Verify quantities were updated
        $fromSlot->refresh();
        $this->assertEquals(30, $fromSlot->quantity);

        $toSlot = WarehouseInventorySlot::where('section_id', $this->toSection->id)
            ->where('product_id', $this->product->id)
            ->first();

        $this->assertNotNull($toSlot);
        $this->assertEquals(20, $toSlot->quantity);
    }

    /**
     * Test transfer validates quantity available
     */
    public function test_transfer_validates_quantity_available(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.inventories.transfer.process'), [
                'product_id' => $this->product->id,
                'from_section_id' => $this->fromSection->id,
                'to_section_id' => $this->toSection->id,
                'quantity' => 100, // More than available (50)
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'No hay suficiente cantidad. Disponible: 50');
    }

    /**
     * Test transfer logs movement to kardex
     */
    public function test_transfer_logs_movement(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.inventories.transfer.process'), [
                'product_id' => $this->product->id,
                'from_section_id' => $this->fromSection->id,
                'to_section_id' => $this->toSection->id,
                'quantity' => 15,
            ]);

        $response->assertStatus(200);

        // Verify movements were recorded
        $movements = WarehouseInventoryMovement::where('product_id', $this->product->id)
            ->where('movement_type', 'subtract')
            ->get();

        $this->assertGreaterThan(0, $movements->count());
    }

    /**
     * Test transfer requires warehouse.transfer permission
     */
    public function test_transfer_requires_warehouse_transfer_permission(): void
    {
        $userWithoutPermission = User::factory()->create();

        $response = $this->actingAs($userWithoutPermission)
            ->postJson(route('warehouse.inventories.transfer.process'), [
                'product_id' => $this->product->id,
                'from_section_id' => $this->fromSection->id,
                'to_section_id' => $this->toSection->id,
                'quantity' => 10,
            ]);

        // Should be 403 Forbidden or 302 redirect depending on middleware
        $this->assertIn($response->getStatusCode(), [302, 403]);
    }

    /**
     * Test transfer must have same warehouse
     */
    public function test_transfer_must_have_same_warehouse(): void
    {
        // Create another warehouse and location
        $warehouse2 = Warehouse::factory()->create(['name' => 'Second Warehouse']);
        $floor2 = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse2->id]);
        $location2 = WarehouseLocation::factory()->create(['floor_id' => $floor2->id]);
        $section2 = WarehouseLocationSection::factory()->create([
            'location_id' => $location2->id,
            'code' => 'SEC-B01',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.inventories.transfer.process'), [
                'product_id' => $this->product->id,
                'from_section_id' => $this->fromSection->id,
                'to_section_id' => $section2->id,
                'quantity' => 10,
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'Las secciones deben estar en la misma estantería');
    }

    /**
     * Test transfer history shows previous transfers
     */
    public function test_history_shows_previous_transfers(): void
    {
        // Create a transfer
        $this->actingAs($this->user)
            ->postJson(route('warehouse.inventories.transfer.process'), [
                'product_id' => $this->product->id,
                'from_section_id' => $this->fromSection->id,
                'to_section_id' => $this->toSection->id,
                'quantity' => 10,
            ]);

        // Get history
        $response = $this->actingAs($this->user)
            ->getJson(route('warehouse.inventories.transfer.history'), [
                'product_id' => $this->product->id,
                'days' => 30,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'success',
            'movements' => [
                'data' => [
                    '*' => ['id', 'product_id', 'movement_type'],
                ],
                'links',
                'meta',
            ],
        ]);
    }

    /**
     * Test transfer with insufficient destination capacity
     */
    public function test_transfer_validates_destination_capacity(): void
    {
        // Create section with low max_quantity
        $smallSection = WarehouseLocationSection::factory()->create([
            'location_id' => $this->location->id,
            'code' => 'SEC-SMALL',
            'max_quantity' => 10,
            'available' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.inventories.transfer.process'), [
                'product_id' => $this->product->id,
                'from_section_id' => $this->fromSection->id,
                'to_section_id' => $smallSection->id,
                'quantity' => 20, // Exceeds capacity of 10
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'La sección destino no tiene capacidad. Máximo: 10, Actual: 0');
    }

    /**
     * Test transfer cannot use same section as source and destination
     */
    public function test_transfer_cannot_use_same_section(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.inventories.transfer.process'), [
                'product_id' => $this->product->id,
                'from_section_id' => $this->fromSection->id,
                'to_section_id' => $this->fromSection->id,
                'quantity' => 10,
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'Las secciones origen y destino no pueden ser iguales');
    }
}
