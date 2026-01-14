# Warehouse Module Test Infrastructure

This directory contains comprehensive test infrastructure for the Warehouse module, including test case base classes, factories, and helper methods.

## Structure

```
tests/
├── README.md                    # This file
├── WarehouseTestCase.php        # Base test case with helpers
├── Feature/                     # Feature tests
└── Unit/                        # Unit tests
```

## Base Test Case: WarehouseTestCase

Located in: `/Modules/Warehouse/tests/WarehouseTestCase.php`

All warehouse-related tests should extend `WarehouseTestCase` instead of the generic `TestCase`.

### Key Methods

#### Authentication & Permissions

```php
// Create an authenticated user with warehouse permissions
$user = $this->createAuthenticatedUser('manager'); // 'manager', 'operator', 'viewer'

// Manually grant warehouse permissions to a user
$this->grantPermissionToUser($user, 'warehouse.view');

// Assign user to a specific warehouse with pivot permissions
$this->assignUserToWarehouse(
    $user,
    $warehouse,
    $canTransfer = true,  // can_transfer flag
    $canInventory = true, // can_inventory flag
    $isDefault = true     // is_default flag
);
```

#### Warehouse Structure Creation

```php
// Create a complete warehouse with all relations (floors, locations, sections, slots)
$warehouse = $this->createWarehouseWithRelations([
    'floors_count' => 2,
    'locations_per_floor' => 3,
    'sections_per_location' => 2,
    'slots_per_section' => 4,
]);

// Create a warehouse with detailed structure array
$structure = $this->createWarehouseStructure([
    'floors' => 2,
    'locations_per_floor' => 3,
    'sections_per_location' => 2,
    'slots_per_section' => 4,
]);

// Return: [
//     'warehouse' => Warehouse,
//     'floors' => [
//         [
//             'floor' => WarehouseFloor,
//             'locations' => [
//                 [
//                     'location' => WarehouseLocation,
//                     'sections' => [
//                         [
//                             'section' => WarehouseLocationSection,
//                             'slots' => Collection<WarehouseInventorySlot>
//                         ]
//                     ]
//                 ]
//             ]
//         ]
//     ]
// ]
```

#### Inventory Slot Management

```php
// Create an occupied slot with a product
$slot = $this->createOccupiedSlot($section, $quantity = 50);

// Create multiple occupied slots
$slots = $this->createMultipleOccupiedSlots($section, $count = 5, $minQuantity = 10, $maxQuantity = 100);

// Create an empty slot (no product)
$slot = $this->createEmptySlot($section);

// Verify warehouse structure integrity
$isValid = $this->verifyWarehouseStructure($warehouse);
```

## Factories

All factories are located in `/database/factories/Warehouse/` and follow Laravel factory conventions.

### WarehouseFactory

Creates `Warehouse` entities with realistic data.

**Default behavior:**
- Generates UUID and code (WH-XXX)
- Creates 2-3 related floors automatically

**States:**

```php
// Mark warehouse as unavailable
Warehouse::factory()->unavailable()->create()

// Set warehouse location
Warehouse::factory()->inLocation('Madrid')->create()
```

**Usage:**

```php
$warehouse = Warehouse::factory()->create();
$warehouse = Warehouse::factory()->count(5)->create();
$warehouse = Warehouse::factory()->unavailable()->create();
```

### WarehouseFloorFactory

Creates `WarehouseFloor` entities for warehouse levels.

**Default behavior:**
- Generates UUID and code (FL-XX)
- Creates floor ordinal names (1st, 2nd, 3rd, etc.)
- Creates 3-5 related locations automatically

**States:**

```php
// Mark floor as unavailable
WarehouseFloor::factory()->unavailable()->create()

// Set specific floor level
WarehouseFloor::factory()->withLevel(3)->create()
```

**Usage:**

```php
$floor = WarehouseFloor::factory()
    ->for($warehouse)
    ->withLevel(2)
    ->create();
```

### WarehouseLocationStyleFactory

Creates `WarehouseLocationStyle` entities (shelf/stand configuration templates).

**Default behavior:**
- Randomly generates row, island, or wall type
- Automatically sets appropriate faces based on type
- Generates realistic width/height dimensions

