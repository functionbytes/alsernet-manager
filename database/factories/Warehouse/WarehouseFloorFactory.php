<?php

namespace Database\Factories\Warehouse;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Warehouse\Entities\WarehouseFloor;
use Modules\Warehouse\Entities\WarehouseLocation;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Warehouse\Entities\WarehouseFloor>
 */
class WarehouseFloorFactory extends Factory
{
    protected $model = WarehouseFloor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $level = $this->faker->numberBetween(1, 10);

        return [
            'uid' => Str::uuid(),
            'code' => 'FL-' . str_pad($level, 2, '0', STR_PAD_LEFT),
            'name' => 'Floor ' . $this->getFloorOrdinal($level),
            'description' => $this->faker->optional(0.7)->sentence(),
            'level' => $level,
            'available' => true,
        ];
    }

    /**
     * Indicate that the floor should be unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'available' => false,
        ]);
    }

    /**
     * Indicate that the floor should have a specific level.
     */
    public function withLevel(int $level): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => $level,
            'code' => 'FL-' . str_pad($level, 2, '0', STR_PAD_LEFT),
            'name' => 'Floor ' . $this->getFloorOrdinal($level),
        ]);
    }

    /**
     * Configure the factory to create related locations.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (WarehouseFloor $floor) {
            // Create 3-5 locations per floor
            $locationCount = $this->faker->numberBetween(3, 5);
            for ($i = 1; $i <= $locationCount; $i++) {
                WarehouseLocation::factory()
                    ->for($floor->warehouse)
                    ->for($floor)
                    ->create();
            }
        });
    }

    /**
     * Get ordinal suffix for floor level (1st, 2nd, 3rd, etc.).
     */
    private function getFloorOrdinal(int $number): string
    {
        $suffix = match (true) {
            $number % 100 === 11, $number % 100 === 12, $number % 100 === 13 => 'th',
            $number % 10 === 1 => 'st',
            $number % 10 === 2 => 'nd',
            $number % 10 === 3 => 'rd',
            default => 'th',
        };

        return $number . $suffix;
    }
}
