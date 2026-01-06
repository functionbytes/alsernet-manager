<?php

namespace Database\Factories\Warehouse;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Warehouse\Entities\WarehouseLocationStyle;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Warehouse\Entities\WarehouseLocationStyle>
 */
class WarehouseLocationStyleFactory extends Factory
{
    protected $model = WarehouseLocationStyle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement([
            WarehouseLocationStyle::TYPE_ROW,
            WarehouseLocationStyle::TYPE_ISLAND,
            WarehouseLocationStyle::TYPE_WALL,
        ]);

        $faces = WarehouseLocationStyle::getFacesByType($type);

        return [
            'uid' => Str::uuid(),
            'code' => 'STYLE-'.strtoupper($this->faker->lexify('???')),
            'name' => $this->getStyleName($type),
            'description' => $this->faker->optional(0.8)->sentence(),
            'type' => $type,
            'faces' => $faces,
            'width' => $this->faker->randomFloat(1, 1.0, 2.5),
            'height' => $this->faker->randomFloat(1, 1.5, 3.0),
            'default_levels' => $this->faker->numberBetween(2, 5),
            'default_sections' => $this->faker->numberBetween(2, 4),
            'available' => true,
        ];
    }

    /**
     * Indicate that the style should be of row type.
     */
    public function rowType(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => WarehouseLocationStyle::TYPE_ROW,
            'faces' => [
                WarehouseLocationStyle::FACE_FRONT,
                WarehouseLocationStyle::FACE_BACK,
            ],
            'name' => 'Row Shelf',
        ]);
    }

    /**
     * Indicate that the style should be of island type.
     */
    public function islandType(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => WarehouseLocationStyle::TYPE_ISLAND,
            'faces' => [
                WarehouseLocationStyle::FACE_FRONT,
                WarehouseLocationStyle::FACE_BACK,
                WarehouseLocationStyle::FACE_LEFT,
                WarehouseLocationStyle::FACE_RIGHT,
            ],
            'name' => 'Island Shelf',
        ]);
    }

    /**
     * Indicate that the style should be of wall type.
     */
    public function wallType(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => WarehouseLocationStyle::TYPE_WALL,
            'faces' => [WarehouseLocationStyle::FACE_FRONT],
            'name' => 'Wall Shelf',
        ]);
    }

    /**
     * Indicate that the style should be unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'available' => false,
        ]);
    }

    /**
     * Get a friendly name for the style type.
     */
    private function getStyleName(string $type): string
    {
        return match ($type) {
            WarehouseLocationStyle::TYPE_ROW => 'Linear Row Shelf - '.$this->faker->lexify('???'),
            WarehouseLocationStyle::TYPE_ISLAND => 'Island Shelf - '.$this->faker->lexify('???'),
            WarehouseLocationStyle::TYPE_WALL => 'Wall Mounted Shelf - '.$this->faker->lexify('???'),
            default => 'Generic Shelf - '.$this->faker->lexify('???'),
        };
    }
}
