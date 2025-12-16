<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse\Warehouse;
use App\Models\Warehouse\WarehouseFloor;
use App\Models\Warehouse\WarehouseLocation;
use App\Models\Warehouse\WarehouseLocationSection;
use App\Models\Warehouse\WarehouseLocationStyle;
use App\Services\WarehouseLayoutParser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WarehouseLayoutSeeder extends Seeder
{
    private $warehouse;
    private $floors = [];
    private $styles = [];
    private $parser;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $this->command->info("🚀 Starting Complete Warehouse Layout Seeding...\n");

            // 1. Get warehouse COR
            $this->warehouse = Warehouse::where('code', 'COR')->first();
            if (!$this->warehouse) {
                $this->command->error("❌ Warehouse with code 'COR' not found!");
                return;
            }

            $this->command->info("✓ Warehouse: {$this->warehouse->code} (ID: {$this->warehouse->id})");

            // 2. Initialize parser
            $this->parser = new WarehouseLayoutParser();

            // 3. Load LAYOUT_SPEC
            $layoutSpec = $this->getLayoutSpec();
            $this->command->info("✓ Loaded LAYOUT_SPEC with " . count($layoutSpec) . " sections\n");

            // 4. Parse complete layout
            $this->command->info("📊 Parsing layout structure...");
            $parsed = $this->parser->parse($layoutSpec);

            // 5. Display summary
            $this->displaySummary($parsed['summary']);

            // 6. Create floors
            $this->command->info("\n📁 Creating floors...");
            $this->createFloorsFromParsed($parsed['floors']);

            // 7. Ensure styles exist
            $this->command->info("\n🎨 Loading location styles...");
            $this->loadStyles();

            // 8. Create locations and sections
            $this->command->info("\n📍 Creating locations and sections...");
            $this->createLocationsAndSections($parsed['locations']);

            // 9. Save JSON export
            $this->command->info("\n💾 Saving JSON export...");
            $this->saveJsonExport($parsed);

            // 10. Display errors and warnings
            $this->displayErrorsAndWarnings($parsed['errors'], $parsed['warnings']);

            DB::commit();
            $this->command->info("\n✅ Seeding completed successfully!");
            $this->command->info("Total locations created: " . $parsed['summary']['total_locations']);
            $this->command->info("Total sections created: " . $parsed['summary']['total_sections']);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("\n❌ Error: " . $e->getMessage());
            $this->command->error($e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Get LAYOUT_SPEC array
     * This would normally come from a JSON file, but for now it's hardcoded
     */
    private function getLayoutSpec(): array
    {
        // Return the complete LAYOUT_SPEC array
        // In production, this would be loaded from storage/app/layout_spec.json
        return require(__DIR__ . '/layout_spec.php');
    }

    /**
     * Display parsing summary
     */
    private function displaySummary(array $summary): void
    {
        $this->command->info("─────────────────────────────────────");
        $this->command->info("📊 PARSING SUMMARY");
        $this->command->info("─────────────────────────────────────");
        $this->command->info("Total Floors: " . $summary['total_floors']);
        $this->command->info("Total Locations: " . $summary['total_locations']);
        $this->command->info("Total Sections: " . $summary['total_sections']);

        if (!empty($summary['floor_breakdown'])) {
            $this->command->info("\nBreakdown by Floor:");
            foreach ($summary['floor_breakdown'] as $floor => $count) {
                $this->command->info("  - Floor {$floor}: {$count} locations");
            }
        }

        if (!empty($summary['style_breakdown'])) {
            $this->command->info("\nBreakdown by Style:");
            foreach ($summary['style_breakdown'] as $style => $count) {
                $this->command->info("  - {$style}: {$count} locations");
            }
        }

        $this->command->info("─────────────────────────────────────");
    }

    /**
     * Create floors from parsed data
     */
    private function createFloorsFromParsed(array $floorsData): void
    {
        foreach ($floorsData as $floorData) {
            $floor = WarehouseFloor::firstOrCreate(
                [
                    'warehouse_id' => $this->warehouse->id,
                    'level' => $floorData['level']
                ],
                [
                    'uid' => Str::uuid(),
                    'code' => $floorData['code'],
                    'name' => $floorData['name'],
                    'description' => $floorData['description'] ?? '',
                    'available' => true,
                ]
            );

            $this->floors[$floorData['level']] = $floor;
            $this->command->info("  ✓ Floor {$floorData['level']}: {$floor->name} (ID: {$floor->id})");
        }
    }

    /**
     * Load all location styles
     */
    private function loadStyles(): void
    {
        $allStyles = WarehouseLocationStyle::all();

        foreach ($allStyles as $style) {
            $this->styles[$style->code] = $style;
        }

        $this->command->info("  ✓ Loaded " . count($this->styles) . " location styles");
    }

    /**
     * Create locations and their sections
     */
    private function createLocationsAndSections(array $locations): void
    {
        $createdCount = 0;
        $sectionsCount = 0;

        foreach ($locations as $locationData) {
            // Get floor
            $floor = $this->floors[$locationData['floor_level']] ?? null;
            if (!$floor) {
                $this->command->warn("  ⚠ Floor level {$locationData['floor_level']} not found for {$locationData['code']}");
                continue;
            }

            // Get style
            $style = $this->styles[$locationData['style_code']] ?? null;
            if (!$style) {
                $this->command->warn("  ⚠ Style {$locationData['style_code']} not found for {$locationData['code']}");
                continue;
            }

            // Create location
            $location = WarehouseLocation::create([
                'uid' => Str::uuid(),
                'warehouse_id' => $this->warehouse->id,
                'floor_id' => $floor->id,
                'style_id' => $style->id,
                'code' => $locationData['code'],
                'visual_position_x' => $locationData['visual_position_x'],
                'visual_position_y' => $locationData['visual_position_y'],
                'visual_width_m' => $locationData['visual_width_m'],
                'visual_height_m' => $locationData['visual_height_m'],
                'total_levels' => $locationData['total_levels'],
                'available' => true,
            ]);

            $createdCount++;

            // Create sections
            foreach ($locationData['sections'] as $sectionData) {
                WarehouseLocationSection::create([
                    'uid' => Str::uuid(),
                    'location_id' => $location->id,
                    'code' => $sectionData['code'],
                    'barcode' => $sectionData['barcode'],
                    'face' => $sectionData['face'],
                    'level' => $sectionData['level'],
                    'available' => true,
                ]);

                $sectionsCount++;
            }

            if ($createdCount % 10 === 0) {
                $this->command->info("  ✓ Created {$createdCount} locations...");
            }
        }

        $this->command->info("  ✓ Created {$createdCount} locations with {$sectionsCount} sections");
    }

    /**
     * Save JSON export of parsed data
     */
    private function saveJsonExport(array $parsed): void
    {
        $export = [
            'warehouse_id' => $this->warehouse->id,
            'warehouse_code' => $this->warehouse->code,
            'generated_at' => now()->toDateTimeString(),
            'summary' => $parsed['summary'],
            'floors' => $parsed['floors'],
            'locations' => $parsed['locations'],
            'sections' => $parsed['sections'],
            'errors' => $parsed['errors'],
            'warnings' => $parsed['warnings'],
        ];

        $json = json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        Storage::disk('local')->put('warehouse_layout.json', $json);

        $this->command->info("  ✓ JSON export saved to storage/app/warehouse_layout.json");
    }

    /**
     * Display errors and warnings
     */
    private function displayErrorsAndWarnings(array $errors, array $warnings): void
    {
        if (!empty($errors)) {
            $this->command->warn("\n⚠ ERRORS FOUND:");
            foreach ($errors as $error) {
                $this->command->error("  - {$error}");
            }
        }

        if (!empty($warnings)) {
            $this->command->warn("\n⚠ WARNINGS:");
            foreach ($warnings as $warning) {
                $this->command->warn("  - {$warning}");
            }
        }

        if (empty($errors) && empty($warnings)) {
            $this->command->info("\n✅ No errors or warnings!");
        }
    }
}
