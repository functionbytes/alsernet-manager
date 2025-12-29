<?php

namespace Modules\Warehouse\Tests\Feature\Warehouses;

use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseFloor;
use Modules\Warehouse\Entities\WarehouseInventorySlot;
use Modules\Warehouse\Entities\WarehouseLocation;
use Modules\Warehouse\Entities\WarehouseLocationSection;
use Tests\TestCase;

class WarehousesLocationsValidateControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Warehouse $warehouse;

    private WarehouseLocation $location;

    private WarehouseLocationSection $section;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user with warehouse.inventory permission
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['warehouse.inventory', 'warehouse.transfer']);

        // Create warehouse structure
        $this->warehouse = Warehouse::factory()->create(['name' => 'Test Warehouse']);
        $floor = WarehouseFloor::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'name' => 'Floor 1',
        ]);

        $this->location = WarehouseLocation::factory()->create([
            'floor_id' => $floor->id,
            'code' => 'LOC-VAL-001',
        ]);

        $this->section = WarehouseLocationSection::factory()->create([
            'location_id' => $this->location->id,
            'code' => 'SEC-VAL-A01',
            'level' => 1,
            'face' => 'North',
            'max_quantity' => 100,
            'available' => true,
        ]);

        // Create product
        $this->product = Product::factory()->create([
            'barcode' => 'VAL-PROD-001',
            'reference' => 'VAL-REF-001',
        ]);
    }

    /**
     * Test validate location checks inventory exists
     */
    public function test_validate_location_checks_inventory(): void
    {
        // Add product to section
        WarehouseInventorySlot::factory()->create([
            'section_id' => $this->section->id,
            'product_id' => $this->product->id,
            'quantity' => 25,
            'is_occupied' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.warehouses.location.validate.location'), [
                'location_id' => $this->location->id,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    /**
     * Test validate location fails when location not found
     */
    public function test_validate_location_fails_not_found(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.warehouses.location.validate.location'), [
                'location_id' => 99999,
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
    }

    /**
     * Test validate section checks section inventory
     */
    public function test_validate_section_checks_section_inventory(): void
    {
        // Add product to section
        WarehouseInventorySlot::factory()->create([
            'section_id' => $this->section->id,
            'product_id' => $this->product->id,
            'quantity' => 15,
            'is_occupied' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.warehouses.location.validate.section'), [
                'section_id' => $this->section->id,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'section_id',
                'total_quantity',
                'occupied_slots',
                'available_slots',
            ],
        ]);
    }

    /**
     * Test validate section with empty slots
     */
    public function test_validate_section_with_empty_slots(): void
    {
        // Create empty section with no products
        $emptySection = WarehouseLocationSection::factory()->create([
            'location_id' => $this->location->id,
            'code' => 'SEC-EMPTY',
            'max_quantity' => 50,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.warehouses.location.validate.section'), [
                'section_id' => $emptySection->id,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.total_quantity', 0);
    }

    /**
     * Test validate product checks product existence
     */
    public function test_validate_product_checks_product_existence(): void
    {
        // Add product to location
        WarehouseInventorySlot::factory()->create([
            'section_id' => $this->section->id,
            'product_id' => $this->product->id,
            'quantity' => 30,
            'is_occupied' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.warehouses.location.validate.product'), [
                'product_id' => $this->product->id,
                'location_id' => $this->location->id,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'product_id',
                'location_id',
                'total_quantity',
                'sections' => [
                    '*' => [
                        'section_id',
                        'section_code',
                        'quantity',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Test validate product with no stock in location
     */
    public function test_validate_product_no_stock_in_location(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.warehouses.location.validate.product'), [
                'product_id' => $this->product->id,
                'location_id' => $this->location->id,
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'Producto no encontrado en esta ubicación');
    }

    /**
     * Test validate returns errors for issues
     */
    public function test_validate_returns_errors_for_issues(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.warehouses.location.validate.location'), [
                'location_id' => 'invalid',
            ]);

        // Should validate input and return error
        $response->assertStatus(422);
    }

    /**
     * Test validate requires warehouse.inventory permission
     */
    public function test_validate_requires_warehouse_inventory_permission(): void
    {
        $userWithoutPermission = User::factory()->create();

        $response = $this->actingAs($userWithoutPermission)
            ->postJson(route('warehouse.warehouses.location.validate.location'), [
                'location_id' => $this->location->id,
            ]);

        // Should be 403 Forbidden or redirect
        $this->assertIn($response->getStatusCode(), [302, 403]);
    }

    /**
     * Test validate location with multiple sections
     */
    public function test_validate_location_with_multiple_sections(): void
    {
        // Create multiple sections with inventory
        $section2 = WarehouseLocationSection::factory()->create([
            'location_id' => $this->location->id,
            'code' => 'SEC-VAL-B01',
            'level' => 2,
        ]);

        $product2 = Product::factory()->create();

        WarehouseInventorySlot::factory()->create([
            'section_id' => $this->section->id,
            'product_id' => $this->product->id,
            'quantity' => 20,
        ]);

        WarehouseInventorySlot::factory()->create([
            'section_id' => $section2->id,
            'product_id' => $product2->id,
            'quantity' => 15,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.warehouses.location.validate.location'), [
                'location_id' => $this->location->id,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    /**
     * Test validate section occupancy percentage
     */
    public function test_validate_section_occupancy_percentage(): void
    {
        // Create multiple slots with products
        for ($i = 0; $i < 5; $i++) {
            $product = Product::factory()->create();
            WarehouseInventorySlot::factory()->create([
                'section_id' => $this->section->id,
                'product_id' => $product->id,
                'quantity' => $i > 0 ? 10 : 0, // Some empty slots
            ]);
        }

        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.warehouses.location.validate.section'), [
                'section_id' => $this->section->id,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'section_id',
                'total_quantity',
                'occupied_slots',
                'available_slots',
                'occupancy_percentage',
            ],
        ]);
    }

    /**
     * Test validate product returns correct aggregated quantities
     */
    public function test_validate_product_aggregates_quantities(): void
    {
        // Add product to multiple sections in same location
        WarehouseInventorySlot::factory()->create([
            'section_id' => $this->section->id,
            'product_id' => $this->product->id,
            'quantity' => 25,
        ]);

        $section2 = WarehouseLocationSection::factory()->create([
            'location_id' => $this->location->id,
            'code' => 'SEC-VAL-C01',
        ]);

        WarehouseInventorySlot::factory()->create([
            'section_id' => $section2->id,
            'product_id' => $this->product->id,
            'quantity' => 15,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('warehouse.warehouses.location.validate.product'), [
                'product_id' => $this->product->id,
                'location_id' => $this->location->id,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.total_quantity', 40);
        $response->assertJsonCount(2, 'data.sections');
    }
}
