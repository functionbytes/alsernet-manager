<?php

namespace Modules\Warehouse\Tests\Unit\Entities;

use Modules\Warehouse\Entities\WarehouseLocationStyle;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseFloor;
use Modules\Warehouse\Entities\WarehouseLocation;
use Tests\TestCase;
use Illuminate\Support\Str;

class WarehouseLocationStyleTest extends TestCase
{
    /**
     * Test: WarehouseLocationStyle has correct fillable attributes
     */
    public function test_style_has_correct_fillable_attributes(): void
    {
        $style = new WarehouseLocationStyle();

        $expectedFillable = [
            'uid',
            'code',
            'name',
            'description',
            'type',
            'faces',
            'width',
            'height',
            'default_levels',
            'default_sections',
            'available',
        ];

        $this->assertEquals($expectedFillable, $style->getFillable());
    }

    /**
     * Test: WarehouseLocationStyle has correct casts
     */
    public function test_style_has_correct_casts(): void
    {
        $style = new WarehouseLocationStyle();
        $casts = $style->getCasts();

        $this->assertEquals('array', $casts['faces']);
        $this->assertEquals('boolean', $casts['available']);
        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
    }

    /**
     * Test: WarehouseLocationStyle has correct type constants
     */
    public function test_style_has_correct_type_constants(): void
    {
        $this->assertEquals('row', WarehouseLocationStyle::TYPE_ROW);
        $this->assertEquals('island', WarehouseLocationStyle::TYPE_ISLAND);
        $this->assertEquals('wall', WarehouseLocationStyle::TYPE_WALL);
    }

    /**
     * Test: WarehouseLocationStyle has correct face constants
     */
    public function test_style_has_correct_face_constants(): void
    {
        $this->assertEquals('left', WarehouseLocationStyle::FACE_LEFT);
        $this->assertEquals('right', WarehouseLocationStyle::FACE_RIGHT);
        $this->assertEquals('front', WarehouseLocationStyle::FACE_FRONT);
        $this->assertEquals('back', WarehouseLocationStyle::FACE_BACK);
    }

    /**
     * Test: WarehouseLocationStyle types array contains all valid types
     */
    public function test_style_types_array_contains_all_types(): void
    {
        $this->assertContains('row', WarehouseLocationStyle::$types);
        $this->assertContains('island', WarehouseLocationStyle::$types);
        $this->assertContains('wall', WarehouseLocationStyle::$types);
    }

    /**
     * Test: WarehouseLocationStyle validFaces array contains all valid faces
     */
    public function test_style_valid_faces_array_contains_all_faces(): void
    {
        $this->assertContains('left', WarehouseLocationStyle::$validFaces);
        $this->assertContains('right', WarehouseLocationStyle::$validFaces);
        $this->assertContains('front', WarehouseLocationStyle::$validFaces);
        $this->assertContains('back', WarehouseLocationStyle::$validFaces);
    }

    /**
     * Test: getFacesByType returns correct faces for island type
     */
    public function test_get_faces_by_type_returns_island_faces(): void
    {
        $faces = WarehouseLocationStyle::getFacesByType('island');

        $this->assertCount(4, $faces);
        $this->assertContains('front', $faces);
        $this->assertContains('back', $faces);
        $this->assertContains('left', $faces);
        $this->assertContains('right', $faces);
    }

    /**
     * Test: getFacesByType returns correct faces for row type
     */
    public function test_get_faces_by_type_returns_row_faces(): void
    {
        $faces = WarehouseLocationStyle::getFacesByType('row');

        $this->assertCount(2, $faces);
        $this->assertContains('front', $faces);
        $this->assertContains('back', $faces);
    }

    /**
     * Test: getFacesByType returns correct faces for wall type
     */
    public function test_get_faces_by_type_returns_wall_faces(): void
    {
        $faces = WarehouseLocationStyle::getFacesByType('wall');

        $this->assertCount(1, $faces);
        $this->assertContains('front', $faces);
    }

