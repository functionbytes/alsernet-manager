<?php

namespace Database\Factories\Warehouse;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Warehouse\Entities\WarehouseInventoryOperation;
use Modules\Warehouse\Entities\WarehouseInventorySlot;
use Modules\Warehouse\Entities\WarehouseOperationItem;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Warehouse\Entities\WarehouseOperationItem>
 */
class WarehouseOperationItemFactory extends Factory
{
    protected $model = WarehouseOperationItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $expectedQuantity = $this->faker->numberBetween(0, 150);
        $status = $this->faker->randomElement(['pending', 'validated', 'discrepancy', 'missing']);

        // If not pending, generate actual quantity and validation data
        $actualQuantity = null;
        $difference = null;
        $isValidated = false;
        $validatedAt = null;
        $validatedBy = null;

        if ($status !== 'pending') {
            $actualQuantity = $this->getActualQuantityForStatus($status, $expectedQuantity);
            $difference = $actualQuantity - $expectedQuantity;
            $isValidated = true;
            $validatedAt = $this->faker->dateTimeBetween('-1 week', 'now');
            $validatedBy = User::factory();
        }

        return [
            'uid' => Str::uuid(),
            'operation_id' => WarehouseInventoryOperation::factory(),
            'slot_id' => WarehouseInventorySlot::factory(),
            'expected_quantity' => $expectedQuantity,
            'actual_quantity' => $actualQuantity,
            'difference' => $difference,
            'status' => $status,
            'is_validated' => $isValidated,
            'validated_at' => $validatedAt,
            'validated_by' => $validatedBy,
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    /**
     * Get actual quantity based on status.
     */
    private function getActualQuantityForStatus(string $status, int $expected): int
    {
        return match ($status) {
            'validated' => $expected, // Match expected
            'missing' => 0, // Product not found
            'discrepancy' => $this->faker->numberBetween(
                max(0, $expected - 20),
                $expected + 20
            ), // Some difference
            default => $expected,
        };
    }

    /**
     * Indicate that the item should be pending validation.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'actual_quantity' => null,
            'difference' => null,
            'is_validated' => false,
            'validated_at' => null,
            'validated_by' => null,
        ]);
    }

    /**
     * Indicate that the item should be validated with no discrepancy.
     */
    public function validated(): static
    {
        return $this->state(function (array $attributes) {
            $expectedQuantity = $attributes['expected_quantity'];

            return [
                'status' => 'validated',
                'actual_quantity' => $expectedQuantity,
                'difference' => 0,
                'is_validated' => true,
                'validated_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
                'validated_by' => User::factory(),
            ];
        });
    }

    /**
     * Indicate that the item should have a discrepancy.
     */
    public function withDiscrepancy(?int $discrepancy = null): static
    {
        return $this->state(function (array $attributes) use ($discrepancy) {
            $expectedQuantity = $attributes['expected_quantity'];
            $actualDiscrepancy = $discrepancy ?? $this->faker->numberBetween(-20, 20);
            $actualQuantity = max(0, $expectedQuantity + $actualDiscrepancy);

            return [
                'status' => 'discrepancy',
                'actual_quantity' => $actualQuantity,
                'difference' => $actualQuantity - $expectedQuantity,
                'is_validated' => true,
                'validated_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
                'validated_by' => User::factory(),
                'notes' => 'Discrepancia encontrada durante el conteo',
            ];
        });
    }

    /**
     * Indicate that the item should be missing.
     */
    public function missing(): static
    {
        return $this->state(function (array $attributes) {
            $expectedQuantity = $attributes['expected_quantity'];

            return [
                'status' => 'missing',
                'actual_quantity' => 0,
                'difference' => -$expectedQuantity,
                'is_validated' => true,
                'validated_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
                'validated_by' => User::factory(),
                'notes' => 'Producto no encontrado en ubicación',
            ];
        });
    }

    /**
     * Indicate that the item should belong to a specific operation.
     */
    public function forOperation(WarehouseInventoryOperation $operation): static
    {
        return $this->state(fn (array $attributes) => [
            'operation_id' => $operation->id,
        ]);
    }

    /**
     * Indicate that the item should be for a specific slot.
     */
    public function forSlot(WarehouseInventorySlot $slot): static
    {
        return $this->state(fn (array $attributes) => [
            'slot_id' => $slot->id,
            'expected_quantity' => $slot->quantity ?? 0,
        ]);
    }

    /**
     * Indicate that the item should be validated by a specific user.
     */
    public function validatedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'validated_by' => $user->id,
            'is_validated' => true,
            'validated_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the item should have a specific expected quantity.
     */
    public function withExpectedQuantity(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'expected_quantity' => $quantity,
        ]);
    }
}