**States:**

```php
// Row-type shelf (front/back faces)
WarehouseLocationStyle::factory()->rowType()->create()

// Island-type shelf (4-face access)
WarehouseLocationStyle::factory()->islandType()->create()

// Wall-type shelf (1-face front)
WarehouseLocationStyle::factory()->wallType()->create()

// Mark as unavailable
WarehouseLocationStyle::factory()->unavailable()->create()
```

**Usage:**

```php
$style = WarehouseLocationStyle::factory()->islandType()->create();
$style = WarehouseLocationStyle::factory()->count(3)->create();
```

### WarehouseLocationFactory

Creates `WarehouseLocation` entities (physical shelving units).

**Default behavior:**
- Generates UUID and code (LOC-XXX)
- Automatically assigns a location style
- Creates 2-3 related sections per location

**States:**

```php
// Mark location as unavailable
WarehouseLocation::factory()->unavailable()->create()

// Set custom visual properties
WarehouseLocation::factory()->withCustomVisuals()->create()

// Set specific dimensions
WarehouseLocation::factory()->withDimensions($levels = 3, $totalSections = 5)->create()
```

**Usage:**

```php
$location = WarehouseLocation::factory()
    ->for($warehouse)
    ->for($floor)
    ->for($style)
    ->withCustomVisuals()
    ->create();
```

### WarehouseLocationSectionFactory

Creates `WarehouseLocationSection` entities (shelving faces/levels).

**Default behavior:**
- Generates UUID and code (SEC-XXX-{Face})
- Generates EAN13 barcode
- Creates 3-5 related inventory slots automatically

**States:**

```php
// Mark section as unavailable
WarehouseLocationSection::factory()->unavailable()->create()

// Set specific level (1-5)
WarehouseLocationSection::factory()->withLevel(2)->create()

// Set specific face (A, B, C, D)
WarehouseLocationSection::factory()->withFace('A')->create()

// Add weight restrictions
WarehouseLocationSection::factory()->withWeightLimit(250)->create()

// Add quantity limits
WarehouseLocationSection::factory()->withQuantityLimit(500)->create()
```

**Usage:**

```php
$section = WarehouseLocationSection::factory()
    ->for($location)
    ->withLevel(1)
    ->withFace('A')
    ->withQuantityLimit(1000)
    ->create();
```

### WarehouseInventorySlotFactory

Creates `WarehouseInventorySlot` entities (individual storage positions).

**Default behavior:**
- Generates UUID
- 50% chance of being empty (no product)
- Randomly assigns products and quantities
- Tracks last movement timestamp

**States:**

```php
// Create occupied slot with product
WarehouseInventorySlot::factory()->occupied()->create()

// Create empty slot (no product, quantity = 0)
WarehouseInventorySlot::factory()->empty()->create()

// Assign specific product
WarehouseInventorySlot::factory()->withProduct($product)->create()

// Create low stock slot (1-10 units)
WarehouseInventorySlot::factory()->lowStock(10)->create()

// Create overstocked slot (200+ units)
WarehouseInventorySlot::factory()->overstock(200)->create()

// Recently updated
WarehouseInventorySlot::factory()->recentlyUpdated()->create()

// Stale data (not updated in months)
WarehouseInventorySlot::factory()->staleData()->create()

// Specific quantity
WarehouseInventorySlot::factory()->withQuantity(75)->create()

// Conflicting kardex (accounting vs physical)
WarehouseInventorySlot::factory()->withConflictingKardex()->create()
```

**Usage:**

```php
$slot = WarehouseInventorySlot::factory()
    ->for($section)
    ->occupied()
    ->recentlyUpdated()
    ->create();

$slots = WarehouseInventorySlot::factory()
    ->for($section)
    ->count(10)
    ->occupied()
    ->create();
```

## Example Test Cases

### Basic Feature Test

