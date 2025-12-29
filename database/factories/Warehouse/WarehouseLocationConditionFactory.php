<?php

namespace Database\Factories\Warehouse;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Warehouse\Entities\WarehouseLocationCondition;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Warehouse\Entities\WarehouseLocationCondition>
 */
class WarehouseLocationConditionFactory extends Factory
{
    protected $model = WarehouseLocationCondition::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $conditions = [
            ['title' => 'Excellent', 'slug' => 'excellent', 'color' => '#28A745', 'badge' => 'badge-success'],
            ['title' => 'Good', 'slug' => 'good', 'color' => '#17A2B8', 'badge' => 'badge-info'],
            ['title' => 'Fair', 'slug' => 'fair', 'color' => '#FFC107', 'badge' => 'badge-warning'],
            ['title' => 'Poor', 'slug' => 'poor', 'color' => '#FD7E14', 'badge' => 'badge-warning'],
            ['title' => 'Damaged', 'slug' => 'damaged', 'color' => '#DC3545', 'badge' => 'badge-danger'],
            ['title' => 'Under Maintenance', 'slug' => 'under-maintenance', 'color' => '#6C757D', 'badge' => 'badge-secondary'],
        ];

        $condition = $this->faker->randomElement($conditions);

        return [
            'uid' => Str::uuid(),
            'title' => $condition['title'],
            'slug' => $condition['slug'],
            'description' => $this->faker->optional(0.7)->sentence(),
            'color' => $condition['color'],
            'badge_class' => $condition['badge'],
            'available' => true,
        ];
    }

    /**
     * Indicate that the condition should be archived/unavailable.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'available' => false,
        ]);
    }

    /**
     * Indicate that the condition is "Excellent".
     */
    public function excellent(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Excellent',
            'slug' => 'excellent',
            'color' => '#28A745',
            'badge_class' => 'badge-success',
        ]);
    }

    /**
     * Indicate that the condition is "Damaged".
     */
    public function damaged(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Damaged',
            'slug' => 'damaged',
            'color' => '#DC3545',
            'badge_class' => 'badge-danger',
        ]);
    }

    /**
     * Indicate that the condition is "Under Maintenance".
     */
    public function underMaintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Under Maintenance',
            'slug' => 'under-maintenance',
            'color' => '#6C757D',
            'badge_class' => 'badge-secondary',
            'available' => false,
        ]);
    }
}
