<?php

namespace Modules\Warehouse\Tests;

use App\Models\User;
use Illuminate\Support\Str;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseFloor;
use Modules\Warehouse\Entities\WarehouseInventorySlot;
use Modules\Warehouse\Entities\WarehouseLocation;
use Modules\Warehouse\Entities\WarehouseLocationSection;
use Modules\Warehouse\Entities\WarehouseLocationStyle;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase as BaseTestCase;

abstract class WarehouseTestCase extends BaseTestCase
{
    /**
     * Create an authenticated user with warehouse permissions.
     *
     * @param string $role The role to assign ('manager', 'operator', 'viewer')
     * @return User
     */
    protected function createAuthenticatedUser(string $role = 'manager'): User
    {
        $user = User::factory()->create();

        // Create role if it doesn't exist
        if (!Role::where('name', $role)->exists()) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
        }

        // Assign role to user
        $user->assignRole($role);

        // Grant warehouse permissions based on role
        $this->grantPermissionToUser($user, $role);

        return $user;
    }

    /**
     * Grant warehouse permissions to a user.
     *
     * @param User $user
     * @param string $permission The permission or role to grant
     * @return void
     */
    protected function grantPermissionToUser(User $user, string $permission): void
    {
        $permissions = match ($permission) {
            'manager' => [
                'warehouse.view',
                'warehouse.create',
                'warehouse.edit',
                'warehouse.delete',
                'warehouse.transfer',
                'warehouse.inventory',
            ],
            'operator' => [
                'warehouse.view',
                'warehouse.transfer',
                'warehouse.inventory',
            ],
            'viewer' => [
                'warehouse.view',
            ],
            default => [$permission],
        };

        foreach ($permissions as $perm) {
            if (!Permission::where('name', $perm)->exists()) {
                Permission::create(['name' => $perm, 'guard_name' => 'web']);
            }
            $user->givePermissionTo($perm);
        }
    }

    /**
     * Assign a user to a warehouse with permissions.
     *
     * @param User $user
     * @param Warehouse $warehouse
     * @param bool $canTransfer
     * @param bool $canInventory
     * @param bool $isDefault
     * @return void
     */
    protected function assignUserToWarehouse(
        User $user,
        Warehouse $warehouse,
        bool $canTransfer = true,
        bool $canInventory = true,
        bool $isDefault = true
    ): void {
        $warehouse->users()->attach($user, [
            'is_default' => $isDefault,
            'can_transfer' => $canTransfer,
            'can_inventory' => $canInventory,
        ]);
    }

    /**
     * Create a complete warehouse structure with related entities.
     *
     * @param array $warehouseData Custom warehouse data
     * @return Warehouse
     */
    protected function createWarehouseWithRelations(array $warehouseData = []): Warehouse
    {
        $warehouse = Warehouse::factory()
            ->create($warehouseData);

        // Create location style
        $style = WarehouseLocationStyle::factory()->create();

        // Create floors (typically 2-3 floors)
        $floorsCount = $warehouseData['floors_count'] ?? 2;
        for ($floorLevel = 1; $floorLevel <= $floorsCount; $floorLevel++) {
            $floor = WarehouseFloor::factory()
                ->for($warehouse)
                ->create([
                    'level' => $floorLevel,
                ]);

            // Create locations per floor (typically 3-5)
            $locationsCount = $warehouseData['locations_per_floor'] ?? 3;
            for ($locIndex = 1; $locIndex <= $locationsCount; $locIndex++) {
                $location = WarehouseLocation::factory()
                    ->for($warehouse)
                    ->for($floor)
                    ->for($style)
                    ->create([
                        'position_x' => $locIndex * 2,
                        'position_y' => $floorLevel * 2,
                    ]);

                // Create sections per location (typically 2-3 sections)
                $sectionsCount = $warehouseData['sections_per_location'] ?? 2;
                for ($secIndex = 1; $secIndex <= $sectionsCount; $secIndex++) {
                    $section = WarehouseLocationSection::factory()
                        ->for($location)
                        ->create([
                            'level' => $secIndex,
                        ]);

                    // Create inventory slots per section (typically 3-5)
                    $slotsCount = $warehouseData['slots_per_section'] ?? 4;
                    for ($slotIndex = 1; $slotIndex <= $slotsCount; $slotIndex++) {
                        WarehouseInventorySlot::factory()
                            ->for($section)
                            ->create([
                                'position' => $slotIndex,
                            ]);
                    }
                }
            }
        }

        return $warehouse->refresh();
    }

    /**
     * Create a warehouse with specific counts and return detailed structure.
     *
     * @param array $counts Configuration for structure counts
     * @return array
     */
    protected function createWarehouseStructure(array $counts = []): array
    {
        $config = array_merge([
            'floors' => 2,
            'locations_per_floor' => 3,
            'sections_per_location' => 2,
            'slots_per_section' => 4,
        ], $counts);

        $warehouse = $this->createWarehouseWithRelations($config);

        $structure = [
            'warehouse' => $warehouse,
            'floors' => [],
        ];

        foreach ($warehouse->floors as $floor) {
            $floorData = [
                'floor' => $floor,
                'locations' => [],
            ];

            foreach ($floor->locations as $location) {
                $locationData = [
                    'location' => $location,
                    'sections' => [],
                ];

                foreach ($location->sections as $section) {
                    $sectionData = [
                        'section' => $section,
                        'slots' => $section->slots,
                    ];

                    $locationData['sections'][] = $sectionData;
                }

                $floorData['locations'][] = $locationData;
            }

            $structure['floors'][] = $floorData;
        }

        return $structure;
    }

    /**
     * Create an occupied inventory slot with a product.
     *
     * @param WarehouseLocationSection $section
     * @param int $quantity
     * @return WarehouseInventorySlot
     */
    protected function createOccupiedSlot(WarehouseLocationSection $section, int $quantity = 50): WarehouseInventorySlot
    {
        return WarehouseInventorySlot::factory()
            ->for($section)
            ->occupied()
            ->create([
                'quantity' => $quantity,
            ]);
    }

    /**
     * Create multiple occupied slots with a product.
     *
     * @param WarehouseLocationSection $section
     * @param int $count
     * @param int $minQuantity
     * @param int $maxQuantity
     * @return array
     */
    protected function createMultipleOccupiedSlots(
        WarehouseLocationSection $section,
        int $count = 5,
        int $minQuantity = 10,
        int $maxQuantity = 100
    ): array {
        $slots = [];
        for ($i = 0; $i < $count; $i++) {
            $slots[] = $this->createOccupiedSlot($section, random_int($minQuantity, $maxQuantity));
        }

        return $slots;
    }

    /**
     * Create an empty inventory slot.
     *
     * @param WarehouseLocationSection $section
     * @return WarehouseInventorySlot
     */
    protected function createEmptySlot(WarehouseLocationSection $section): WarehouseInventorySlot
    {
        return WarehouseInventorySlot::factory()
            ->for($section)
            ->empty()
            ->create();
    }

    /**
     * Verify warehouse structure integrity.
     *
     * @param Warehouse $warehouse
     * @return bool
     */
    protected function verifyWarehouseStructure(Warehouse $warehouse): bool
    {
        return $warehouse->floors()->count() > 0
            && $warehouse->locations()->count() > 0;
    }
}
