<?php

namespace Database\Factories\Warehouse;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Warehouse\Entities\WarehouseInventorySlot;
use Modules\Warehouse\Entities\WarehouseLocationSection;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Warehouse\Entities\WarehouseLocationSection>
 */
class WarehouseLocationSectionFactory extends Factory
{
    protected $model = WarehouseLocationSection::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $level = $this->faker->numberBetween(1, 5);
        $face = $this->faker->randomElement(['A', 'B', 'C', 'D']);

        return [
            'uid' => Str::uuid(),
            'code' => 'SEC-'.strtoupper($this->faker->lexify('???')).'-'.$face,
            'barcode' => $this->faker->ean13(),
            'level' => $level,
            'face' => $face,
            'weight_max' => $this->faker->optional(0.7)->randomFloat(2, 50, 500),
            'max_quantity' => $this->faker->optional(0.7)->numberBetween(100, 1000),
            'available' => true,
            'notes' => $this->faker->optional(0.5)->sentence(),
        ];
    }

    /**
     * Indicate that the section should be unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'available' => false,
        ]);
    }

    /**
     * Indicate that the section should have a specific level.
     */
    public function withLevel(int $level): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => $level,
        ]);
    }

    /**
     * Indicate that the section should have a specific face.
     */
    public function withFace(string $face): static
    {
        return $this->state(fn (array $attributes) => [
            'face' => $face,
        ]);
    }

    /**
     * Indicate that the section has weight restrictions.
     */
    public function withWeightLimit(float $maxWeight = 250): static
    {
        return $this->state(fn (array $attributes) => [
            'weight_max' => $maxWeight,
        ]);
    }

    /**
     * Indicate that the section has quantity limits.
     */
    public function withQuantityLimit(int $maxQuantity = 500): static
    {
        return $this->state(fn (array $attributes) => [
            'max_quantity' => $maxQuantity,
        ]);
    }

    /**
     * Configure the factory to create related inventory slots.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (WarehouseLocationSection $section) {
            // Create 3-5 inventory slots per section
            $slotCount = $this->faker->numberBetween(3, 5);
            for ($i = 1; $i <= $slotCount; $i++) {
                WarehouseInventorySlot::factory()
                    ->for($section)
                    ->create(['position' => $i]);
            }
        });
    }
}
