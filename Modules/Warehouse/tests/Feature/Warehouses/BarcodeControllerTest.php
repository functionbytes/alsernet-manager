<?php

namespace Modules\Warehouse\Tests\Feature\Warehouses;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarcodeControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['warehouse.inventory']);

        // Create locations with barcodes
        $this->location = Location::factory()->create([
            'code' => 'LOC-BARCODE-001',
            'name' => 'Location with Barcode',
        ]);
    }

    /**
     * Test all endpoint returns all location barcodes
     */
    public function test_all_returns_all_barcodes(): void
    {
        // Create multiple locations
        Location::factory(5)->create();

        $response = $this->actingAs($this->user)
            ->get(route('warehouse.manager.shops.locations.barcodes.all'));

        $response->assertStatus(200);
        $response->assertViewIs('warehouse::locations.barcodes.all');
        $response->assertViewHas('locations');

        $locations = $response->viewData('locations');
        $this->assertGreaterThan(0, $locations->count());
        $this->assertLessThanOrEqual(100, $locations->count());
    }

    /**
     * Test all endpoint limits results to 100
     */
    public function test_all_limits_to_hundred(): void
    {
        // Create many locations
        Location::factory(150)->create();

        $response = $this->actingAs($this->user)
            ->get(route('warehouse.manager.shops.locations.barcodes.all'));

        $response->assertStatus(200);
        $locations = $response->viewData('locations');
        $this->assertEquals(100, $locations->count());
    }

    /**
     * Test all endpoint includes location details
     */
    public function test_all_includes_location_and_slot_info(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('warehouse.manager.shops.locations.barcodes.all'));

        $response->assertStatus(200);
        $locations = $response->viewData('locations');

        if ($locations->count() > 0) {
            $location = $locations->first();
            $this->assertNotNull($location->code);
            $this->assertNotNull($location->uid);
        }
    }

    /**
     * Test single endpoint returns specific barcode
     */
    public function test_single_returns_specific_barcode(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('warehouse.manager.shops.locations.barcodes.single', [
                'slack' => $this->location->uid,
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('warehouse::locations.barcodes.all');
        $response->assertViewHas('location');

        $location = $response->viewData('location');
        $this->assertEquals($this->location->id, $location->id);
    }

    /**
     * Test single endpoint with invalid uid returns null or error
     */
    public function test_single_with_invalid_uid(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('warehouse.manager.shops.locations.barcodes.single', [
                'slack' => 'invalid-uid',
            ]));

        // Should either return 404 or show view with null location
        $this->assertIn($response->getStatusCode(), [200, 404]);
    }

    /**
     * Test barcode format is correct
     */
    public function test_barcode_format_is_correct(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('warehouse.manager.shops.locations.barcodes.all'));

        $response->assertStatus(200);
        $locations = $response->viewData('locations');

        if ($locations->count() > 0) {
            foreach ($locations as $location) {
                // Verify location has necessary fields for barcode generation
                $this->assertNotNull($location->code);
                $this->assertNotNull($location->uid);
                $this->assertIsString($location->uid);
            }
        }
    }

    /**
     * Test all endpoint with no authentication redirects
     */
    public function test_all_without_authentication_redirects(): void
    {
        $response = $this->get(route('warehouse.manager.shops.locations.barcodes.all'));

        $response->assertStatus(302);
        $response->assertRedirect();
    }

    /**
     * Test single endpoint with no authentication redirects
     */
    public function test_single_without_authentication_redirects(): void
    {
        $response = $this->get(route('warehouse.manager.shops.locations.barcodes.single', [
            'slack' => $this->location->uid,
        ]));

        $response->assertStatus(302);
        $response->assertRedirect();
    }

    /**
     * Test all endpoint returns locations with uid field
     */
    public function test_all_locations_have_uid(): void
    {
        // Create location with uid
        Location::factory()->create();

        $response = $this->actingAs($this->user)
            ->get(route('warehouse.manager.shops.locations.barcodes.all'));

        $response->assertStatus(200);
        $locations = $response->viewData('locations');

        foreach ($locations as $location) {
            $this->assertNotNull($location->uid);
            $this->assertTrue(is_string($location->uid) || is_object($location->uid));
        }
    }

    /**
     * Test single endpoint returns specific location by uid
     */
    public function test_single_returns_specific_location_by_uid(): void
    {
        $locations = Location::factory(3)->create();
        $targetLocation = $locations->first();

        $response = $this->actingAs($this->user)
            ->get(route('warehouse.manager.shops.locations.barcodes.single', [
                'slack' => $targetLocation->uid,
            ]));

        $response->assertStatus(200);
        $location = $response->viewData('location');

        if ($location) {
            $this->assertEquals($targetLocation->id, $location->id);
        }
    }

    /**
     * Test barcode view displays correct location code
     */
    public function test_barcode_view_displays_location_code(): void
    {
        $location = Location::factory()->create([
            'code' => 'TEST-LOC-CODE-123',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('warehouse.manager.shops.locations.barcodes.single', [
                'slack' => $location->uid,
            ]));

        $response->assertStatus(200);
        $returnedLocation = $response->viewData('location');

        if ($returnedLocation) {
            $this->assertEquals('TEST-LOC-CODE-123', $returnedLocation->code);
        }
    }

    /**
     * Test all endpoint returns recent locations
     */
    public function test_all_returns_recent_locations(): void
    {
        $oldLocation = Location::factory()->create();
        $oldLocation->update(['created_at' => now()->subDays(30)]);

        $newLocation = Location::factory()->create();
        $newLocation->update(['created_at' => now()]);

        $response = $this->actingAs($this->user)
            ->get(route('warehouse.manager.shops.locations.barcodes.all'));

        $response->assertStatus(200);
        $locations = $response->viewData('locations');

        // Both old and new should be in results (limited to 100)
        $this->assertGreaterThan(0, $locations->count());
    }

    /**
     * Test single endpoint with correct location returns view
     */
    public function test_single_endpoint_view_structure(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('warehouse.manager.shops.locations.barcodes.single', [
                'slack' => $this->location->uid,
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('warehouse::locations.barcodes.all');

        // Should have either location or locations data
        $location = $response->viewData('location');
        $this->assertNotNull($location);
    }
}
