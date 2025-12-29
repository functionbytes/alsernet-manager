<?php

namespace Modules\Helpdesk\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Helpdesk\Models\Category;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Technical Support', 'slug' => 'technical-support', 'description' => 'Technical issues and troubleshooting', 'color' => '#0D6EFD', 'icon' => 'fas fa-cog'],
            ['name' => 'Billing', 'slug' => 'billing', 'description' => 'Billing and payment issues', 'color' => '#198754', 'icon' => 'fas fa-credit-card'],
            ['name' => 'General Inquiry', 'slug' => 'general-inquiry', 'description' => 'General questions and inquiries', 'color' => '#0DCAF0', 'icon' => 'fas fa-question-circle'],
        ];

        foreach ($categories as $data) {
            Category::create([
                'uid' => \Illuminate\Support\Str::uuid(),
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'color' => $data['color'],
                'icon' => $data['icon'],
                'order' => 0,
                'is_active' => true,
            ]);
        }
    }
}
