<?php

namespace Database\Factories;

use App\Models\Warehouse\WarehouseShop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Warehouse\WarehouseShop>
 */
class WarehouseShopFactory extends Factory
{
    protected $model = WarehouseShop::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->words(3, true);
        $slug = Str::slug($title);

        return [
            'uid' => Str::uuid(),
            'title' => ucfirst($title),
            'slug' => $slug,
            'reference' => strtoupper($this->faker->lexify('???-###')),
            'barcode' => $this->faker->ean13(),
            'stock' => $this->faker->numberBetween(0, 1000),
            'available' => $this->faker->boolean(90),
        ];
    }

    /**
     * Indicate that the shop should be unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'available' => false,
        ]);
    }

    /**
     * Indicate that the shop should have low stock.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $this->faker->numberBetween(1, 10),
        ]);
    }

    /**
     * Indicate that the shop should be out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
            'available' => false,
        ]);
    }
}
