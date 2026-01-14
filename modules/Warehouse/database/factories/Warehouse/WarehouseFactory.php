<?php

namespace Database\Factories\Warehouse;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseFloor;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Warehouse\Entities\Warehouse>
 */
class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(2, true);
        $code = 'WH-' . strtoupper($this->faker->lexify('???'));

        return [
            'uid' => Str::uuid(),
            'code' => $code,
            'name' => ucfirst($name),
            'description' => $this->faker->sentence(),
            'available' => true,
        ];
    }

    /**
     * Indicate that the warehouse should be unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'available' => false,
        ]);
    }

    /**
     * Indicate that the warehouse should have a specific location.
     */
    public function inLocation(string $location): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => "{$location} Warehouse",
        ]);
    }

    /**
     * Configure the factory to create related floors.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Warehouse $warehouse) {
            // Create 2-3 floors by default
            $floorCount = $this->faker->numberBetween(2, 3);
            for ($i = 1; $i <= $floorCount; $i++) {
                WarehouseFloor::factory()
                    ->for($warehouse)
                    ->create(['level' => $i]);
            }
        });
    }
}
