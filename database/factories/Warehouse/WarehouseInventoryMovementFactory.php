<?php

namespace Database\Factories\Warehouse;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseInventoryMovement;
use Modules\Warehouse\Entities\WarehouseInventorySlot;
use Modules\Warehouse\Models\Product\Product;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Warehouse\Entities\WarehouseInventoryMovement>
 */
class WarehouseInventoryMovementFactory extends Factory
{
    protected $model = WarehouseInventoryMovement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $movementType = $this->faker->randomElement([
            WarehouseInventoryMovement::TYPE_ADD,
            WarehouseInventoryMovement::TYPE_SUBTRACT,
            WarehouseInventoryMovement::TYPE_MOVE,
            WarehouseInventoryMovement::TYPE_COUNT,
        ]);

        $fromQuantity = $this->faker->numberBetween(0, 100);
        $delta = $this->calculateDelta($movementType);
        $toQuantity = max(0, $fromQuantity + $delta);

        $fromWeight = $fromQuantity * $this->faker->randomFloat(2, 0.5, 5.0);
        $toWeight = $toQuantity * $this->faker->randomFloat(2, 0.5, 5.0);

        return [
            'uid' => Str::uuid(),
            'slot_id' => WarehouseInventorySlot::factory(),
            'product_id' => Product::factory(),
            'movement_type' => $movementType,
            'from_quantity' => $fromQuantity,
            'to_quantity' => $toQuantity,
            'quantity_delta' => $delta,
            'from_weight' => $fromWeight,
            'to_weight' => $toWeight,
            'weight_delta' => $toWeight - $fromWeight,
            'reason' => $this->getReasonForType($movementType),
            'warehouse_id' => Warehouse::factory(),
            'warehouse_location_item_id' => null,
            'user_id' => User::factory(),
            'notes' => $this->faker->optional(0.3)->sentence(),
            'recorded_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Calculate delta based on movement type.
     */
    private function calculateDelta(string $type): int
    {
        return match ($type) {
            WarehouseInventoryMovement::TYPE_ADD => $this->faker->numberBetween(1, 50),
            WarehouseInventoryMovement::TYPE_SUBTRACT => -$this->faker->numberBetween(1, 30),
            WarehouseInventoryMovement::TYPE_CLEAR => -$this->faker->numberBetween(50, 100),
            WarehouseInventoryMovement::TYPE_MOVE => $this->faker->numberBetween(-20, 20),
            WarehouseInventoryMovement::TYPE_COUNT => $this->faker->numberBetween(-10, 10),
            default => 0,
        };
    }

    /**
     * Get reason based on movement type.
     */
    private function getReasonForType(string $type): string
    {
        return match ($type) {
            WarehouseInventoryMovement::TYPE_ADD => 'Recepción de mercancía',
            WarehouseInventoryMovement::TYPE_SUBTRACT => 'Venta de producto',
            WarehouseInventoryMovement::TYPE_CLEAR => 'Limpieza de ubicación',
            WarehouseInventoryMovement::TYPE_MOVE => 'Reubicación interna',
            WarehouseInventoryMovement::TYPE_COUNT => 'Ajuste por inventario',
            default => 'Movimiento general',
        };
    }

    /**
     * Indicate that the movement should be an addition.
     */
    public function add(): static
    {
        $delta = $this->faker->numberBetween(1, 50);

        return $this->state(function (array $attributes) use ($delta) {
            $toQuantity = $attributes['from_quantity'] + $delta;
            $toWeight = $toQuantity * $this->faker->randomFloat(2, 0.5, 5.0);

            return [
                'movement_type' => WarehouseInventoryMovement::TYPE_ADD,
                'to_quantity' => $toQuantity,
                'quantity_delta' => $delta,
                'to_weight' => $toWeight,
                'weight_delta' => $toWeight - $attributes['from_weight'],
                'reason' => 'Recepción de mercancía',
            ];
        });
    }

    /**
     * Indicate that the movement should be a subtraction.
     */
    public function subtract(): static
    {
        $delta = -$this->faker->numberBetween(1, 30);

        return $this->state(function (array $attributes) use ($delta) {
            $toQuantity = max(0, $attributes['from_quantity'] + $delta);
            $toWeight = $toQuantity * $this->faker->randomFloat(2, 0.5, 5.0);

            return [
                'movement_type' => WarehouseInventoryMovement::TYPE_SUBTRACT,
                'to_quantity' => $toQuantity,
                'quantity_delta' => $delta,
                'to_weight' => $toWeight,
                'weight_delta' => $toWeight - $attributes['from_weight'],
                'reason' => 'Venta de producto',
            ];
        });
    }

    /**
     * Indicate that the movement should be a transfer.
     */
    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'movement_type' => WarehouseInventoryMovement::TYPE_MOVE,
            'reason' => 'Reubicación interna',
        ]);
    }

    /**
     * Indicate that the movement should be an inventory count adjustment.
     */
    public function count(): static
    {
        $delta = $this->faker->numberBetween(-10, 10);

        return $this->state(function (array $attributes) use ($delta) {
            $toQuantity = max(0, $attributes['from_quantity'] + $delta);
            $toWeight = $toQuantity * $this->faker->randomFloat(2, 0.5, 5.0);

            return [
                'movement_type' => WarehouseInventoryMovement::TYPE_COUNT,
                'to_quantity' => $toQuantity,
                'quantity_delta' => $delta,
                'to_weight' => $toWeight,
                'weight_delta' => $toWeight - $attributes['from_weight'],
                'reason' => 'Ajuste por inventario',
            ];
        });
    }

    /**
     * Indicate that the movement should clear a slot.
     */
    public function clear(): static
    {
        return $this->state(function (array $attributes) {
            $delta = -$attributes['from_quantity'];

            return [
                'movement_type' => WarehouseInventoryMovement::TYPE_CLEAR,
                'to_quantity' => 0,
                'quantity_delta' => $delta,
                'to_weight' => 0,
                'weight_delta' => -$attributes['from_weight'],
                'reason' => 'Limpieza de ubicación',
            ];
        });
    }

    /**
     * Indicate that the movement should be for a specific slot.
     */
    public function forSlot(WarehouseInventorySlot $slot): static
    {
        return $this->state(fn (array $attributes) => [
            'slot_id' => $slot->id,
        ]);
    }

    /**
     * Indicate that the movement should be for a specific product.
     */
    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    /**
     * Indicate that the movement should be performed by a specific user.
     */
    public function byUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the movement should be recent.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'recorded_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