```php
<?php

namespace Modules\Warehouse\Tests\Feature;

use Modules\Warehouse\Tests\WarehouseTestCase;

class WarehouseViewTest extends WarehouseTestCase
{
    public function test_manager_can_view_warehouses(): void
    {
        $user = $this->createAuthenticatedUser('manager');
        $warehouse = $this->createWarehouseWithRelations();

        $this->assignUserToWarehouse($user, $warehouse);

        $this->actingAs($user)
            ->get(route('manager.warehouses.index'))
            ->assertOk()
            ->assertSee($warehouse->name);
    }
}
```

### Test with Complete Warehouse Structure

```php
<?php

namespace Modules\Warehouse\Tests\Feature;

use Modules\Warehouse\Tests\WarehouseTestCase;

class InventoryTransferTest extends WarehouseTestCase
{
    public function test_can_transfer_inventory_between_sections(): void
    {
        $user = $this->createAuthenticatedUser('operator');
        $structure = $this->createWarehouseStructure([
            'floors' => 1,
            'locations_per_floor' => 2,
            'sections_per_location' => 2,
        ]);

        $warehouse = $structure['warehouse'];
        $sourceSection = $structure['floors'][0]['locations'][0]['sections'][0]['section'];
        $targetSection = $structure['floors'][0]['locations'][1]['sections'][0]['section'];

        $slot = $this->createOccupiedSlot($sourceSection, 50);

        $this->assignUserToWarehouse($user, $warehouse, canTransfer: true);

        $this->actingAs($user)
            ->post(route('manager.warehouses.inventory.transfer'), [
                'from_section_id' => $sourceSection->id,
                'to_section_id' => $targetSection->id,
                'quantity' => 25,
            ])
            ->assertOk();
    }
}
```

### Test Multiple Occupied Slots

```php
<?php

namespace Modules\Warehouse\Tests\Feature;

use Modules\Warehouse\Tests\WarehouseTestCase;

class InventoryReportTest extends WarehouseTestCase
{
    public function test_occupancy_report_calculates_correctly(): void
    {
        $structure = $this->createWarehouseStructure();
        $section = $structure['floors'][0]['locations'][0]['sections'][0]['section'];

        $slots = $this->createMultipleOccupiedSlots($section, count: 5);

        $occupancyPercentage = $section->getOccupancyPercentage();

        $this->assertGreaterThan(0, $occupancyPercentage);
        $this->assertLessThanOrEqual(100, $occupancyPercentage);
    }
}
```

## Database State Management

The test infrastructure uses Laravel's transaction-based rollback by default. Each test:

1. Starts a database transaction
2. Executes test code
3. Rolls back all changes (no test data persists)

This is configured in `phpunit.xml` via `DB_CONNECTION=testing` and transactional test traits.

## Permission Hierarchy

Default roles and their permissions:

**Manager:**
- warehouse.view
- warehouse.create
- warehouse.edit
- warehouse.delete
- warehouse.transfer
- warehouse.inventory

**Operator:**
- warehouse.view
- warehouse.transfer
- warehouse.inventory

**Viewer:**
- warehouse.view

## Tips & Best Practices

1. **Always extend WarehouseTestCase** - Don't use the generic TestCase
2. **Use test helpers** - `createAuthenticatedUser()`, `createWarehouseWithRelations()`, etc.
3. **Assign users to warehouses** - Use `assignUserToWarehouse()` to set pivot permissions
4. **Test with realistic data** - Factories create realistic warehouse structures
5. **Use state methods** - Apply `.occupied()`, `.lowStock()`, etc. to factories
6. **Verify structure** - Use `verifyWarehouseStructure()` before complex tests
7. **Isolate concerns** - One test should focus on one feature/behavior

## Common Issues

### Factory Not Found

If you get "Class not found" for factories:
```bash
composer dump-autoload
```

### Test Database Not Isolated

If tests affect each other, ensure `DB_CONNECTION=testing` is set in `phpunit.xml`.

### Relations Not Loading

Always use eager loading in tests to avoid N+1 queries:
```php
$warehouse = $warehouse->load('floors.locations.sections.slots');
```

## Running Tests

```bash
# Run all warehouse tests
php artisan test --filter=Warehouse

# Run specific test file
php artisan test tests/Feature/WarehouseTest.php

# Run with coverage
php artisan test --coverage --filter=Warehouse

# Run only unit tests
php artisan test --filter=Warehouse tests/Unit
```
