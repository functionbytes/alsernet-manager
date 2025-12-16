<?php

namespace Database\Factories;

use App\Models\Helpdesk\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Helpdesk\Campaign>
 */
class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['popup', 'banner', 'slide-in', 'full-screen'];
        $statuses = ['draft', 'scheduled', 'active', 'ended', 'paused'];

        return [
            'name' => fake()->words(3, true).' - Campaña',
            'description' => fake()->sentence(12),
            'type' => fake()->randomElement($types),
            'status' => fake()->randomElement($statuses),
            'content' => [
                [
                    'type' => 'heading',
                    'text' => fake()->sentence(4),
                ],
                [
                    'type' => 'paragraph',
                    'text' => fake()->sentence(10),
                ],
                [
                    'type' => 'button',
                    'text' => fake()->randomElement(['Ver más', 'Obtener descuento', 'Saber más', 'Comenzar']),
                    'url' => fake()->url(),
                ],
            ],
            'appearance' => [
                'background_color' => fake()->hexColor(),
                'text_color' => '#333333',
                'primary_color' => '#90bb13',
                'border_radius' => '8px',
                'padding' => '24px',
            ],
            'conditions' => [
                [
                    'field' => 'page_url',
                    'operator' => 'contains',
                    'value' => '/productos',
                ],
            ],
            'metadata' => [
                'created_by' => 'System',
                'version' => '1.0',
            ],
            'published_at' => fake()->optional(0.6)->dateTimeBetween('-30 days', 'now'),
            'ends_at' => fake()->optional(0.3)->dateTimeBetween('now', '+30 days'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the campaign is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
            'ends_at' => null,
        ]);
    }

    /**
     * Indicate that the campaign is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'published_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the campaign is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'published_at' => fake()->dateTimeBetween('now', '+7 days'),
        ]);
    }

    /**
     * Indicate that the campaign is paused.
     */
    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paused',
            'published_at' => fake()->dateTimeBetween('-14 days', '-1 day'),
        ]);
    }

    /**
     * Indicate that the campaign has ended.
     */
    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ended',
            'published_at' => fake()->dateTimeBetween('-30 days', '-15 days'),
            'ends_at' => fake()->dateTimeBetween('-14 days', 'now'),
        ]);
    }
}
