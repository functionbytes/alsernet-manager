<?php

namespace Database\Factories\Warehouse;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseInventoryOperation;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Warehouse\Entities\WarehouseInventoryOperation>
 */
class WarehouseInventoryOperationFactory extends Factory
{
    protected $model = WarehouseInventoryOperation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement([
            WarehouseInventoryOperation::STATUS_PENDING,
            WarehouseInventoryOperation::STATUS_IN_PROGRESS,
            WarehouseInventoryOperation::STATUS_COMPLETED,
        ]);

        $startedAt = $this->faker->dateTimeBetween('-1 month', 'now');
        $closedAt = in_array($status, [WarehouseInventoryOperation::STATUS_COMPLETED])
            ? $this->faker->dateTimeBetween($startedAt, 'now')
            : null;

        return [
            'uid' => Str::uuid(),
            'warehouse_id' => Warehouse::factory(),
            'user_id' => User::factory(),
            'started_at' => $startedAt,
            'closed_at' => $closedAt,
            'closed_by' => $closedAt ? User::factory() : null,
            'description' => $this->faker->optional(0.7)->sentence(),
            'status' => $status,
            'total_items' => 0,
            'validated_items' => 0,
            'discrepancy_items' => 0,
        ];
    }

    /**
     * Indicate that the operation should be pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WarehouseInventoryOperation::STATUS_PENDING,
            'started_at' => now(),
            'closed_at' => null,
            'closed_by' => null,
        ]);
    }

    /**
     * Indicate that the operation should be in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WarehouseInventoryOperation::STATUS_IN_PROGRESS,
            'started_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'closed_at' => null,
            'closed_by' => null,
        ]);
    }

    /**
     * Indicate that the operation should be completed.
     */
    public function completed(): static
    {
        $startedAt = $this->faker->dateTimeBetween('-1 month', '-1 day');

        return $this->state(fn (array $attributes) => [
            'status' => WarehouseInventoryOperation::STATUS_COMPLETED,
            'started_at' => $startedAt,
            'closed_at' => $this->faker->dateTimeBetween($startedAt, 'now'),
            'closed_by' => User::factory(),
        ]);
    }

    /**
     * Indicate that the operation should be cancelled.
     */
    public function cancelled(): static
    {
        $startedAt = $this->faker->dateTimeBetween('-1 month', '-1 day');

        return $this->state(fn (array $attributes) => [
            'status' => WarehouseInventoryOperation::STATUS_CANCELLED,
            'started_at' => $startedAt,
            'closed_at' => $this->faker->dateTimeBetween($startedAt, 'now'),
            'closed_by' => User::factory(),
            'description' => $this->faker->sentence()."\n[CANCELADA: ".$this->faker->sentence().']',
        ]);
    }

    /**
     * Indicate that the operation should have specific statistics.
     */
    public function withStats(int $total, int $validated, int $discrepancy): static
    {
        return $this->state(fn (array $attributes) => [
            'total_items' => $total,
            'validated_items' => $validated,
            'discrepancy_items' => $discrepancy,
        ]);
    }

    /**
     * Indicate that the operation should belong to a specific warehouse.
     */
    public function forWarehouse(Warehouse $warehouse): static
    {
        return $this->state(fn (array $attributes) => [
            'warehouse_id' => $warehouse->id,
        ]);
    }

    /**
     * Indicate that the operation should be started by a specific user.
     */
    public function startedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