    /**
     * Test: getFacesByType returns default face for unknown type
     */
    public function test_get_faces_by_type_returns_default_for_unknown(): void
    {
        $faces = WarehouseLocationStyle::getFacesByType('unknown');

        $this->assertCount(1, $faces);
        $this->assertContains('front', $faces);
    }

    /**
     * Test: WarehouseLocationStyle hasMany locations
     */
    public function test_style_has_many_locations(): void
    {
        $style = WarehouseLocationStyle::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);

        WarehouseLocation::factory(3)->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
        ]);

        $locations = $style->locations()->get();

        $this->assertCount(3, $locations);
    }

    /**
     * Test: available scope returns only available styles
     */
    public function test_available_scope_returns_available_styles(): void
    {
        WarehouseLocationStyle::factory(3)->create(['available' => true]);
        WarehouseLocationStyle::factory(2)->create(['available' => false]);

        $available = WarehouseLocationStyle::available()->get();

        $this->assertCount(3, $available);
        $available->each(fn ($style) => $this->assertTrue($style->available));
    }

    /**
     * Test: byCode scope filters by code
     */
    public function test_by_code_scope_filters_correctly(): void
    {
        $code = 'STYLE-001';
        $style = WarehouseLocationStyle::factory()->create(['code' => $code]);
        WarehouseLocationStyle::factory()->create();

        $result = WarehouseLocationStyle::byCode($code)->first();

        $this->assertNotNull($result);
        $this->assertEquals($code, $result->code);
    }

    /**
     * Test: search scope searches by name
     */
    public function test_search_scope_searches_by_name(): void
    {
        WarehouseLocationStyle::factory()->create(['name' => 'Island Style']);
        WarehouseLocationStyle::factory()->create(['name' => 'Row Style']);
        WarehouseLocationStyle::factory()->create(['name' => 'Wall Style']);

        $results = WarehouseLocationStyle::search('Island')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Island Style', $results->first()->name);
    }

    /**
     * Test: search scope searches by code
     */
    public function test_search_scope_searches_by_code(): void
    {
        WarehouseLocationStyle::factory()->create(['code' => 'STY-001', 'name' => 'Style One']);
        WarehouseLocationStyle::factory()->create(['code' => 'STY-002', 'name' => 'Style Two']);

        $results = WarehouseLocationStyle::search('STY-001')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('STY-001', $results->first()->code);
    }

    /**
     * Test: getTypeName returns friendly type name for island
     */
    public function test_get_type_name_returns_island(): void
    {
        $style = WarehouseLocationStyle::factory()->create(['type' => 'island']);

        $this->assertEquals('Isla (360°)', $style->getTypeName());
    }

    /**
     * Test: getTypeName returns friendly type name for row
     */
    public function test_get_type_name_returns_row(): void
    {
        $style = WarehouseLocationStyle::factory()->create(['type' => 'row']);

        $this->assertEquals('Pasillo Lineal', $style->getTypeName());
    }

    /**
     * Test: getTypeName returns friendly type name for wall
     */
    public function test_get_type_name_returns_wall(): void
    {
        $style = WarehouseLocationStyle::factory()->create(['type' => 'wall']);

        $this->assertEquals('Pared', $style->getTypeName());
    }

    /**
     * Test: getFacesLabel returns readable face labels
     */
    public function test_get_faces_label_returns_labels(): void
    {
        $style = WarehouseLocationStyle::factory()->create([
            'faces' => ['front', 'back', 'left'],
        ]);

        $label = $style->getFacesLabel();

        $this->assertStringContainsString('Frente', $label);
        $this->assertStringContainsString('Atrás', $label);
        $this->assertStringContainsString('Izquierda', $label);
    }

    /**
     * Test: hasValidFaces returns true for valid faces
     */
    public function test_has_valid_faces_returns_true_for_valid(): void
    {
        $style = WarehouseLocationStyle::factory()->create([
            'faces' => ['front', 'back', 'left', 'right'],
        ]);

        $this->assertTrue($style->hasValidFaces());
    }

    /**
     * Test: hasValidFaces returns false for invalid faces
     */
    public function test_has_valid_faces_returns_false_for_invalid(): void
    {
        $style = WarehouseLocationStyle::factory()->create([
            'faces' => ['front', 'invalid-face'],
        ]);

        $this->assertFalse($style->hasValidFaces());
    }

    /**
     * Test: hasValidFaces returns false for empty faces
     */
    public function test_has_valid_faces_returns_false_for_empty(): void
    {
        $style = WarehouseLocationStyle::factory()->create([
            'faces' => [],
        ]);

        $this->assertFalse($style->hasValidFaces());
    }

    /**
     * Test: getStandCount returns total locations count
     */
    public function test_get_stand_count_returns_total_locations(): void
    {
        $style = WarehouseLocationStyle::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);

        WarehouseLocation::factory(5)->create([
            'warehouse_id' => $warehouse->id,
            'floor_id' => $floor->id,
            'style_id' => $style->id,
        ]);

        $this->assertEquals(5, $style->getStandCount());
    }

    /**
     * Test: getStandCount returns zero when no locations
     */
    public function test_get_stand_count_returns_zero_when_no_locations(): void
    {
        $style = WarehouseLocationStyle::factory()->create();

        $this->assertEquals(0, $style->getStandCount());
    }

    /**
     * Test: getActiveStandCount returns count of available locations
     */
    public function test_get_active_stand_count_returns_available_locations(): void
    {
        $style = WarehouseLocationStyle::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $floor = WarehouseFloor::factory()->create(['warehouse_id' => $warehouse->id]);

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

        $this->assertEquals(3, $style->getActiveStandCount());
    }

    /**
     * Test: getSummary returns correct array structure
     */
    public function test_get_summary_returns_correct_structure(): void
    {
        $style = WarehouseLocationStyle::factory()->create([
            'faces' => ['front', 'back'],
        ]);

        $summary = $style->getSummary();

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('id', $summary);
        $this->assertArrayHasKey('uid', $summary);
        $this->assertArrayHasKey('code', $summary);
        $this->assertArrayHasKey('name', $summary);
        $this->assertArrayHasKey('type', $summary);
        $this->assertArrayHasKey('type_name', $summary);
        $this->assertArrayHasKey('faces', $summary);
        $this->assertArrayHasKey('width', $summary);
        $this->assertArrayHasKey('height', $summary);
        $this->assertArrayHasKey('faces_label', $summary);
        $this->assertArrayHasKey('locations_count', $summary);
        $this->assertArrayHasKey('active_locations_count', $summary);
    }

    /**
     * Test: WarehouseLocationStyle auto-generates UUID on creation
     */
    public function test_style_auto_generates_uuid(): void
    {
        $style = WarehouseLocationStyle::factory()->create();

        $this->assertNotNull($style->uid);
        $this->assertTrue(Str::isUuid($style->uid));
    }

    /**
     * Test: WarehouseLocationStyle faces are stored and retrieved as array
     */
    public function test_style_faces_cast_to_array(): void
    {
        $faces = ['front', 'back', 'left'];
        $style = WarehouseLocationStyle::factory()->create(['faces' => $faces]);

        $this->assertIsArray($style->faces);
        $this->assertEquals($faces, $style->faces);
    }

    /**
     * Test: WarehouseLocationStyle table configuration
     */
    public function test_style_table_configuration(): void
    {
        $style = new WarehouseLocationStyle();

        $this->assertEquals('warehouse_location_styles', $style->getTable());
        $this->assertEquals('id', $style->getKeyName());
        $this->assertEquals('int', $style->getKeyType());
        $this->assertTrue($style->getIncrementing());
    }
}
