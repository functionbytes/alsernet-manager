<?php

namespace Database\Factories\Warehouse;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Warehouse\Entities\WarehouseLocation;
use Modules\Warehouse\Entities\WarehouseLocationSection;
use Modules\Warehouse\Entities\WarehouseLocationStyle;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Warehouse\Entities\WarehouseLocation>
 */
class WarehouseLocationFactory extends Factory
{
    protected $model = WarehouseLocation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ensure a style exists
        $style = WarehouseLocationStyle::first() ?? WarehouseLocationStyle::factory()->create();

        return [
            'uid' => Str::uuid(),
            'code' => 'LOC-'.strtoupper($this->faker->lexify('???')),
            'style_id' => $style->id,
            'position_x' => $this->faker->randomFloat(1, 0, 10),
            'position_y' => $this->faker->randomFloat(1, 0, 10),
            'total_levels' => $this->faker->numberBetween(2, 5),
            'available' => true,
            'notes' => $this->faker->optional(0.6)->sentence(),
        ];
    }

    /**
     * Indicate that the location should be unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'available' => false,
        ]);
    }

    /**
     * Indicate that the location should use custom visual properties.
     */
    public function withCustomVisuals(): static
    {
        return $this->state(fn (array $attributes) => [
            'use_custom_visual' => true,
            'visual_width_m' => $this->faker->randomFloat(1, 1.0, 3.0),
            'visual_height_m' => $this->faker->randomFloat(1, 1.5, 4.0),
            'visual_position_x' => $this->faker->randomFloat(1, 0, 20),
            'visual_position_y' => $this->faker->randomFloat(1, 0, 20),
            'visual_rotation' => $this->faker->randomElement([0, 90, 180, 270]),
        ]);
    }

    /**
     * Indicate that the location should have specific dimensions.
     */
    public function withDimensions(int $levels = 3, int $totalSections = 5): static
    {
        return $this->state(fn (array $attributes) => [
            'total_levels' => $levels,
        ]);
    }

    /**
     * Configure the factory to create related sections.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (WarehouseLocation $location) {
            // Create 2-3 sections per location
            $sectionCount = $this->faker->numberBetween(2, 3);
            for ($i = 1; $i <= $sectionCount; $i++) {
                WarehouseLocationSection::factory()
                    ->for($location)
                    ->create(['level' => $i]);
            }
        });
    }
}
