<?php

namespace Database\Seeders;

use App\Models\Helpdesk\Campaign;
use App\Models\Helpdesk\CampaignImpression;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 5 draft campaigns
        Campaign::factory()->count(5)->draft()->create();

        // Create 3 active campaigns with impressions
        Campaign::factory()->count(3)->active()->create()->each(function ($campaign) {
            // Add random impressions for active campaigns
            $impressionCount = rand(50, 500);
            $devices = ['mobile', 'tablet', 'desktop'];
            $browsers = ['Chrome', 'Firefox', 'Safari', 'Edge'];

            for ($i = 0; $i < $impressionCount; $i++) {
                $viewedAt = fake()->dateTimeBetween('-7 days', 'now');

                CampaignImpression::create([
                    'campaign_id' => $campaign->id,
                    'customer_id' => fake()->optional(0.7)->numberBetween(1, 100),
                    'customer_session_id' => fake()->optional(0.8)->numberBetween(1, 500),
                    'page_url' => fake()->url(),
                    'device_type' => fake()->randomElement($devices),
                    'browser' => fake()->randomElement($browsers),
                    'ip_address' => fake()->ipv4(),
                    'country' => fake()->countryCode(),
                    'viewed_at' => $viewedAt,
                    'clicked_at' => fake()->optional(0.15)->dateTimeBetween($viewedAt, 'now'),
                    'metadata' => [
                        'referrer' => fake()->optional(0.5)->url(),
                        'user_agent' => fake()->userAgent(),
                    ],
                ]);
            }
        });

        // Create 2 scheduled campaigns
        Campaign::factory()->count(2)->scheduled()->create();

        // Create 2 paused campaigns
        Campaign::factory()->count(2)->paused()->create();

        // Create 3 ended campaigns
        Campaign::factory()->count(3)->ended()->create();

        $this->command->info('Created 15 campaigns with various statuses');
    }
}
