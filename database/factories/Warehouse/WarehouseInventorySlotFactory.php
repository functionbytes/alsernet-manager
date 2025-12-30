<?php

namespace Database\Factories\Warehouse;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Warehouse\Entities\WarehouseInventorySlot;
use Modules\Warehouse\Models\Product\Product;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Warehouse\Entities\WarehouseInventorySlot>
 */
class WarehouseInventorySlotFactory extends Factory
{
    protected $model = WarehouseInventorySlot::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->randomElement([0, 0, 0, 25, 50, 75, 100]); // 50% chance of empty

        return [
            'uid' => Str::uuid(),
            'product_id' => $quantity > 0 ? Product::inRandomOrder()->first()?->id : null,
            'quantity' => $quantity,
            'kardex' => $this->faker->optional(0.5)->numberBetween(-50, 150),
            'is_occupied' => $quantity > 0,
            'last_movement' => $quantity > 0 ? $this->faker->dateTimeThisMonth() : null,
        ];
    }

    /**
     * Indicate that the slot should be occupied with a product.
     */
    public function occupied(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::inRandomOrder()->first()?->id ?? Product::factory()->create()->id,
            'quantity' => $this->faker->numberBetween(1, 150),
            'is_occupied' => true,
            'last_movement' => $this->faker->dateTimeThisMonth(),
        ]);
    }

    /**
     * Indicate that the slot should be empty.
     */
    public function empty(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => null,
            'quantity' => 0,
            'is_occupied' => false,
            'last_movement' => null,
        ]);
    }

    /**
     * Indicate that the slot should have a specific product.
     */
    public function withProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'quantity' => $this->faker->numberBetween(10, 150),
            'is_occupied' => true,
            'last_movement' => $this->faker->dateTimeThisMonth(),
        ]);
    }

    /**
     * Indicate that the slot should have low stock.
     */
    public function lowStock(int $threshold = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::inRandomOrder()->first()?->id ?? Product::factory()->create()->id,
            'quantity' => $this->faker->numberBetween(1, $threshold),
            'is_occupied' => true,
            'last_movement' => $this->faker->dateTimeThisMonth(),
        ]);
    }

    /**
     * Indicate that the slot should be overstocked.
     */
    public function overstock(int $minQuantity = 200): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::inRandomOrder()->first()?->id ?? Product::factory()->create()->id,
            'quantity' => $this->faker->numberBetween($minQuantity, 500),
            'is_occupied' => true,
            'last_movement' => $this->faker->dateTimeThisMonth(),
        ]);
    }

    /**
     * Indicate that the slot was recently updated.
     */
    public function recentlyUpdated(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_movement' => $this->faker->dateTimeThisWeek(),
        ]);
    }

    /**
     * Indicate that the slot has not been updated recently.
     */
    public function staleData(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_movement' => $this->faker->dateTimeBetween('-6 months', '-1 month'),
        ]);
    }

    /**
     * Indicate that the slot should have a specific quantity.
     */
    public function withQuantity(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity,
            'is_occupied' => $quantity > 0,
            'product_id' => $quantity > 0 ? (Product::inRandomOrder()->first()?->id ?? Product::factory()->create()->id) : null,
            'last_movement' => $quantity > 0 ? $this->faker->dateTimeThisMonth() : null,
        ]);
    }

    /**
     * Indicate that the slot should have conflicting kardex.
     */
    public function withConflictingKardex(): static
    {
        $quantity = $this->faker->numberBetween(50, 100);

        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity,
            'kardex' => $quantity + $this->faker->numberBetween(10, 50), // kardex higher than quantity
            'is_occupied' => true,
            'product_id' => Product::inRandomOrder()->first()?->id ?? Product::factory()->create()->id,
        ]);
    }
}
