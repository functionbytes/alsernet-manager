<?php

namespace Database\Seeders;

use App\Models\Document\DocumentUploadType;
use Illuminate\Database\Seeder;

class DocumentUploadTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $uploadTypes = [
            [
                'key' => 'automatic',
                'label' => 'Automatic',
                'description' => 'Document uploaded automatically via API or system process',
                'icon' => 'fas fa-robot',
                'color' => '#13C672',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'key' => 'manual',
                'label' => 'Manual',
                'description' => 'Document uploaded manually by a user',
                'icon' => 'fas fa-hand-holding-heart',
                'color' => '#90bb13',
                'is_active' => true,
                'order' => 2,
            ],
        ];

        foreach ($uploadTypes as $type) {
            DocumentUploadType::firstOrCreate(
                ['key' => $type['key']],
                $type
            );
        }
    }
}
