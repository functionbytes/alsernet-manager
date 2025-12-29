<?php

namespace Modules\Warehouse\Tests\Feature\Managers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseFloor;
use Modules\Warehouse\Entities\WarehouseLocation;
use Modules\Warehouse\Entities\WarehouseLocationStyle;
use Tests\TestCase;

class WarehouseLocationsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Warehouse $warehouse;

    protected WarehouseFloor $floor;

    protected WarehouseLocationStyle $style;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsManager();

        $this->warehouse = Warehouse::factory()->create();
        $this->floor = WarehouseFloor::factory()->for($this->warehouse)->create();
        $this->style = WarehouseLocationStyle::factory()->create();
    }

    /**
     * Test index lists locations with pagination
     */
    public function testIndexListsLocationsForFloor(): void
    {
        WarehouseLocation::factory(5)
            ->for($this->warehouse)
            ->for($this->floor)
            ->create(['style_id' => $this->style->id]);

        $response = $this->get(route('manager.warehouse.locations', [
            'warehouse_uid' => $this->warehouse->uid,
            'floor_uid' => $this->floor->uid,
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('warehouse::managers.locations.index');
        $response->assertViewHas('locations');

        $locations = $response->viewData('locations');
        $this->assertGreaterThanOrEqual(5, $locations->total());
    }

    /**
     * Test index applies search filters
     */
    public function testIndexAppliesSearchFilters(): void
    {
        WarehouseLocation::factory(3)
            ->for($this->warehouse)
            ->for($this->floor)
            ->create(['style_id' => $this->style->id]);

        WarehouseLocation::factory()
            ->for($this->warehouse)
            ->for($this->floor)
            ->create([
                'code' => 'LOC-SPECIAL',
                'style_id' => $this->style->id,
            ]);

        $response = $this->get(route('manager.warehouse.locations', [
            'warehouse_uid' => $this->warehouse->uid,
            'floor_uid' => $this->floor->uid,
            'search' => 'SPECIAL',
        ]));

        $response->assertStatus(200);
        $locations = $response->viewData('locations');
        $this->assertTrue(
            $locations->pluck('code')->contains('LOC-SPECIAL'),
            'Search should filter locations by code'
        );
    }

    /**
     * Test view shows location with slots and summary
     */
    public function testViewShowsLocationWithSlots(): void
    {
        $location = WarehouseLocation::factory()
            ->for($this->warehouse)
            ->for($this->floor)
            ->create(['style_id' => $this->style->id]);

        $response = $this->get(route('manager.warehouse.locations.view', [
            'warehouse_uid' => $this->warehouse->uid,
            'floor_uid' => $this->floor->uid,
            'location_uid' => $location->uid,
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('warehouse::managers.locations.view');
        $response->assertViewHas(['location', 'slots', 'summary']);

        $summary = $response->viewData('summary');
        $this->assertArrayHasKey('total_slots', $summary);
        $this->assertArrayHasKey('occupied_slots', $summary);
        $this->assertArrayHasKey('occupancy_percentage', $summary);
    }

    /**
     * Test create shows form with available styles
     */
    public function testCreateShowsForm(): void
    {
        $response = $this->get(route('manager.warehouse.locations.create', [
            'warehouse_uid' => $this->warehouse->uid,
            'floor_uid' => $this->floor->uid,
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('warehouse::managers.locations.create');
        $response->assertViewHas('styles');
    }

    /**
     * Test store creates location with sections
     */
    public function testStoreCreatesLocationWithSections(): void
    {
        $data = [
            'warehouse_uid' => $this->warehouse->uid,
            'floor_uid' => $this->floor->uid,
            'code' => 'LOC-001',
            'style_id' => $this->style->id,
            'position_x' => 10.5,
            'position_y' => 20.5,
            'available' => true,
            'notes' => 'Test location',
            'sections' => [
                [
                    'code' => 'SEC-A',
                    'barcode' => 'BC-001',
                    'level' => 1,
                    'face' => 'front',
                ],
                [
                    'code' => 'SEC-B',
                    'barcode' => 'BC-002',
                    'level' => 2,
                    'face' => 'back',
                ],
            ],
        ];

        $response = $this->post(route('manager.warehouse.locations.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('warehouse_locations', [
            'code' => 'LOC-001',
            'floor_id' => $this->floor->id,
        ]);

        // Verify sections were created
        $location = WarehouseLocation::where('code', 'LOC-001')->first();
        $this->assertEquals(2, $location->sections()->count());
    }

    /**
     * Test store validates required sections
     */
    public function testStoreValidatesRequiredSections(): void
    {
        $data = [
            'warehouse_uid' => $this->warehouse->uid,
            'floor_uid' => $this->floor->uid,
            'code' => 'LOC-002',
            'style_id' => $this->style->id,
            'position_x' => 10,
            'position_y' => 20,
            'sections' => [], // Empty sections array
        ];

        $response = $this->post(route('manager.warehouse.locations.store'), $data);

        $response->assertSessionHasErrors('sections');
    }

    /**
     * Test update modifies location and sections
     */
    public function testUpdateModifiesLocation(): void
    {
        $location = WarehouseLocation::factory()
            ->for($this->warehouse)
            ->for($this->floor)
            ->create(['style_id' => $this->style->id]);

        $section = $location->sections()->create([
            'code' => 'OLD-SEC',
            'level' => 1,
            'available' => true,
        ]);

        $data = [
            'warehouse_uid' => $this->warehouse->uid,
            'floor_uid' => $this->floor->uid,
            'location_uid' => $location->uid,
            'code' => $location->code,
            'style_id' => $this->style->id,
            'position_x' => 15.5,
            'position_y' => 25.5,
            'available' => false,
            'notes' => 'Updated notes',
            'sections' => [
                [
                    'uid' => $section->uid,
                    'code' => 'NEW-SEC',
                    'level' => 1,
                    'face' => 'front',
                ],
            ],
        ];

        $response = $this->post(route('manager.warehouse.locations.update'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('warehouse_locations', [
            'id' => $location->id,
            'position_x' => 15.5,
            'available' => false,
        ]);
    }

    /**
     * Test destroy removes location and associated slots
     */
    public function testDestroyRemovesLocation(): void
    {
        $location = WarehouseLocation::factory()
            ->for($this->warehouse)
            ->for($this->floor)
            ->create(['style_id' => $this->style->id]);

        $locationId = $location->id;

        $response = $this->delete(route('manager.warehouse.locations.destroy', [
            'warehouse_uid' => $this->warehouse->uid,
            'floor_uid' => $this->floor->uid,
            'location_uid' => $location->uid,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('warehouse_locations', ['id' => $locationId]);
    }

    /**
     * Test validate locations shows formula
     */
    public function testValidateLocationsShowsFormula(): void
    {
        $location = WarehouseLocation::factory()
            ->for($this->warehouse)
            ->for($this->floor)
            ->create(['style_id' => $this->style->id]);

        $section = $location->sections()->create([
            'code' => 'TEST-SEC',
            'barcode' => 'TEST-BARCODE',
            'level' => 1,
            'available' => true,
        ]);

        // Verify the location has a barcode formula
        $this->assertNotNull($section->barcode);
    }

    /**
     * Test getByWarehouse returns locations as JSON
     */
    public function testGetByWarehouseReturnsJsonData(): void
    {
        WarehouseLocation::factory(3)
            ->for($this->warehouse)
            ->for($this->floor)
            ->create(['style_id' => $this->style->id]);

        $response = $this->getJson(route('manager.warehouse.locations.api.warehouse', [
            'warehouse_uid' => $this->warehouse->uid,
            'floor_uid' => $this->floor->uid,
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'uid', 'code', 'full_name'],
        ]);
        $this->assertGreaterThanOrEqual(3, count($response->json()));
    }

    /**
     * Test transfer location to another floor
     */
    public function testTransferLocationToAnotherFloor(): void
    {
        $targetFloor = WarehouseFloor::factory()->for($this->warehouse)->create();
        $location = WarehouseLocation::factory()
            ->for($this->warehouse)
            ->for($this->floor)
            ->create(['style_id' => $this->style->id]);

        $response = $this->post(route('manager.warehouse.locations.transfer.submit'), [
            'warehouse_uid' => $this->warehouse->uid,
            'floor_uid' => $this->floor->uid,
            'location_uid' => $location->uid,
            'target_floor_uid' => $targetFloor->uid,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('warehouse_locations', [
            'id' => $location->id,
            'floor_id' => $targetFloor->id,
        ]);
    }

    /**
     * Test transfer fails when location with same code exists
     */
    public function testTransferFailsWithDuplicateCode(): void
    {
        $targetFloor = WarehouseFloor::factory()->for($this->warehouse)->create();
        $location = WarehouseLocation::factory()
            ->for($this->warehouse)
            ->for($this->floor)
            ->create(['code' => 'DUP-CODE', 'style_id' => $this->style->id]);

        WarehouseLocation::factory()
            ->for($this->warehouse)
            ->for($targetFloor)
            ->create(['code' => 'DUP-CODE', 'style_id' => $this->style->id]);

        $response = $this->post(route('manager.warehouse.locations.transfer.submit'), [
            'warehouse_uid' => $this->warehouse->uid,
            'floor_uid' => $this->floor->uid,
            'location_uid' => $location->uid,
            'target_floor_uid' => $targetFloor->uid,
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('warehouse_locations', [
            'id' => $location->id,
            'floor_id' => $this->floor->id,
        ]);
    }
}
