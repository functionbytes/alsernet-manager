<?php

namespace Modules\Warehouse\Tests\Feature\Managers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseFloor;
use Tests\TestCase;

class WarehouseControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsManager();
    }

    /**
     * Test index returns warehouses with search and filter applied
     */
    public function testIndexReturnsWarehousesWithSearchAndFilter(): void
    {
        Warehouse::factory(5)->create();
        Warehouse::factory()->create(['name' => 'Special Warehouse', 'available' => true]);
        Warehouse::factory()->create(['name' => 'Inactive Warehouse', 'available' => false]);

        $response = $this->get(route('manager.warehouse.index', ['search' => 'Special']));

        $response->assertStatus(200);
        $response->assertViewIs('warehouse::managers.warehouses.index');
        $response->assertViewHas('warehouses');

        $warehouses = $response->viewData('warehouses');
        $this->assertGreaterThan(0, $warehouses->total());
        $this->assertTrue(
            $warehouses->pluck('name')->contains('Special Warehouse'),
            'Search should filter warehouses by name'
        );
    }

    /**
     * Test index applies availability filter correctly
     */
    public function testIndexFiltersByAvailableStatus(): void
    {
        Warehouse::factory(3)->create(['available' => true]);
        Warehouse::factory(2)->create(['available' => false]);

        $response = $this->get(route('manager.warehouse.index', ['available' => 1]));

        $response->assertStatus(200);
        $warehouses = $response->viewData('warehouses');
        $this->assertEquals(3, $warehouses->total());
    }

    /**
     * Test unauthenticated users are redirected
     */
    public function testIndexRequiresAuthentication(): void
    {
        $this->withoutMiddleware();

        $response = $this->actingAs(null)->get(route('manager.warehouse.index'));

        $response->assertStatus(401);
    }

    /**
     * Test create shows form
     */
    public function testCreateShowsForm(): void
    {
        $response = $this->get(route('manager.warehouse.create'));

        $response->assertStatus(200);
        $response->assertViewIs('warehouse::managers.warehouses.create');
    }

    /**
     * Test store creates warehouse with valid data
     */
    public function testStoreCreatesWarehouseWithValidData(): void
    {
        $data = [
            'code' => 'WH-TEST-001',
            'name' => 'Test Warehouse',
            'description' => 'A warehouse for testing',
            'available' => true,
        ];

        $response = $this->post(route('manager.warehouse.store'), $data);

        $response->assertRedirect(route('manager.warehouse.index'));
        $this->assertDatabaseHas('warehouses', [
            'code' => 'WH-TEST-001',
            'name' => 'Test Warehouse',
        ]);
    }

    /**
     * Test store validates unique code
     */
    public function testStoreValidatesUniqueCode(): void
    {
        $warehouse = Warehouse::factory()->create(['code' => 'WH-DUP']);

        $response = $this->post(route('manager.warehouse.store'), [
            'code' => 'WH-DUP',
            'name' => 'Another Warehouse',
        ]);

        $response->assertSessionHasErrors('code');
    }

    /**
     * Test store validates required fields
     */
    public function testStoreValidatesRequiredFields(): void
    {
        $response = $this->post(route('manager.warehouse.store'), [
            'name' => 'Test',
            // Missing required 'code'
        ]);

        $response->assertSessionHasErrors('code');
    }

    /**
     * Test edit shows form with warehouse data
     */
    public function testEditShowsFormWithWarehouseData(): void
    {
        $warehouse = Warehouse::factory()->create();

        $response = $this->get(route('manager.warehouse.edit', $warehouse->uid));

        $response->assertStatus(200);
        $response->assertViewIs('warehouse::managers.warehouses.edit');
        $response->assertViewHas('warehouse', $warehouse);
    }

    /**
     * Test update modifies warehouse
     */
    public function testUpdateModifiesWarehouse(): void
    {
        $warehouse = Warehouse::factory()->create([
            'name' => 'Original Name',
            'available' => true,
        ]);

        $response = $this->post(route('manager.warehouse.update', $warehouse->uid), [
            'code' => $warehouse->code,
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'available' => false,
        ]);

        $response->assertRedirect(route('manager.warehouse.index'));
        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'name' => 'Updated Name',
            'available' => false,
        ]);
    }

    /**
     * Test destroy removes warehouse (soft delete)
     */
    public function testDestroyRemovesWarehouse(): void
    {
        $warehouse = Warehouse::factory()->create();
        $warehouseId = $warehouse->id;

        $response = $this->delete(route('manager.warehouse.destroy', $warehouse->uid));

        $response->assertRedirect(route('manager.warehouse.index'));
        $this->assertDatabaseHas('warehouses', ['id' => $warehouseId, 'deleted_at' => null]);

        // Verify soft delete occurred
        $this->assertTrue(Warehouse::find($warehouseId)->trashed());
    }

    /**
     * Test destroy fails when warehouse has floors
     */
    public function testDestroyFailsWithFloors(): void
    {
        $warehouse = Warehouse::factory()->create();
        WarehouseFloor::factory()->for($warehouse)->create();

        $response = $this->delete(route('manager.warehouse.destroy', $warehouse->uid));

        $response->assertStatus(422);
        $response->assertJsonStructure(['status', 'message']);
        $response->assertJson(['status' => false]);

        // Verify warehouse was not deleted
        $this->assertFalse(Warehouse::find($warehouse->id)->trashed());
    }

    /**
     * Test getSummary returns statistics
     */
    public function testGetSummaryReturnsStatistics(): void
    {
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->for($warehouse)->create();

        $response = $this->getJson(route('manager.warehouse.summary', $warehouse->uid));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'warehouse_id',
            'warehouse_uid',
            'name',
            'total_floors',
            'total_locations',
            'total_slots',
            'occupied_slots',
        ]);
        $response->assertJson([
            'warehouse_id' => $warehouse->id,
            'total_floors' => 1,
        ]);
    }

    /**
     * Test getSummary returns 404 for non-existent warehouse
     */
    public function testGetSummaryReturns404ForMissingWarehouse(): void
    {
        $response = $this->getJson(route('manager.warehouse.summary', 'non-existent-uid'));

        $response->assertStatus(404);
        $response->assertJsonStructure(['error']);
    }
}
